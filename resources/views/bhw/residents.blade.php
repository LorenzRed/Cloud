<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - Patient Masterlist</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 0;
    }

    .residents-page-content {
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .residents-page-layout {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        width: 100%;
        max-width: 80rem;
        margin: 0 auto;
    }

    .residents-page-header { flex-shrink: 0; }

    .residents-panel {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .residents-toolbar {
        flex-shrink: 0;
        margin-bottom: 0.75rem;
    }

    .residents-toolbar-row {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        width: 100%;
    }

    .residents-search-wrap {
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
        width: 100%;
        min-width: 0;
    }

    #filtersPanel {
        display: none;
    }
    #filtersPanel.open { display: flex; }

    .residents-page-title {
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }
    @media (min-width: 768px) {
        .residents-page-title { font-size: 1.25rem; }
    }

    .residents-filter-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .residents-filter-field label {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }
    .dark .residents-filter-field label {
        color: rgba(224, 224, 224, 0.55);
    }

    .residents-view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        min-height: 2.25rem;
        padding: 0.375rem 0.875rem;
        border-radius: 0.625rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #4A90E2;
        background: rgba(74, 144, 226, 0.08);
        border: 1px solid rgba(74, 144, 226, 0.2);
        transition: background-color 0.15s ease;
    }
    .residents-view-btn:hover {
        background: rgba(74, 144, 226, 0.14);
    }

    @media (min-width: 768px) {
        .residents-toolbar-row {
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
        }

        .residents-search-wrap {
            flex: 1 1 auto;
            min-width: 10rem;
            max-width: none;
            width: auto;
        }

        #filtersPanel {
            display: flex !important;
            flex: 0 0 auto;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            width: auto;
            min-width: 0;
            padding-top: 0;
            border-top: none;
        }

        #toggleFiltersBtn { display: none; }

        .residents-filter-field {
            flex-direction: row;
            align-items: center;
            gap: 0;
        }

        .residents-filter-field label { display: none; }
    }

    @media (max-width: 767px) {
        #filtersPanel {
            padding-top: 0.625rem;
            border-top: 1px solid #EAEAEA;
        }
        .dark #filtersPanel { border-top-color: #3D3D3D; }
        #filtersPanel.collapsed:not(.open) { display: none; }
        #filtersPanel.open {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.625rem;
        }
    }

    #filtersPanel {
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .residents-filter-control {
        height: 2.25rem;
        min-width: 0;
        padding: 0 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
        background: #FFFFFF;
        font-size: 0.8125rem;
    }
    .dark .residents-filter-control {
        border-color: #3D3D3D;
        background: #1A1A1A;
        color: #E0E0E0;
    }
    @media (min-width: 768px) {
        .residents-filter-control { width: auto; min-width: 7.5rem; }
    }
    @media (max-width: 767px) {
        .residents-filter-control { width: 100%; }
    }

    #toggleFiltersBtn.active {
        border-color: #4A90E2;
        background: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
    }

    .residents-table-wrap {
        flex: 1;
        min-height: 0;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 0.5rem;
        border: 1px solid #EAEAEA;
    }
    .dark .residents-table-wrap { border-color: #3D3D3D; }

    .residents-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #FFFFFF;
    }
    .dark .residents-table-wrap thead th { background: #2C2C2C; }

    .residents-list-footer {
        flex-shrink: 0;
        padding-top: 0.75rem;
        margin-top: 0.75rem;
        border-top: 1px solid #EAEAEA;
    }
    .dark .residents-list-footer { border-top-color: #3D3D3D; }

    @media (max-width: 1023px) {
        main.residents-main {
            overflow: visible;
        }

        .page-content.residents-page-scroll {
            height: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .residents-panel {
            overflow: visible;
            flex: none;
        }

        .residents-table-wrap {
            overflow: visible;
            flex: none;
            min-height: 0;
        }
    }

    @media (max-width: 767px) {
        .residents-page-layout {
            max-width: none;
            margin: 0;
        }

        .residents-panel {
            padding: 0 !important;
            border: none;
            box-shadow: none;
            background: transparent !important;
            border-radius: 0;
        }

        .residents-toolbar {
            padding: 0.75rem 1rem 0.625rem;
            margin-bottom: 0;
            background: #FFFFFF;
            border-bottom: 1px solid #EAEAEA;
        }
        .dark .residents-toolbar {
            background: #2C2C2C;
            border-bottom-color: #3D3D3D;
        }

        .residents-table-wrap {
            border: none;
            margin-top: 0 !important;
        }
        .residents-table-wrap table { min-width: 0 !important; }
        .residents-table-wrap thead { display: none; }
        .residents-table-wrap tbody {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .residents-table-wrap .resident-row {
            display: block;
            border: none;
            border-bottom: 1px solid #EAEAEA;
            border-radius: 0;
            background: #FFFFFF;
            padding: 0;
            box-shadow: none;
        }
        .dark .residents-table-wrap .resident-row {
            border-bottom-color: #3D3D3D;
            background: #2C2C2C;
        }

        .masterlist-mobile-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem 1rem;
            margin: 0;
            border: none;
            background: transparent;
            color: inherit;
            text-align: left;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .masterlist-mobile-toggle:active {
            background: rgba(74, 144, 226, 0.06);
        }

        .masterlist-mobile-toggle .masterlist-chevron {
            margin-left: auto;
            flex-shrink: 0;
            font-size: 1.25rem;
            color: rgba(74, 74, 74, 0.45);
            transition: transform 0.2s ease;
        }
        .dark .masterlist-mobile-toggle .masterlist-chevron {
            color: rgba(224, 224, 224, 0.45);
        }
        .resident-row.is-expanded .masterlist-chevron {
            transform: rotate(180deg);
        }

        .masterlist-mobile-status {
            flex-shrink: 0;
            max-width: 5.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
        }
        .resident-row.is-expanded .masterlist-mobile-status {
            display: none;
        }

        .masterlist-resident-desktop { display: none; }

        .masterlist-mobile-extra {
            display: none;
            padding: 0 1rem 0.625rem;
            font-size: 0.75rem;
            color: rgba(74, 74, 74, 0.65);
        }
        .dark .masterlist-mobile-extra {
            color: rgba(224, 224, 224, 0.65);
        }
        .resident-row.is-expanded .masterlist-mobile-extra {
            display: block;
        }

        .masterlist-row-details {
            display: none;
            padding: 0 1rem 0.875rem;
            border-top: 1px solid #EAEAEA;
        }
        .dark .masterlist-row-details {
            border-top-color: #3D3D3D;
        }
        .resident-row.is-expanded .masterlist-row-details {
            display: block;
        }

        .masterlist-detail-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.5rem 0;
            font-size: 0.8125rem;
        }
        .masterlist-detail-item + .masterlist-detail-item {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        .dark .masterlist-detail-item + .masterlist-detail-item {
            border-top-color: rgba(255, 255, 255, 0.06);
        }

        .masterlist-detail-label {
            flex-shrink: 0;
            width: 34%;
            max-width: 6.25rem;
            font-size: 0.625rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: rgba(74, 74, 74, 0.55);
            padding-top: 0.125rem;
        }
        .dark .masterlist-detail-label {
            color: rgba(224, 224, 224, 0.55);
        }

        .masterlist-detail-value {
            flex: 1;
            min-width: 0;
            text-align: right;
        }

        .masterlist-detail-item--action {
            padding-top: 0.625rem;
            border-top: none;
        }
        .masterlist-detail-item--action .residents-view-btn {
            width: 100%;
        }

        .residents-table-wrap .resident-row td.masterlist-desktop-cell {
            display: none !important;
        }

        .residents-table-wrap .resident-row td.masterlist-mobile-cell {
            display: block;
            height: auto !important;
            padding: 0;
            border: none;
        }

        .residents-table-wrap .resident-row.hidden {
            display: none !important;
        }

        #emptyRow td,
        #noResultsRow td {
            display: block !important;
            border: none;
            border-radius: 0;
            background: #FFFFFF;
            padding: 1.5rem 1rem !important;
        }
        .dark #emptyRow td,
        .dark #noResultsRow td {
            background: #2C2C2C;
        }

        .residents-list-footer {
            border-top: 1px solid #EAEAEA;
            padding: 0.75rem 1rem 0.25rem;
            margin-top: 0;
            text-align: left;
            background: #FFFFFF;
        }
        .dark .residents-list-footer {
            border-top-color: #3D3D3D;
            background: #2C2C2C;
        }
    }

    @media (min-width: 768px) {
        .masterlist-resident-desktop { display: flex !important; }
        .masterlist-mobile-toggle,
        .masterlist-mobile-extra,
        .masterlist-row-details,
        .masterlist-mobile-cell { display: none !important; }
        .masterlist-desktop-cell { display: table-cell !important; }
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
@include('sidebars.bhw')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-mobile-topbar')

    <main class="residents-main flex-1 flex flex-col min-h-0 overflow-hidden pt-3 sm:pt-4 px-0 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-8 overflow-hidden">
        <div class="residents-page-content page-content residents-page-scroll">
            <div class="residents-page-layout sm:py-0">
                @php
                    $totalResidents = $acceptedResidents->count();
                    $purokOptions = $acceptedResidents
                        ->map(fn ($r) => trim((string) ($r->purok?->name ?? $r->user?->purok?->name ?? '')))
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                @endphp

                @if (session('status'))
                    <div class="mb-3 mx-4 md:mx-0 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200 shrink-0">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="residents-panel bg-card-light dark:bg-card-dark rounded-none sm:rounded-xl shadow-none sm:shadow-sm border-0 sm:border border-border-light dark:border-border-dark p-0 sm:p-4 flex-1 min-h-0">
                    <div class="residents-toolbar flex flex-col gap-2.5">
                        <h1 class="residents-page-title text-text-light dark:text-text-dark">Residents Masterlist</h1>
                        <div class="residents-toolbar-row">
                            <div class="residents-search-wrap">
                                <div class="relative flex-1 min-w-0">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50 text-lg pointer-events-none">search</span>
                                    <input id="residentSearch" type="search" maxlength="100" autocomplete="off" placeholder="Search by name, family, or contact..." class="w-full h-10 pl-10 pr-3 rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-2 focus:ring-primary/25 focus:border-primary text-sm"/>
                                </div>
                                <button type="button" id="toggleFiltersBtn" class="md:hidden h-10 px-3.5 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-xs font-semibold inline-flex items-center justify-center gap-1.5 shrink-0" aria-expanded="false" aria-controls="filtersPanel">
                                    <span class="material-symbols-outlined text-base">tune</span>
                                    Filters
                                </button>
                            </div>
                            <div id="filtersPanel" class="collapsed w-full md:w-auto">
                                <div class="residents-filter-field">
                                    <label for="purokFilter">Purok</label>
                                    <select id="purokFilter" class="residents-filter-control">
                                        <option value="">All puroks</option>
                                        @foreach ($purokOptions as $purokName)
                                            <option value="{{ $purokName }}">{{ $purokName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="residents-filter-field">
                                    <label for="ageGroupFilter">Age group</label>
                                    <select id="ageGroupFilter" class="residents-filter-control">
                                        <option value="">All ages</option>
                                        <option value="child">Child (0–5)</option>
                                        <option value="youth">Youth (6–17)</option>
                                        <option value="adult">Adult (18–59)</option>
                                        <option value="senior">Senior (60+)</option>
                                    </select>
                                </div>
                                <div class="residents-filter-field">
                                    <label for="genderFilter">Gender</label>
                                    <select id="genderFilter" class="residents-filter-control">
                                        <option value="">All genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <button type="button" id="clearFiltersBtn" class="h-9 md:h-9 px-3 rounded-lg border border-border-light dark:border-border-dark text-xs font-semibold text-text-light/70 dark:text-text-dark/70 hover:bg-background-light dark:hover:bg-background-dark shrink-0 md:ml-0">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="residents-table-wrap mt-3 sm:mt-3">
                        <table class="w-full md:min-w-[760px]" id="residentsTable">
                            <thead class="hidden md:table-header-group">
                                <tr class="border-b border-border-light dark:border-border-dark">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Resident</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Family</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70 hidden lg:table-cell">Purok</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Age</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70 hidden sm:table-cell">Last consultation</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70"></th>
                                </tr>
                            </thead>
                            <tbody id="residentsTableBody">
                                @forelse ($acceptedResidents as $resident)
                                    @php
                                        $familyNumber = trim((string) ($resident->family_number ?? $resident->user?->family_number ?? ''));
                                        $purokName = trim((string) ($resident->purok?->name ?? $resident->user?->purok?->name ?? ''));
                                        $gender = trim((string) ($resident->user?->gender ?? ''));
                                        $age = (int) ($resident->age ?? 0);
                                        $ageGroup = match (true) {
                                            $age <= 5 => 'child',
                                            $age <= 17 => 'youth',
                                            $age >= 60 => 'senior',
                                            default => 'adult',
                                        };
                                        $nameParts = preg_split('/\s+/', trim($resident->full_name)) ?: [];
                                        $initials = strtoupper(substr($nameParts[0] ?? 'R', 0, 1).substr($nameParts[count($nameParts) - 1] ?? 'R', 0, 1));
                                        $lastConsultation = filled($resident->last_consultation_at ?? null)
                                            ? \Carbon\Carbon::parse($resident->last_consultation_at)->format('M d, Y')
                                            : null;
                                        $registeredLabel = $resident->reviewed_at?->format('M d, Y') ?? 'N/A';
                                        $mobileStatusClass = 'bg-background-light dark:bg-background-dark text-text-light/60 dark:text-text-dark/60';
                                        $mobileStatusLabel = $age > 0 ? $age.' yrs' : 'N/A';
                                    @endphp
                                    <tr
                                        class="resident-row border-b border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark"
                                        data-search="{{ strtolower(implode(' ', array_filter([$resident->full_name, $familyNumber, $resident->contact, $purokName]))) }}"
                                        data-purok="{{ $purokName }}"
                                        data-age-group="{{ $ageGroup }}"
                                        data-gender="{{ $gender }}"
                                    >
                                        <td class="masterlist-mobile-cell md:hidden" colspan="6">
                                            <button type="button" class="masterlist-mobile-toggle" aria-expanded="false" aria-label="Show details for {{ $resident->full_name }}">
                                                <div class="size-9 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0">{{ $initials }}</div>
                                                <span class="text-sm font-semibold text-text-light dark:text-text-dark truncate min-w-0 flex-1">{{ $resident->full_name }}</span>
                                                <span class="masterlist-mobile-status {{ $mobileStatusClass }}">{{ $mobileStatusLabel }}</span>
                                                <span class="material-symbols-outlined masterlist-chevron" aria-hidden="true">expand_more</span>
                                            </button>
                                            <div class="masterlist-mobile-extra">
                                                <p>{{ $gender !== '' ? $gender : '—' }} · {{ $purokName !== '' ? $purokName : 'No purok' }}</p>
                                                @if ($familyNumber !== '')
                                                    <p class="text-[11px] mt-0.5 opacity-80">Family {{ $familyNumber }}</p>
                                                @endif
                                            </div>
                                            <div class="masterlist-row-details">
                                                <div class="masterlist-detail-item">
                                                    <span class="masterlist-detail-label">Family</span>
                                                    <span class="masterlist-detail-value">{{ $familyNumber !== '' ? $familyNumber : '—' }}</span>
                                                </div>
                                                <div class="masterlist-detail-item">
                                                    <span class="masterlist-detail-label">Age</span>
                                                    <span class="masterlist-detail-value font-medium text-text-light dark:text-text-dark">{{ $age > 0 ? $age.' yrs' : 'N/A' }}</span>
                                                </div>
                                                <div class="masterlist-detail-item">
                                                    <span class="masterlist-detail-label">Purok</span>
                                                    <span class="masterlist-detail-value">{{ $purokName !== '' ? $purokName : '—' }}</span>
                                                </div>
                                                <div class="masterlist-detail-item">
                                                    <span class="masterlist-detail-label">Last visit</span>
                                                    <span class="masterlist-detail-value">{{ $lastConsultation ?? $registeredLabel }}</span>
                                                </div>
                                                <div class="masterlist-detail-item masterlist-detail-item--action">
                                                    <a href="{{ route('bhw.residents.profile', $resident) }}" class="residents-view-btn">
                                                        View profile
                                                        <span class="material-symbols-outlined text-base">chevron_right</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="masterlist-desktop-cell px-4 py-3 text-sm font-medium" data-label="Resident">
                                            <div class="masterlist-resident-desktop items-center gap-3 min-w-0">
                                                <div class="size-9 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0">{{ $initials }}</div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <p class="font-semibold text-text-light dark:text-text-dark truncate">{{ $resident->full_name }}</p>
                                                    </div>
                                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5 truncate">{{ $gender !== '' ? $gender : '—' }} · {{ $purokName !== '' ? $purokName : 'No purok' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="masterlist-desktop-cell px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70" data-label="Family">
                                            {{ $familyNumber !== '' ? $familyNumber : '—' }}
                                        </td>
                                        <td class="masterlist-desktop-cell px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70 hidden lg:table-cell" data-label="Purok">
                                            {{ $purokName !== '' ? $purokName : '—' }}
                                        </td>
                                        <td class="masterlist-desktop-cell px-4 py-3 text-sm whitespace-nowrap" data-label="Age">
                                            <span class="font-medium">{{ $age > 0 ? $age.' yrs' : 'N/A' }}</span>
                                        </td>
                                        <td class="masterlist-desktop-cell px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70 hidden sm:table-cell whitespace-nowrap" data-label="Last consultation">
                                            {{ $lastConsultation ?? $registeredLabel }}
                                        </td>
                                        <td class="masterlist-desktop-cell px-4 py-3 text-right" data-label="">
                                            <a href="{{ route('bhw.residents.profile', $resident) }}" class="residents-view-btn">
                                                View profile
                                                <span class="material-symbols-outlined text-base">chevron_right</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow">
                                        <td colspan="6" class="px-4 py-10 text-center">
                                                <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">person_off</span>
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">No accepted residents yet. Review requests to approve residents.</p>
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="noResultsRow" class="hidden">
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">search_off</span>
                                        <p class="text-sm text-text-light/70 dark:text-text-dark/70">No residents match your search or filters.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav class="residents-list-footer sm:px-0" aria-label="Results">
                        <p class="text-xs sm:text-sm text-text-light/70 dark:text-text-dark/70">
                                Showing
                            <span id="statVisible" class="font-semibold text-text-light dark:text-text-dark">{{ number_format($totalResidents) }}</span>
                                of
                            <span class="font-semibold text-text-light dark:text-text-dark">{{ number_format($totalResidents) }}</span>
                            residents
                            </p>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    @include('partials.bhw-mobile-nav')
</div>

<script>
    const toggleFiltersBtn = document.getElementById('toggleFiltersBtn');
    const filtersPanel = document.getElementById('filtersPanel');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    const residentSearch = document.getElementById('residentSearch');
    const purokFilter = document.getElementById('purokFilter');
    const ageGroupFilter = document.getElementById('ageGroupFilter');
    const genderFilter = document.getElementById('genderFilter');
    const statVisible = document.getElementById('statVisible');
    const noResultsRow = document.getElementById('noResultsRow');
    const residentRows = Array.from(document.querySelectorAll('.resident-row'));

    toggleFiltersBtn?.addEventListener('click', () => {
        const isOpen = filtersPanel?.classList.contains('open');
        filtersPanel?.classList.toggle('open', !isOpen);
        filtersPanel?.classList.toggle('collapsed', isOpen);
        toggleFiltersBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        toggleFiltersBtn.classList.toggle('active', !isOpen);
    });

    if (window.innerWidth >= 768) {
        filtersPanel?.classList.add('open');
        filtersPanel?.classList.remove('collapsed');
    }

    function filterResidents() {
        const query = (residentSearch?.value || '').trim().toLowerCase();
        const purok = purokFilter?.value || '';
        const ageGroup = ageGroupFilter?.value || '';
        const gender = genderFilter?.value || '';
        let visibleCount = 0;

        residentRows.forEach((row) => {
            const matchesSearch = !query || (row.dataset.search || '').includes(query);
            const matchesPurok = !purok || row.dataset.purok === purok;
            const matchesAgeGroup = !ageGroup || row.dataset.ageGroup === ageGroup;
            const matchesGender = !gender || row.dataset.gender === gender;
            const visible = matchesSearch && matchesPurok && matchesAgeGroup && matchesGender;

            row.classList.toggle('hidden', !visible);
            if (visible) visibleCount += 1;
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', visibleCount > 0 || residentRows.length === 0);
        }
        if (statVisible) {
            statVisible.textContent = String(visibleCount);
        }
    }

    function clearFilters() {
        if (residentSearch) residentSearch.value = '';
        if (purokFilter) purokFilter.value = '';
        if (ageGroupFilter) ageGroupFilter.value = '';
        if (genderFilter) genderFilter.value = '';
        filterResidents();
    }

    residentSearch?.addEventListener('input', filterResidents);
    purokFilter?.addEventListener('change', filterResidents);
    ageGroupFilter?.addEventListener('change', filterResidents);
    genderFilter?.addEventListener('change', filterResidents);
    clearFiltersBtn?.addEventListener('click', clearFilters);
    filterResidents();

    const mobileResidentsQuery = window.matchMedia('(max-width: 767px)');

    function collapseResidentRows() {
        document.querySelectorAll('.resident-row.is-expanded').forEach((row) => {
            row.classList.remove('is-expanded');
            row.querySelector('.masterlist-mobile-toggle')?.setAttribute('aria-expanded', 'false');
        });
    }

    function toggleResidentRow(row) {
        if (!mobileResidentsQuery.matches) {
            return;
        }

        const isExpanded = row.classList.contains('is-expanded');
        collapseResidentRows();

        if (!isExpanded) {
            row.classList.add('is-expanded');
            row.querySelector('.masterlist-mobile-toggle')?.setAttribute('aria-expanded', 'true');
        }
    }

    document.querySelectorAll('.masterlist-mobile-toggle').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const row = button.closest('.resident-row');
            if (row) {
                toggleResidentRow(row);
            }
        });
    });

    mobileResidentsQuery.addEventListener('change', (event) => {
        if (!event.matches) {
            collapseResidentRows();
        }
    });
</script>
</body>
</html>
