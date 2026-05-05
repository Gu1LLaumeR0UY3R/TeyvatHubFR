<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Liste paginée des logs avec filtres.
     * Accès réservé au super_admin.
     */
    public function index(Request $request): View
    {
        $admin = Admin::find(session('admin_id'));
        abort_unless($admin && in_array($admin->role, ['super_admin', 'superadmin']), 403);

        $query = ActivityLog::recent();

        if ($request->filled('level')) {
            $query->level($request->level);
        }

        if ($request->filled('action')) {
            $query->where('action', 'LIKE', '%' . $request->action . '%');
        }

        if ($request->filled('user_type')) {
            $query->byUserType($request->user_type);
        }

        if ($request->filled('user_label')) {
            $query->where('user_label', 'LIKE', '%' . $request->user_label . '%');
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', $request->ip_filter);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->paginate(50)->withQueryString();

        $levels  = ['debug', 'info', 'notice', 'warning', 'error', 'critical'];
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.logs.index', compact('logs', 'levels', 'actions'));
    }
}
