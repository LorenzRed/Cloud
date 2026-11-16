<?php

namespace App\Http\Controllers;

use App\Models\SystemActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === User::ROLE_LGU, 403);
        \App\Support\SecurityLogRetention::purgeIfDue();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'outcome' => ['nullable', 'in:completed,rejected,error'],
        ]);
        $logs = SystemActivityLog::query()
            ->whereIn('event_type', ['login_success', 'login_failed', 'system_error'])
            ->where('created_at', '>=', now()->subDays(30))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(fn ($q) => $q->where('actor_name', 'like', '%'.$search.'%')->orWhere('ip_address', 'like', '%'.$search.'%')
                    ->orWhere('actor_role', 'like', '%'.$search.'%')->orWhere('action', 'like', '%'.$search.'%'));
            })
            ->when($filters['outcome'] ?? null, fn ($query, $outcome) => $query->where('outcome', $outcome))
            ->orderByDesc('id')->paginate(30)->withQueryString();
        if ($request->expectsJson()) {
            return response()->json(['html' => view('lgu.partials.system-log-rows', compact('logs'))->render(),
                'pagination' => $logs->links()->toHtml(), 'checked_at' => now()->timezone('Asia/Manila')->format('h:i:s A')])
                ->header('Cache-Control', 'no-store, private');
        }
        return response()->view('lgu.system-logs', compact('logs', 'filters'))->header('Cache-Control', 'no-store, private');
    }
}
