<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('module'))  $query->where('module', $request->module);
        if ($request->filled('action'))  $query->where('action', $request->action);
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs    = $query->paginate(25)->withQueryString();
        $users   = User::select('id', 'name')->get();
        $modules = AuditLog::distinct()->pluck('module');
        $actions = AuditLog::distinct()->pluck('action');

        return view('audit-logs.index', compact('logs', 'users', 'modules', 'actions'));
    }
}