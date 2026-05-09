<?php

// ══════════════════════════════════════════════════════════
//  DocumentCategoryController
//  Path: app/Http/Controllers/DocumentCategoryController.php
// ══════════════════════════════════════════════════════════

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = DocumentCategory::withCount('documents')->latest()->paginate(15);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'prefix'      => 'required|string|max:10|unique:document_categories,prefix',
            'description' => 'nullable|string|max:500',
        ]);

        $cat = DocumentCategory::create($request->only('name', 'prefix', 'description'));

        AuditLog::record('create', 'categories',
            "Tambah kategori: {$cat->name} (prefix: {$cat->prefix})",
            $cat->id, DocumentCategory::class
        );

        return redirect()->route('categories.index')
            ->with('success', "Kategori {$cat->name} berhasil ditambahkan.");
    }

    public function edit(DocumentCategory $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, DocumentCategory $category)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'prefix'      => "required|string|max:10|unique:document_categories,prefix,{$category->id}",
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $category->update($request->only('name', 'prefix', 'description', 'is_active'));

        AuditLog::record('update', 'categories',
            "Update kategori: {$category->name}",
            $category->id, DocumentCategory::class
        );

        return redirect()->route('categories.index')
            ->with('success', "Kategori berhasil diperbarui.");
    }

    public function destroy(DocumentCategory $category)
    {
        abort_if($category->documents()->exists(), 403,
            'Kategori masih memiliki dokumen, tidak bisa dihapus.'
        );

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Kategori berhasil dihapus.");
    }

    public function show(DocumentCategory $category)
    {
        return view('categories.show', compact('category'));
    }
}