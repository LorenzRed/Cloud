<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Logs - Bantay Kalusugan</title>
    @include('partials.favicon')
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <script>tailwind.config = {darkMode: 'class', theme: {extend: {colors: {primary: '#4A90E2', 'card-light': '#ffffff', 'card-dark': '#1f2937', 'border-light': '#e5e7eb', 'border-dark': '#374151', 'text-light': '#111827', 'text-dark': '#f3f4f6'}}}};</script>
    @include('partials.lgu-responsive-styles')
</head>
<body class="lgu-layout bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
<div class="flex min-h-screen">
    <div class="hidden lg:block">@include('sidebars.lgu')</div>
    <main class="min-w-0 flex-1 p-4 md:p-8">
        <a href="{{ route('lgu.main') }}" class="lgu-back-link text-primary md:hidden">Back to dashboard</a>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-2xl font-bold">System Logs</h1><p class="mt-2 text-sm text-gray-500">Latest activity across all roles, including page visits, account actions, updates, deletions and restores. Times are in Philippine time.</p></div>
            <button type="button" id="refreshLogs" class="rounded-lg border bg-white px-4 py-2 text-sm font-semibold text-gray-900">Refresh</button>
        </div>
        <p class="mt-3 text-xs text-gray-500" id="logRefreshStatus" role="status">Updates automatically every 10 seconds.</p>
        <form method="GET" action="{{ route('lgu.system-logs') }}" class="mt-6 flex flex-wrap items-end gap-3">
            <label class="flex-1 min-w-48 text-sm">Search activity or person<input type="search" name="search" maxlength="100" value="{{ $filters['search'] ?? '' }}" placeholder="Name, role or action" class="mt-1 block w-full rounded-lg border-gray-300 text-gray-900"></label>
            <label class="text-sm">Result<select name="outcome" class="mt-1 block rounded-lg border-gray-300 text-gray-900"><option value="">All results</option>@foreach (['completed', 'rejected', 'error'] as $outcome)<option value="{{ $outcome }}" @selected(($filters['outcome'] ?? '') === $outcome)>{{ ucfirst($outcome) }}</option>@endforeach</select></label>
            <button class="rounded-lg bg-primary px-4 py-2 font-semibold text-white" type="submit">Filter</button>
            <a class="px-2 py-2 text-sm text-primary" href="{{ route('lgu.system-logs') }}">Clear</a>
        </form>
        @if ($errors->any())<p role="alert" class="mt-3 text-red-700">{{ $errors->first() }}</p>@endif
        <div class="mt-5 overflow-x-auto rounded-xl border bg-white dark:bg-gray-800">
            <table class="lgu-mobile-table w-full text-left text-sm"><thead class="bg-gray-100 dark:bg-gray-700"><tr><th class="p-4">When</th><th class="p-4">Who</th><th class="p-4">Activity</th><th class="p-4">Record</th><th class="p-4">Result</th></tr></thead>
                <tbody id="systemLogRows">@include('lgu.partials.system-log-rows')</tbody>
            </table>
        </div>
        <div class="mt-4" id="systemLogPagination">{{ $logs->links() }}</div>
    </main>
</div>
<script>
    let refreshingLogs = false;
    async function refreshLogs() {
        if (document.hidden || refreshingLogs) return;
        refreshingLogs = true;
        const status = document.getElementById('logRefreshStatus');
        try {
            const response = await fetch(window.location.href, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
            if (!response.ok) throw new Error('Unable to refresh');
            const data = await response.json();
            document.getElementById('systemLogRows').innerHTML = data.html;
            document.getElementById('systemLogPagination').innerHTML = data.pagination;
            status.textContent = `Updated ${data.checked_at} · Checks every 10 seconds.`;
        } catch (_) {
            status.textContent = 'Could not refresh. Your current logs are still shown. Retrying automatically.';
        } finally {
            refreshingLogs = false;
        }
    }
    document.getElementById('refreshLogs').addEventListener('click', refreshLogs);
    window.addEventListener('focus', refreshLogs);
    document.addEventListener('visibilitychange', refreshLogs);
    window.setInterval(refreshLogs, 10000);
</script>
@include('partials.lgu-mobile-nav')
</body>
</html>
