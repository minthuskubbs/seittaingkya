<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:audit.view');
    }

    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('audit.index', compact('logs'));
    }
}
