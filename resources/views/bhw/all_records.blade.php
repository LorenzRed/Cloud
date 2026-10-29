<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - All Records</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include($shellStylesPartial ?? 'partials.bhw-shell-styles')
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    .records-page {
        width: 100%;
        max-width: 48rem;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    @media (min-width: 1024px) {
        .records-page.records-page--wide-desktop {
            max-width: none;
            margin: 0;
        }
    }

    .records-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-height: 2.35rem;
        padding: 0.4rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid #EAEAEA;
        background: #fff;
        color: #4A4A4A;
        transition: background-color 0.12s ease, color 0.12s ease, border-color 0.12s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .dark .records-chip {
        background: #2C2C2C;
        border-color: #3D3D3D;
        color: #E0E0E0;
    }
    .records-chip:hover {
        background: rgba(74, 144, 226, 0.08);
        border-color: rgba(74, 144, 226, 0.35);
        color: #4A90E2;
    }
    .records-chip.is-active {
        background: #4A90E2;
        border-color: #4A90E2;
        color: #fff;
        box-shadow: 0 1px 2px rgb(74 144 226 / 0.28);
    }
    .records-chip-scroll {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.1rem;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .records-chip-scroll::-webkit-scrollbar { display: none; }

    .records-stat {
        border-radius: 1rem;
        border: 1px solid #EAEAEA;
        background: #fff;
        padding: 0.9rem 1rem;
        min-width: 0;
    }
    .dark .records-stat {
        border-color: #3D3D3D;
        background: #2C2C2C;
    }
    .records-stat--tx {
        background: linear-gradient(180deg, rgba(74, 144, 226, 0.1) 0%, #fff 55%);
        border-color: rgba(74, 144, 226, 0.28);
    }
    .dark .records-stat--tx {
        background: linear-gradient(180deg, rgba(74, 144, 226, 0.18) 0%, #2C2C2C 55%);
        border-color: rgba(74, 144, 226, 0.4);
    }
    .records-stat--mnt {
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.1) 0%, #fff 55%);
        border-color: rgba(34, 197, 94, 0.28);
    }
    .dark .records-stat--mnt {
        background: linear-gradient(180deg, rgba(34, 197, 94, 0.16) 0%, #2C2C2C 55%);
        border-color: rgba(34, 197, 94, 0.4);
    }

    .records-filter-card {
        border-radius: 1rem;
        border: 1px solid #EAEAEA;
        background: #fff;
        padding: 0.95rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
    }
    .dark .records-filter-card {
        border-color: #3D3D3D;
        background: #2C2C2C;
        box-shadow: none;
    }

    .records-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
        min-width: 0;
    }

    .record-card {
        width: 100%;
        min-width: 0;
        border-radius: 1rem;
        overflow: hidden;
        border: 1px solid #EAEAEA;
        background: #fff;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
    }
    .dark .record-card {
        border-color: #3D3D3D;
        background: #2C2C2C;
        box-shadow: none;
    }
    .record-card--transaction {
        border-color: rgba(74, 144, 226, 0.35);
        box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.06), 0 4px 12px rgb(74 144 226 / 0.08);
    }
    .dark .record-card--transaction {
        border-color: rgba(74, 144, 226, 0.45);
        box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.12);
    }
    .record-card--maintenance {
        border-color: rgba(34, 197, 94, 0.32);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.06), 0 4px 12px rgb(34 197 94 / 0.08);
    }
    .dark .record-card--maintenance {
        border-color: rgba(34, 197, 94, 0.4);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.12);
    }
    .record-card--maintenance.is-completed {
        border-color: rgba(107, 114, 128, 0.35);
        box-shadow: 0 0 0 1px rgba(107, 114, 128, 0.06), 0 4px 12px rgb(107 114 128 / 0.08);
    }
    .dark .record-card--maintenance.is-completed {
        border-color: rgba(156, 163, 175, 0.35);
        box-shadow: 0 0 0 1px rgba(156, 163, 175, 0.12);
    }

    .record-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.55rem 0.9rem;
        font-size: 0.75rem;
        font-weight: 750;
    }
    .record-card--transaction .record-banner {
        background: rgba(74, 144, 226, 0.1);
        color: #2f6fb8;
    }
    .dark .record-card--transaction .record-banner {
        background: rgba(74, 144, 226, 0.18);
        color: #93c5fd;
    }
    .record-card--maintenance .record-banner {
        background: rgba(34, 197, 94, 0.14);
        color: #15803d;
    }
    .dark .record-card--maintenance .record-banner {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }
    .record-card--maintenance.is-completed .record-banner {
        background: rgba(107, 114, 128, 0.14);
        color: #4b5563;
    }
    .dark .record-card--maintenance.is-completed .record-banner {
        background: rgba(156, 163, 175, 0.18);
        color: #d1d5db;
    }
    .record-banner .material-symbols-outlined {
        font-size: 1.05rem;
        font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 20;
    }

    .record-toggle {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.85rem 0.9rem 0.95rem;
        min-height: 11.5rem;
        -webkit-tap-highlight-color: transparent;
        cursor: pointer;
    }
    .record-toggle:focus-visible {
        outline: 2px solid rgba(74, 144, 226, 0.45);
        outline-offset: -2px;
    }

    .record-main {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        min-height: 100%;
    }
    .record-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.9rem;
        flex-shrink: 0;
    }
    .record-card--transaction .record-icon {
        background: rgba(74, 144, 226, 0.12);
        color: #2f6fb8;
    }
    .dark .record-card--transaction .record-icon {
        background: rgba(74, 144, 226, 0.2);
        color: #93c5fd;
    }
    .record-card--maintenance .record-icon {
        background: rgba(34, 197, 94, 0.14);
        color: #15803d;
    }
    .dark .record-card--maintenance .record-icon {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }
    .record-card--maintenance.is-completed .record-icon {
        background: rgba(107, 114, 128, 0.14);
        color: #4b5563;
    }
    .dark .record-card--maintenance.is-completed .record-icon {
        background: rgba(156, 163, 175, 0.18);
        color: #d1d5db;
    }

    .record-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.25;
        color: #2f2f2f;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dark .record-title { color: #f3f4f6; }
    .record-subtitle {
        margin: 0.25rem 0 0;
        font-size: 0.8125rem;
        line-height: 1.35;
        color: rgba(74, 74, 74, 0.7);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dark .record-subtitle { color: rgba(224, 224, 224, 0.7); }
    .record-date {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 750;
        color: rgba(74, 74, 74, 0.72);
        white-space: nowrap;
    }
    .dark .record-date { color: rgba(224, 224, 224, 0.72); }

    .record-chevron {
        margin-left: auto;
        color: rgba(74, 74, 74, 0.4);
        transition: transform 0.18s ease;
        flex-shrink: 0;
    }
    .dark .record-chevron { color: rgba(224, 224, 224, 0.4); }
    .record-toggle[aria-expanded="true"] .record-chevron {
        transform: rotate(180deg);
    }

    .record-chips {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.45rem;
        margin-top: 0.75rem;
        min-height: 5.6rem;
    }
    @media (min-width: 480px) {
        .record-chips {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            min-height: 2.7rem;
        }
    }
    .record-chip-pill {
        min-width: 0;
        min-height: 2.55rem;
        border-radius: 0.7rem;
        border: 1px solid #EAEAEA;
        background: #f7f9fc;
        padding: 0.45rem 0.55rem;
    }
    .dark .record-chip-pill {
        border-color: #3D3D3D;
        background: #242424;
    }
    .record-card--transaction .record-chip-pill {
        background: rgba(74, 144, 226, 0.06);
        border-color: rgba(74, 144, 226, 0.18);
    }
    .dark .record-card--transaction .record-chip-pill {
        background: rgba(74, 144, 226, 0.1);
        border-color: rgba(74, 144, 226, 0.28);
    }
    .record-card--maintenance .record-chip-pill {
        background: rgba(34, 197, 94, 0.06);
        border-color: rgba(34, 197, 94, 0.18);
    }
    .dark .record-card--maintenance .record-chip-pill {
        background: rgba(34, 197, 94, 0.1);
        border-color: rgba(34, 197, 94, 0.28);
    }
    .record-chip-pill.is-empty {
        opacity: 0.45;
    }
    .record-chip-pill span { display: block; }
    .record-chip-pill__label {
        font-size: 0.625rem;
        font-weight: 750;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
        margin-bottom: 0.1rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dark .record-chip-pill__label { color: rgba(224, 224, 224, 0.55); }
    .record-chip-pill__value {
        font-size: 0.875rem;
        font-weight: 750;
        line-height: 1.25;
        color: #2f2f2f;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dark .record-chip-pill__value { color: #f3f4f6; }

    .record-details {
        display: none;
        padding: 0 0.9rem 0.95rem;
    }
    .record-details.is-open { display: block; }
    .record-details-panel {
        border-radius: 0.85rem;
        border: 1px solid #EAEAEA;
        background: #f7f9fc;
        padding: 0.85rem;
    }
    .dark .record-details-panel {
        border-color: #3D3D3D;
        background: #242424;
    }
    .record-details-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.55rem;
    }
    @media (min-width: 640px) {
        .record-details-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    .record-detail-item { min-width: 0; }
    .record-detail-item--full { grid-column: 1 / -1; }
    .record-detail-label {
        display: block;
        font-size: 0.6875rem;
        font-weight: 750;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
        margin-bottom: 0.15rem;
    }
    .dark .record-detail-label { color: rgba(224, 224, 224, 0.55); }
    .record-detail-value {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 650;
        line-height: 1.4;
        color: #2f2f2f;
        word-break: break-word;
    }
    .dark .record-detail-value { color: #f3f4f6; }

    .records-empty {
        border-radius: 1rem;
        border: 1px dashed #EAEAEA;
        background: #fff;
        padding: 2.5rem 1.25rem;
        text-align: center;
    }
    .dark .records-empty {
        border-color: #3D3D3D;
        background: #2C2C2C;
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
                fontFamily: { "display": ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include($sidebarPartial ?? 'sidebars.bhw')

@php
    $records = $records ?? collect();
    $activeType = $activeType ?? 'all';
    $activePeriod = $activePeriod ?? 'all';
    $periodLabel = $periodLabel ?? 'All time';
    $fromDate = $fromDate ?? null;
    $toDate = $toDate ?? null;
    $transactionCount = (int) ($transactionCount ?? 0);
    $maintenanceCount = (int) ($maintenanceCount ?? 0);
    $totalCount = $records->count();
    $recordsRouteName = $recordsRouteName ?? 'bhw.all-records';
    $backRouteName = $backRouteName ?? 'bhw.my-profile';
    $mobileNavPartial = $mobileNavPartial ?? 'partials.bhw-mobile-nav';
    $mobileTopbarPartial = $mobileTopbarPartial ?? 'partials.bhw-head-mobile-topbar';
    $contentElementId = $contentElementId ?? 'bhwContent';
    $showMobileTopbar = (bool) ($showMobileTopbar ?? false);
    $wideDesktop = (bool) ($wideDesktop ?? false);
    $purokOptions = collect($purokOptions ?? []);
    $activePurokId = (string) ($activePurokId ?? 'all');
    $hasPurokFilter = $purokOptions->isNotEmpty();
    $activePurokLabel = 'All puroks';
    if ($hasPurokFilter && $activePurokId !== 'all') {
        $matchedPurok = $purokOptions->first(function ($purok) use ($activePurokId) {
            $id = is_array($purok) ? ($purok['id'] ?? null) : ($purok->id ?? null);

            return (string) $id === (string) $activePurokId;
        });
        if ($matchedPurok) {
            $activePurokLabel = is_array($matchedPurok)
                ? (string) ($matchedPurok['name'] ?? 'Purok')
                : (string) ($matchedPurok->name ?? 'Purok');
        }
    }

    $typeFilters = [
        ['key' => 'all', 'label' => 'All', 'icon' => 'list_alt'],
        ['key' => 'transactions', 'label' => 'Transactions', 'icon' => 'medication'],
        ['key' => 'maintenance', 'label' => 'Maintenance', 'icon' => 'monitor_heart'],
    ];

    $periodFilters = [
        ['key' => 'this_week', 'label' => 'This week', 'icon' => 'date_range'],
        ['key' => 'last_week', 'label' => 'Last week', 'icon' => 'history'],
        ['key' => 'this_month', 'label' => 'This month', 'icon' => 'calendar_month'],
        ['key' => 'last_month', 'label' => 'Last month', 'icon' => 'event_repeat'],
        ['key' => 'all', 'label' => 'All time', 'icon' => 'all_inclusive'],
        ['key' => 'custom', 'label' => 'Custom', 'icon' => 'edit_calendar'],
    ];

    $filterQuery = function (array $overrides = []) use ($activeType, $activePeriod, $activePurokId, $fromDate, $toDate, $hasPurokFilter) {
        $query = [
            'type' => $overrides['type'] ?? $activeType,
            'period' => $overrides['period'] ?? $activePeriod,
        ];

        if ($hasPurokFilter) {
            $purok = array_key_exists('purok', $overrides) ? $overrides['purok'] : $activePurokId;
            if ((string) $purok !== 'all' && filled($purok)) {
                $query['purok'] = $purok;
            }
        }

        if (($query['period'] ?? '') === 'custom') {
            $query['from_date'] = array_key_exists('from_date', $overrides) ? $overrides['from_date'] : $fromDate;
            $query['to_date'] = array_key_exists('to_date', $overrides) ? $overrides['to_date'] : $toDate;
        }

        return array_filter($query, fn ($value) => filled($value));
    };
@endphp

<div id="{{ $contentElementId }}" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @if ($showMobileTopbar)
        @include($mobileTopbarPartial)
    @endif
    <main class="flex-1 flex flex-col min-h-0 px-3 sm:px-5 md:px-6 lg:px-8 pt-4 lg:pt-6 pb-24 lg:pb-6 overflow-y-auto">
        <div class="records-page{{ $wideDesktop ? ' records-page--wide-desktop' : '' }}">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <a href="{{ route($backRouteName) }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark hover:bg-primary/10 transition-colors shrink-0" aria-label="Back to profile">
                    <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                </a>
                <div class="min-w-0 flex-1">
                    <h1 class="text-[1.45rem] sm:text-2xl font-extrabold tracking-tight text-text-light dark:text-text-dark truncate">All Records</h1>
                    <p class="text-[13px] text-text-light/60 dark:text-text-dark/60 mt-0.5 leading-snug">
                        {{ $totalCount }} result{{ $totalCount === 1 ? '' : 's' }} · {{ $periodLabel }}
                        @if ($hasPurokFilter)
                            · {{ $activePurokId === 'all' ? 'All puroks' : $activePurokLabel }}
                        @endif
                        @if ($activePeriod === 'custom' && (filled($fromDate) || filled($toDate)))
                            · {{ filled($fromDate) ? \Illuminate\Support\Carbon::parse($fromDate)->format('M d, Y') : '…' }}
                            –
                            {{ filled($toDate) ? \Illuminate\Support\Carbon::parse($toDate)->format('M d, Y') : '…' }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                <div class="records-stat records-stat--tx">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-primary/80">
                        <span class="material-symbols-outlined text-[16px]">medication</span>
                        Transactions
                    </div>
                    <p class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-primary leading-none">{{ number_format($transactionCount) }}</p>
                </div>
                <div class="records-stat records-stat--mnt">
                    <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        <span class="material-symbols-outlined text-[16px]">monitor_heart</span>
                        Maintenance
                    </div>
                    <p class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-emerald-700 dark:text-emerald-300 leading-none">{{ number_format($maintenanceCount) }}</p>
                </div>
            </div>

            <section class="records-filter-card space-y-3">
                @if ($hasPurokFilter)
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-text-light/55 dark:text-text-dark/55 mb-2">Purok</p>
                        <div class="records-chip-scroll">
                            <a
                                href="{{ route($recordsRouteName, $filterQuery(['purok' => 'all'])) }}"
                                class="records-chip {{ $activePurokId === 'all' ? 'is-active' : '' }}"
                            >
                                <span class="material-symbols-outlined text-[16px]">map</span>
                                All puroks
                            </a>
                            @foreach ($purokOptions as $purok)
                                @php
                                    $purokId = (string) (is_array($purok) ? ($purok['id'] ?? '') : ($purok->id ?? ''));
                                    $purokName = is_array($purok) ? ($purok['name'] ?? 'Purok') : ($purok->name ?? 'Purok');
                                @endphp
                                <a
                                    href="{{ route($recordsRouteName, $filterQuery(['purok' => $purokId])) }}"
                                    class="records-chip {{ $activePurokId === $purokId ? 'is-active' : '' }}"
                                >
                                    <span class="material-symbols-outlined text-[16px]">home_pin</span>
                                    {{ $purokName }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-text-light/55 dark:text-text-dark/55 mb-2">Record type</p>
                    <div class="records-chip-scroll">
                        @foreach ($typeFilters as $filter)
                            <a
                                href="{{ route($recordsRouteName, $filterQuery(['type' => $filter['key']])) }}"
                                class="records-chip {{ $activeType === $filter['key'] ? 'is-active' : '' }}"
                            >
                                <span class="material-symbols-outlined text-[16px]">{{ $filter['icon'] }}</span>
                                {{ $filter['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wide text-text-light/55 dark:text-text-dark/55 mb-2">Date range</p>
                    <div class="records-chip-scroll">
                        @foreach ($periodFilters as $filter)
                            <a
                                href="{{ route($recordsRouteName, $filterQuery([
                                    'period' => $filter['key'],
                                    'from_date' => $filter['key'] === 'custom' ? ($fromDate ?: now()->startOfMonth()->toDateString()) : null,
                                    'to_date' => $filter['key'] === 'custom' ? ($toDate ?: now()->toDateString()) : null,
                                ])) }}"
                                class="records-chip {{ $activePeriod === $filter['key'] ? 'is-active' : '' }}"
                            >
                                <span class="material-symbols-outlined text-[16px]">{{ $filter['icon'] }}</span>
                                {{ $filter['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @if ($activePeriod === 'custom')
                    <form method="GET" action="{{ route($recordsRouteName) }}" class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 pt-1">
                        <input type="hidden" name="type" value="{{ $activeType }}" />
                        <input type="hidden" name="period" value="custom" />
                        @if ($hasPurokFilter && $activePurokId !== 'all')
                            <input type="hidden" name="purok" value="{{ $activePurokId }}" />
                        @endif
                        <label class="block">
                            <span class="sr-only">From date</span>
                            <input type="date" name="from_date" value="{{ $fromDate }}" class="w-full rounded-xl border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-sm" />
                        </label>
                        <label class="block">
                            <span class="sr-only">To date</span>
                            <input type="date" name="to_date" value="{{ $toDate }}" class="w-full rounded-xl border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-sm" />
                        </label>
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white hover:opacity-95">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Apply
                        </button>
                    </form>
                @endif
            </section>

            @if ($records->isEmpty())
                <div class="records-empty">
                    <span class="material-symbols-outlined text-[46px] text-text-light/35 dark:text-text-dark/35">folder_off</span>
                    <p class="mt-3 text-base font-bold text-text-light dark:text-text-dark">No records found</p>
                    <p class="mt-1 text-sm text-text-light/60 dark:text-text-dark/60 max-w-xs mx-auto">Try another {{ $hasPurokFilter ? 'purok, ' : '' }}type or date range to see transactions and maintenance visits.</p>
                </div>
            @else
                <div class="records-list" id="allRecordsList" role="list">
                    @foreach ($records as $index => $record)
                        @php
                            $isTransaction = ($record['type'] ?? '') === 'transaction';
                            $isCompleted = (bool) ($record['is_completed'] ?? false);
                            $details = $record['details'] ?? [];
                            $panelId = 'record-details-'.$index;
                            $statusLabel = $record['status_label']
                                ?? ($isTransaction ? 'Medicine transaction' : 'Maintenance visit');
                        @endphp
                        <article
                            class="record-card {{ $isTransaction ? 'record-card--transaction' : 'record-card--maintenance' }} {{ $isCompleted ? 'is-completed' : '' }}"
                            role="listitem"
                        >
                            <div class="record-banner">
                                <span class="inline-flex items-center gap-1.5 min-w-0">
                                    <span class="material-symbols-outlined shrink-0" aria-hidden="true">{{ $record['icon'] }}</span>
                                    <span class="truncate">{{ $statusLabel }}</span>
                                </span>
                                <span class="shrink-0 opacity-80">{{ $record['date_label'] }}</span>
                            </div>

                            <button
                                type="button"
                                class="record-toggle"
                                aria-expanded="false"
                                aria-controls="{{ $panelId }}"
                                data-record-toggle
                            >
                                <div class="record-main">
                                    <div class="record-icon" aria-hidden="true">
                                        <span class="material-symbols-outlined text-[22px]">{{ $record['icon'] }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="record-title">{{ $record['title'] }}</p>
                                                <p class="record-subtitle">{{ $record['subtitle'] }}</p>
                                            </div>
                                            <span class="material-symbols-outlined record-chevron text-[22px]" aria-hidden="true">expand_more</span>
                                        </div>

                                        @php
                                            $previewChips = collect($record['preview_chips'] ?? [])
                                                ->take(4)
                                                ->pad(4, ['label' => '—', 'value' => '—', 'empty' => true])
                                                ->values()
                                                ->all();
                                        @endphp
                                        <div class="record-chips">
                                            @foreach ($previewChips as $chip)
                                                <div class="record-chip-pill {{ ! empty($chip['empty']) ? 'is-empty' : '' }}">
                                                    <span class="record-chip-pill__label">{{ $chip['label'] }}</span>
                                                    <span class="record-chip-pill__value">{{ $chip['value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <p class="mt-2.5 text-[11px] font-bold uppercase tracking-wide text-text-light/45 dark:text-text-dark/45">
                                            Tap for full details
                                        </p>
                                    </div>
                                </div>
                            </button>

                            <div id="{{ $panelId }}" class="record-details" hidden>
                                <div class="record-details-panel">
                                    <div class="record-details-grid">
                                        @foreach ($details as $detailRow)
                                            @php
                                                $isLong = in_array($detailRow['label'] ?? '', ['Medications', 'Notes', 'Issue notes', 'Health concerns'], true)
                                                    || mb_strlen((string) ($detailRow['value'] ?? '')) > 48;
                                            @endphp
                                            <div class="record-detail-item {{ $isLong ? 'record-detail-item--full' : '' }}">
                                                <span class="record-detail-label">{{ $detailRow['label'] }}</span>
                                                <p class="record-detail-value">{{ $detailRow['value'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    @include($mobileNavPartial)
</div>

<script>
    (function () {
        const list = document.getElementById('allRecordsList');
        if (!list) return;

        list.querySelectorAll('[data-record-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const panelId = button.getAttribute('aria-controls');
                const panel = panelId ? document.getElementById(panelId) : null;
                if (!panel) return;

                const willOpen = button.getAttribute('aria-expanded') !== 'true';

                list.querySelectorAll('[data-record-toggle]').forEach((other) => {
                    if (other === button) return;
                    other.setAttribute('aria-expanded', 'false');
                    const otherPanel = document.getElementById(other.getAttribute('aria-controls') || '');
                    if (otherPanel) {
                        otherPanel.classList.remove('is-open');
                        otherPanel.hidden = true;
                    }
                });

                button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                panel.classList.toggle('is-open', willOpen);
                panel.hidden = !willOpen;
            });
        });
    })();
</script>
</body>
</html>
