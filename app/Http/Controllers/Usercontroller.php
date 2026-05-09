<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['department', 'roles']);

        if ($request->filled('role'))       $query->role($request->role);
        if ($request->filled('department')) $query->where('department_id', $request->department);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2
                ->where('name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
            );
        }

        $users       = $query->latest()->paginate(15)->withQueryString();
        $roles       = Role::all();
        $departments = Department::where('is_active', true)->get();

        return view('users.index', compact('users', 'roles', 'departments'));
    }

    public function create()
    {
        $roles       = Role::all();
        $departments = Department::where('is_active', true)->get();
        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8|confirmed',
            'role'          => 'required|exists:roles,name',
            'department_id' => 'nullable|exists:departments,id',
            'phone'         => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'department_id' => $request->department_id,
            'phone'         => $request->phone,
            'is_active'     => true,
        ]);

        $user->assignRole($request->role);

        AuditLog::record('create', 'users',
            "Tambah user baru: {$user->name} ({$user->email}) sebagai {$request->role}",
            $user->id, User::class
        );

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function show(User $user)
    {
        $user->load(['department', 'roles', 'documents']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles       = Role::all();
        $departments = Department::where('is_active', true)->get();
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,{$user->id}",
            'role'          => 'required|exists:roles,name',
            'department_id' => 'nullable|exists:departments,id',
            'phone'         => 'nullable|string|max:20',
        ]);

        $user->update([
            'name'          => $request->name,
            'email'         => $request->email,
            'department_id' => $request->department_id,
            'phone'         => $request->phone,
        ]);

        $user->syncRoles([$request->role]);

        AuditLog::record('update', 'users',
            "Update user: {$user->name}",
            $user->id, User::class
        );

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');

        AuditLog::record('delete', 'users',
            "Hapus user: {$user->name} ({$user->email})",
            $user->id, User::class
        );

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User berhasil dihapus.");
    }

    public function toggleStatus(User $user)
    {
        abort_if($user->id === auth()->id(), 403);

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::record('toggle_status', 'users',
            "User {$user->name} {$status}",
            $user->id, User::class
        );

        return back()->with('success', "User berhasil {$status}.");
    }

    public function resetPassword(User $user)
    {
        $newPassword = 'password';
        $user->update(['password' => Hash::make($newPassword)]);

        AuditLog::record('reset_password', 'users',
            "Reset password user: {$user->name}",
            $user->id, User::class
        );

        return back()->with('success', "Password user direset ke 'password'.");
    }
}