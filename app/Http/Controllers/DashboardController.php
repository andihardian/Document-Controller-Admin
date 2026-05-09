<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Department;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return view('dashboard.admin', $this->adminStats());
        }

        if ($user->hasRole('department_head')) {
            return view('dashboard.department_head', $this->headStats($user));
        }

        return view('dashboard.employee', $this->employeeStats($user));
    }

    // ─────────────────────────────────────────────────────
    private function adminStats(): array
    {
        return [
            'totalUsers'       => User::count(),
            'totalDepartments' => Department::count(),
            'totalDocuments'   => Document::count(),
            'pendingApprovals' => Document::where('status', 'pending_approval')->count(),
            'approvedDocs'     => Document::where('status', 'approved')->count(),
            'rejectedDocs'     => Document::where('status', 'rejected')->count(),
            'expiredDocs'      => Document::approved()->expired()->count(),
            'recentLogs'       => AuditLog::with('user')
                                    ->latest('created_at')
                                    ->take(10)
                                    ->get(),
            'docsByDepartment' => Department::withCount('documents')->get(),
        ];
    }

    private function headStats($user): array
    {
        $deptId = $user->department_id;

        return [
            'totalDocs'   => Document::forDepartment($deptId)->count(),
            'draftDocs'   => Document::forDepartment($deptId)->where('status', 'draft')->count(),
            'pendingDocs' => Document::forDepartment($deptId)->where('status', 'pending_approval')->count(),
            'approvedDocs'=> Document::forDepartment($deptId)->where('status', 'approved')->count(),
            'rejectedDocs'=> Document::forDepartment($deptId)->where('status', 'rejected')->count(),
            'pendingList' => Document::forDepartment($deptId)
                                ->where('status', 'pending_approval')
                                ->with(['category', 'creator'])
                                ->latest()
                                ->take(5)
                                ->get(),
        ];
    }

    private function employeeStats($user): array
    {
        return [
            'myDocs'      => Document::where('created_by', $user->id)->count(),
            'draftDocs'   => Document::where('created_by', $user->id)->where('status', 'draft')->count(),
            'pendingDocs' => Document::where('created_by', $user->id)->where('status', 'pending_approval')->count(),
            'approvedDocs'=> Document::where('created_by', $user->id)->where('status', 'approved')->count(),
            'rejectedDocs'=> Document::where('created_by', $user->id)->where('status', 'rejected')->count(),
            'recentDocs'  => Document::where('created_by', $user->id)
                                ->with(['category', 'department'])
                                ->latest()
                                ->take(5)
                                ->get(),
        ];
    }
}