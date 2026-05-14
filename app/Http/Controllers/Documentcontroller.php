<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentEmbedding;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentVersion;
use App\Models\Department;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Document::with(['category', 'department', 'creator', 'currentVersion']);

        if ($user->hasRole('employee')) {
            $query->where('created_by', $user->id);
        }

        if ($user->hasRole('department_head')) {
            $query->forDepartment($user->department_id);
        }

        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('department')) $query->where('department_id', $request->department);
        if ($request->filled('category'))   $query->where('category_id', $request->category);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2
                ->where('title', 'like', "%$q%")
                ->orWhere('document_number', 'like', "%$q%")
            );
        }

        $documents   = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::where('is_active', true)->get();
        $categories  = DocumentCategory::where('is_active', true)->get();

        return view('documents.index', compact('documents', 'departments', 'categories'));
    }

    public function create()
    {
        $categories  = DocumentCategory::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        return view('documents.create', compact('categories', 'departments'));
    }

    public function store(StoreDocumentRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user       = auth()->user();
            $category   = DocumentCategory::findOrFail($request->category_id);
            $department = Department::findOrFail($request->department_id);

            $docNumber = $category->generateDocumentNumber($department);

            $file     = $request->file('document_file');
            $fileName = 'v1.0.pdf';
            $filePath = $file->storeAs(
                "documents/{$department->code}/{$docNumber}",
                $fileName,
                'public'
            );

            $document = Document::create([
                'document_number' => $docNumber,
                'title'           => $request->title,
                'description'     => $request->description,
                'category_id'     => $request->category_id,
                'department_id'   => $request->department_id,
                'created_by'      => $user->id,
                'current_version' => '1.0',
                'status'          => Document::STATUS_DRAFT,
                'effective_date'  => $request->effective_date,
                'expiry_date'     => $request->expiry_date,
                'allow_ai_access' => true, // default aktifkan AI access
            ]);

            $version = DocumentVersion::create([
                'document_id'      => $document->id,
                'version_number'   => '1.0',
                'revision_note'    => $request->revision_note ?? 'Versi awal',
                'file_path'        => $filePath,
                'file_size'        => $file->getSize(),
                'uploaded_by'      => $user->id,
                'status'           => 'draft',
                'is_current'       => true,
                'embedding_status' => 'pending',
            ]);

            // Dispatch job untuk parsing + embedding PDF
            ProcessDocumentEmbedding::dispatch($version->id)
                ->onQueue(config('ai.queue', 'default'));

            AuditLog::record('upload', 'documents',
                "Upload dokumen baru: {$docNumber} - {$request->title}",
                $document->id, Document::class
            );
        });

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil diunggah dan sedang diproses untuk AI.');
    }

    public function show(Document $document)
    {
        $this->authorizeDocumentAccess($document);

        $document->load([
            'category', 'department', 'creator',
            'versions.uploader', 'versions.approver',
            'versions.approvals.approver',
        ]);

        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $this->authorizeDocumentAccess($document);

        abort_if(
            !in_array($document->status, [Document::STATUS_DRAFT, Document::STATUS_REJECTED]),
            403,
            'Dokumen yang sudah disubmit tidak dapat diedit.'
        );

        $categories  = DocumentCategory::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('documents.edit', compact('document', 'categories', 'departments'));
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $this->authorizeDocumentAccess($document);

        $document->update($request->only([
            'title', 'description', 'effective_date', 'expiry_date',
        ]));

        AuditLog::record('edit', 'documents',
            "Edit metadata dokumen: {$document->document_number}",
            $document->id, Document::class
        );

        return redirect()->route('documents.show', $document)
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function submit(Document $document)
    {
        $this->authorizeDocumentAccess($document);

        abort_if(
            !in_array($document->status, [Document::STATUS_DRAFT, Document::STATUS_REJECTED]),
            403
        );

        DB::transaction(function () use ($document) {
            $document->update(['status' => Document::STATUS_PENDING_APPROVAL]);

            $version = $document->currentVersion;
            $version->update(['status' => 'pending_approval']);

            $headUser = \App\Models\User::role('department_head')
                ->where('department_id', $document->department_id)
                ->first();

            if ($headUser) {
                \App\Models\Approval::create([
                    'document_version_id' => $version->id,
                    'approver_id'         => $headUser->id,
                    'status'              => 'pending',
                ]);
            }

            AuditLog::record('submit', 'documents',
                "Submit dokumen untuk approval: {$document->document_number}",
                $document->id, Document::class
            );
        });

        return redirect()->route('documents.show', $document)
            ->with('success', 'Dokumen berhasil disubmit untuk approval.');
    }

    public function revise(Request $request, Document $document)
    {
        $this->authorizeDocumentAccess($document);

        abort_if($document->status !== Document::STATUS_REJECTED, 403,
            'Hanya dokumen yang ditolak yang bisa direvisi.'
        );

        $request->validate([
            'document_file' => 'required|file|mimes:pdf|max:10240',
            'revision_note' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $document) {
            $user       = auth()->user();
            $newVersion = DocumentVersion::nextVersionNumber($document->id);
            $department = $document->department;
            $fileName   = "v{$newVersion}.pdf";
            $filePath   = $request->file('document_file')->storeAs(
                "documents/{$department->code}/{$document->document_number}",
                $fileName, 'public'
            );

            $document->versions()->update(['is_current' => false]);

            $version = DocumentVersion::create([
                'document_id'      => $document->id,
                'version_number'   => $newVersion,
                'revision_note'    => $request->revision_note,
                'file_path'        => $filePath,
                'file_size'        => $request->file('document_file')->getSize(),
                'uploaded_by'      => $user->id,
                'status'           => 'draft',
                'is_current'       => true,
                'embedding_status' => 'pending',
            ]);

            // Dispatch embedding untuk versi baru
            ProcessDocumentEmbedding::dispatch($version->id)
                ->onQueue(config('ai.queue', 'default'));

            $document->update([
                'current_version' => $newVersion,
                'status'          => Document::STATUS_DRAFT,
            ]);

            AuditLog::record('revise', 'documents',
                "Upload revisi dokumen: {$document->document_number} v{$newVersion}",
                $document->id, Document::class
            );
        });

        return redirect()->route('documents.show', $document)
            ->with('success', 'Revisi berhasil diunggah dan sedang diproses untuk AI.');
    }

    public function download(Document $document, DocumentVersion $version = null)
    {
        $this->authorizeDocumentAccess($document);

        $version ??= $document->currentVersion;
        abort_if(!$version, 404);

        AuditLog::record('download', 'documents',
            "Download: {$document->document_number} v{$version->version_number}",
            $document->id, Document::class
        );

        return Storage::disk('public')->download(
            $version->file_path,
            "{$document->document_number}_v{$version->version_number}.pdf"
        );
    }

    public function restore(int $id)
    {
        $document = Document::withTrashed()->findOrFail($id);
        $document->restore();

        AuditLog::record('restore', 'documents',
            "Restore dokumen: {$document->document_number}",
            $document->id, Document::class
        );

        return back()->with('success', 'Dokumen berhasil di-restore.');
    }

    public function forceDelete(Document $document)
    {
        AuditLog::record('delete', 'documents',
            "Hapus permanen: {$document->document_number}",
            $document->id, Document::class
        );

        $document->forceDelete();

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dihapus permanen.');
    }

    private function authorizeDocumentAccess(Document $document): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) return;

        if ($user->hasRole('department_head')) {
            abort_if($document->department_id !== $user->department_id, 403);
            return;
        }

        if ($user->hasRole('employee')) {
            abort_if($document->created_by !== $user->id, 403);
            return;
        }

        if ($user->hasRole('viewer')) {
            abort_if($document->status !== Document::STATUS_APPROVED, 403);
        }
    }
}