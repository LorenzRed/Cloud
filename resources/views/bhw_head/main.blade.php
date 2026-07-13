<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - BHW Head Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
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
    }

    .dash-chart-wrap {
        position: relative;
        height: 16rem;
        min-height: 14rem;
    }
    @media (min-width: 640px) {
        .dash-chart-wrap { height: 18rem; }
    }

    .illness-purok-scroll {
        max-height: 20rem;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
    @media (min-width: 1280px) {
        .illness-purok-scroll { max-height: 22rem; }
    }

    .illness-purok-table {
        width: 100%;
        min-width: 40rem;
        table-layout: fixed;
        border-collapse: collapse;
    }

    .illness-purok-table th,
    .illness-purok-table td {
        width: 16.666%;
        padding: 0.875rem 0.75rem;
        font-size: 0.9375rem;
        line-height: 1.35;
        vertical-align: middle;
        word-break: break-word;
    }

    .illness-purok-table th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #FFFFFF;
        box-shadow: 0 1px 0 #EAEAEA;
        font-size: 0.75rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .dark .illness-purok-table th {
        background: #2C2C2C;
        box-shadow: 0 1px 0 #3D3D3D;
    }

    @media (min-width: 640px) {
        .illness-purok-table {
            min-width: 100%;
        }

        .illness-purok-table th,
        .illness-purok-table td {
            padding: 1rem 0.875rem;
            font-size: 1rem;
        }

        .illness-purok-table th {
            font-size: 0.8125rem;
        }
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

    .dash-concern-pill {
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
    .dark .dash-concern-pill {
        border-color: rgba(255,255,255,0.08);
        background: rgba(0,0,0,0.15);
    }

    .mnt-mix-legend {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        width: 100%;
    }

    .mnt-mix-legend__item {
        border-radius: 0.75rem;
        border: 1px solid #EAEAEA;
        background: rgba(244, 244, 244, 0.5);
        padding: 0.625rem 0.75rem;
        width: 100%;
        min-width: 0;
    }

    .dark .mnt-mix-legend__item {
        border-color: #3D3D3D;
        background: rgba(26, 26, 26, 0.5);
    }

    .mnt-mix-legend__item--wide {
        grid-column: 1 / -1;
    }

    button.mnt-mix-legend__item {
        cursor: pointer;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }

    button.mnt-mix-legend__item:hover,
    button.mnt-mix-legend__item:focus-visible {
        border-color: rgba(74, 144, 226, 0.45);
        background: rgba(74, 144, 226, 0.08);
        outline: none;
    }

    @media (min-width: 768px) {
        .mnt-mix-legend {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .mnt-mix-legend__item--wide {
            grid-column: auto;
        }
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2",
                    "secondary": "#50E3C2",
                    "accent-orange": "#F5A623",
                    "accent-red": "#D0021B",
                    "background-light": "#F4F4F4",
                    "background-dark": "#1A1A1A",
                    "text-light": "#4A4A4A",
                    "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF",
                    "card-dark": "#2C2C2C",
                    "border-light": "#EAEAEA",
                    "border-dark": "#3D3D3D",
                },
                fontFamily: {
                    "display": ["Manrope", "sans-serif"]
                },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('partials.app-flash')
@include('sidebars.bhw_head')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-head-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-4 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto space-y-6 pb-8">
                @php
                    $bhwStats = $bhwStats ?? [];
                    $dashboardCharts = $dashboardCharts ?? [];
                    $chartPeriods = $dashboardCharts['chart_periods'] ?? [];
                    $defaultChartPeriod = $dashboardCharts['default_period'] ?? 'month';
                    $monitoringMix = $monitoringMix ?? ['labels' => [], 'values' => [], 'colors' => []];
                    $illnessByPurok = $illnessByPurok ?? [];
                    $purokAlertLevels = $purokAlertLevels ?? [];
                    $purokLevelStyles = [
                        'high' => [
                            'card' => 'border-accent-red/40 bg-accent-red/10 dark:bg-accent-red/15',
                            'badge' => 'bg-accent-red text-white',
                            'text' => 'text-accent-red',
                            'dot' => 'bg-accent-red',
                        ],
                        'moderate' => [
                            'card' => 'border-accent-orange/40 bg-accent-orange/10 dark:bg-accent-orange/15',
                            'badge' => 'bg-accent-orange text-white',
                            'text' => 'text-accent-orange',
                            'dot' => 'bg-accent-orange',
                        ],
                        'low' => [
                            'card' => 'border-primary/30 bg-primary/5 dark:bg-primary/10',
                            'badge' => 'bg-primary text-white',
                            'text' => 'text-primary',
                            'dot' => 'bg-primary',
                        ],
                        'clear' => [
                            'card' => 'border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark',
                            'badge' => 'bg-emerald-600 text-white',
                            'text' => 'text-emerald-700 dark:text-emerald-400',
                            'dot' => 'bg-emerald-500',
                        ],
                    ];

                    $primaryKpis = [
                        ['label' => 'Households covered', 'key' => 'total_households_assigned', 'icon' => 'home', 'tone' => 'bg-primary/10 text-primary'],
                        ['label' => 'Active health concerns', 'key' => 'active_illness_cases', 'icon' => 'cardiology', 'tone' => 'bg-accent-red/10 text-accent-red'],
                        ['label' => 'Transactions this month', 'key' => 'transactions_this_month', 'icon' => 'medication', 'tone' => 'bg-secondary/15 text-teal-700 dark:text-secondary'],
                    ];

                    $secondaryKpis = [
                        ['label' => 'BHW workers', 'key' => '_managed_bhw'],
                        ['label' => 'Puroks covered', 'key' => '_purok_count'],
                        ['label' => 'Households with concerns', 'key' => 'households_with_concern'],
                        ['label' => 'Medicine pcs dispensed', 'key' => 'medicine_pcs_dispensed'],
                        ['label' => 'Upcoming consultations', 'key' => 'upcoming_consultations'],
                        ['label' => 'Active monitoring', 'key' => 'active_monitoring_cases'],
                        ['label' => 'Pregnant monitored', 'key' => 'pregnant_women_monitored'],
                        ['label' => 'Seniors monitored', 'key' => 'senior_citizens_monitored'],
                    ];
                @endphp

                <header class="dash-hero rounded-2xl border border-border-light dark:border-border-dark p-5 md:p-6">
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-primary mb-1">BHW Head</p>
                            <h1 class="text-2xl sm:text-3xl font-bold leading-tight text-text-light dark:text-text-dark">Barangay Dashboard</h1>
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1.5 max-w-xl">
                                Combined overview from all assigned BHWs — residents, medicine transactions, monitoring, and illness across every covered purok.
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            @if (filled($assignedPurokLabel ?? null))
                                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-sm font-semibold bg-white/80 dark:bg-card-dark/80 border border-border-light dark:border-border-dark text-primary shadow-sm max-w-[16rem] sm:max-w-xs">
                                    <span class="material-symbols-outlined text-base shrink-0">location_city</span>
                                    <span class="truncate" title="{{ $assignedPurokLabel }}">{{ $assignedPurokLabel }}</span>
                                </span>
                            @endif
                            @if (filled($purokCoverageLabel ?? null))
                                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-white/70 dark:bg-card-dark/70 border border-border-light dark:border-border-dark text-text-light/80 dark:text-text-dark/80 max-w-[16rem] sm:max-w-xs" title="{{ $purokCoverageLabel }}">
                                    <span class="material-symbols-outlined text-sm shrink-0">home_pin</span>
                                    <span class="truncate">{{ $purokCoverageLabel }}</span>
                                </span>
                            @endif
                            <p class="text-xs text-text-light/50 dark:text-text-dark/50">{{ now()->format('l, M d, Y') }}</p>
                        </div>
                    </div>
                </header>

                <section class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm">
                    <div class="flex flex-col gap-2 mb-4">
                        <div>
                            <h2 class="text-base font-bold">Purok alert levels</h2>
                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Puroks turn red when they carry the most health-concern cases — monitor every covered purok here</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-[11px] sm:text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-accent-red/10 text-accent-red font-semibold"><span class="size-2 rounded-full bg-accent-red"></span>High alert</span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-accent-orange/10 text-accent-orange font-semibold"><span class="size-2 rounded-full bg-accent-orange"></span>Watch</span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-primary/10 text-primary font-semibold"><span class="size-2 rounded-full bg-primary"></span>Low</span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-semibold"><span class="size-2 rounded-full bg-emerald-500"></span>Clear</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                        @forelse ($purokAlertLevels as $alert)
                            @php
                                $level = $alert['level'] ?? 'clear';
                                $styles = $purokLevelStyles[$level] ?? $purokLevelStyles['clear'];
                            @endphp
                            <div class="rounded-xl border px-4 py-3 {{ $styles['card'] }}">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0">
                                        <p class="text-sm sm:text-base font-bold truncate" title="{{ $alert['purok'] }}">{{ $alert['purok'] }}</p>
                                        <p class="text-[11px] sm:text-xs {{ $styles['text'] }} font-semibold mt-0.5">{{ $alert['level_label'] }}</p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wide {{ $styles['badge'] }}">
                                        {{ $level === 'high' ? 'Hot' : ($level === 'moderate' ? 'Watch' : ($level === 'low' ? 'Low' : 'OK')) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-2">
                                    <div>
                                        <p class="text-2xl font-bold tabular-nums {{ $styles['text'] }}">{{ number_format((int) ($alert['cases'] ?? 0)) }}</p>
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">concern cases</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold tabular-nums">{{ number_format((int) ($alert['households'] ?? 0)) }}</p>
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">households</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-black/5 dark:border-white/10 text-center">
                                    <div>
                                        <p class="text-sm font-bold tabular-nums">{{ number_format((int) ($alert['Hypertension'] ?? 0)) }}</p>
                                        <p class="text-[10px] uppercase tracking-wide text-text-light/50">HTN</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold tabular-nums">{{ number_format((int) ($alert['Diabetes'] ?? 0)) }}</p>
                                        <p class="text-[10px] uppercase tracking-wide text-text-light/50">DM</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold tabular-nums">{{ number_format((int) ($alert['Hyperlipidemia'] ?? 0)) }}</p>
                                        <p class="text-[10px] uppercase tracking-wide text-text-light/50">Lipid</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="sm:col-span-2 xl:col-span-3 rounded-xl border border-border-light dark:border-border-dark px-4 py-8 text-center text-sm text-text-light/60 dark:text-text-dark/60">
                                No covered puroks to monitor yet. Assign BHWs to puroks first.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-3">At a glance</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach ($primaryKpis as $card)
                            <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 md:p-5 shadow-sm">
                                <div class="flex items-start justify-between gap-2 mb-3">
                                    <span class="inline-flex size-10 items-center justify-center rounded-xl {{ $card['tone'] }}">
                                        <span class="material-symbols-outlined text-xl">{{ $card['icon'] }}</span>
                                    </span>
                                </div>
                                <p class="text-2xl md:text-3xl font-bold tabular-nums">{{ number_format((int) ($bhwStats[$card['key']] ?? 0)) }}</p>
                                <p class="text-xs sm:text-sm text-text-light/60 dark:text-text-dark/60 mt-1 leading-snug">{{ $card['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-3">Community coverage</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 dash-kpi-grid-mobile-span">
                        @foreach ($secondaryKpis as $card)
                            @php
                                $kpiValue = match ($card['key']) {
                                    '_managed_bhw' => (int) ($managedBhwCount ?? 0),
                                    '_purok_count' => (int) ($purokCount ?? 0),
                                    default => (int) ($bhwStats[$card['key']] ?? 0),
                                };
                            @endphp
                            <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-3">
                                <p class="text-xl font-bold tabular-nums">{{ number_format($kpiValue) }}</p>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 leading-snug">{{ $card['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm xl:col-span-2">
                        <div class="flex flex-col gap-3 mb-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="text-base font-bold">Medicine transaction trend</h2>
                                    <p id="transactionTrendSubtitle" class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Dispensing records with medicines in selected period</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5 shrink-0" role="group" aria-label="Transaction trend period filter">
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-trend-period="week" aria-pressed="false">This week</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-trend-period="month" aria-pressed="true">This month</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-trend-period="year" aria-pressed="false">This year</button>
                                </div>
                            </div>
                            <p id="transactionRangeLabel" class="text-[11px] font-medium text-primary/80 dark:text-primary/90"></p>
                        </div>
                        <div class="dash-chart-wrap">
                            <canvas id="monthlyIllnessTrendChart" aria-label="Medicine transaction trend"></canvas>
                        </div>
                        <p id="transactionTrendFootnote" class="text-[11px] text-text-light/50 dark:text-text-dark/50 mt-3 text-center sm:text-left"></p>
                    </div>

                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm">
                        <div class="flex flex-col gap-3 mb-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="text-base font-bold">Common health concerns</h2>
                                    <p id="concernSubtitle" class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Inferred from medicines given in selected period</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5 shrink-0" role="group" aria-label="Health concern period filter">
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-concern-period="week" aria-pressed="false">This week</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-concern-period="month" aria-pressed="true">This month</button>
                                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-concern-period="year" aria-pressed="false">This year</button>
                                </div>
                            </div>
                            <p id="concernRangeLabel" class="text-[11px] font-medium text-primary/80 dark:text-primary/90"></p>
                        </div>
                        <div class="dash-chart-wrap">
                            <canvas id="commonIllnessesBarChart" aria-label="Common health concerns bar chart"></canvas>
                        </div>
                        <div id="concernLegendPills" class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-4"></div>
                    </div>

                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm">
                        <div class="mb-4">
                            <h2 class="text-base font-bold">Health concern mix</h2>
                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Share of hypertension, diabetes, and cholesterol cases in selected period</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                            <div class="h-56 sm:h-64 max-w-xs mx-auto w-full">
                                <canvas id="healthConcernMixChart" aria-label="Health concern mix pie chart"></canvas>
                            </div>
                            <div id="concernMixLegend" class="grid grid-cols-1 gap-3"></div>
                        </div>
                    </div>

                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm xl:col-span-2">
                        <div class="mb-4">
                            <h2 class="text-base font-bold">Household monitoring mix</h2>
                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Active pregnant, senior, and general monitoring cases</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                            <div class="h-56 sm:h-64 max-w-xs mx-auto w-full">
                                <canvas id="monitoringMixChart" aria-label="Monitoring mix doughnut chart"></canvas>
                            </div>
                            <div class="mnt-mix-legend">
                                @foreach ([
                                    ['label' => 'Pregnant', 'key' => 'Pregnant', 'tone' => 'bg-pink-500', 'clickable' => true],
                                    ['label' => 'Senior', 'key' => 'Senior', 'tone' => 'bg-violet-500', 'clickable' => true],
                                    ['label' => 'General', 'key' => 'General', 'tone' => 'bg-primary', 'clickable' => false],
                                ] as $index => $item)
                                    @php
                                        $value = (int) (($monitoringMix['values'] ?? [])[$index] ?? 0);
                                    @endphp
                                    @if ($item['clickable'])
                                        <button
                                            type="button"
                                            class="mnt-mix-legend__item {{ $item['key'] === 'General' ? 'mnt-mix-legend__item--wide' : '' }} text-left"
                                            data-monitoring-type="{{ $item['key'] }}"
                                            aria-label="View {{ strtolower($item['label']) }} members"
                                        >
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="size-2.5 rounded-full {{ $item['tone'] }}"></span>
                                                <span class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $item['label'] }}</span>
                                                <span class="material-symbols-outlined text-sm text-primary/70 ml-auto">chevron_right</span>
                                            </div>
                                            <p class="text-lg font-bold tabular-nums">{{ number_format($value) }}</p>
                                        </button>
                                    @else
                                        <div class="mnt-mix-legend__item mnt-mix-legend__item--wide">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="size-2.5 rounded-full {{ $item['tone'] }}"></span>
                                                <span class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $item['label'] }}</span>
                                            </div>
                                            <p class="text-lg font-bold tabular-nums">{{ number_format($value) }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm xl:col-span-2">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                            <div>
                                <h2 class="text-base font-bold">Illness by purok</h2>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Health concerns inferred from medicines across all BHW-covered puroks</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
                            <div class="dash-chart-wrap xl:h-full xl:min-h-[22rem]">
                                <canvas id="illnessByPurokChart" aria-label="Illness cases by purok"></canvas>
                            </div>
                            <div class="illness-purok-scroll rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark">
                                <table class="illness-purok-table">
                                    <thead>
                                        <tr class="border-b border-border-light dark:border-border-dark text-text-light/60 dark:text-text-dark/60">
                                            <th class="font-semibold text-left">Purok</th>
                                            <th class="font-semibold text-center">HH</th>
                                            <th class="font-semibold text-center">Cases</th>
                                            <th class="font-semibold text-center">HTN</th>
                                            <th class="font-semibold text-center">DM</th>
                                            <th class="font-semibold text-center">Lipid</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($purokAlertLevels as $row)
                                            @php
                                                $rowLevel = $row['level'] ?? 'clear';
                                                $rowTone = match ($rowLevel) {
                                                    'high' => 'bg-accent-red/10 text-accent-red',
                                                    'moderate' => 'bg-accent-orange/10 text-accent-orange',
                                                    'low' => 'bg-primary/5',
                                                    default => '',
                                                };
                                            @endphp
                                            <tr class="border-b border-border-light dark:border-border-dark {{ $rowTone }}">
                                                <td class="font-semibold text-left">
                                                    <span class="inline-flex items-center gap-2">
                                                        <span class="size-2 rounded-full shrink-0 {{ ($purokLevelStyles[$rowLevel] ?? $purokLevelStyles['clear'])['dot'] }}"></span>
                                                        {{ $row['purok'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center tabular-nums font-semibold">{{ number_format((int) ($row['households'] ?? 0)) }}</td>
                                                <td class="text-center tabular-nums font-bold">{{ number_format((int) ($row['cases'] ?? 0)) }}</td>
                                                <td class="text-center tabular-nums">{{ number_format((int) ($row['Hypertension'] ?? 0)) }}</td>
                                                <td class="text-center tabular-nums">{{ number_format((int) ($row['Diabetes'] ?? 0)) }}</td>
                                                <td class="text-center tabular-nums">{{ number_format((int) ($row['Hyperlipidemia'] ?? 0)) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-10 text-center text-text-light/60 dark:text-text-dark/60">No illness cases recorded across puroks yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <div id="monitoringMembersModal" class="hidden fixed inset-0 z-[120] flex items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4" role="dialog" aria-modal="true" aria-labelledby="monitoringMembersTitle">
        <div class="w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-xl max-h-[80dvh] flex flex-col overflow-hidden">
            <div class="flex items-start justify-between gap-3 px-4 py-4 border-b border-border-light dark:border-border-dark shrink-0">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary/80">Monitoring members</p>
                    <h3 id="monitoringMembersTitle" class="text-lg font-bold mt-0.5">Pregnant</h3>
                </div>
                <button type="button" id="closeMonitoringMembersModal" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            <div id="monitoringMembersList" class="overflow-y-auto px-4 py-3 space-y-2"></div>
        </div>
    </div>

    @include('partials.bhw-head-mobile-nav')
</div>

<script>
    const chartPeriods = @json($chartPeriods);
    const defaultChartPeriod = @json($defaultChartPeriod);
    const monitoringMix = @json($monitoringMix);
    const illnessByPurok = @json($illnessByPurok);

    const trendSubtitleByPeriod = {
        week: 'Daily medicine transactions this week',
        month: 'Weekly medicine transactions this month',
        year: 'Monthly medicine transactions this year',
    };

    const concernSubtitleByPeriod = {
        week: 'Inferred from medicines given this week',
        month: 'Inferred from medicines given this month',
        year: 'Inferred from medicines given this year',
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

    let trendChart = null;
    let concernBarChart = null;
    let concernMixChart = null;
    let activeTrendPeriod = defaultChartPeriod || 'month';
    let activeConcernPeriod = defaultChartPeriod || 'month';

    function periodBucket(period) {
        return chartPeriods[period] || {};
    }

    function rangeLabelFor(period) {
        return periodBucket(period).range_label || '';
    }

    function trendDataFor(period) {
        return periodBucket(period).transaction_trend || { labels: [], values: [] };
    }

    function concernDataFor(period) {
        return periodBucket(period).health_concerns || { labels: [], values: [], colors: [], transaction_count: 0 };
    }

    function setPeriodButtons(groupAttr, activePeriod) {
        document.querySelectorAll(`[${groupAttr}]`).forEach((button) => {
            const isActive = button.getAttribute(groupAttr) === activePeriod;
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function renderConcernPills(data) {
        const container = document.getElementById('concernLegendPills');
        if (!container) return;

        const labels = data.labels || [];
        const values = data.values || [];
        const colors = data.colors || ['#BE123C', '#0EA5E9', '#F59E0B'];

        container.innerHTML = labels.map((label, index) => `
            <div class="dash-concern-pill">
                <span class="flex items-center gap-2 min-w-0">
                    <span class="size-2.5 rounded-full shrink-0" style="background:${colors[index] || '#9CA3AF'}"></span>
                    <span class="truncate text-text-light/70 dark:text-text-dark/70">${label}</span>
                </span>
                <span class="font-bold tabular-nums shrink-0">${Number(values[index] || 0)}</span>
            </div>
        `).join('');
    }

    function renderConcernMixLegend(data) {
        const container = document.getElementById('concernMixLegend');
        if (!container) return;

        const labels = data.labels || [];
        const values = data.values || [];
        const toneClass = {
            Hypertension: 'bg-rose-600',
            Diabetes: 'bg-sky-500',
            Hyperlipidemia: 'bg-amber-500',
        };

        container.innerHTML = labels.map((label, index) => `
            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light/50 dark:bg-background-dark/50 px-3 py-2.5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="size-2.5 rounded-full ${toneClass[label] || 'bg-gray-400'}"></span>
                    <span class="text-xs text-text-light/60 dark:text-text-dark/60">${label}</span>
                </div>
                <p class="text-lg font-bold tabular-nums">${Number(values[index] || 0)}</p>
            </div>
        `).join('');
    }

    function updateTrendChart() {
        const data = trendDataFor(activeTrendPeriod);
        const labels = data.labels || [];
        const values = data.values || [];
        const total = values.reduce((sum, n) => sum + Number(n || 0), 0);

        const rangeEl = document.getElementById('transactionRangeLabel');
        const subtitleEl = document.getElementById('transactionTrendSubtitle');
        const footnoteEl = document.getElementById('transactionTrendFootnote');

        if (rangeEl) rangeEl.textContent = rangeLabelFor(activeTrendPeriod);
        if (subtitleEl) subtitleEl.textContent = trendSubtitleByPeriod[activeTrendPeriod] || trendSubtitleByPeriod.month;
        if (footnoteEl) {
            footnoteEl.textContent = total
                ? `${total} medicine transaction${total === 1 ? '' : 's'} in this period`
                : 'No medicine transactions recorded for this period yet';
        }

        setPeriodButtons('data-trend-period', activeTrendPeriod);

        if (!trendChart) return;

        trendChart.data.labels = labels;
        trendChart.data.datasets[0].data = values;
        trendChart.update();
    }

    function updateConcernCharts() {
        const data = concernDataFor(activeConcernPeriod);
        const labels = data.labels || [];
        const values = data.values || [];
        const colors = data.colors || ['#BE123C', '#0EA5E9', '#F59E0B'];
        const txCount = data.transaction_count ?? 0;

        const rangeEl = document.getElementById('concernRangeLabel');
        const subtitleEl = document.getElementById('concernSubtitle');

        if (rangeEl) {
            rangeEl.textContent = `${rangeLabelFor(activeConcernPeriod)} · ${txCount} transaction${txCount === 1 ? '' : 's'}`;
        }
        if (subtitleEl) {
            subtitleEl.textContent = concernSubtitleByPeriod[activeConcernPeriod] || concernSubtitleByPeriod.month;
        }

        renderConcernPills(data);
        renderConcernMixLegend(data);
        setPeriodButtons('data-concern-period', activeConcernPeriod);

        if (concernBarChart) {
            concernBarChart.data.labels = labels;
            concernBarChart.data.datasets[0].data = values;
            concernBarChart.data.datasets[0].backgroundColor = colors;
            concernBarChart.update();
        }

        if (concernMixChart) {
            concernMixChart.data.labels = labels;
            concernMixChart.data.datasets[0].data = values;
            concernMixChart.data.datasets[0].backgroundColor = colors;
            concernMixChart.update();
        }
    }

    if (typeof Chart !== 'undefined') {
        const lineCanvas = document.getElementById('monthlyIllnessTrendChart');
        if (lineCanvas) {
            const initialTrend = trendDataFor(activeTrendPeriod);
            trendChart = new Chart(lineCanvas, {
                type: 'line',
                data: {
                    labels: initialTrend.labels || [],
                    datasets: [{
                        label: 'Transactions with medicine',
                        data: initialTrend.values || [],
                        borderColor: '#4A90E2',
                        backgroundColor: 'rgba(74, 144, 226, 0.15)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#4A90E2',
                    }],
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: tickColor(), font: { family: 'Manrope', size: 11 } },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: tickColor(),
                                font: { family: 'Manrope', size: 11 },
                            },
                            grid: { color: gridColor() },
                        },
                    },
                },
            });
        }

        const barCanvas = document.getElementById('commonIllnessesBarChart');
        if (barCanvas) {
            const initialConcern = concernDataFor(activeConcernPeriod);
            concernBarChart = new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: initialConcern.labels || [],
                    datasets: [{
                        label: 'Cases from transactions',
                        data: initialConcern.values || [],
                        backgroundColor: initialConcern.colors || ['#BE123C', '#0EA5E9', '#F59E0B'],
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 56,
                    }],
                },
                options: {
                    ...chartDefaults,
                    plugins: {
                        ...chartDefaults.plugins,
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: tickColor(), font: { family: 'Manrope', size: 11 } },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: tickColor(),
                                font: { family: 'Manrope', size: 11 },
                            },
                            grid: { color: gridColor() },
                        },
                    },
                },
            });
        }

        const concernCanvas = document.getElementById('healthConcernMixChart');
        if (concernCanvas) {
            const initialConcern = concernDataFor(activeConcernPeriod);
            concernMixChart = new Chart(concernCanvas, {
                type: 'doughnut',
                data: {
                    labels: initialConcern.labels || [],
                    datasets: [{
                        data: initialConcern.values || [],
                        backgroundColor: initialConcern.colors || ['#BE123C', '#0EA5E9', '#F59E0B'],
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

        const monitoringCanvas = document.getElementById('monitoringMixChart');
        if (monitoringCanvas) {
            const monitoringChart = new Chart(monitoringCanvas, {
                type: 'doughnut',
                data: {
                    labels: monitoringMix.labels || [],
                    datasets: [{
                        data: monitoringMix.values || [],
                        backgroundColor: monitoringMix.colors || ['#EC4899', '#8B5CF6', '#4A90E2'],
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
                    onClick: (_event, elements) => {
                        if (!elements.length) return;
                        const label = monitoringMix.labels?.[elements[0].index];
                        if (label === 'Pregnant' || label === 'Senior') {
                            openMonitoringMembersModal(label);
                        }
                    },
                },
            });

            monitoringCanvas.style.cursor = 'pointer';
        }

        const illnessPurokCanvas = document.getElementById('illnessByPurokChart');
        if (illnessPurokCanvas) {
            new Chart(illnessPurokCanvas, {
                type: 'bar',
                data: {
                    labels: (illnessByPurok || []).map((row) => row.purok),
                    datasets: [
                        {
                            label: 'Hypertension',
                            data: (illnessByPurok || []).map((row) => row.Hypertension || 0),
                            backgroundColor: '#BE123C',
                        },
                        {
                            label: 'Diabetes',
                            data: (illnessByPurok || []).map((row) => row.Diabetes || 0),
                            backgroundColor: '#0EA5E9',
                        },
                        {
                            label: 'Hyperlipidemia',
                            data: (illnessByPurok || []).map((row) => row.Hyperlipidemia || 0),
                            backgroundColor: '#F59E0B',
                        },
                    ],
                },
                options: {
                    ...chartDefaults,
                    scales: {
                        x: {
                            stacked: true,
                            grid: { color: gridColor() },
                            ticks: { color: tickColor(), maxRotation: 45, minRotation: 0 },
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: { color: gridColor() },
                            ticks: { color: tickColor(), precision: 0 },
                        },
                    },
                    plugins: {
                        ...chartDefaults.plugins,
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: { color: tickColor(), boxWidth: 12, padding: 12 },
                        },
                    },
                },
            });
        }

        updateTrendChart();
        updateConcernCharts();
    }

    document.querySelectorAll('[data-trend-period]').forEach((button) => {
        button.addEventListener('click', () => {
            activeTrendPeriod = button.getAttribute('data-trend-period') || 'month';
            updateTrendChart();
        });
    });

    document.querySelectorAll('[data-concern-period]').forEach((button) => {
        button.addEventListener('click', () => {
            activeConcernPeriod = button.getAttribute('data-concern-period') || 'month';
            updateConcernCharts();
        });
    });

    const monitoringMembersModal = document.getElementById('monitoringMembersModal');
    const monitoringMembersTitle = document.getElementById('monitoringMembersTitle');
    const monitoringMembersList = document.getElementById('monitoringMembersList');
    const closeMonitoringMembersModalBtn = document.getElementById('closeMonitoringMembersModal');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function openMonitoringMembersModal(type) {
        const members = (monitoringMix.members && monitoringMix.members[type]) || [];

        if (monitoringMembersTitle) {
            monitoringMembersTitle.textContent = type;
        }

        if (monitoringMembersList) {
            if (!members.length) {
                monitoringMembersList.innerHTML = `
                    <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light/50 dark:bg-background-dark/50 px-3 py-6 text-sm text-center text-text-light/60 dark:text-text-dark/60">
                        No ${escapeHtml(String(type).toLowerCase())} members monitored yet.
                    </div>
                `;
            } else {
                monitoringMembersList.innerHTML = members.map((member) => `
                    <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light/50 dark:bg-background-dark/50 px-3 py-3">
                        <p class="text-sm font-semibold break-words">${escapeHtml(member.name || '—')}</p>
                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Family no. <span class="font-semibold text-text-light dark:text-text-dark">${escapeHtml(member.family_number || 'Not assigned')}</span></p>
                        ${member.purok && member.purok !== '—' ? `<p class="text-xs text-text-light/50 dark:text-text-dark/50 mt-0.5 truncate">Purok: ${escapeHtml(member.purok)}</p>` : ''}
                        ${member.household && member.household !== '—' ? `<p class="text-xs text-text-light/50 dark:text-text-dark/50 mt-0.5 truncate">Household: ${escapeHtml(member.household)}</p>` : ''}
                    </div>
                `).join('');
            }
        }

        monitoringMembersModal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMonitoringMembersModal() {
        monitoringMembersModal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('[data-monitoring-type]').forEach((button) => {
        button.addEventListener('click', () => {
            const type = button.getAttribute('data-monitoring-type');
            if (type) {
                openMonitoringMembersModal(type);
            }
        });
    });

    closeMonitoringMembersModalBtn?.addEventListener('click', closeMonitoringMembersModal);
    monitoringMembersModal?.addEventListener('click', (event) => {
        if (event.target === monitoringMembersModal) {
            closeMonitoringMembersModal();
        }
    });
</script>
</body>
</html>
