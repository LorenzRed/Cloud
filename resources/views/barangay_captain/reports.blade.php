<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - Barangay Captain Reports</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.captain-shell-styles')
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    .page-content { height: 100%; overflow-y: auto; -webkit-overflow-scrolling: touch; }

    @media (max-width: 639px) {
        .report-period-grid > *:last-child:nth-child(odd) {
            grid-column: span 2 / span 2;
        }
    }

    .report-scroll-panel {
        height: 18rem;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }

    .report-scroll-panel thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #FFFFFF;
        box-shadow: 0 1px 0 #EAEAEA;
    }

    .dark .report-scroll-panel thead th {
        background: #2C2C2C;
        box-shadow: 0 1px 0 #3D3D3D;
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2", "secondary": "#50E3C2", "accent-orange": "#F5A623", "accent-red": "#D0021B",
                    "background-light": "#F4F4F4", "background-dark": "#1A1A1A", "text-light": "#4A4A4A", "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF", "card-dark": "#2C2C2C", "border-light": "#EAEAEA", "border-dark": "#3D3D3D",
                },
                fontFamily: { "display": ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('sidebars.barangay_captain')

<div id="captainContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.captain-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-4 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto" id="reportRoot">
                @php
                    $report = $report ?? [];
                    $overview = $report['overview'] ?? [];
                    $periodActivity = $report['period_activity'] ?? [];
                    $healthConcerns = $report['health_concerns'] ?? [];
                    $monitoring = $report['monitoring'] ?? [];
                    $concernMembers = $report['concern_members'] ?? [];
                    $householdsByPurok = $report['households_by_purok'] ?? [];
                    $illnessByPurok = $report['illness_by_purok'] ?? [];
                    $recentActivities = $report['recent_activities'] ?? [];
                    $activityChart = $report['activity_chart'] ?? ['labels' => [], 'transactions' => [], 'visits' => [], 'consultations' => []];
                    $chartGranularity = $report['period']['chart_granularity'] ?? 'monthly';
                    $activePeriod = $activePeriod ?? ($report['period']['key'] ?? 'this_month');
                    $periodPresets = [
                        ['key' => 'this_week', 'label' => 'This week', 'icon' => 'date_range'],
                        ['key' => 'last_week', 'label' => 'Last week', 'icon' => 'history'],
                        ['key' => 'this_month', 'label' => 'This month', 'icon' => 'calendar_month'],
                        ['key' => 'last_month', 'label' => 'Last month', 'icon' => 'event_repeat'],
                        ['key' => 'all', 'label' => 'All time', 'icon' => 'all_inclusive'],
                    ];
                @endphp

                <header class="hidden lg:flex flex-wrap justify-between items-start gap-4 mb-6">
                    <div class="min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold">Barangay Health Reports</h1>
                        <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">
                            Whole-barangay health summary · Generated {{ $report['generated_at'] ?? now()->format('M d, Y') }}
                            · <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $activePeriod)) }}</span>
                            · {{ $report['period']['from_label'] ?? 'All time' }} – {{ $report['period']['to_label'] ?? 'Present' }}
                        </p>
                        @if (($report['managed_bhw_count'] ?? null) !== null)
                            <p class="text-xs text-text-light/50 dark:text-text-dark/50 mt-1">
                                {{ number_format((int) $report['managed_bhw_count']) }} health worker(s)
                            </p>
                        @endif
                    </div>
                </header>

                <div class="bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark p-4 md:p-5 mb-6 w-full">
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 mb-3">Quick inspect</p>
                    <div class="report-period-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 w-full">
                        @foreach ($periodPresets as $preset)
                            <a
                                href="{{ route('barangay-captain.reports', ['period' => $preset['key']]) }}"
                                class="h-10 w-full px-3 sm:px-4 rounded-lg text-sm font-semibold flex items-center justify-center gap-1.5 transition-colors {{ $activePeriod === $preset['key'] ? 'bg-primary text-white shadow-sm' : 'bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark hover:bg-primary/10 hover:text-primary' }}"
                            >
                                <span class="material-symbols-outlined text-base shrink-0">{{ $preset['icon'] }}</span>
                                <span class="truncate">{{ $preset['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div id="reportContent" class="space-y-6">
                    <section>
                        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">groups</span> Coverage overview
                        </h2>
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                            @foreach ([
                                ['label' => 'Households', 'value' => $overview['households'] ?? 0, 'icon' => 'home', 'icon_class' => 'text-primary'],
                                ['label' => 'Residents', 'value' => $overview['residents'] ?? 0, 'icon' => 'groups', 'icon_class' => 'text-primary'],
                                ['label' => 'Pending requests', 'value' => $overview['pending_requests'] ?? 0, 'icon' => 'pending_actions', 'icon_class' => 'text-accent-orange'],
                                ['label' => 'Active monitoring', 'value' => $overview['active_monitoring'] ?? 0, 'icon' => 'monitor_heart', 'icon_class' => 'text-secondary'],
                                ['label' => 'Health concern cases', 'value' => $overview['health_concern_cases'] ?? 0, 'icon' => 'cardiology', 'icon_class' => 'text-accent-red'],
                                ['label' => 'Upcoming consultations', 'value' => $overview['upcoming_consultations'] ?? 0, 'icon' => 'event', 'icon_class' => 'text-primary'],
                            ] as $card)
                                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $card['label'] }}</p>
                                        <span class="material-symbols-outlined {{ $card['icon_class'] }} text-lg">{{ $card['icon'] }}</span>
                                    </div>
                                    <p class="text-2xl font-bold">{{ number_format((int) $card['value']) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">event_note</span>
                            Activity in selected {{ $chartGranularity === 'daily' ? 'week' : ($chartGranularity === 'weekly' ? 'month' : 'period') }}
                        </h2>
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                            @foreach ([
                                ['label' => 'Transactions', 'value' => $periodActivity['transactions'] ?? 0],
                                ['label' => 'Medicine pcs', 'value' => $periodActivity['medicine_pcs'] ?? 0],
                                ['label' => 'Households with concerns', 'value' => $periodActivity['households_with_concern'] ?? 0],
                                ['label' => 'Monitoring visits', 'value' => $periodActivity['monitoring_visits'] ?? 0],
                                ['label' => 'Consultations', 'value' => $periodActivity['consultations'] ?? 0],
                                ['label' => 'Pregnant monitored', 'value' => $periodActivity['pregnant_monitored'] ?? 0],
                            ] as $card)
                                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mb-1">{{ $card['label'] }}</p>
                                    <p class="text-2xl font-bold">{{ number_format((int) $card['value']) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section>
                        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">cardiology</span> Health concerns from transactions
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                            @foreach ([
                                ['label' => 'Hypertension', 'value' => $healthConcerns['Hypertension'] ?? 0, 'icon' => 'monitor_heart', 'icon_class' => 'text-accent-red'],
                                ['label' => 'Diabetes', 'value' => $healthConcerns['Diabetes'] ?? 0, 'icon' => 'glucose', 'icon_class' => 'text-sky-500'],
                                ['label' => 'Hyperlipidemia', 'value' => $healthConcerns['Hyperlipidemia'] ?? 0, 'icon' => 'bloodtype', 'icon_class' => 'text-accent-orange'],
                            ] as $card)
                                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $card['label'] }}</p>
                                        <span class="material-symbols-outlined {{ $card['icon_class'] }} text-lg">{{ $card['icon'] }}</span>
                                    </div>
                                    <p class="text-2xl font-bold">{{ number_format((int) $card['value']) }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
                            <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                                <h3 class="text-base font-bold mb-4 shrink-0">Concern distribution</h3>
                                <div class="h-72 max-w-md mx-auto w-full"><canvas id="concernChart"></canvas></div>
                            </div>
                            <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                                <h3 class="text-base font-bold mb-4 shrink-0">Members with health concerns</h3>
                                <div class="report-scroll-panel">
                                    <table class="w-full min-w-[480px]">
                                        <thead>
                                            <tr class="border-b border-border-light dark:border-border-dark">
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Member</th>
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Household</th>
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Purok</th>
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Date</th>
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Concern</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($concernMembers as $row)
                                                <tr class="border-b border-border-light dark:border-border-dark">
                                                    <td class="px-2 py-2 text-sm font-medium">{{ $row['member_name'] }}</td>
                                                    <td class="px-2 py-2 text-sm">{{ $row['household'] }}</td>
                                                    <td class="px-2 py-2 text-sm">{{ $row['purok'] ?? '—' }}</td>
                                                    <td class="px-2 py-2 text-sm whitespace-nowrap">{{ $row['date'] }}</td>
                                                    <td class="px-2 py-2 text-sm">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-200">{{ $row['conditions'] }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-2 py-6 text-sm text-center text-text-light/60">No medicine transactions with inferred health concerns in this period.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5">
                            <h2 class="text-lg font-bold mb-4">
                                @if ($chartGranularity === 'daily')
                                    Daily activity
                                @elseif ($chartGranularity === 'weekly')
                                    Weekly activity
                                @else
                                    Monthly activity
                                @endif
                            </h2>
                            <div class="h-72"><canvas id="activityChart"></canvas></div>
                        </section>
                        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5">
                            <h2 class="text-lg font-bold mb-4">Household monitoring mix</h2>
                            <div class="h-72 max-w-sm mx-auto"><canvas id="monitoringChart"></canvas></div>
                            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-sm">
                                <div><span class="block font-bold text-pink-500">{{ number_format((int) ($monitoring['pregnant'] ?? 0)) }}</span>Pregnant</div>
                                <div><span class="block font-bold text-violet-500">{{ number_format((int) ($monitoring['senior'] ?? 0)) }}</span>Senior</div>
                                <div><span class="block font-bold text-primary">{{ number_format((int) ($monitoring['general'] ?? 0)) }}</span>General</div>
                            </div>
                        </section>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
                        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                            <h2 class="text-lg font-bold mb-4 shrink-0">Households by purok</h2>
                            <div class="h-72"><canvas id="purokChart"></canvas></div>
                        </section>
                        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                            <h2 class="text-lg font-bold mb-4 shrink-0">Illness by purok</h2>
                            <div class="h-72"><canvas id="illnessByPurokChart"></canvas></div>
                        </section>
                    </div>

                    <div class="grid grid-cols-1 gap-6 items-stretch">
                        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                            <h2 class="text-lg font-bold mb-4 shrink-0">Recent activities</h2>
                            <div class="report-scroll-panel">
                                <table class="w-full min-w-[420px]">
                                    <thead>
                                        <tr class="border-b border-border-light dark:border-border-dark">
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Date</th>
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Type</th>
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Name</th>
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentActivities as $activity)
                                            <tr class="border-b border-border-light dark:border-border-dark">
                                                <td class="px-2 py-2 text-sm whitespace-nowrap">{{ $activity['date'] }}</td>
                                                <td class="px-2 py-2 text-sm">{{ $activity['type'] }}</td>
                                                <td class="px-2 py-2 text-sm font-medium">{{ $activity['name'] }}</td>
                                                <td class="px-2 py-2 text-sm text-text-light/70">{{ $activity['detail'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-2 py-6 text-sm text-center text-text-light/60">No activities recorded for this period yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.captain-mobile-nav')
</div>

<script>
    const reportPayload = @json($report);

    function initCharts() {
        if (typeof Chart === 'undefined') return;

        const activity = reportPayload.activity_chart || {};
        const activityCanvas = document.getElementById('activityChart');
        if (activityCanvas) {
            new Chart(activityCanvas, {
                type: 'bar',
                data: {
                    labels: activity.labels || [],
                    datasets: [
                        { label: 'Transactions', data: activity.transactions || [], backgroundColor: '#4A90E2' },
                        { label: 'Visits', data: activity.visits || [], backgroundColor: '#50E3C2' },
                        { label: 'Consultations', data: activity.consultations || [], backgroundColor: '#F5A623' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: false },
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        }

        const concerns = reportPayload.health_concerns?.chart || {};
        const concernCanvas = document.getElementById('concernChart');
        if (concernCanvas) {
            new Chart(concernCanvas, {
                type: 'doughnut',
                data: {
                    labels: concerns.labels || [],
                    datasets: [{
                        data: concerns.values || [],
                        backgroundColor: concerns.colors || ['#BE123C', '#0EA5E9', '#F59E0B'],
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        }

        const monitoring = reportPayload.monitoring?.chart || {};
        const monitoringCanvas = document.getElementById('monitoringChart');
        if (monitoringCanvas) {
            new Chart(monitoringCanvas, {
                type: 'doughnut',
                data: {
                    labels: monitoring.labels || [],
                    datasets: [{
                        data: monitoring.values || [],
                        backgroundColor: monitoring.colors || ['#EC4899', '#8B5CF6', '#4A90E2'],
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        }

        const purokData = reportPayload.households_by_purok || [];
        const purokCanvas = document.getElementById('purokChart');
        if (purokCanvas) {
            new Chart(purokCanvas, {
                type: 'bar',
                data: {
                    labels: purokData.map((row) => row.purok),
                    datasets: [{
                        label: 'Households',
                        data: purokData.map((row) => row.count),
                        backgroundColor: '#4A90E2',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        }

        const illnessPurok = reportPayload.illness_by_purok || [];
        const illnessPurokCanvas = document.getElementById('illnessByPurokChart');
        if (illnessPurokCanvas) {
            new Chart(illnessPurokCanvas, {
                type: 'bar',
                data: {
                    labels: illnessPurok.map((row) => row.purok),
                    datasets: [
                        { label: 'Hypertension', data: illnessPurok.map((row) => row.Hypertension || 0), backgroundColor: '#BE123C' },
                        { label: 'Diabetes', data: illnessPurok.map((row) => row.Diabetes || 0), backgroundColor: '#0EA5E9' },
                        { label: 'Hyperlipidemia', data: illnessPurok.map((row) => row.Hyperlipidemia || 0), backgroundColor: '#F59E0B' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                    },
                },
            });
        }
    }

    initCharts();
</script>
</body>
</html>
