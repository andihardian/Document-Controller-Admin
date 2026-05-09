<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('documents', 'users')->latest()->paginate(15);
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string|max:500',
        ]);

        $dept = Department::create($request->only('name', 'code', 'description'));

        AuditLog::record('create', 'departments',
            "Tambah departemen: {$dept->name} ({$dept->code})",
            $dept->id, Department::class
        );

        return redirect()->route('departments.index')
            ->with('success', "Departemen {$dept->name} berhasil ditambahkan.");
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => "required|string|max:10|unique:departments,code,{$department->id}",
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $department->update($request->only('name', 'code', 'description', 'is_active'));

        AuditLog::record('update', 'departments',
            "Update departemen: {$department->name}",
            $department->id, Department::class
        );

        return redirect()->route('departments.index')
            ->with('success', "Departemen berhasil diperbarui.");
    }

    public function destroy(Department $department)
    {
        abort_if($department->users()->exists(), 403,
            'Departemen masih memiliki user aktif, tidak bisa dihapus.'
        );

        AuditLog::record('delete', 'departments',
            "Hapus departemen: {$department->name}",
            $department->id, Department::class
        );

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', "Departemen berhasil dihapus.");
    }

    public function show(Department $department)
    {
        $department->load(['users.roles', 'documents']);
        return view('departments.show', compact('department'));
    }
}