<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bns-viewport')
<title>Bantay Kalusugan - BNS Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bns-shell-styles')
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    .page-content { height: 100%; overflow-y: auto; scrollbar-width: thin; -webkit-overflow-scrolling: touch; }

    .dash-card {
        background: linear-gradient(145deg, rgba(255,255,255,1) 0%, rgba(248,250,252,1) 100%);
    }
    .dark .dash-card {
        background: linear-gradient(145deg, rgba(44,44,44,1) 0%, rgba(38,38,38,1) 100%);
    }

    .dash-hero {
        background: linear-gradient(135deg, rgba(74,144,226,0.12) 0%, rgba(80,227,194,0.08) 50%, transparent 100%);
    }
    .dark .dash-hero {
        background: linear-gradient(135deg, rgba(74,144,226,0.18) 0%, rgba(80,227,194,0.1) 50%, transparent 100%);
    }

    @media (max-width: 639px) {
        .dash-kpi-grid-mobile-span > *:last-child:nth-child(odd) {
            grid-column: span 2 / span 2;
        }

        .dash-legend-grid-mobile-span > *:last-child:nth-child(odd) {
            grid-column: span 2 / span 2;
        }
    .dash-chart-wrap {
        position: relative;
        height: 16rem;
        min-height: 14rem;
    }
    @media (min-width: 640px) {
        .dash-chart-wrap { height: 18rem; }
    }

    .dash-period-btn {
        transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }
    .dash-period-btn[aria-pressed="true"] {
        background: #4A90E2;
        color: #fff;
        box-shadow: 0 1px 3px rgba(74, 144, 226, 0.35);
    }
    .dark .dash-period-btn[aria-pressed="true"] {
        background: #4A90E2;
        color: #fff;
    }

    .dash-nutrition-pill {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.625rem;
        border: 1px solid rgba(0,0,0,0.06);
        background: rgba(255,255,255,0.6);
        font-size: 0.75rem;
    }
    .dark .dash-nutrition-pill {
        border-color: rgba(255,255,255,0.08);
        background: rgba(0,0,0,0.15);
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
@include('partials.app-flash')
    @include('sidebars.bns')
    
<div id="bnsContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bns-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-4 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto space-y-6 pb-8">
                    @php
                        $nutritionStats = $nutritionStats ?? [];
                    $dashboardCharts = $dashboardCharts ?? [];
                    $chartPeriods = $dashboardCharts['chart_periods'] ?? [];
                    $defaultChartPeriod = $dashboardCharts['default_period'] ?? 'month';
                    $nutritionSplit = $dashboardCharts['nutrition_split'] ?? ['labels' => [], 'values' => []];

                    $primaryKpis = [
                        ['label' => 'Children monitored', 'key' => 'total_children_0_5', 'icon' => 'child_care', 'tone' => 'bg-primary/10 text-primary'],
                        ['label' => 'Households', 'key' => 'households_covered', 'icon' => 'home', 'tone' => 'bg-secondary/15 text-secondary'],
                        ['label' => 'Malnourished', 'key' => 'malnutrition_cases', 'icon' => 'warning', 'tone' => 'bg-accent-red/10 text-accent-red'],
                        ['label' => 'Normal nutrition', 'key' => 'normal_bmi_count', 'icon' => 'check_circle', 'tone' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                    ];

                    $secondaryKpis = [
                        ['label' => 'Puroks covered', 'key' => 'puroks_covered'],
                        ['label' => 'Underweight / stunted', 'key' => 'underweight_cases'],
                        ['label' => 'Severely malnourished', 'key' => 'severely_underweight_cases'],
                        ['label' => 'Overweight / obese', 'key' => 'overweight_cases'],
                        ['label' => 'No weight record', 'key' => 'no_weight_record'],
                        ];
                    @endphp

                <header class="dash-hero rounded-2xl border border-border-light dark:border-border-dark p-5 md:p-6">
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-primary mb-1">Barangay Nutrition Scholar</p>
                            <h1 class="text-2xl sm:text-3xl font-bold leading-tight text-text-light dark:text-text-dark">Dashboard</h1>
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1.5 max-w-xl">
                                Overview of children 0–5, nutrition status, and program coverage in your barangay.
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            @if (filled($barangayName ?? Auth::user()->barangay))
                                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-sm font-semibold bg-white/80 dark:bg-card-dark/80 border border-border-light dark:border-border-dark text-primary shadow-sm">
                                    <span class="material-symbols-outlined text-base">location_city</span>
                                    {{ $assignedBarangay?->name ?? ($barangayName ?? Auth::user()->barangay) }}
                                </span>
                            @endif
                            <p class="text-xs text-text-light/50 dark:text-text-dark/50">{{ now()->format('l, M d, Y') }}</p>
                        </div>
                    </div>
                </header>

                <section>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-3">At a glance</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 dash-kpi-grid-mobile-span">
                        @foreach ($primaryKpis as $card)
                            <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 md:p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <span class="inline-flex size-10 items-center justify-center rounded-xl {{ $card['tone'] }}">
                                        <span class="material-symbols-outlined text-xl">{{ $card['icon'] }}</span>
                                    </span>
                                </div>
                                <p class="text-2xl md:text-3xl font-bold tabular-nums">{{ number_format((int) ($nutritionStats[$card['key']] ?? 0)) }}</p>
                                <p class="text-xs sm:text-sm text-text-light/60 dark:text-text-dark/60 mt-1 leading-snug">{{ $card['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-3">Nutrition breakdown</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 dash-kpi-grid-mobile-span">
                        @foreach ($secondaryKpis as $card)
                            <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-3">
                                <p class="text-xl font-bold tabular-nums">{{ number_format((int) ($nutritionStats[$card['key']] ?? 0)) }}</p>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 leading-snug">{{ $card['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm">
                        <div class="flex flex-col gap-3 mb-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="text-base font-bold">Nutrition status</h2>
                                    <p id="nutritionStatusSubtitle" class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Latest assessment per child in selected period</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5 shrink-0" role="group" aria-label="Nutrition period filter">
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-chart-period="week" aria-pressed="false">This week</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-chart-period="month" aria-pressed="true">This month</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-chart-period="year" aria-pressed="false">This year</button>
                                </div>
                            </div>
                            <p id="nutritionRangeLabel" class="text-[11px] font-medium text-primary/80 dark:text-primary/90"></p>
                        </div>
                        <div class="dash-chart-wrap">
                            <canvas id="nutritionStatusBarChart" aria-label="Nutrition status bar chart"></canvas>
                        </div>
                        <div id="nutritionLegendPills" class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4"></div>
                        </div>

                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm">
                        <div class="flex flex-col gap-3 mb-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="text-base font-bold">Average weight trend</h2>
                                    <p id="weightTrendSubtitle" class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Average weight from weigh-ins in selected period</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5 shrink-0" role="group" aria-label="Weight trend period filter">
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-weight-period="week" aria-pressed="false">This week</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-weight-period="month" aria-pressed="true">This month</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-weight-period="year" aria-pressed="false">This year</button>
                                </div>
                            </div>
                            <p id="weightRangeLabel" class="text-[11px] font-medium text-primary/80 dark:text-primary/90"></p>
                        </div>
                        <div class="dash-chart-wrap">
                            <canvas id="monthlyWeightLineChart" aria-label="Average weight trend line chart"></canvas>
                        </div>
                        <p id="weightTrendFootnote" class="text-[11px] text-text-light/50 dark:text-text-dark/50 mt-3 text-center sm:text-left"></p>
                        </div>

                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-5 shadow-sm xl:col-span-2">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                            <div>
                                <h2 class="text-base font-bold">Population nutrition mix</h2>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Share of monitored children by nutrition category</p>
                            </div>
                            <a href="{{ route('bns.reports') }}" class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1">
                                View full reports
                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                            <div class="h-56 sm:h-64 max-w-xs mx-auto w-full">
                                <canvas id="malnutritionPieChart"></canvas>
                            </div>
                            <div class="grid grid-cols-2 gap-3 dash-legend-grid-mobile-span">
                                @foreach (array_combine($nutritionSplit['labels'] ?? [], $nutritionSplit['values'] ?? []) as $label => $value)
                                    @php
                                        $legendTone = match ($label) {
                                            'Malnourished' => 'bg-accent-red',
                                            'Normal' => 'bg-secondary',
                                            'Overweight' => 'bg-accent-orange',
                                            default => 'bg-gray-400',
                                        };
                                    @endphp
                                    <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light/50 dark:bg-background-dark/50 px-3 py-2.5">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="size-2.5 rounded-full {{ $legendTone }}"></span>
                                            <span class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $label }}</span>
                                        </div>
                                        <p class="text-lg font-bold tabular-nums">{{ number_format((int) $value) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    @include('partials.bns-mobile-nav')
</div>

<script>
    const chartPeriods = @json($chartPeriods);
    const defaultChartPeriod = @json($defaultChartPeriod);
    const nutritionSplit = @json($nutritionSplit);

    const periodLabels = {
        week: 'This week',
        month: 'This month',
        year: 'This year',
    };

    const weightSubtitleByPeriod = {
        week: 'Daily average weight from weigh-ins this week',
        month: 'Weekly average weight from weigh-ins this month',
        year: 'Monthly average weight from weigh-ins this year',
    };

    const isDarkMode = () => document.documentElement.classList.contains('dark');
    const gridColor = () => isDarkMode() ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
    const tickColor = () => isDarkMode() ? '#C4C4C4' : '#6B7280';

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                labels: { font: { family: 'Manrope', size: 12 }, boxWidth: 12, padding: 16 },
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                titleFont: { family: 'Manrope', size: 13, weight: '600' },
                bodyFont: { family: 'Manrope', size: 12 },
                padding: 12,
                cornerRadius: 10,
                displayColors: true,
            },
        },
    };

    let nutritionChart = null;
    let weightChart = null;
    let activeNutritionPeriod = defaultChartPeriod || 'month';
    let activeWeightPeriod = defaultChartPeriod || 'month';

    function formatKg(value) {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return 'No data';
        }
        return `${Number(value).toFixed(2)} kg`;
    }

    function nutritionDataFor(period) {
        const bucket = chartPeriods[period] || {};
        return bucket.nutrition_categories || { labels: [], values: [], colors: [], weighed_count: 0 };
    }

    function weightDataFor(period) {
        const bucket = chartPeriods[period] || {};
        return bucket.weight_trend || { labels: [], values: [], unit: 'kg' };
    }

    function rangeLabelFor(period) {
        return (chartPeriods[period] || {}).range_label || '';
    }

    function setPeriodButtons(groupAttr, activePeriod) {
        document.querySelectorAll(`[${groupAttr}]`).forEach((button) => {
            const isActive = button.getAttribute(groupAttr) === activePeriod;
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function renderNutritionLegend(data) {
        const container = document.getElementById('nutritionLegendPills');
        if (!container) return;

        const labels = data.labels || [];
        const values = data.values || [];
        const colors = data.colors || ['#BE123C', '#F59E0B', '#10B981', '#F97316'];
        const weighed = data.weighed_count ?? values.reduce((sum, n) => sum + Number(n || 0), 0);

        container.innerHTML = labels.map((label, index) => `
            <div class="dash-nutrition-pill">
                <span class="flex items-center gap-2 min-w-0">
                    <span class="size-2.5 rounded-full shrink-0" style="background:${colors[index] || '#9CA3AF'}"></span>
                    <span class="truncate text-text-light/70 dark:text-text-dark/70">${label}</span>
                </span>
                <span class="font-bold tabular-nums shrink-0">${Number(values[index] || 0)}</span>
            </div>
        `).join('');

        const rangeEl = document.getElementById('nutritionRangeLabel');
        if (rangeEl) {
            rangeEl.textContent = `${rangeLabelFor(activeNutritionPeriod)} · ${weighed} child${weighed === 1 ? '' : 'ren'} weighed`;
        }
    }

    function weightYAxisBounds(values) {
        const numeric = (values || []).filter((v) => v !== null && v !== undefined && !Number.isNaN(Number(v))).map(Number);
        if (numeric.length === 0) {
            return { min: 0, max: 15 };
        }

        const min = Math.min(...numeric);
        const max = Math.max(...numeric);
        const padding = Math.max(0.5, (max - min) * 0.15);

        return {
            min: Math.max(0, Math.floor((min - padding) * 10) / 10),
            max: Math.ceil((max + padding) * 10) / 10,
        };
    }

    function updateNutritionChart() {
        const data = nutritionDataFor(activeNutritionPeriod);
        const labels = data.labels || [];
        const values = data.values || [];
        const colors = data.colors || ['#BE123C', '#F59E0B', '#10B981', '#F97316'];

        renderNutritionLegend(data);
        setPeriodButtons('data-chart-period', activeNutritionPeriod);

        if (!nutritionChart) return;

        nutritionChart.data.labels = labels;
        nutritionChart.data.datasets[0].data = values;
        nutritionChart.data.datasets[0].backgroundColor = colors;
        nutritionChart.update();
    }

    function updateWeightChart() {
        const data = weightDataFor(activeWeightPeriod);
        const labels = data.labels || [];
        const values = data.values || [];
        const bounds = weightYAxisBounds(values);

        const rangeEl = document.getElementById('weightRangeLabel');
        const subtitleEl = document.getElementById('weightTrendSubtitle');
        const footnoteEl = document.getElementById('weightTrendFootnote');

        if (rangeEl) rangeEl.textContent = rangeLabelFor(activeWeightPeriod);
        if (subtitleEl) subtitleEl.textContent = weightSubtitleByPeriod[activeWeightPeriod] || weightSubtitleByPeriod.month;
        if (footnoteEl) {
            const valid = values.filter((v) => v !== null && v !== undefined);
            footnoteEl.textContent = valid.length
                ? `Showing ${valid.length} data point${valid.length === 1 ? '' : 's'} with recorded weigh-ins`
                : 'No weigh-ins recorded for this period yet';
        }

        setPeriodButtons('data-weight-period', activeWeightPeriod);

        if (!weightChart) return;

        weightChart.data.labels = labels;
        weightChart.data.datasets[0].data = values;
        weightChart.options.scales.y.min = bounds.min;
        weightChart.options.scales.y.max = bounds.max;
        weightChart.update();
    }

    if (typeof Chart !== 'undefined') {
        const barCanvas = document.getElementById('nutritionStatusBarChart');
        if (barCanvas) {
            const initialNutrition = nutritionDataFor(activeNutritionPeriod);

            nutritionChart = new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: initialNutrition.labels || [],
                    datasets: [{
                        label: 'Children',
                        data: initialNutrition.values || [],
                        backgroundColor: initialNutrition.colors || ['#BE123C', '#F59E0B', '#10B981', '#F97316'],
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 56,
                    }],
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Manrope', size: window.innerWidth < 640 ? 10 : 11 },
                                color: tickColor,
                                maxRotation: window.innerWidth < 640 ? 45 : 0,
                                minRotation: window.innerWidth < 640 ? 45 : 0,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { family: 'Manrope', size: 11 },
                                color: tickColor,
                                padding: 8,
                            },
                            grid: { color: gridColor, drawBorder: false },
                            border: { display: false },
                        },
                    },
                    plugins: {
                        ...chartDefaults.plugins,
                        legend: { display: false },
                        tooltip: {
                            ...chartDefaults.plugins.tooltip,
                            callbacks: {
                                label: (ctx) => ` ${ctx.parsed.y} child${ctx.parsed.y === 1 ? '' : 'ren'}`,
                            },
                        },
                    },
                },
            });

            renderNutritionLegend(initialNutrition);
        }

        const lineCanvas = document.getElementById('monthlyWeightLineChart');
        if (lineCanvas) {
            const initialWeight = weightDataFor(activeWeightPeriod);
            const initialBounds = weightYAxisBounds(initialWeight.values);

            weightChart = new Chart(lineCanvas, {
                type: 'line',
                data: {
                    labels: initialWeight.labels || [],
                    datasets: [{
                        label: 'Avg weight',
                        data: initialWeight.values || [],
                        borderColor: '#4A90E2',
                        backgroundColor: (context) => {
                            const { chart } = context;
                            const { ctx, chartArea } = chart;
                            if (!chartArea) return 'rgba(74, 144, 226, 0.12)';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(74, 144, 226, 0.28)');
                            gradient.addColorStop(1, 'rgba(74, 144, 226, 0.02)');
                            return gradient;
                        },
                        fill: true,
                        tension: 0.35,
                        spanGaps: false,
                        pointRadius: (context) => (context.raw === null || context.raw === undefined ? 0 : 5),
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#4A90E2',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 2.5,
                    }],
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Manrope', size: window.innerWidth < 640 ? 10 : 11 },
                                color: tickColor,
                                maxRotation: window.innerWidth < 640 ? 0 : 0,
                                autoSkip: true,
                                maxTicksLimit: window.innerWidth < 640 ? 5 : 12,
                            },
                            border: { display: false },
                        },
                        y: {
                            min: initialBounds.min,
                            max: initialBounds.max,
                            ticks: {
                                font: { family: 'Manrope', size: 11 },
                                color: tickColor,
                                padding: 8,
                                callback: (value) => `${value} kg`,
                            },
                            grid: { color: gridColor, drawBorder: false },
                            border: { display: false },
                        },
                    },
                    plugins: {
                        ...chartDefaults.plugins,
                        legend: { display: false },
                        tooltip: {
                            ...chartDefaults.plugins.tooltip,
                            callbacks: {
                                label: (ctx) => ` ${formatKg(ctx.parsed.y)}`,
                            },
                        },
                    },
                },
            });

            updateWeightChart();
        }

        document.querySelectorAll('[data-chart-period]').forEach((button) => {
            button.addEventListener('click', () => {
                activeNutritionPeriod = button.getAttribute('data-chart-period') || 'month';
                updateNutritionChart();
            });
        });

        document.querySelectorAll('[data-weight-period]').forEach((button) => {
            button.addEventListener('click', () => {
                activeWeightPeriod = button.getAttribute('data-weight-period') || 'month';
                updateWeightChart();
            });
        });

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                nutritionChart?.resize();
                weightChart?.resize();
            }, 150);
        });

        const pieCanvas = document.getElementById('malnutritionPieChart');
        if (pieCanvas) {
            new Chart(pieCanvas, {
                type: 'doughnut',
                data: {
                    labels: nutritionSplit.labels || [],
                    datasets: [{
                        data: nutritionSplit.values || [],
                        backgroundColor: ['#D0021B', '#50E3C2', '#F5A623', '#9CA3AF'],
                        borderWidth: 0,
                        hoverOffset: 6,
                    }],
                },
                options: {
                    ...chartDefaults,
                    cutout: '62%',
                    plugins: {
                        ...chartDefaults.plugins,
                        legend: { display: false },
                    },
                },
            });
        }
    }
</script>
</body>
</html>
