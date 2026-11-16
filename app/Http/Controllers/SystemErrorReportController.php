<?php

namespace App\Http\Controllers;

use App\Models\SystemError;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SystemErrorReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === User::ROLE_LGU, 403);
        \App\Support\SecurityLogRetention::purgeIfDue();
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'print' => ['nullable', 'boolean'],
        ]);
        $from = $filters['from'] ?? now('Asia/Manila')->subDays(29)->toDateString();
        $to = $filters['to'] ?? now('Asia/Manila')->toDateString();
        $request->merge(['from' => $from, 'to' => $to])->validate(['to' => ['after_or_equal:from']]);
        $query = SystemError::query()->where('created_at', '>=', now()->subDays(30))->whereBetween('created_at', [
            Carbon::parse($from, 'Asia/Manila')->startOfDay()->timezone(config('app.timezone')),
            Carbon::parse($to, 'Asia/Manila')->endOfDay()->timezone(config('app.timezone')),
        ]);
        $total = (clone $query)->count();
        $categories = (clone $query)->selectRaw('category, COUNT(*) as total')->groupBy('category')->orderByDesc('total')->orderBy('category')->get();
        $affectedUsers = (clone $query)->whereNotNull('user_id')->distinct()->count('user_id');
        $topRoute = (clone $query)->whereNotNull('route_name')->selectRaw('route_name, COUNT(*) as total')->groupBy('route_name')->orderByDesc('total')->orderBy('route_name')->first();
        $printReport = $request->boolean('print');
        $incidents = $printReport ? (clone $query)->orderByDesc('id')->get()
            : (clone $query)->orderByDesc('id')->paginate(25)->withQueryString();
        return response()->view('lgu.reports', compact('from', 'to', 'total', 'categories', 'affectedUsers', 'topRoute', 'incidents', 'printReport'))
            ->header('Cache-Control', 'no-store, private');
    }
}
