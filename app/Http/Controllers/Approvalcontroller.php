<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $query = Approval::with([
            'documentVersion.document.category',
            'documentVersion.document.department',
            'documentVersion.uploader',
            'approver',
        ])->where('status', 'pending');

        // Department head hanya lihat approval dari departemennya
        if ($user->hasRole('department_head')) {
            $query->where('approver_id', $user->id);
        }

        $pendingApprovals = $query->latest()->paginate(15);

        return view('approvals.index', compact('pendingApprovals'));
    }

    public function show(DocumentVersion $documentVersion)
    {
        $documentVersion->load([
            'document.category',
            'document.department',
            'document.creator',
            'uploader',
            'approvals.approver',
        ]);

        return view('approvals.show', compact('documentVersion'));
    }

    public function approve(Request $request, DocumentVersion $documentVersion)
    {
        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $documentVersion) {
            $user     = auth()->user();
            $document = $documentVersion->document;

            // Update record approval
            Approval::where('document_version_id', $documentVersion->id)
                ->where('approver_id', $user->id)
                ->update([
                    'status'    => 'approved',
                    'comments'  => $request->comments,
                    'action_at' => now(),
                ]);

            // Update versi dokumen
            $documentVersion->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Update status dokumen utama
            $document->update(['status' => Document::STATUS_APPROVED]);

            AuditLog::record('approve', 'approvals',
                "Approve dokumen: {$document->document_number} v{$documentVersion->version_number}",
                $document->id, Document::class
            );
        });

        return redirect()->route('approvals.index')
            ->with('success', 'Dokumen berhasil disetujui.');
    }

    public function reject(Request $request, DocumentVersion $documentVersion)
    {
        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $documentVersion) {
            $user     = auth()->user();
            $document = $documentVersion->document;

            Approval::where('document_version_id', $documentVersion->id)
                ->where('approver_id', $user->id)
                ->update([
                    'status'    => 'rejected',
                    'comments'  => $request->comments,
                    'action_at' => now(),
                ]);

            $documentVersion->update(['status' => 'rejected']);
            $document->update(['status' => Document::STATUS_REJECTED]);

            AuditLog::record('reject', 'approvals',
                "Reject dokumen: {$document->document_number} v{$documentVersion->version_number}. Alasan: {$request->comments}",
                $document->id, Document::class
            );
        });

        return redirect()->route('approvals.index')
            ->with('success', 'Dokumen berhasil ditolak.');
    }
}