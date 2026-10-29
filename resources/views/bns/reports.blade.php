<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
@include('partials.bns-viewport')
<title>Bantay Kalusugan - Reports</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bns-shell-styles')
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    .page-content { height: 100%; overflow-y: auto; -webkit-overflow-scrolling: touch; }

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

    @media (max-width: 639px) {
        .report-period-grid > *:last-child:nth-child(odd) {
            grid-column: span 2 / span 2;
        }
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
    @include('sidebars.bns')
    
<div id="bnsContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bns-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-4 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto" id="reportRoot">
                @php
                    $report = $report ?? [];
                    $overview = $report['overview'] ?? [];
                    $periodActivity = $report['period_activity'] ?? [];
                    $immunization = $report['immunization'] ?? [];
                    $nutrition = $report['nutrition'] ?? [];
                    $nutritionChildren = $report['nutrition_children'] ?? [];
                    $monthlyActivity = $report['activity_chart'] ?? ['labels' => [], 'weight' => [], 'deworming' => [], 'vitaminA' => [], 'immunization' => []];
                    $childrenByPurok = $report['children_by_purok'] ?? [];
                    $recentActivities = $report['recent_activities'] ?? [];
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

                <header class="hidden lg:flex flex-wrap justify-between items-start gap-4 mb-6 print-break">
                    <div class="min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold">Nutrition Reports</h1>
                        <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Generated {{ $report['generated_at'] ?? now()->format('M d, Y') }} · <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $activePeriod)) }}</span> · {{ $report['period']['from_label'] ?? 'All time' }} – {{ $report['period']['to_label'] ?? 'Present' }}</p>
                    </div>
                </header>

                <div class="bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark p-4 md:p-5 mb-6 w-full">
                    <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 mb-3">Quick inspect</p>
                    <div class="report-period-grid grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 w-full">
                        @foreach ($periodPresets as $preset)
                            <a
                                href="{{ route('bns.reports', ['period' => $preset['key']]) }}"
                                class="h-10 w-full px-3 sm:px-4 rounded-lg text-sm font-semibold flex items-center justify-center gap-1.5 transition-colors {{ $activePeriod === $preset['key'] ? 'bg-primary text-white shadow-sm' : 'bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark hover:bg-primary/10 hover:text-primary' }}"
                            >
                                <span class="material-symbols-outlined text-base shrink-0">{{ $preset['icon'] }}</span>
                                <span class="truncate">{{ $preset['label'] }}</span>
                            </a>
                        @endforeach
                        </div>
                        </div>

                <div id="reportContent" class="space-y-6">
                    <section class="print-break">
                        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">groups</span> Masterlist overview
                        </h2>
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                            @foreach ([
                                ['label' => 'Children (0–5)', 'value' => $overview['total_children'] ?? 0, 'icon' => 'child_care', 'icon_class' => 'text-primary'],
                                ['label' => 'Households', 'value' => $overview['households'] ?? 0, 'icon' => 'home', 'icon_class' => 'text-primary'],
                                ['label' => 'Puroks', 'value' => $overview['puroks'] ?? 0, 'icon' => 'map', 'icon_class' => 'text-primary'],
                                ['label' => 'With weight records', 'value' => $overview['children_with_weight'] ?? 0, 'icon' => 'monitor_weight', 'icon_class' => 'text-secondary'],
                                ['label' => 'Fully immunized', 'value' => $overview['fully_immunized'] ?? 0, 'icon' => 'vaccines', 'icon_class' => 'text-secondary'],
                                ['label' => 'Missed doses', 'value' => $overview['children_with_missed_doses'] ?? 0, 'icon' => 'warning', 'icon_class' => 'text-accent-red'],
                            ] as $card)
                                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $card['label'] }}</p>
                                        <span class="material-symbols-outlined {{ $card['icon_class'] }} text-lg">{{ $card['icon'] }}</span>
                        </div>
                                    <p class="text-2xl font-bold" data-report-value>{{ number_format((int) $card['value']) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="print-break">
                        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">monitor_weight</span> Nutritional status (latest weight)
                        </h2>
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
                            @foreach ([
                                ['label' => 'Malnourished', 'value' => $nutrition['malnutrition_cases'] ?? 0, 'icon' => 'warning', 'icon_class' => 'text-accent-red'],
                                ['label' => 'Severely malnourished', 'value' => $nutrition['severely_malnourished'] ?? 0, 'icon' => 'error', 'icon_class' => 'text-accent-red'],
                                ['label' => 'Underweight / stunted / wasted', 'value' => $nutrition['malnourished'] ?? 0, 'icon' => 'trending_down', 'icon_class' => 'text-accent-orange'],
                                ['label' => 'Normal', 'value' => $nutrition['normal'] ?? 0, 'icon' => 'check_circle', 'icon_class' => 'text-secondary'],
                                ['label' => 'Overweight / obese', 'value' => $nutrition['overweight'] ?? 0, 'icon' => 'trending_up', 'icon_class' => 'text-accent-orange'],
                                ['label' => 'No weight record', 'value' => $nutrition['no_weight_record'] ?? 0, 'icon' => 'help', 'icon_class' => 'text-text-light/50'],
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
                                <h3 class="text-base font-bold mb-4 shrink-0">Nutrition status distribution</h3>
                                <div class="h-72 max-w-md mx-auto w-full"><canvas id="nutritionChart"></canvas></div>
                                </div>
                            <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                                <h3 class="text-base font-bold mb-4 shrink-0">Children needing attention</h3>
                                <div class="report-scroll-panel">
                                    <table class="w-full min-w-[480px]">
                                        <thead>
                                            <tr class="border-b border-border-light dark:border-border-dark">
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Child</th>
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Age</th>
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Purok</th>
                                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $attentionChildren = collect($nutritionChildren)->filter(
                                                    fn ($row) => in_array($row['status_group'] ?? '', ['severely_malnourished', 'malnourished', 'overweight'], true)
                                                );
                                            @endphp
                                            @forelse ($attentionChildren as $row)
                                                @php
                                                    $badgeClass = match ($row['status_group'] ?? '') {
                                                        'severely_malnourished' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-200',
                                                        'malnourished' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200',
                                                        'overweight' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-200',
                                                        default => 'bg-background-light dark:bg-background-dark text-text-light/60',
                                                    };
                                                @endphp
                                                <tr class="border-b border-border-light dark:border-border-dark">
                                                    <td class="px-2 py-2 text-sm font-medium">{{ $row['child_name'] }}</td>
                                                    <td class="px-2 py-2 text-sm whitespace-nowrap">{{ $row['age_label'] }}</td>
                                                    <td class="px-2 py-2 text-sm">{{ $row['purok'] }}</td>
                                                    <td class="px-2 py-2 text-sm">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $row['status_label'] }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="px-2 py-6 text-sm text-center text-text-light/60">No malnourished or overweight children based on latest weight records.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="print-break">
                        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">event_note</span>
                            Activity in selected {{ $chartGranularity === 'daily' ? 'week' : ($chartGranularity === 'weekly' ? 'month' : 'period') }}
                        </h2>
                        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                            @foreach ([
                                ['label' => 'Weight entries', 'value' => $periodActivity['weight_entries'] ?? 0],
                                ['label' => 'Children weighed', 'value' => $periodActivity['children_weighed'] ?? 0],
                                ['label' => 'Avg weight (kg)', 'value' => $periodActivity['avg_weight_kg'] ?? '—'],
                                ['label' => 'Deworming given', 'value' => $periodActivity['deworming_given'] ?? 0],
                                ['label' => 'Vitamin A given', 'value' => $periodActivity['vitamin_a_given'] ?? 0],
                                ['label' => 'Vaccines given', 'value' => $periodActivity['immunizations_given'] ?? 0],
                            ] as $card)
                                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mb-1">{{ $card['label'] }}</p>
                                    <p class="text-2xl font-bold">{{ is_numeric($card['value']) ? number_format((int) $card['value']) : $card['value'] }}</p>
                                </div>
                            @endforeach
                                </div>
                    </section>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 print-break">
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
                            <h2 class="text-lg font-bold mb-4">Immunization dose status</h2>
                            <div class="h-72 max-w-sm mx-auto"><canvas id="immunizationChart"></canvas></div>
                            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-sm">
                                <div><span class="block font-bold text-secondary">{{ number_format((int) ($immunization['given'] ?? 0)) }}</span>Given</div>
                                <div><span class="block font-bold text-accent-red">{{ number_format((int) ($immunization['missed'] ?? 0)) }}</span>Missed</div>
                                <div><span class="block font-bold text-accent-orange">{{ number_format((int) ($immunization['pending'] ?? 0)) }}</span>Pending</div>
                            </div>
                        </section>
                        </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 print-break items-stretch">
                        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                            <h2 class="text-lg font-bold mb-4 shrink-0">Children by purok</h2>
                            <div class="h-72"><canvas id="purokChart"></canvas></div>
                        </section>
                        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                            <h2 class="text-lg font-bold mb-4 shrink-0">Recent activities</h2>
                            <div class="report-scroll-panel">
                                <table class="w-full min-w-[420px]">
                                    <thead>
                                        <tr class="border-b border-border-light dark:border-border-dark">
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Date</th>
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Type</th>
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Child</th>
                                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentActivities as $activity)
                                            <tr class="border-b border-border-light dark:border-border-dark">
                                                <td class="px-2 py-2 text-sm whitespace-nowrap">{{ $activity['date'] }}</td>
                                                <td class="px-2 py-2 text-sm">{{ $activity['type'] }}</td>
                                                <td class="px-2 py-2 text-sm font-medium">{{ $activity['child_name'] }}</td>
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

    @include('partials.bns-mobile-nav')
</div>

<script>
    const reportPayload = @json($report);

    function initCharts() {
        if (typeof Chart === 'undefined') return;

        const monthly = reportPayload.activity_chart || reportPayload.monthly_activity || {};
        const activityCanvas = document.getElementById('activityChart');
        if (activityCanvas) {
            new Chart(activityCanvas, {
                type: 'bar',
                data: {
                    labels: monthly.labels || [],
                    datasets: [
                        { label: 'Weight', data: monthly.weight || [], backgroundColor: '#4A90E2' },
                        { label: 'Deworming', data: monthly.deworming || [], backgroundColor: '#50E3C2' },
                        { label: 'Vitamin A', data: monthly.vitaminA || [], backgroundColor: '#F5A623' },
                        { label: 'Immunization', data: monthly.immunization || [], backgroundColor: '#7B68EE' },
                    ],
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        }

        const imm = reportPayload.immunization || {};
        const immCanvas = document.getElementById('immunizationChart');
        if (immCanvas) {
            new Chart(immCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Given', 'Missed', 'Pending'],
                    datasets: [{
                        data: [imm.given || 0, imm.missed || 0, imm.pending || 0],
                        backgroundColor: ['#50E3C2', '#D0021B', '#F5A623'],
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        }

        const nutrition = reportPayload.nutrition || {};
        const nutritionChart = nutrition.chart || {};
        const nutritionCanvas = document.getElementById('nutritionChart');
        if (nutritionCanvas) {
            new Chart(nutritionCanvas, {
                type: 'doughnut',
                data: {
                    labels: nutritionChart.labels || [],
                    datasets: [{
                        data: nutritionChart.values || [],
                        backgroundColor: ['#D0021B', '#F5A623', '#50E3C2', '#FF8C42', '#9CA3AF'],
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        }

        const purokData = reportPayload.children_by_purok || [];
        const purokCanvas = document.getElementById('purokChart');
        if (purokCanvas && purokData.length) {
            new Chart(purokCanvas, {
                type: 'bar',
                data: {
                    labels: purokData.map((row) => row.purok),
                    datasets: [{ label: 'Children', data: purokData.map((row) => row.count), backgroundColor: '#4A90E2' }],
                },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        }
    }

    initCharts();
</script>
</body>
</html>
