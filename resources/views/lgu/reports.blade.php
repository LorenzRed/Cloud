<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Error Report - Bantay Kalusugan</title>
    @include('partials.favicon')
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Material+Symbols+Outlined" rel="stylesheet">
    <script>tailwind.config = {darkMode: 'class', theme: {extend: {colors: {primary: '#4A90E2', 'card-light': '#ffffff', 'card-dark': '#1f2937', 'border-light': '#e5e7eb', 'border-dark': '#374151', 'text-light': '#111827', 'text-dark': '#f3f4f6'}}}};</script>
    <style>
        :root {color-scheme:light; --surface:#fff; --ink:#172b3a; --muted:#71808e; --line:#e5ebef; --subtle:#f7f9fb; --blue:#287dbb;}
        body {margin:0;background:#f4f7fa;color:var(--ink);font-family:Manrope,sans-serif;font-size:14px;}
        .material-symbols-outlined {font-size:21px;vertical-align:middle;font-weight:400;}
        .report-shell {display:flex;min-height:100dvh;}
        .report-sidebar {flex:0 0 256px;}
        .report-sidebar #sidebar {position:sticky;top:0;height:100dvh;overflow-y:auto;}
        .report-main {min-width:0;flex:1;padding:32px clamp(20px,3vw,48px) 40px;}
        .report-content {max-width:1440px;margin:0 auto;}
        .report-eyebrow {font-size:11px;letter-spacing:.12em;text-transform:uppercase;font-weight:800;color:var(--muted);}
        .report-header {display:flex;justify-content:space-between;align-items:center;gap:20px;margin:12px 0 28px;}
        .report-title {font-size:clamp(24px,2.4vw,32px);line-height:1.25;letter-spacing:-.04em;font-weight:800;margin:0 0 8px;}
        .report-subtitle {color:var(--muted);font-size:13px;line-height:1.7;max-width:600px;}
        .report-button {display:inline-flex;justify-content:center;align-items:center;gap:8px;min-height:44px;padding:10px 17px;border-radius:10px;border:1px solid var(--line);background:var(--surface);color:var(--ink);font-size:12px;font-weight:800;text-decoration:none;white-space:nowrap;cursor:pointer;}
        .report-button:hover {background:var(--subtle);border-color:#bbcbd6;}
        .report-button:focus-visible, .report-filter input:focus-visible, details summary:focus-visible {outline:3px solid #93c5fd;outline-offset:3px;}
        .report-button.primary {background:var(--blue);border-color:var(--blue);color:#fff;}
        .report-button.primary:hover {background:#21699f;}
        .report-panel {background:var(--surface);border:1px solid var(--line);border-radius:16px;box-shadow:0 2px 5px #153e5803;}
        .report-filter {display:flex;align-items:center;gap:24px;padding:20px 24px;}
        .report-filter-heading {min-width:140px;}
        .report-section-title {font-size:14px;font-weight:800;letter-spacing:-.02em;}
        .report-caption {font-size:11px;color:var(--muted);line-height:1.7;}
        .report-filter-fields {display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;flex:1;}
        .report-filter label {display:block;font-size:11px;font-weight:700;color:var(--muted);}
        .report-filter input {display:block;width:100%;min-width:0;height:44px;margin-top:6px;padding:9px 12px;border:1px solid var(--line);border-radius:9px;background:var(--subtle);color:var(--ink);font:600 12px Manrope,sans-serif;}
        .report-meta {display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin:14px 2px 24px;font-size:11px;color:var(--muted);}
        .report-metrics {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:22px;}
        .report-metric {padding:22px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
        .report-metric-label {font-size:12px;font-weight:600;color:var(--muted);}
        .report-metric-value {font-size:34px;line-height:1.2;letter-spacing:-.05em;font-weight:800;margin:10px 0 7px;}
        .report-metric-icon {display:grid;place-items:center;width:42px;height:42px;border-radius:12px;flex-shrink:0;}
        .report-overview {display:grid;grid-template-columns:minmax(0,1.6fr) minmax(240px,1fr);gap:20px;margin-bottom:26px;}
        .report-summary {padding:24px;}
        .report-summary-heading {display:flex;align-items:center;gap:10px;margin-bottom:16px;}
        .report-summary-copy {font-size:13px;line-height:1.9;color:#526577;}
        .report-priority {margin-top:18px;padding:14px 16px;border-left:3px solid #7fb8df;background:var(--subtle);border-radius:0 8px 8px 0;overflow-wrap:anywhere;}
        .report-category {margin-top:18px;}
        .report-category-label {display:flex;justify-content:space-between;gap:10px;font-size:12px;margin-bottom:7px;}
        .report-category-track {height:6px;border-radius:99px;background:var(--subtle);overflow:hidden;}
        .report-category-fill {height:100%;background:#6fabcF;border-radius:99px;}
        .report-list-header {display:flex;align-items:center;justify-content:space-between;gap:16px;padding:22px 24px;border-bottom:1px solid var(--line);}
        .report-count {background:var(--subtle);border:1px solid var(--line);padding:5px 10px;border-radius:99px;font-size:11px;font-weight:700;color:var(--muted);white-space:nowrap;}
        .report-table {overflow-x:auto;}
        .report-table table {width:100%;table-layout:fixed;border-collapse:collapse;text-align:left;}
        .report-table th {padding:13px 20px;background:var(--subtle);font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);font-weight:800;}
        .report-table td {padding:20px;vertical-align:top;border-top:1px solid var(--line);font-size:12px;line-height:1.7;overflow-wrap:anywhere;}
        .report-table tbody tr:first-child td {border-top:0;}
        .report-table tbody tr:hover {background:#f8fafc;}
        .report-badge {display:inline-flex;align-items:center;gap:5px;border-radius:6px;padding:3px 8px;background:#fff1ee;color:#bb5142;font-size:10px;font-weight:800;}
        .report-detail {margin-top:10px;font-size:10px;color:var(--muted);}
        .report-detail summary {cursor:pointer;color:var(--blue);font-weight:700;}
        .report-empty {padding:44px 20px!important;text-align:center;}
        .report-empty-icon {display:grid;place-items:center;margin:0 auto 14px;width:54px;height:54px;border-radius:50%;background:#eef8f3;color:#388565;}
        .report-footnote {margin:18px 2px;color:var(--muted);font-size:11px;line-height:1.8;}
        .report-pagination {padding:16px 22px;border-top:1px solid var(--line);}
        .mobile-back {display:none;}
        .dark {color-scheme:dark;--surface:#1f2937;--ink:#f3f4f6;--muted:#a8b4c1;--line:#374151;--subtle:#273343;}
        .dark body {background:#111827;}.dark .report-summary-copy {color:#bdc9d5;}.dark .report-table tbody tr:hover {background:#273343;}
        @media(min-width:768px) and (max-width:1100px) {.report-filter {align-items:flex-start;flex-direction:column;gap:14px;}.report-filter-fields {width:100%;}.report-overview {grid-template-columns:1fr;}.report-metric {padding:17px;}.report-metric-icon {width:32px;height:32px;}.report-table table {min-width:760px;}}
        @media(max-width:767px) {
            .report-sidebar {display:none;}.report-main {padding:20px 16px 30px;}.mobile-back {display:inline-flex;align-items:center;gap:5px;color:var(--blue);font-size:12px;margin-bottom:20px;}
            .report-header {align-items:flex-start;flex-direction:column;gap:16px;margin-bottom:20px;}.report-header .report-button {width:100%;}
            .report-filter {padding:18px;flex-direction:column;align-items:stretch;gap:14px;}.report-filter-fields {grid-template-columns:1fr 1fr;}.report-filter-fields button {grid-column:1/-1;}
            .report-metrics {grid-template-columns:1fr;gap:10px;margin-bottom:16px;}.report-metric {padding:18px;align-items:center;}.report-metric-value {display:inline-block;margin:6px 10px 0 0;font-size:28px;}.report-metric .report-caption {display:inline;}
            .report-overview {grid-template-columns:1fr;gap:14px;margin-bottom:20px;}.report-summary {padding:20px;}.report-list-header {padding:18px;}
            .report-table table,.report-table tbody,.report-table tr,.report-table td {display:block;width:100%;}.report-table thead {display:none;}.report-table tbody tr {padding:16px 18px;border-top:1px solid var(--line);}.report-table tbody tr:first-child {border-top:0;}
            .report-table td {padding:7px 0;border:0;}.report-table td::before {content:attr(data-label);display:block;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:5px;}.report-table td.report-empty::before {display:none;}
        }
        @media print {
            .no-print,.report-sidebar {display:none!important;} body {background:white!important;color:#172b3a!important;font-size:11px;}.report-main {padding:0!important;}.report-content {max-width:none;}.report-panel {box-shadow:none;border-color:#ddd;break-inside:avoid;}.report-header {margin:0 0 20px;}.report-title {font-size:24px;}.report-metrics {gap:10px;}.report-metric {padding:14px;}.report-metric-value {font-size:26px;}.report-overview {grid-template-columns:1.6fr 1fr;}.report-table {overflow:visible!important;}.report-table table {font-size:10px!important;}.report-table th,.report-table td {padding:10px;font-size:10px;}.report-incidents {break-inside:auto;} tr {break-inside:avoid;} thead {display:table-header-group;}.report-detail summary {display:none;}.report-detail>div {display:block!important;} .report-meta {margin-bottom:16px;}
        }
    </style>
    @include('partials.lgu-responsive-styles')
</head>
<body class="lgu-layout">
<div class="report-shell">
    <div class="report-sidebar no-print">@include('sidebars.lgu')</div>
    <main class="report-main"><div class="report-content">
        <a href="{{ route('lgu.main') }}" class="mobile-back no-print"><span class="material-symbols-outlined">arrow_back</span>LGU dashboard</a>
        <p class="report-eyebrow">LGU / System monitoring</p>
        <header class="report-header">
            <div><h1 class="report-title">System Error Report</h1><p class="report-subtitle">An overview of system issues, affected operations and the next steps to investigate.</p></div>
            <a href="{{ route('lgu.reports', ['from' => $from, 'to' => $to, 'print' => 1]) }}" target="_blank" rel="noopener" class="report-button no-print"><span class="material-symbols-outlined">print</span>Print / Save PDF</a>
        </header>
        <form method="GET" action="{{ route('lgu.reports') }}" class="report-panel report-filter no-print">
            <div class="report-filter-heading"><h2 class="report-section-title">Reporting period</h2><p class="report-caption mt-1">Choose the dates to review.</p></div>
            <div class="report-filter-fields">
                <label for="report-from">Start date<input id="report-from" type="date" name="from" required value="{{ $from }}"></label>
                <label for="report-to">End date<input id="report-to" type="date" name="to" required value="{{ $to }}" min="{{ $from }}"></label>
                <button class="report-button primary" type="submit"><span class="material-symbols-outlined">description</span>Generate Report</button>
            </div>
        </form>
        @if ($errors->any())<p role="alert" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
        <div class="report-meta"><span>{{ \Illuminate\Support\Carbon::parse($from)->format('M d, Y') }} &ndash; {{ \Illuminate\Support\Carbon::parse($to)->format('M d, Y') }}</span><span>Generated {{ now('Asia/Manila')->format('M d, Y, h:i A') }} &middot; Philippine time</span></div>
        <section class="report-metrics" aria-label="Report totals">
            <div class="report-panel report-metric"><div><p class="report-metric-label">System errors</p><p class="report-metric-value">{{ number_format($total) }}</p><p class="report-caption">Recorded in this period</p></div><span class="report-metric-icon" style="background:#fff0ed;color:#ce6958"><span class="material-symbols-outlined">error</span></span></div>
            <div class="report-panel report-metric"><div><p class="report-metric-label">Affected users</p><p class="report-metric-value">{{ number_format($affectedUsers) }}</p><p class="report-caption">Registered accounts</p></div><span class="report-metric-icon" style="background:#eef5fd;color:#568ac2"><span class="material-symbols-outlined">group</span></span></div>
            <div class="report-panel report-metric"><div><p class="report-metric-label">Error categories</p><p class="report-metric-value">{{ $categories->count() }}</p><p class="report-caption">Types of issues detected</p></div><span class="report-metric-icon" style="background:#f1effb;color:#8b79bc"><span class="material-symbols-outlined">category</span></span></div>
        </section>
        <div class="report-overview">
            <section class="report-panel report-summary">
                <div class="report-summary-heading"><span class="material-symbols-outlined" style="color:var(--blue)">summarize</span><h2 class="report-section-title">Generated Summary</h2></div>
                @if ($total)
                    <p class="report-summary-copy">The system recorded <strong>{{ number_format($total) }} server error{{ $total === 1 ? '' : 's' }}</strong> during this period, involving {{ $affectedUsers }} registered user{{ $affectedUsers === 1 ? '' : 's' }}. The most frequent category was <strong>{{ $categories->first()->category }}</strong> ({{ $categories->first()->total }} occurrences).</p>
                    @if ($topRoute)<div class="report-priority"><p class="report-eyebrow" style="font-size:9px">Priority for review</p><p class="report-summary-copy mt-1">{{ $topRoute->route_name }}</p><p class="report-caption">{{ $topRoute->total }} errors recorded. Review this operation first.</p></div>@endif
                @else
                    <p class="report-summary-copy">No system errors were recorded for this period.</p><div class="report-priority"><p class="report-caption">Select another date range to review earlier activity, or generate this report again after new activity.</p></div>
                @endif
            </section>
            <section class="report-panel report-summary">
                <div class="report-summary-heading"><span class="material-symbols-outlined" style="color:var(--muted)">bar_chart</span><h2 class="report-section-title">Errors by category</h2></div>
                @forelse ($categories as $category)
                    <div class="report-category"><div class="report-category-label"><span>{{ $category->category }}</span><strong>{{ $category->total }}</strong></div><div class="report-category-track"><div class="report-category-fill" style="width:{{ $total ? round($category->total / $total * 100, 1) : 0 }}%"></div></div></div>
                @empty
                    <p class="report-caption">Category totals will appear when errors are recorded.</p>
                @endforelse
            </section>
        </div>
        <section class="report-panel report-incidents" aria-labelledby="latest-errors-title">
            <div class="report-list-header"><div><h2 id="latest-errors-title" class="report-section-title">Latest errors</h2><p class="report-caption mt-1">Most recent first. Review each cause and recommended action.</p></div><span class="report-count">{{ number_format($total) }} total</span></div>
            <div class="report-table">
                <table><caption class="sr-only">Recorded system errors for the selected reporting period</caption>
                    <colgroup><col style="width:22%"><col style="width:21%"><col style="width:28%"><col style="width:29%"></colgroup>
                    <thead><tr><th scope="col">When / Who</th><th scope="col">Operation</th><th scope="col">Cause</th><th scope="col">Recommended action</th></tr></thead>
                    <tbody>
                    @forelse ($incidents as $incident)
                        <tr>
                            <td data-label="When / Who"><p class="font-bold">{{ $incident->created_at->timezone('Asia/Manila')->format('M d, Y') }} <span class="report-caption font-normal">{{ $incident->created_at->timezone('Asia/Manila')->format('h:i:s A') }}</span></p><p class="mt-2">{{ $incident->actor_name }}</p><p class="report-caption">{{ $incident->actor_role ?? 'System / guest' }} &middot; #{{ $incident->id }}</p></td>
                            <td data-label="Operation"><p class="font-semibold">{{ $incident->route_name ?? 'Background task' }}</p><p class="report-caption mt-2">{{ $incident->method }} &middot; HTTP {{ $incident->status_code }}</p></td>
                            <td data-label="Cause"><span class="report-badge">{{ $incident->category }}</span><p class="mt-2">{{ $incident->cause }}</p>@if ($incident->exception_type)<details class="report-detail" @if ($printReport) open @endif><summary>Technical details</summary><div class="mt-2">{{ $incident->exception_type }}@if ($incident->source_file)<br>{{ $incident->source_file }}:{{ $incident->source_line }}@endif</div></details>@endif</td>
                            <td data-label="Recommended action">{{ $incident->suggested_action }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="report-empty"><div class="report-empty-icon"><span class="material-symbols-outlined" style="font-size:28px">task_alt</span></div><p class="report-section-title">No recorded errors in this period.</p><p class="report-caption mt-2">New system errors will appear here when they are recorded.</p></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @unless ($printReport)
                @if ($incidents->hasPages())
                    <div class="report-pagination no-print">{{ $incidents->links() }}</div>
                @endif
            @endunless
        </section>
        <p class="report-footnote"><strong>About this report:</strong> Causes describe the recorded error category. Use the matching server log entry for exact diagnosis. Error records are kept for 30 days. Login activity and failed login attempts appear in <a href="{{ route('lgu.system-logs') }}" style="color:var(--blue);font-weight:700">System Logs</a>.</p>
        @include('partials.report-signature')
    </div></main>
</div>
<script>
    const fromInput = document.getElementById('report-from');
    const toInput = document.getElementById('report-to');
    fromInput?.addEventListener('change', () => { toInput.min = fromInput.value; });
</script>
@if ($printReport)<script>window.addEventListener('load', () => window.print());</script>@endif
@include('partials.lgu-mobile-nav')
</body>
</html>
