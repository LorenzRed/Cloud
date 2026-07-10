<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Patient Masterlist</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    @media (max-width: 768px) {
        #sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            position: fixed;
            z-index: 40;
            height: 100vh;
        }
        #sidebar.active { transform: translateX(0); }
        #overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
        }
        #overlay.active { display: block; }
        main { width: 100%; }
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    
    .main-container {
        height: 100vh;
        display: flex;
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

    #filtersPanel {
        display: none;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    #filtersPanel.open { display: flex; }
    @media (min-width: 768px) {
        #filtersPanel { display: flex; }
        #toggleFiltersBtn { display: none; }
    }
    @media (max-width: 767px) {
        #filtersPanel {
            padding-top: 0.5rem;
            border-top: 1px solid #EAEAEA;
        }
        .dark #filtersPanel { border-top-color: #3D3D3D; }
        #filtersPanel.collapsed:not(.open) { display: none; }
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

    @media (max-width: 767px) {
        .residents-table-wrap { border: none; }
        .residents-table-wrap table { min-width: 0 !important; }
        .residents-table-wrap thead { display: none; }
        .residents-table-wrap tbody { display: flex; flex-direction: column; gap: 0.625rem; }
        .residents-table-wrap .resident-row {
            display: block;
            border: 1px solid #EAEAEA;
            border-radius: 0.75rem;
            background: #FFFFFF;
            padding: 0.75rem 0.875rem;
        }
        .dark .residents-table-wrap .resident-row {
            border-color: #3D3D3D;
            background: #2C2C2C;
        }
        .residents-table-wrap .resident-row td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.375rem 0;
            border: none;
            font-size: 0.8125rem;
        }
        .residents-table-wrap .resident-row td::before {
            content: attr(data-label);
            flex-shrink: 0;
            width: 36%;
            max-width: 6.5rem;
            font-size: 0.625rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: rgba(74, 74, 74, 0.55);
        }
        .dark .residents-table-wrap .resident-row td::before { color: rgba(224, 224, 224, 0.55); }
        .residents-table-wrap .resident-row td[data-label="Resident"] {
            display: block;
            padding-bottom: 0.5rem;
            margin-bottom: 0.25rem;
            border-bottom: 1px solid #EAEAEA;
        }
        .dark .residents-table-wrap .resident-row td[data-label="Resident"] { border-bottom-color: #3D3D3D; }
        .residents-table-wrap .resident-row td[data-label="Resident"]::before { display: none; }
        .residents-table-wrap .resident-row.hidden { display: none !important; }
        #emptyRow td,
        #noResultsRow td {
            display: block !important;
            border: 1px solid #EAEAEA;
            border-radius: 0.75rem;
            background: #FFFFFF;
        }
        .dark #emptyRow td,
        .dark #noResultsRow td { border-color: #3D3D3D; background: #2C2C2C; }
        #emptyRow td::before,
        #noResultsRow td::before { display: none !important; }
    }

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }
    .modal.active { display: flex; }
    
    .modal-content {
        background-color: white;
        border-radius: 0.75rem;
        width: 90%;
        max-width: 1000px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    .dark .modal-content {
        background-color: #2C2C2C;
        color: #E0E0E0;
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
<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    
    <div id="overlay"></div>
    @include('sidebars.bhw')
    
    <main class="flex-1 p-4 md:p-8 overflow-hidden md:ml-0 max-md:pt-16 min-h-0">
        <div class="residents-page-content">
            <div class="residents-page-layout">
                @php
                    $totalResidents = $acceptedResidents->count();
                    $purokOptions = $acceptedResidents
                        ->map(fn ($r) => trim((string) ($r->purok?->name ?? $r->user?->purok?->name ?? '')))
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                @endphp

                <header class="residents-page-header flex flex-wrap justify-between items-start gap-3 mb-4 max-md:pl-11">
                    <div class="flex flex-col gap-0.5 min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold leading-tight">Residents Masterlist</h1>
                        <p class="text-xs sm:text-sm text-text-light/70 dark:text-text-dark/70">View and manage accepted residents in your assigned purok.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button id="openAddResidentModal" type="button" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 bg-green-600 text-white rounded-lg text-xs sm:text-sm font-semibold shadow-sm hover:opacity-90">
                            <span class="material-symbols-outlined text-base">person_add</span>
                            <span>Add Resident</span>
                            </button>
                        <a href="{{ route('bhw.requests') }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 bg-primary text-white rounded-lg text-xs sm:text-sm font-semibold shadow-sm hover:opacity-90">
                            <span class="material-symbols-outlined text-base">group_add</span>
                            <span>Requests</span>
                            </a>
                        </div>
                </header>

                @if (session('status'))
                    <div class="mb-3 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200 shrink-0">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="residents-panel bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-3 sm:p-4 flex-1 min-h-0">
                    <div class="residents-toolbar flex flex-wrap items-center gap-2">
                        <div class="relative flex-1 min-w-[10rem]">
                            <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50 text-lg">search</span>
                            <input id="residentSearch" type="search" placeholder="Search by name, family, or contact..." class="w-full h-9 pl-9 pr-3 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-1 focus:ring-primary/40 focus:border-primary text-sm"/>
                        </div>
                        <button type="button" id="toggleFiltersBtn" class="md:hidden h-9 px-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-xs font-semibold inline-flex items-center gap-1.5 shrink-0" aria-expanded="false" aria-controls="filtersPanel">
                            <span class="material-symbols-outlined text-base">tune</span>
                            Filters
                        </button>
                        <div id="filtersPanel" class="collapsed w-full md:w-auto">
                            <select id="purokFilter" class="residents-filter-control">
                                <option value="">All puroks</option>
                                @foreach ($purokOptions as $purokName)
                                    <option value="{{ $purokName }}">{{ $purokName }}</option>
                                @endforeach
                            </select>
                            <select id="ageGroupFilter" class="residents-filter-control">
                                <option value="">All ages</option>
                                <option value="child">Child (0–5)</option>
                                <option value="youth">Youth (6–17)</option>
                                <option value="adult">Adult (18–59)</option>
                                <option value="senior">Senior (60+)</option>
                            </select>
                            <select id="genderFilter" class="residents-filter-control">
                                <option value="">All genders</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <button type="button" id="clearFiltersBtn" class="h-9 px-3 rounded-lg border border-border-light dark:border-border-dark text-xs font-semibold text-text-light/70 dark:text-text-dark/70 hover:bg-background-light dark:hover:bg-background-dark shrink-0">
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="residents-table-wrap">
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
                                        $unreadCount = (int) ($unreadByResident[$resident->user_id] ?? 0);
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
                                    @endphp
                                    <tr
                                        class="resident-row border-b border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark"
                                        data-search="{{ strtolower(implode(' ', array_filter([$resident->full_name, $familyNumber, $resident->contact, $purokName]))) }}"
                                        data-purok="{{ $purokName }}"
                                        data-age-group="{{ $ageGroup }}"
                                        data-gender="{{ $gender }}"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium" data-label="Resident">
                                            <div class="flex items-center gap-3 min-w-0 md:text-left text-right ml-auto md:ml-0">
                                                <div class="size-9 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0">{{ $initials }}</div>
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center justify-end md:justify-start gap-2">
                                                        <p class="font-semibold text-text-light dark:text-text-dark truncate">{{ $resident->full_name }}</p>
                                                        @if ($unreadCount > 0)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200 shrink-0">{{ $unreadCount }} new</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5 truncate">{{ $gender !== '' ? $gender : '—' }} · {{ $purokName !== '' ? $purokName : 'No purok' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70" data-label="Family">
                                            <span class="md:block text-right md:text-left">{{ $familyNumber !== '' ? $familyNumber : '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70 hidden lg:table-cell" data-label="Purok">
                                            {{ $purokName !== '' ? $purokName : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap" data-label="Age">
                                            <span class="md:block text-right md:text-left font-medium">{{ $age > 0 ? $age.' yrs' : 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70 hidden sm:table-cell whitespace-nowrap" data-label="Last consultation">
                                            <span class="md:block text-right md:text-left">{{ $lastConsultation ?? $registeredLabel }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right" data-label="">
                                            <a href="{{ route('bhw.residents.profile', $resident) }}" class="inline-flex items-center gap-1 text-primary text-sm font-semibold hover:underline">
                                                View
                                                <span class="material-symbols-outlined text-base md:hidden">chevron_right</span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow">
                                        <td colspan="6" class="px-4 py-10 text-center">
                                                <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">person_off</span>
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">No accepted residents yet. Review requests to add residents.</p>
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

                    <nav class="residents-list-footer" aria-label="Results">
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
</div>

<div id="addResidentModal" class="modal" aria-hidden="true">
    <div class="modal-content p-6 w-[94vw] max-w-[720px]">
        <form method="POST" action="{{ route('bhw.residents.store') }}">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Add Resident</h2>
                <button type="button" id="closeAddResidentModal" class="text-text-light/70 dark:text-text-dark/70">&times;</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-light/80 dark:text-text-dark/80">First name</label>
                    <input name="first_name" required class="mt-1 block w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light/80 dark:text-text-dark/80">Middle initial</label>
                    <input name="middle_initial" maxlength="1" class="mt-1 block w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light/80 dark:text-text-dark/80">Last name</label>
                    <input name="last_name" required class="mt-1 block w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light/80 dark:text-text-dark/80">Gender</label>
                    <select name="gender" class="mt-1 block w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 py-2">
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light/80 dark:text-text-dark/80">Birthdate</label>
                    <input name="birthdate" type="date" class="mt-1 block w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-light/80 dark:text-text-dark/80">Household / Purok</label>
                    <input name="household_no" class="mt-1 block w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 py-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" id="cancelAddResident" class="px-4 py-2 rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-text-light/80">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const openBtn = document.getElementById('openAddResidentModal');
        const modal = document.getElementById('addResidentModal');
        const closeBtn = document.getElementById('closeAddResidentModal');
        const cancelBtn = document.getElementById('cancelAddResident');

        if (!openBtn || !modal) return;

        openBtn.addEventListener('click', () => modal.classList.add('active'));
        closeBtn?.addEventListener('click', () => modal.classList.remove('active'));
        cancelBtn?.addEventListener('click', () => modal.classList.remove('active'));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    }());
</script>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
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

    menuToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('active');
        overlay?.classList.toggle('active');
    });
    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('active');
        overlay?.classList.remove('active');
    });

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
</script>
</body>
</html>
