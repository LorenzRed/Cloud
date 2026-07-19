<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    @include('partials.bhw-viewport')
    <title>Bantay Kalusugan - BHW Workers</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    @include('partials.captain-shell-styles')
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
        .page-content { height: 100%; overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .worker-list-scroll { max-height: min(28rem, 55dvh); overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .purok-map { height: 100%; width: 100%; min-height: 40vh; }
        @media (min-width: 1024px) {
            .purok-map { min-height: 0; }
        }
        .purok-option.is-selected > div { border-color: rgb(74 144 226 / 1); background-color: rgb(74 144 226 / 0.10); box-shadow: 0 0 0 1px rgb(74 144 226 / 0.18); }
        .purok-option.is-occupied-locked { cursor: not-allowed; opacity: 0.72; }
        .purok-option.is-occupied-locked > div { border-color: rgb(148 163 184 / 0.9); background-color: rgb(148 163 184 / 0.12); }
        .toast-enter { animation: toast-in 180ms ease-out; }
        .toast-exit { animation: toast-out 180ms ease-in forwards; }
        @keyframes toast-in { from { transform: translateY(-8px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateY(0); opacity: 1; } to { transform: translateY(-8px); opacity: 0; } }
        .leaflet-control-container { z-index: 10; }
        .purok-map-label {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid #4A90E2;
            color: #1e3a5f;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 9999px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            pointer-events: none;
            white-space: nowrap;
        }
        .dark .purok-map-label {
            background: rgba(44, 44, 44, 0.94);
            border-color: #4A90E2;
            color: #E0E0E0;
        }
        .purok-occupied-label {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #111111;
            color: #374151;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 3px 10px;
            border-radius: 9999px;
            pointer-events: none;
            white-space: nowrap;
        }
        .edit-purok-corner-handle {
            width: 22px !important;
            height: 22px !important;
            margin: 0 !important;
            border-radius: 50%;
            background: #2563eb;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 2px #1d4ed8, 0 2px 10px rgba(0, 0, 0, 0.45);
            cursor: grab;
        }
        .edit-purok-corner-handle:active { cursor: grabbing; }
        .edit-purok-mid-handle {
            width: 14px !important;
            height: 14px !important;
            margin: 0 !important;
            border-radius: 50%;
            background: #ffffff;
            border: 2px dashed #2563eb;
            box-shadow: 0 1px 8px rgba(0, 0, 0, 0.35);
            cursor: crosshair;
        }
        #editPurokBoundaryMap .leaflet-editing-icon {
            width: 18px !important;
            height: 18px !important;
            margin-left: -9px !important;
            margin-top: -9px !important;
            border-radius: 50%;
            border: 3px solid #ffffff !important;
            background: #2563eb !important;
            box-shadow: 0 0 0 2px #1d4ed8, 0 2px 10px rgba(0, 0, 0, 0.45);
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#4A90E2", "secondary": "#50E3C2", "accent-orange": "#F5A623",
                        "accent-red": "#D0021B", "background-light": "#F4F4F4", "background-dark": "#1A1A1A",
                        "text-light": "#4A4A4A", "text-dark": "#E0E0E0", "card-light": "#FFFFFF",
                        "card-dark": "#2C2C2C", "border-light": "#EAEAEA", "border-dark": "#3D3D3D",
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
            <div class="w-full max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-4">
                <header class="space-y-3">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between lg:gap-4">
                        <div class="hidden lg:flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between lg:flex-1 lg:min-w-0">
                            <div class="min-w-0">
                                <h1 class="text-2xl sm:text-3xl font-bold leading-tight">BHW Workers</h1>
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">View BHW worker names and manage purok for your barangay.</p>
                            </div>
                            @if ($assignedBarangay)
                                <span class="inline-flex self-start items-center gap-1.5 px-3 py-2 rounded-full text-sm font-semibold bg-primary/10 text-primary">
                                    <span class="material-symbols-outlined text-base">location_city</span>
                                    {{ $assignedBarangay->name }}
                                </span>
                            @endif
                        </div>
                        @if ($assignedBarangay)
                            <div class="grid grid-cols-3 gap-2 lg:flex lg:flex-nowrap lg:items-center lg:justify-end lg:shrink-0">
                                <button type="button" id="openPurokListModalBtn" class="inline-flex items-center justify-center gap-1.5 rounded-xl h-11 px-3 bg-gray-600 text-white text-xs sm:text-sm font-bold hover:bg-gray-700" title="List Purok">
                                    <span class="material-symbols-outlined text-base">list</span>
                                    <span class="hidden sm:inline">List</span>
                                </button>
                                <button type="button" id="openAddPurokModalBtn" class="inline-flex items-center justify-center gap-1.5 rounded-xl h-11 px-3 sm:px-4 bg-primary text-white text-xs sm:text-sm font-bold hover:opacity-90">
                                    <span class="material-symbols-outlined text-base">add_location_alt</span>
                                    <span>Purok</span>
                                </button>
                                <button type="button" id="openAddBhwModalBtn" class="inline-flex items-center justify-center gap-1.5 rounded-xl h-11 px-3 sm:px-4 bg-secondary text-white text-xs sm:text-sm font-bold hover:opacity-90">
                                    <span class="material-symbols-outlined text-base">person_add</span>
                                    <span class="sm:hidden">BHW</span>
                                    <span class="hidden sm:inline">Add BHW</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </header>

                @if (session('status'))
                    <div class="sr-only" id="toastData" data-toast-type="success" data-toast-message="{{ session('status') }}"></div>
                @endif
                @if ($errors->any())
                    <div class="sr-only" id="toastData" data-toast-type="error" data-toast-message="{{ $errors->first() }}"></div>
                @endif

                @if (! $assignedBarangay)
                    <div class="rounded-xl border border-border-light dark:border-border-dark p-4 bg-card-light dark:bg-card-dark">
                        <p class="text-sm text-text-light/80 dark:text-text-dark/80">No barangay assigned yet. BHW Workers and purok setup will be available once your barangay is assigned.</p>
                    </div>
                @endif

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-4 sm:p-6 w-full space-y-4 sm:space-y-5">
                    <div class="flex flex-col gap-3">
                        <h2 class="text-lg font-bold">Worker Names</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <select id="purokFilter" class="h-11 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm">
                                <option value="">All Workers</option>
                                <option value="unassigned">Not Assigned</option>
                                @foreach ($manageablePuroks as $purok)
                                    <option value="{{ $purok->name }}">{{ $purok->name }}</option>
                                @endforeach
                            </select>
                            <div class="relative w-full">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                                <input id="workerSearch" type="text" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark pl-10 pr-3 text-sm" placeholder="Search worker name..." />
                            </div>
                        </div>
                    </div>

                    <div id="workerCards" class="space-y-3 sm:hidden">
                        @forelse ($bhwWorkers as $worker)
                            @php
                                $assignedPurokNames = $worker->assignedPuroks->pluck('name');
                                $assignedPurokIds = $worker->assignedPuroks->pluck('id');
                            @endphp
                            <div class="worker-row rounded-xl border border-border-light dark:border-border-dark p-4 space-y-3"
                                data-worker-name="{{ strtolower($worker->name) }}"
                                data-purok-labels="{{ $assignedPurokNames->join('|') }}">
                                <div class="min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-base font-bold truncate">{{ $worker->name }}</p>
                                        @if ($worker->role === 'BHWHead')
                                            <span class="shrink-0 inline-flex items-center rounded-full bg-secondary/15 px-2.5 py-0.5 text-xs font-semibold text-secondary">BHW Head</span>
                                        @else
                                            <span class="shrink-0 inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">BHW</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 break-all mt-0.5">{{ $worker->email }}</p>
                                    <p class="text-xs text-text-light/50 dark:text-text-dark/50 mt-1">{{ $worker->barangay ?: 'Not assigned' }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-text-light/50 mb-1.5">Assigned purok</p>
                                    @if ($assignedPurokNames->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($assignedPurokNames as $purokName)
                                                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $purokName }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-text-light/60">Not assigned</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 pt-1">
                                    @if ($worker->role === 'BHW')
                                        <button type="button" class="open-assign-modal-btn flex-1 inline-flex items-center justify-center gap-2 rounded-xl h-11 px-3 bg-primary text-white text-sm font-bold hover:opacity-90"
                                            data-worker-id="{{ $worker->id }}" data-worker-name="{{ $worker->name }}"
                                            data-current-purok-ids="{{ $assignedPurokIds->join(',') }}">
                                            <span class="material-symbols-outlined text-sm">assignment_ind</span> Assign
                                        </button>
                                    @else
                                        <span class="flex-1 inline-flex items-center justify-center h-11 rounded-xl border border-border-light dark:border-border-dark text-xs text-text-light/50 dark:text-text-dark/50">Supervisor</span>
                                    @endif
                                    <button type="button" class="open-kick-modal-btn inline-flex items-center justify-center rounded-xl h-11 w-11 border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/50 dark:text-red-300"
                                        data-worker-id="{{ $worker->id }}" data-worker-name="{{ $worker->name }}" title="Remove permission">
                                        <span class="material-symbols-outlined text-base">person_remove</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-border-light dark:border-border-dark px-4 py-8 text-center text-sm text-text-light/70 dark:text-text-dark/70">
                                No health workers found for your barangay.
                            </div>
                        @endforelse
                    </div>

                    <div class="hidden sm:block overflow-x-auto border border-border-light dark:border-border-dark rounded-lg worker-list-scroll">
                        <table class="w-full min-w-[640px]">
                            <thead class="sticky top-0 z-10">
                                <tr class="border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/60">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Role</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Barangay</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Assigned Purok</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Action</th>
                                </tr>
                            </thead>
                            <tbody id="workerTableBody">
                                @forelse ($bhwWorkers as $worker)
                                    @php
                                        $assignedPurokNames = $worker->assignedPuroks->pluck('name');
                                        $assignedPurokIds = $worker->assignedPuroks->pluck('id');
                                    @endphp
                                    <tr class="border-b border-border-light dark:border-border-dark worker-row" data-worker-name="{{ strtolower($worker->name) }}" data-purok-labels="{{ $assignedPurokNames->join('|') }}">
                                        <td class="px-4 py-3 text-sm font-semibold">{{ $worker->name }}</td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70">{{ $worker->email }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($worker->role === 'BHWHead')
                                                <span class="inline-flex items-center rounded-full bg-secondary/15 px-2.5 py-0.5 text-xs font-semibold text-secondary">BHW Head</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">BHW</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $worker->barangay ?: 'Not assigned' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($assignedPurokNames->isNotEmpty())
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($assignedPurokNames as $purokName)
                                                        <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $purokName }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                Not assigned
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex items-center justify-center gap-2">
                                                @if ($worker->role === 'BHW')
                                                <button type="button" class="open-assign-modal-btn inline-flex items-center gap-2 rounded-lg h-10 px-3 bg-primary text-white text-sm font-bold hover:opacity-90"
                                                    data-worker-id="{{ $worker->id }}" data-worker-name="{{ $worker->name }}"
                                                    data-current-purok-ids="{{ $assignedPurokIds->join(',') }}">
                                                    <span class="material-symbols-outlined text-sm">assignment_ind</span> Assign Purok
                                                </button>
                                                @else
                                                    <span class="text-xs text-text-light/50 dark:text-text-dark/50">Supervisor</span>
                                                @endif
                                                <button type="button" class="open-kick-modal-btn inline-flex items-center justify-center rounded h-8 px-2 border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/50 dark:text-red-300 dark:hover:bg-red-950/70 transition"
                                                    data-worker-id="{{ $worker->id }}" data-worker-name="{{ $worker->name }}" title="Remove BHW permission">
                                                    <span class="material-symbols-outlined text-xs">person_remove</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-text-light/70 dark:text-text-dark/70">No health workers found for your barangay.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.captain-mobile-nav')
</div>

<!-- Modals (same as before) -->
<div id="bhwModal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4">
    <div class="w-full sm:max-w-lg max-h-[92dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-start justify-between gap-3 border-b border-border-light dark:border-border-dark px-4 sm:px-6 py-3">
            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl font-bold leading-tight">Add Health Worker</h2>
                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-0.5 leading-snug">Create a BHW or BHW Head account for your barangay.</p>
            </div>
            <button type="button" id="closeAddBhwModalBtn" class="p-1.5 rounded-lg hover:bg-background-light dark:hover:bg-background-dark shrink-0"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="{{ route('barangay-captain.bhw-workers.store') }}" class="space-y-3 px-4 sm:px-6 pt-2 pb-4">
            @csrf
            <input type="hidden" name="worker_form" value="1" />
            <div>
                <label for="worker_role" class="block text-sm font-medium mb-1">Role</label>
                <select id="worker_role" name="worker_role" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3" required>
                    <option value="BHW" @selected(old('worker_role', 'BHW') === 'BHW')>BHW — handles residents in assigned purok</option>
                    <option value="BHWHead" @selected(old('worker_role') === 'BHWHead')>BHW Head — manages BHW team and records</option>
                </select>
            </div>
            <div><label for="email" class="block text-sm font-medium mb-1">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3" placeholder="worker@example.com" required/></div>
            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 pl-3 pr-12" placeholder="Enter a temporary password" required/>
                    <button id="togglePassword" type="button" class="absolute inset-y-0 right-0 flex items-center justify-center w-11 text-text-light/60 dark:text-text-dark/60 hover:text-primary" aria-label="Show password" aria-pressed="false">
                        <svg id="passwordEyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg id="passwordEyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.998 2.998 0 0 0 12 15a2.998 2.998 0 0 0 2.413-1.416" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a19.23 19.23 0 0 1-3.21 4.24" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61A19.3 19.3 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.9 10.9 0 0 0 5.39-1.42" />
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 pl-3 pr-12" placeholder="Re-enter the password" required/>
                    <button id="toggleConfirmPassword" type="button" class="absolute inset-y-0 right-0 flex items-center justify-center w-11 text-text-light/60 dark:text-text-dark/60 hover:text-primary" aria-label="Show password" aria-pressed="false">
                        <svg id="confirmPasswordEyeOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg id="confirmPasswordEyeClosed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="w-5 h-5 hidden" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.998 2.998 0 0 0 12 15a2.998 2.998 0 0 0 2.413-1.416" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5a19.23 19.23 0 0 1-3.21 4.24" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61A19.3 19.3 0 0 0 2.25 12s3.75 7.5 9.75 7.5a10.9 10.9 0 0 0 5.39-1.42" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="cancelAddBhwModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-secondary text-white text-sm font-bold hover:opacity-90"><span class="material-symbols-outlined text-base">person_add</span> Create Worker</button>
            </div>
        </form>
    </div>
</div>

<div id="purokModal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/50 px-2 sm:px-4 py-2 sm:py-6">
    <div class="w-full max-w-6xl h-[min(92dvh,calc(100vh-1rem))] sm:h-[calc(100vh-3rem)] overflow-hidden rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl flex flex-col">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4 bg-card-light dark:bg-card-dark z-10">
            <div>
                <h2 class="text-lg font-semibold text-text-light dark:text-text-dark">Create Purok</h2>
                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Draw inside your barangay. Gray areas are taken — new shapes auto-align so they do not overlap.</p>
        </div>
            <button type="button" id="closeAddPurokModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
                </div>

        <div class="grid flex-1 min-h-0 gap-0 lg:grid-cols-[minmax(0,1.8fr)_minmax(320px,1fr)]">
            <div class="border-b lg:border-b-0 lg:border-r border-border-light dark:border-border-dark min-h-0">
                <div id="purokBoundaryMap" class="purok-map"></div>
            </div>

            <div class="p-6 flex flex-col min-h-0 overflow-y-auto">
                <form id="purokForm" method="POST" action="{{ route('barangay-captain.puroks.store') }}" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" name="purok_form" value="1" />
                <input id="purok_boundary_geojson" name="boundary_geojson" type="hidden" value="{{ old('boundary_geojson') }}" required />

                    @if ($assignedBarangay)
                        <div class="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/10 px-3 py-2 text-sm font-semibold text-primary w-fit">
                            <span class="material-symbols-outlined text-base">location_city</span>
                            {{ $assignedBarangay->name }}
                </div>
                    @endif

                    <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                        <label for="purokName" class="block text-xs uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Purok Name</label>
                        <input id="purokName" name="name" type="text" value="{{ old('name') }}" required placeholder="e.g., Purok 1"
                            class="mt-2 w-full h-11 px-3 rounded-lg bg-card-light dark:bg-background-dark border border-border-light dark:border-border-dark text-text-light dark:text-text-dark text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                        <p class="mt-2 text-xs text-text-light/60 dark:text-text-dark/60">Draw in the space beside gray purok. A very thin gap is kept between boundaries so they do not overlap.</p>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 h-11 px-4 bg-primary text-white text-sm font-bold rounded-lg hover:opacity-90 transition-colors">
                        <span class="material-symbols-outlined text-base">add_location_alt</span>
                        Create Purok
                    </button>
                    <button type="button" id="cancelAddPurokModalBtn" class="inline-flex w-full items-center justify-center gap-2 h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                        Cancel
                    </button>
            </form>
            </div>
        </div>
    </div>
</div>

<div id="assignModal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4">
    <div class="w-full sm:max-w-3xl max-h-[92dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-4 sm:px-6 py-4">
            <div><h2 class="text-lg sm:text-xl font-bold">Assign Purok</h2><p id="assignModalSubtitle" class="text-sm text-text-light/70 dark:text-text-dark/70">Select one or more purok for the chosen BHW worker. Each purok can only have one BHW.</p></div>
            <button type="button" id="closeAssignModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="px-4 sm:px-6 pt-2 pb-5 space-y-2">
            <form id="assignWorkerForm" method="POST" action="{{ route('barangay-captain.bhw-workers.assign') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="worker_id" id="assignWorkerId" value="" />
                <div id="assignPurokIdsContainer"></div>
                <p id="assignSelectionSummary" class="text-sm font-medium text-primary">0 purok selected</p>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                    <input id="purokSearch" type="text" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark pl-10 pr-3 text-sm" placeholder="Search purok..." />
                </div>
                <div class="max-h-[28rem] overflow-y-auto border border-border-light dark:border-border-dark rounded-lg">
                    <div id="purokOptions" class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3">
                        @forelse ($manageablePuroks as $purok)
                            @php
                                $assignedBhw = $purok->bhwWorkers->first();
                                $assignedBhwLabel = $assignedBhw
                                    ? trim((string) ($assignedBhw->full_name ?: $assignedBhw->name))
                                    : '';
                            @endphp
                            <button type="button"
                                class="purok-option text-left cursor-pointer"
                                data-purok-id="{{ $purok->id }}"
                                data-purok-name="{{ strtolower($purok->name) }}"
                                data-purok-label="{{ $purok->name }}"
                                data-assigned-bhw-id="{{ $assignedBhw?->id }}"
                                data-assigned-bhw-name="{{ $assignedBhwLabel }}">
                                <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 transition">
                                    <p class="font-semibold">{{ $purok->name }}</p>
                                    <p class="purok-assign-status mt-1 text-xs text-text-light/60 dark:text-text-dark/60">
                                        {{ $assignedBhwLabel !== '' ? 'Assigned to '.$assignedBhwLabel : 'Available' }}
                                    </p>
                                </div>
                            </button>
                        @empty
                            <div class="col-span-full rounded-xl border border-dashed border-border-light dark:border-border-dark p-4 text-sm text-text-light/70 dark:text-text-dark/70">No purok available. Create one first.</div>
                        @endforelse
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" id="cancelAssignModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">Cancel</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold hover:opacity-90"><span class="material-symbols-outlined text-base">assignment_ind</span> Save Purok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="kickModal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4">
    <div class="w-full sm:max-w-lg max-h-[92dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <div><h2 class="text-xl font-bold text-red-600 dark:text-red-300"><span class="material-symbols-outlined align-text-bottom mr-2">person_remove</span> Remove Permission</h2><p id="kickModalSubtitle" class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">This action cannot be undone.</p></div>
            <button type="button" id="closeKickModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="{{ route('barangay-captain.bhw-workers.kick') }}" class="space-y-4 px-6 py-5">
            @csrf
            <input type="hidden" name="worker_id" id="kickWorkerId" value="" />
            <p class="text-sm leading-6 text-text-light/80 dark:text-text-dark/80">Remove BHW permission from <span id="kickWorkerName" class="font-semibold text-text-light dark:text-text-dark"></span>? They will become a regular user and lose all BHW privileges.</p>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="rounded-lg h-9 px-3 bg-red-100 text-red-700 text-xs border border-red-300 hover:bg-red-200">Confirm</button>
                <button type="button" id="cancelKickModalBtn" class="rounded-lg h-11 px-4 bg-gray-600 text-white text-sm font-semibold hover:bg-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="deletePurokModal" class="fixed inset-0 z-[70] hidden items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4">
    <div class="w-full sm:max-w-lg max-h-[92dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <div>
                <h2 class="text-xl font-bold text-red-600 dark:text-red-300"><span class="material-symbols-outlined align-text-bottom mr-2">delete</span> Delete Purok</h2>
                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">This action cannot be undone.</p>
            </div>
            <button type="button" id="closeDeletePurokModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form id="deletePurokForm" method="POST" action="" class="space-y-4 px-6 py-5">
            @csrf
            <p class="text-sm leading-6 text-text-light/80 dark:text-text-dark/80">Are you sure you want to delete <span id="deletePurokName" class="font-semibold text-text-light dark:text-text-dark"></span>? BHW workers assigned to this purok will be unassigned from it.</p>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit" class="rounded-lg h-9 px-3 bg-red-100 text-red-700 text-xs border border-red-300 hover:bg-red-200 dark:bg-red-950/50 dark:text-red-300 dark:border-red-800 dark:hover:bg-red-950/70">Confirm Delete</button>
                <button type="button" id="cancelDeletePurokModalBtn" class="rounded-lg h-11 px-4 bg-gray-600 text-white text-sm font-semibold hover:bg-gray-700">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="purokListModal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4">
    <div class="w-full sm:max-w-lg max-h-[92dvh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <h2 class="text-xl font-bold"><span class="material-symbols-outlined align-text-bottom mr-2">list</span> Purok</h2>
            <button type="button" id="closePurokListModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="px-6 py-5 space-y-3 max-h-96 overflow-y-auto" id="purokListContainer"></div>
    </div>
</div>

<div id="editPurokModal" class="fixed inset-0 z-[60] hidden items-end sm:items-center justify-center bg-black/50 px-2 sm:px-4 py-2 sm:py-6">
    <div class="w-full max-w-6xl h-[min(92dvh,calc(100vh-1rem))] sm:h-[calc(100vh-3rem)] overflow-hidden rounded-t-2xl sm:rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl flex flex-col">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4 bg-card-light dark:bg-card-dark z-10">
            <div>
                <h2 class="text-lg font-semibold text-text-light dark:text-text-dark">Edit Purok</h2>
                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Drag the blue corner dots or white edge dots to resize. Use Reset All to remove the shape and draw again.</p>
            </div>
            <button type="button" id="closeEditPurokModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="grid flex-1 min-h-0 gap-0 lg:grid-cols-[minmax(0,1.8fr)_minmax(320px,1fr)]">
            <div class="border-b lg:border-b-0 lg:border-r border-border-light dark:border-border-dark min-h-0">
                <div id="editPurokBoundaryMap" class="purok-map"></div>
            </div>

            <div class="p-6 flex flex-col min-h-0 overflow-y-auto">
                <form id="editPurokForm" method="POST" action="" class="flex flex-col gap-4">
                    @csrf
                    <input type="hidden" name="edit_purok_form" value="1" />
                    <input type="hidden" name="edit_purok_id" id="editPurokId" value="" />
                    <input id="edit_purok_boundary_geojson" name="boundary_geojson" type="hidden" value="" required />

                    @if ($assignedBarangay)
                        <div class="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/10 px-3 py-2 text-sm font-semibold text-primary w-fit">
                            <span class="material-symbols-outlined text-base">location_city</span>
                            {{ $assignedBarangay->name }}
                        </div>
                    @endif

                    <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                        <label for="editPurokName" class="block text-xs uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Purok Name</label>
                        <input id="editPurokName" name="name" type="text" required placeholder="e.g., Purok 1"
                            class="mt-2 w-full h-11 px-3 rounded-lg bg-card-light dark:bg-background-dark border border-border-light dark:border-border-dark text-text-light dark:text-text-dark text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                        <p class="mt-2 text-xs text-text-light/60 dark:text-text-dark/60"><strong>Blue dots</strong> = corners (drag to move). <strong>White dashed dots</strong> = edge midpoints (drag to add a corner).</p>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 h-11 px-4 bg-primary text-white text-sm font-bold rounded-lg hover:opacity-90 transition-colors">
                        <span class="material-symbols-outlined text-base">save</span>
                        Save Changes
                    </button>
                    <button type="button" id="resetEditPurokAllBtn" class="inline-flex w-full items-center justify-center gap-2 h-11 px-4 border border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200 text-sm font-semibold rounded-lg hover:bg-amber-100 dark:hover:bg-amber-950/60 transition-colors">
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                        Reset All
                    </button>
                    <button type="button" id="cancelEditPurokModalBtn" class="inline-flex w-full items-center justify-center gap-2 h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold rounded-lg hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS & Draw JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script src="https://unpkg.com/@turf/turf@7.2.0/turf.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ---------- Data from backend ----------
        const barangayBoundaryGeoJSON = @json($assignedBarangay ? $assignedBarangay->boundary_geojson : null);

        @php
            $puroksWithBoundary = $manageablePuroks->filter(function($p) {
                return !is_null($p->boundary_geojson);
            })->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'boundary' => $p->boundary_geojson,
                ];
            })->values();

            $allPuroksPayload = $manageablePuroks->map(function ($purok) {
                return [
                    'id' => $purok->id,
                    'name' => $purok->name,
                    'boundary' => $purok->boundary_geojson,
                ];
            })->values();
        @endphp
        const existingPuroks = @json($puroksWithBoundary);
        const allPuroks = @json($allPuroksPayload);

        const mindoroBounds = L.latLngBounds([
            [12.35, 120.35],
            [13.65, 121.45],
        ]);
        const mindoroCenter = [12.95, 120.90];
        const mindoroDefaultZoom = 9;
        const purokUpdateBaseUrl = @json(url('/barangay-captain/puroks'));
        const PUROK_GAP_METERS = 0.6;
        const PUROK_OVERLAP_AREA_TOLERANCE = 0.1;

        function capitalizePurokNameFirstLetter(value) {
            const text = String(value ?? '').trim();
            if (!text) {
                return '';
            }

            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        function bindPurokNameCapitalize(input) {
            if (!input) {
                return;
            }

            input.addEventListener('input', function() {
                const value = this.value;
                if (!value || value.charAt(0) === value.charAt(0).toUpperCase()) {
                    return;
                }

                this.value = value.charAt(0).toUpperCase() + value.slice(1);
            });

            input.addEventListener('blur', function() {
                const next = capitalizePurokNameFirstLetter(this.value);
                if (next === this.value) {
                    return;
                }

                this.value = next;
                this.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function parseGeoJsonPayload(raw) {
            if (!raw) {
                return null;
            }

            try {
                return typeof raw === 'string' ? JSON.parse(raw) : raw;
            } catch (error) {
                return null;
            }
        }

        function boundaryHasGeometry(geojson) {
            if (!geojson || typeof geojson !== 'object') {
                return false;
            }

            const type = geojson.type || '';

            if (type === 'FeatureCollection') {
                return Array.isArray(geojson.features) && geojson.features.length > 0;
            }

            if (type === 'Feature') {
                return Boolean(geojson.geometry);
            }

            return type === 'Polygon' || type === 'MultiPolygon';
        }

        function pointInRing(lat, lng, ring) {
            let inside = false;

            if (!Array.isArray(ring) || ring.length < 3) {
                return false;
            }

            for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                const current = ring[i] || [];
                const previous = ring[j] || [];
                const xi = Number(current[0]);
                const yi = Number(current[1]);
                const xj = Number(previous[0]);
                const yj = Number(previous[1]);
                const intersects = ((yi > lat) !== (yj > lat))
                    && (lng < ((xj - xi) * (lat - yi)) / ((yj - yi) || 0.0000001) + xi);

                if (intersects) {
                    inside = !inside;
                }
            }

            return inside;
        }

        function pointOnRingEdge(lat, lng, ring, epsilon = 1e-6) {
            if (!Array.isArray(ring) || ring.length < 2) {
                return false;
            }

            const epsilonSquared = epsilon * epsilon;

            for (let i = 0; i < ring.length; i++) {
                const pointA = ring[i] || [];
                const pointB = ring[(i + 1) % ring.length] || [];
                const latA = Number(pointA[1]);
                const lngA = Number(pointA[0]);
                const latB = Number(pointB[1]);
                const lngB = Number(pointB[0]);
                const candidate = closestPointOnSegment(lat, lng, latA, lngA, latB, lngB);
                const distanceSquared = ((candidate.lat - lat) ** 2) + ((candidate.lng - lng) ** 2);

                if (distanceSquared <= epsilonSquared) {
                    return true;
                }
            }

            return false;
        }

        function isPointWithinBoundary(lat, lng, boundary, epsilon = 1e-6) {
            if (!boundary) {
                return true;
            }

            if (geometryContainsPoint(boundary, lat, lng)) {
                return true;
            }

            return getBoundaryOuterRings(boundary).some((ring) => pointOnRingEdge(lat, lng, ring, epsilon));
        }

        function geometryContainsPoint(geometry, lat, lng) {
            if (!geometry || typeof geometry !== 'object') {
                return false;
            }

            if (geometry.type === 'FeatureCollection') {
                return Array.isArray(geometry.features)
                    && geometry.features.some((feature) => geometryContainsPoint(feature, lat, lng));
            }

            if (geometry.type === 'Feature') {
                return geometryContainsPoint(geometry.geometry, lat, lng);
            }

            if (geometry.type === 'Polygon') {
                const rings = Array.isArray(geometry.coordinates) ? geometry.coordinates : [];
                const outerRing = rings[0] || [];

                if (!pointInRing(lat, lng, outerRing)) {
                    return false;
                }

                return !rings.slice(1).some((hole) => pointInRing(lat, lng, hole));
            }

            if (geometry.type === 'MultiPolygon') {
                return Array.isArray(geometry.coordinates)
                    && geometry.coordinates.some((polygon) => geometryContainsPoint({ type: 'Polygon', coordinates: polygon }, lat, lng));
            }

            return false;
        }

        function isGeometryWithinBoundary(geometry, boundary) {
            if (!geometry || !boundary) {
                return true;
            }

            const geometryType = geometry.type || '';

            if (geometryType === 'FeatureCollection') {
                return Array.isArray(geometry.features)
                    && geometry.features.length > 0
                    && geometry.features.every((feature) => isGeometryWithinBoundary(feature, boundary));
            }

            if (geometryType === 'Feature') {
                return isGeometryWithinBoundary(geometry.geometry, boundary);
            }

            if (geometryType === 'Polygon') {
                const rings = geometry.coordinates || [];
                const outerRing = rings[0] || [];

                return outerRing.every((point) => {
                    const lng = Number(point[0]);
                    const lat = Number(point[1]);
                    return isPointWithinBoundary(lat, lng, boundary);
                });
            }

            if (geometryType === 'MultiPolygon') {
                return (geometry.coordinates || []).every((polygon) => isGeometryWithinBoundary({
                    type: 'Polygon',
                    coordinates: polygon,
                }, boundary));
            }

            return false;
        }

        function getBoundaryOuterRings(boundary) {
            const rings = [];

            const walk = function(geometry) {
                if (!geometry || typeof geometry !== 'object') {
                    return;
                }

                const type = geometry.type || '';

                if (type === 'FeatureCollection') {
                    (geometry.features || []).forEach((feature) => walk(feature));
                    return;
                }

                if (type === 'Feature') {
                    walk(geometry.geometry);
                    return;
                }

                if (type === 'Polygon') {
                    const outerRing = geometry.coordinates?.[0];
                    if (Array.isArray(outerRing) && outerRing.length >= 3) {
                        rings.push(outerRing);
                    }
                    return;
                }

                if (type === 'MultiPolygon') {
                    (geometry.coordinates || []).forEach((polygon) => {
                        const outerRing = polygon?.[0];
                        if (Array.isArray(outerRing) && outerRing.length >= 3) {
                            rings.push(outerRing);
                        }
                    });
                }
            };

            walk(boundary);
            return rings;
        }

        function closestPointOnSegment(lat, lng, latA, lngA, latB, lngB) {
            const dx = lngB - lngA;
            const dy = latB - latA;

            if (dx === 0 && dy === 0) {
                return { lat: latA, lng: lngA };
            }

            let t = ((lng - lngA) * dx + (lat - latA) * dy) / (dx * dx + dy * dy);
            t = Math.max(0, Math.min(1, t));

            return {
                lat: latA + (t * dy),
                lng: lngA + (t * dx),
            };
        }

        function snapPointToBoundary(lat, lng, boundary) {
            if (!boundary || isPointWithinBoundary(lat, lng, boundary)) {
                return { lat, lng, moved: false };
            }

            const rings = getBoundaryOuterRings(boundary);
            let bestPoint = { lat, lng };
            let bestDistance = Infinity;

            rings.forEach((ring) => {
                for (let i = 0; i < ring.length - 1; i++) {
                    const pointA = ring[i] || [];
                    const pointB = ring[i + 1] || [];
                    const latA = Number(pointA[1]);
                    const lngA = Number(pointA[0]);
                    const latB = Number(pointB[1]);
                    const lngB = Number(pointB[0]);
                    const candidate = closestPointOnSegment(lat, lng, latA, lngA, latB, lngB);
                    const distance = ((candidate.lat - lat) ** 2) + ((candidate.lng - lng) ** 2);

                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestPoint = candidate;
                    }
                }
            });

            return {
                lat: bestPoint.lat,
                lng: bestPoint.lng,
                moved: bestDistance < Infinity,
            };
        }

        function snapPolygonLayerToBoundary(layer, boundary) {
            if (!layer?.getLatLngs || !boundary) {
                return layer;
            }

            const snapLatLng = function(latlng) {
                const snapped = snapPointToBoundary(latlng.lat, latlng.lng, boundary);
                return L.latLng(snapped.lat, snapped.lng);
            };

            const snapLatLngs = function(latlngs) {
                if (!Array.isArray(latlngs) || latlngs.length === 0) {
                    return latlngs;
                }

                if (latlngs[0] instanceof L.LatLng || typeof latlngs[0]?.lat === 'number') {
                    return latlngs.map(snapLatLng);
                }

                return latlngs.map(snapLatLngs);
            };

            layer.setLatLngs(snapLatLngs(layer.getLatLngs()));

            if (typeof layer.redraw === 'function') {
                layer.redraw();
            }

            return layer;
        }

        function nearestPointOnRings(lat, lng, rings) {
            let bestPoint = { lat, lng };
            let bestDistance = Infinity;

            rings.forEach((ring) => {
                for (let i = 0; i < ring.length - 1; i++) {
                    const pointA = ring[i] || [];
                    const pointB = ring[i + 1] || [];
                    const latA = Number(pointA[1]);
                    const lngA = Number(pointA[0]);
                    const latB = Number(pointB[1]);
                    const lngB = Number(pointB[0]);
                    const candidate = closestPointOnSegment(lat, lng, latA, lngA, latB, lngB);
                    const distance = ((candidate.lat - lat) ** 2) + ((candidate.lng - lng) ** 2);

                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestPoint = candidate;
                    }
                }
            });

            return {
                point: bestPoint,
                distanceSquared: bestDistance,
            };
        }

        function distanceMetersBetween(latA, lngA, latB, lngB) {
            if (window.turf) {
                return turf.distance(
                    turf.point([lngA, latA]),
                    turf.point([lngB, latB]),
                    { units: 'meters' }
                );
            }

            const latDistance = (latB - latA) * 111320;
            const lngDistance = (lngB - lngA) * 111320 * Math.cos(((latA + latB) / 2) * (Math.PI / 180));

            return Math.sqrt((latDistance ** 2) + (lngDistance ** 2));
        }

        function snapPointToOccupiedPurokBoundaries(lat, lng, excludePurokId = null) {
            let bestPoint = { lat, lng };
            let bestDistance = Infinity;
            let moved = false;

            existingPuroks.forEach((purok) => {
                if (excludePurokId != null && Number(purok.id) === Number(excludePurokId)) {
                    return;
                }

                const occupied = getOccupiedFeature(purok.boundary, `purok-${purok.id}`);
                if (!occupied) {
                    return;
                }

                const rings = getBoundaryOuterRings(occupied);
                const nearest = nearestPointOnRings(lat, lng, rings);
                const insideOccupied = geometryContainsPoint(occupied, lat, lng);

                if (!insideOccupied) {
                    return;
                }

                if (nearest.distanceSquared < bestDistance) {
                    bestDistance = nearest.distanceSquared;
                    bestPoint = nearest.point;
                    moved = true;
                }
            });

            return {
                lat: bestPoint.lat,
                lng: bestPoint.lng,
                moved,
            };
        }

        function snapPolygonLayerToOccupiedPuroks(layer, excludePurokId = null) {
            if (!layer?.getLatLngs || existingPuroks.length === 0) {
                return layer;
            }

            const snapLatLng = function(latlng) {
                const snapped = snapPointToOccupiedPurokBoundaries(latlng.lat, latlng.lng, excludePurokId);
                return L.latLng(snapped.lat, snapped.lng);
            };

            const snapLatLngs = function(latlngs) {
                if (!Array.isArray(latlngs) || latlngs.length === 0) {
                    return latlngs;
                }

                if (latlngs[0] instanceof L.LatLng || typeof latlngs[0]?.lat === 'number') {
                    return latlngs.map(snapLatLng);
                }

                return latlngs.map(snapLatLngs);
            };

            layer.setLatLngs(snapLatLngs(layer.getLatLngs()));

            if (typeof layer.redraw === 'function') {
                layer.redraw();
            }

            return layer;
        }

        function snapPolygonToAllConstraints(layer, boundary, excludePurokId = null) {
            if (!layer) {
                return layer;
            }

            if (hasValidBoundary && boundary) {
                snapPolygonLayerToBoundary(layer, boundary);
            }

            snapPolygonLayerToOccupiedPuroks(layer, excludePurokId);

            if (hasValidBoundary && boundary) {
                snapPolygonLayerToBoundary(layer, boundary);
            }

            return layer;
        }

        let cachedFreeAreaFeature = null;
        let cachedFreeAreaKey = '';
        const cachedOccupiedClipFeatures = new Map();

        function resetFreeAreaCache() {
            cachedFreeAreaFeature = null;
            cachedFreeAreaKey = '';
            cachedOccupiedClipFeatures.clear();
        }

        function getOccupiedFeature(boundaryGeojson, cacheKey = null) {
            const cacheId = cacheKey ? `${cacheKey}-exact` : null;
            if (cacheId && cachedOccupiedClipFeatures.has(cacheId)) {
                return cachedOccupiedClipFeatures.get(cacheId);
            }

            const occupied = normalizeTurfPolygonFeature(boundaryGeojson);
            if (cacheId && occupied) {
                cachedOccupiedClipFeatures.set(cacheId, occupied);
            }

            return occupied;
        }

        function getOccupiedClipFeature(boundaryGeojson, cacheKey = null) {
            const cacheId = cacheKey ? `${cacheKey}-clip` : null;
            if (cacheId && cachedOccupiedClipFeatures.has(cacheId)) {
                return cachedOccupiedClipFeatures.get(cacheId);
            }

            const occupied = getOccupiedFeature(boundaryGeojson, cacheKey);
            if (!occupied) {
                return null;
            }

            if (!window.turf || PUROK_GAP_METERS <= 0) {
                return occupied;
            }

            let clipFeature = occupied;

            try {
                const buffered = turf.buffer(occupied, PUROK_GAP_METERS, {
                    units: 'meters',
                    steps: 8,
                });
                clipFeature = normalizeTurfPolygonFeature(buffered) || buffered;
            } catch (error) {
                clipFeature = occupied;
            }

            if (cacheId) {
                cachedOccupiedClipFeatures.set(cacheId, clipFeature);
            }

            return clipFeature;
        }

        function subtractOccupiedFromFeature(feature, excludePurokId = null) {
            if (!feature) {
                return null;
            }

            let nextFeature = feature;

            existingPuroks.forEach((purok) => {
                if (!nextFeature) {
                    return;
                }

                if (excludePurokId != null && Number(purok.id) === Number(excludePurokId)) {
                    return;
                }

                const occupiedGap = getOccupiedClipFeature(purok.boundary, `purok-${purok.id}`);
                if (!occupiedGap) {
                    return;
                }

                const diff = turfDifferenceFeatures(nextFeature, occupiedGap);
                if (diff) {
                    nextFeature = diff;
                    return;
                }

                try {
                    if (turf.booleanWithin(nextFeature, occupiedGap)) {
                        nextFeature = null;
                    }
                } catch (error) {
                    // Keep the current feature if turf cannot evaluate containment.
                }
            });

            return nextFeature;
        }

        function turfPairCollection(featureA, featureB) {
            return turf.featureCollection([featureA, featureB]);
        }

        function turfIntersectFeatures(featureA, featureB) {
            if (!window.turf || !featureA || !featureB) {
                return null;
            }

            try {
                return turf.intersect(turfPairCollection(featureA, featureB));
            } catch (error) {
                return null;
            }
        }

        function turfDifferenceFeatures(featureA, featureB) {
            if (!window.turf || !featureA || !featureB) {
                return null;
            }

            try {
                return turf.difference(turfPairCollection(featureA, featureB));
            } catch (error) {
                return null;
            }
        }

        const purokNewMapStyle = {
            color: '#4A90E2',
            weight: 2,
            fillColor: '#4A90E2',
            fillOpacity: 0.2,
        };

        function clipGeoJsonToFreeArea(geojson, excludePurokId = null) {
            if (!window.turf) {
                return { ok: false, adjusted: false, reason: 'no-turf', feature: null };
            }

            let feature = normalizeTurfPolygonFeature(geojson);
            if (!feature) {
                return { ok: false, adjusted: false, reason: 'invalid-shape', feature: null };
            }

            const beforeArea = turf.area(feature);

            if (hasValidBoundary && parsedBarangayBoundary) {
                const barangayFeature = normalizeTurfPolygonFeature(parsedBarangayBoundary);
                if (barangayFeature) {
                    const clippedToBarangay = turfIntersectFeatures(feature, barangayFeature);
                    if (clippedToBarangay) {
                        feature = clippedToBarangay;
                    }
                }
            }

            feature = subtractOccupiedFromFeature(feature, excludePurokId);

            if (!feature) {
                return { ok: false, adjusted: true, reason: 'fully-overlap', feature: null };
            }

            const largest = pickLargestPolygonFeature(feature);
            if (!largest || turf.area(largest) < 1) {
                return { ok: false, adjusted: true, reason: 'fully-overlap', feature: null };
            }

            const afterArea = turf.area(largest);
            const adjusted = afterArea < beforeArea * 0.995;

            return {
                ok: true,
                adjusted,
                feature: largest,
            };
        }

        function createLayerFromTurfFeature(feature, style) {
            if (!feature || !window.L) {
                return null;
            }

            const geojson = feature.type === 'Feature'
                ? feature
                : {
                    type: 'Feature',
                    properties: {},
                    geometry: feature.geometry || feature,
                };

            const group = L.geoJSON(geojson, {
                style: style || purokNewMapStyle,
            });

            const layers = group.getLayers();
            return layers.length > 0 ? layers[0] : null;
        }

        function removeMapLayer(targetMap, layer) {
            if (!targetMap || !layer) {
                return;
            }

            if (targetMap.hasLayer(layer)) {
                targetMap.removeLayer(layer);
            }
        }

        function closePolygonRing(ring) {
            if (!Array.isArray(ring) || ring.length < 3) {
                return ring;
            }

            const first = ring[0];
            const last = ring[ring.length - 1];
            if (Number(first[0]) === Number(last[0]) && Number(first[1]) === Number(last[1])) {
                return ring;
            }

            return [...ring, [...first]];
        }

        function turfFeatureFromGeoJson(geojson) {
            const parsed = parseGeoJsonPayload(geojson);
            if (!parsed) {
                return null;
            }

            if (parsed.type === 'Feature') {
                return parsed;
            }

            if (parsed.type === 'FeatureCollection' && parsed.features?.length) {
                return parsed.features[0];
            }

            if (parsed.type === 'Polygon' || parsed.type === 'MultiPolygon') {
                return {
                    type: 'Feature',
                    properties: {},
                    geometry: parsed,
                };
            }

            return null;
        }

        function normalizeTurfPolygonFeature(geojson) {
            if (!window.turf) {
                return turfFeatureFromGeoJson(geojson);
            }

            let feature = turfFeatureFromGeoJson(geojson);
            if (!feature?.geometry) {
                return null;
            }

            const geometryType = feature.geometry.type;
            if (geometryType !== 'Polygon' && geometryType !== 'MultiPolygon') {
                return null;
            }

            try {
                if (geometryType === 'Polygon' && Array.isArray(feature.geometry.coordinates)) {
                    feature.geometry.coordinates = feature.geometry.coordinates.map(closePolygonRing);
                }

                if (geometryType === 'MultiPolygon' && Array.isArray(feature.geometry.coordinates)) {
                    feature.geometry.coordinates = feature.geometry.coordinates.map((polygon) => polygon.map(closePolygonRing));
                }

                feature = turf.cleanCoords(feature);
                feature = turf.rewind(feature, { reverse: false });

                const area = turf.area(feature);
                if (!Number.isFinite(area) || area <= 0) {
                    return null;
                }

                return feature;
            } catch (error) {
                return turfFeatureFromGeoJson(geojson);
            }
        }

        function flattenToPolygonFeatures(feature) {
            if (!feature || !window.turf) {
                return [];
            }

            const geometry = feature.geometry || feature;
            if (!geometry) {
                return [];
            }

            if (geometry.type === 'Polygon') {
                return [feature.type === 'Feature' ? feature : turf.feature(geometry)];
            }

            if (geometry.type === 'MultiPolygon') {
                return geometry.coordinates.map((coords) => turf.polygon(coords));
            }

            return [];
        }

        function pickLargestPolygonFeature(feature) {
            const pieces = flattenToPolygonFeatures(feature);
            if (!pieces.length) {
                return null;
            }

            return pieces.reduce((best, piece) => {
                if (!best) {
                    return piece;
                }

                return turf.area(piece) > turf.area(best) ? piece : best;
            }, null);
        }

        function geometryToLeafletLatLngs(geometry) {
            if (!geometry) {
                return null;
            }

            if (geometry.type === 'Polygon') {
                const rings = geometry.coordinates || [];
                if (!rings.length || !rings[0]?.length) {
                    return null;
                }

                const mapped = rings.map((ring) => ring.map((point) => L.latLng(Number(point[1]), Number(point[0]))));
                return mapped.length === 1 ? mapped[0] : mapped;
            }

            if (geometry.type === 'MultiPolygon') {
                let bestPolygonCoords = null;
                let bestArea = -1;

                geometry.coordinates.forEach((polygonCoords) => {
                    try {
                        const area = turf.area(turf.polygon(polygonCoords));
                        if (area > bestArea) {
                            bestArea = area;
                            bestPolygonCoords = polygonCoords;
                        }
                    } catch (error) {
                        // Ignore invalid polygon parts.
                    }
                });

                if (!bestPolygonCoords) {
                    return null;
                }

                const mapped = bestPolygonCoords.map((ring) => ring.map((point) => L.latLng(Number(point[1]), Number(point[0]))));
                return mapped.length === 1 ? mapped[0] : mapped;
            }

            return null;
        }

        function setLayerFromTurfFeature(layer, feature) {
            const geometry = feature?.geometry || feature;
            const latlngs = geometryToLeafletLatLngs(geometry);
            if (!layer?.setLatLngs || !latlngs) {
                return false;
            }

            layer.setLatLngs(latlngs);

            if (typeof layer.redraw === 'function') {
                layer.redraw();
            }

            return true;
        }

        function countPolygonVertices(layer) {
            if (!layer?.getLatLngs) {
                return 0;
            }

            const walk = function(latlngs) {
                if (!Array.isArray(latlngs) || latlngs.length === 0) {
                    return 0;
                }

                if (latlngs[0] instanceof L.LatLng || typeof latlngs[0]?.lat === 'number') {
                    return latlngs.length;
                }

                return latlngs.reduce((total, part) => total + walk(part), 0);
            };

            return walk(layer.getLatLngs());
        }

        function getFreeAreaFeature(excludePurokId = null) {
            if (!window.turf) {
                return null;
            }

            const cacheKey = `${excludePurokId ?? 'none'}:${existingPuroks.map((purok) => purok.id).join(',')}`;
            if (cachedFreeAreaKey === cacheKey && cachedFreeAreaFeature) {
                return cachedFreeAreaFeature;
            }

            let free = null;

            if (hasValidBoundary && parsedBarangayBoundary) {
                free = normalizeTurfPolygonFeature(parsedBarangayBoundary);
            }

            if (!free) {
                cachedFreeAreaFeature = null;
                cachedFreeAreaKey = cacheKey;
                return null;
            }

            existingPuroks.forEach((purok) => {
                if (excludePurokId != null && Number(purok.id) === Number(excludePurokId)) {
                    return;
                }

                const occupiedGap = getOccupiedClipFeature(purok.boundary, `purok-${purok.id}`);
                if (!occupiedGap) {
                    return;
                }

                try {
                    const diff = turfDifferenceFeatures(free, occupiedGap);
                    if (diff) {
                        free = normalizeTurfPolygonFeature(diff) || diff;
                    }
                } catch (error) {
                    // Keep the previous free area if this subtraction fails.
                }
            });

            cachedFreeAreaFeature = free;
            cachedFreeAreaKey = cacheKey;
            return free;
        }

        function clipLayerToFreeArea(layer, excludePurokId = null) {
            if (!layer?.toGeoJSON) {
                return { ok: false, adjusted: false, reason: 'invalid-shape' };
            }

            const clipResult = clipGeoJsonToFreeArea(layer.toGeoJSON(), excludePurokId);
            if (!clipResult.ok || !clipResult.feature) {
                return {
                    ok: false,
                    adjusted: clipResult.adjusted,
                    reason: clipResult.reason || 'fully-overlap',
                };
            }

            if (!setLayerFromTurfFeature(layer, clipResult.feature)) {
                return { ok: false, adjusted: clipResult.adjusted, reason: 'invalid-shape' };
            }

            return { ok: true, adjusted: clipResult.adjusted };
        }

        function finalizePurokShape(layer, excludePurokId = null) {
            const result = clipLayerToFreeArea(layer, excludePurokId);
            if (result.ok) {
                snapPolygonLayerToOccupiedPuroks(layer, excludePurokId);
            }

            return result;
        }

        function geometryOverlapsOccupiedPuroks(geometry, excludePurokId = null) {
            if (!geometry || !window.turf) {
                return false;
            }

            const feature = normalizeTurfPolygonFeature(geometry);
            if (!feature) {
                return false;
            }

            return existingPuroks.some((purok) => {
                if (excludePurokId != null && Number(purok.id) === Number(excludePurokId)) {
                    return false;
                }

                const occupiedFeature = normalizeTurfPolygonFeature(purok.boundary);
                if (!occupiedFeature) {
                    return false;
                }

                try {
                    const intersection = turfIntersectFeatures(feature, occupiedFeature);
                    if (intersection && turf.area(intersection) > PUROK_OVERLAP_AREA_TOLERANCE) {
                        return true;
                    }

                    if (turf.booleanOverlap(feature, occupiedFeature)) {
                        return true;
                    }

                    return turf.booleanWithin(feature, occupiedFeature);
                } catch (error) {
                    return false;
                }
            });
        }

        function setPurokOccupiedLabel(layer, name) {
            if (!layer) {
                return;
            }

            const label = (name || '').trim() || 'Taken';
            layer.unbindTooltip();
            layer.bindTooltip(label, {
                permanent: true,
                direction: 'center',
                className: 'purok-occupied-label',
                opacity: 1,
            });
            layer.openTooltip();
        }

        function registerBoundarySnapHandlers(targetMap, boundary, constraintOptions = {}) {
            if (!targetMap) {
                return;
            }

            const excludePurokId = constraintOptions.excludePurokId ?? null;
            const snapOccupied = constraintOptions.snapOccupied !== false;

            const applyConstraints = function(layer) {
                if (!layer) {
                    return;
                }

                if (snapOccupied) {
                    snapPolygonToAllConstraints(layer, boundary, excludePurokId);

                    if (countPolygonVertices(layer) >= 3) {
                        clipLayerToFreeArea(layer, excludePurokId);
                        snapPolygonToAllConstraints(layer, boundary, excludePurokId);
                    }

                    return;
                }

                if (hasValidBoundary && boundary) {
                    snapPolygonLayerToBoundary(layer, boundary);
                }
            };

            const snapMarkerToConstraints = function(marker) {
                const latlng = marker.getLatLng();
                let nextLat = latlng.lat;
                let nextLng = latlng.lng;
                let moved = false;

                if (hasValidBoundary && boundary) {
                    const barangaySnap = snapPointToBoundary(nextLat, nextLng, boundary);
                    nextLat = barangaySnap.lat;
                    nextLng = barangaySnap.lng;
                    moved = moved || barangaySnap.moved;
                }

                if (snapOccupied) {
                    const occupiedSnap = snapPointToOccupiedPurokBoundaries(nextLat, nextLng, excludePurokId);
                    nextLat = occupiedSnap.lat;
                    nextLng = occupiedSnap.lng;
                    moved = moved || occupiedSnap.moved;

                    if (hasValidBoundary && boundary) {
                        const barangaySnap = snapPointToBoundary(nextLat, nextLng, boundary);
                        nextLat = barangaySnap.lat;
                        nextLng = barangaySnap.lng;
                        moved = moved || barangaySnap.moved;
                    }
                }

                if (moved) {
                    marker.setLatLng(L.latLng(nextLat, nextLng));
                }
            };

            targetMap.on(L.Draw.Event.DRAWVERTEX, function(event) {
                if (event.layers) {
                    applyConstraints(event.layers);
                }
            });

            targetMap.on(L.Draw.Event.EDITVERTEX, function(event) {
                if (event.poly) {
                    applyConstraints(event.poly);
                }
            });

            targetMap.on(L.Draw.Event.EDITSTART, function(event) {
                event.layers.eachLayer(function(layer) {
                    const verticesHandler = layer.editing?._verticesHandler;
                    if (!verticesHandler?._markerGroup) {
                        return;
                    }

                    verticesHandler._markerGroup.eachLayer(function(marker) {
                        marker.on('drag', function() {
                            snapMarkerToConstraints(marker);
                        });
                    });
                });
            });
        }

        function setPurokNameLabel(layer, name) {
            if (!layer) {
                return;
            }

            const label = (name || '').trim() || 'New Purok';
            layer.unbindTooltip();
            layer.bindTooltip(label, {
                permanent: true,
                direction: 'center',
                className: 'purok-map-label',
                opacity: 1,
            });
            layer.openTooltip();
        }

        function syncDraftPurokBoundaryInput() {
            const input = document.getElementById('purok_boundary_geojson');
            if (!input || !drawnItems) {
                return;
            }

            const layers = drawnItems.getLayers();
            input.value = layers.length === 0 ? '' : JSON.stringify(layers[0].toGeoJSON());
        }

        const parsedBarangayBoundary = parseGeoJsonPayload(barangayBoundaryGeoJSON);
        const hasValidBoundary = boundaryHasGeometry(parsedBarangayBoundary);
        let barangayBounds = mindoroBounds;
        let barangayCenter = mindoroCenter;

        if (hasValidBoundary) {
            const previewLayer = L.geoJSON(parsedBarangayBoundary);
            const previewBounds = previewLayer.getBounds();
            if (previewBounds.isValid()) {
                barangayBounds = previewBounds;
                barangayCenter = previewBounds.getCenter();
            }
        }

        // ---------- UI Logic (modals, toggles, etc.) ----------
        const bhwModal = document.getElementById('bhwModal');
        const purokModal = document.getElementById('purokModal');
        const assignModal = document.getElementById('assignModal');
        const openAddBhwModalBtn = document.getElementById('openAddBhwModalBtn');
        const openAddPurokModalBtn = document.getElementById('openAddPurokModalBtn');
        const closeAddBhwModalBtn = document.getElementById('closeAddBhwModalBtn');
        const cancelAddBhwModalBtn = document.getElementById('cancelAddBhwModalBtn');
        const closeAddPurokModalBtn = document.getElementById('closeAddPurokModalBtn');
        const cancelAddPurokModalBtn = document.getElementById('cancelAddPurokModalBtn');
        const closeAssignModalBtn = document.getElementById('closeAssignModalBtn');
        const cancelAssignModalBtn = document.getElementById('cancelAssignModalBtn');
        const kickModal = document.getElementById('kickModal');
        const closeKickModalBtn = document.getElementById('closeKickModalBtn');
        const cancelKickModalBtn = document.getElementById('cancelKickModalBtn');
        const purokSearch = document.getElementById('purokSearch');
        const workerSearch = document.getElementById('workerSearch');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        const passwordEyeOpen = document.getElementById('passwordEyeOpen');
        const passwordEyeClosed = document.getElementById('passwordEyeClosed');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordEyeOpen = document.getElementById('confirmPasswordEyeOpen');
        const confirmPasswordEyeClosed = document.getElementById('confirmPasswordEyeClosed');
        const assignModalSubtitle = document.getElementById('assignModalSubtitle');
        const assignWorkerId = document.getElementById('assignWorkerId');
        const assignWorkerForm = document.getElementById('assignWorkerForm');
        const assignPurokIdsContainer = document.getElementById('assignPurokIdsContainer');
        const assignSelectionSummary = document.getElementById('assignSelectionSummary');
        const kickWorkerId = document.getElementById('kickWorkerId');
        const kickWorkerName = document.getElementById('kickWorkerName');
        const kickModalSubtitle = document.getElementById('kickModalSubtitle');
        const purokOptions = document.querySelectorAll('.purok-option');
        const workerRows = document.querySelectorAll('.worker-row');
        const openAssignButtons = document.querySelectorAll('.open-assign-modal-btn');
        const openKickButtons = document.querySelectorAll('.open-kick-modal-btn');
        const presetWorkerId = @json(old('worker_id'));
        const presetPurokIds = @json(old('purok_ids', []));
        const presetEditPurokId = @json(old('edit_purok_id'));
        const presetEditPurokName = @json(old('name'));
        const toastData = document.getElementById('toastData');
        const purokNameInput = document.getElementById('purokName');
        bindPurokNameCapitalize(purokNameInput);
        const purokForm = document.getElementById('purokForm');
        const editPurokModal = document.getElementById('editPurokModal');
        const editPurokForm = document.getElementById('editPurokForm');
        const editPurokNameInput = document.getElementById('editPurokName');
        bindPurokNameCapitalize(editPurokNameInput);
        const closeEditPurokModalBtn = document.getElementById('closeEditPurokModalBtn');
        const cancelEditPurokModalBtn = document.getElementById('cancelEditPurokModalBtn');
        const resetEditPurokAllBtn = document.getElementById('resetEditPurokAllBtn');
        let toastTimer = null;

        // ---------- Modal functions (all) ----------
        function openBhwModal() { if (bhwModal) { bhwModal.classList.remove('hidden'); bhwModal.classList.add('flex'); } }
        function closeBhwModal() { if (bhwModal) { bhwModal.classList.add('hidden'); bhwModal.classList.remove('flex'); } }
        if (openAddBhwModalBtn) openAddBhwModalBtn.addEventListener('click', openBhwModal);
        if (closeAddBhwModalBtn) closeAddBhwModalBtn.addEventListener('click', closeBhwModal);
        if (cancelAddBhwModalBtn) cancelAddBhwModalBtn.addEventListener('click', closeBhwModal);

        function getSelectedAssignPurokIds() {
            return Array.from(document.querySelectorAll('.purok-option.is-selected'))
                .map((option) => option.dataset.purokId || '')
                .filter(Boolean);
        }

        function updateAssignSelectionSummary() {
            const count = getSelectedAssignPurokIds().length;
            if (assignSelectionSummary) {
                assignSelectionSummary.textContent = `${count} purok selected`;
            }
        }

        function refreshAssignPurokAvailability(workerId) {
            const workerKey = String(workerId || '');
            purokOptions.forEach((option) => {
                const assignedId = String(option.dataset.assignedBhwId || '');
                const assignedName = option.dataset.assignedBhwName || '';
                const statusEl = option.querySelector('.purok-assign-status');
                const isOwn = assignedId !== '' && assignedId === workerKey;
                const isTaken = assignedId !== '' && !isOwn;

                option.classList.toggle('is-occupied-locked', isTaken);
                option.disabled = isTaken;

                if (!statusEl) return;
                if (isTaken) {
                    statusEl.textContent = `Assigned to ${assignedName || 'another BHW'}`;
                } else if (isOwn) {
                    statusEl.textContent = 'Currently assigned to this BHW';
                } else {
                    statusEl.textContent = 'Available';
                }
            });
        }

        function openAssignModal(workerId, currentPurokIds = []) {
            if (!assignModal || !assignWorkerId) return;
            assignWorkerId.value = workerId;
            assignModalSubtitle.textContent = 'Select one or more available purok for this BHW worker. Each purok can only have one BHW.';
            assignModal.classList.remove('hidden');
            assignModal.classList.add('flex');
            clearSelectedPurok();
            refreshAssignPurokAvailability(workerId);
            currentPurokIds.forEach((purokId) => {
                const presetOption = document.querySelector(`.purok-option[data-purok-id="${purokId}"]`);
                if (presetOption && !presetOption.classList.contains('is-occupied-locked')) {
                    presetOption.classList.add('is-selected');
                }
            });
            updateAssignSelectionSummary();
        }
        function closeAssignModal() { if (assignModal) { assignModal.classList.add('hidden'); assignModal.classList.remove('flex'); } }
        if (closeAssignModalBtn) closeAssignModalBtn.addEventListener('click', closeAssignModal);
        if (cancelAssignModalBtn) cancelAssignModalBtn.addEventListener('click', closeAssignModal);

        function openKickModal(workerId, workerName) {
            if (!kickModal || !kickWorkerId) return;
            kickWorkerId.value = workerId;
            kickWorkerName.textContent = workerName || 'this worker';
            kickModalSubtitle.textContent = 'This action cannot be undone.';
            kickModal.classList.remove('hidden');
            kickModal.classList.add('flex');
        }
        function closeKickModal() { if (kickModal) { kickModal.classList.add('hidden'); kickModal.classList.remove('flex'); } }
        if (closeKickModalBtn) closeKickModalBtn.addEventListener('click', closeKickModal);
        if (cancelKickModalBtn) cancelKickModalBtn.addEventListener('click', closeKickModal);

        // ---------- Assign buttons ----------
        openAssignButtons.forEach(button => {
            button.addEventListener('click', function() {
                const workerId = button.dataset.workerId || '';
                const currentPurokIds = (button.dataset.currentPurokIds || '')
                    .split(',')
                    .map((value) => value.trim())
                    .filter(Boolean);
                openAssignModal(workerId, currentPurokIds);
            });
        });

        openKickButtons.forEach(button => {
            button.addEventListener('click', function() {
                const workerId = button.dataset.workerId || '';
                const workerName = button.dataset.workerName || '';
                openKickModal(workerId, workerName);
            });
        });

        // ---------- Purok options in assign modal ----------
        const clearSelectedPurok = function() {
            purokOptions.forEach(opt => opt.classList.remove('is-selected'));
            updateAssignSelectionSummary();
        };
        purokOptions.forEach(option => {
            option.addEventListener('click', function() {
                if (option.classList.contains('is-occupied-locked') || option.disabled) {
                    return;
                }
                option.classList.toggle('is-selected');
                updateAssignSelectionSummary();
            });
        });

        if (assignWorkerForm) {
            assignWorkerForm.addEventListener('submit', function() {
                if (!assignPurokIdsContainer) return;
                assignPurokIdsContainer.innerHTML = '';
                getSelectedAssignPurokIds().forEach((purokId) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'purok_ids[]';
                    input.value = purokId;
                    assignPurokIdsContainer.appendChild(input);
                });
            });
        }

        // ---------- Search & filter ----------
        if (purokSearch) {
            purokSearch.addEventListener('input', function() {
                const normalized = (this.value || '').trim().toLowerCase();
                purokOptions.forEach(option => {
                    const name = option.dataset.purokName || '';
                    option.style.display = name.includes(normalized) ? '' : 'none';
                });
            });
        }

        if (workerSearch) {
            workerSearch.addEventListener('input', function() {
                const normalized = (this.value || '').trim().toLowerCase();
                workerRows.forEach(row => {
                    const rowName = row.dataset.workerName || '';
                    row.style.display = rowName.includes(normalized) ? '' : 'none';
                });
            });
        }

        // ---------- Toast ----------
        function showToast(message, type) {
            if (!message) return;
            const existing = document.getElementById('floatingToast');
            if (existing) existing.remove();
            const toast = document.createElement('div');
            toast.id = 'floatingToast';
            toast.className = `toast-enter fixed top-4 right-4 z-[200] max-w-sm rounded-xl px-4 py-3 shadow-lg border ${type === 'error' ? 'border-red-200 bg-red-50 text-red-700 dark:bg-red-950/90 dark:text-red-200 dark:border-red-900' : 'border-green-200 bg-green-50 text-green-700 dark:bg-green-950/90 dark:text-green-200 dark:border-green-900'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = window.setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                window.setTimeout(() => toast.remove(), 180);
            }, 2500);
        }
        if (toastData) {
            showToast(toastData.dataset.toastMessage || '', toastData.dataset.toastType || 'success');
        }

        // ---------- Password toggle ----------
        if (togglePassword && passwordInput && passwordEyeOpen && passwordEyeClosed) {
            togglePassword.addEventListener('click', () => {
                const isHidden = passwordInput.getAttribute('type') === 'password';
                const nextType = isHidden ? 'text' : 'password';
                passwordInput.setAttribute('type', nextType);
                const nowVisible = nextType === 'text';
                togglePassword.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                togglePassword.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                passwordEyeOpen.classList.toggle('hidden', nowVisible);
                passwordEyeClosed.classList.toggle('hidden', !nowVisible);
            });
        }

        if (toggleConfirmPassword && passwordConfirmInput && confirmPasswordEyeOpen && confirmPasswordEyeClosed) {
            toggleConfirmPassword.addEventListener('click', () => {
                const isHidden = passwordConfirmInput.getAttribute('type') === 'password';
                const nextType = isHidden ? 'text' : 'password';
                passwordConfirmInput.setAttribute('type', nextType);
                const nowVisible = nextType === 'text';
                toggleConfirmPassword.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                toggleConfirmPassword.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                confirmPasswordEyeOpen.classList.toggle('hidden', nowVisible);
                confirmPasswordEyeClosed.classList.toggle('hidden', !nowVisible);
            });
        }

        // ---------- Purok List Modal ----------
        const purokListModal = document.getElementById('purokListModal');
        const openPurokListModalBtn = document.getElementById('openPurokListModalBtn');
        const closePurokListModalBtn = document.getElementById('closePurokListModalBtn');
        const purokListContainer = document.getElementById('purokListContainer');
        const purokFilter = document.getElementById('purokFilter');
        const deletePurokModal = document.getElementById('deletePurokModal');
        const deletePurokForm = document.getElementById('deletePurokForm');
        const deletePurokName = document.getElementById('deletePurokName');
        const closeDeletePurokModalBtn = document.getElementById('closeDeletePurokModalBtn');
        const cancelDeletePurokModalBtn = document.getElementById('cancelDeletePurokModalBtn');
        const purokDeleteBaseUrl = @json(url('/barangay-captain/puroks'));

        function openDeletePurokModal(purokId, purokName) {
            if (!deletePurokModal || !deletePurokForm) return;
            deletePurokForm.action = `${purokDeleteBaseUrl}/${purokId}/delete`;
            if (deletePurokName) deletePurokName.textContent = purokName || 'this purok';
            deletePurokModal.classList.remove('hidden');
            deletePurokModal.classList.add('flex');
        }

        function closeDeletePurokModal() {
            if (deletePurokModal) {
                deletePurokModal.classList.add('hidden');
                deletePurokModal.classList.remove('flex');
            }
        }

        if (closeDeletePurokModalBtn) closeDeletePurokModalBtn.addEventListener('click', closeDeletePurokModal);
        if (cancelDeletePurokModalBtn) cancelDeletePurokModalBtn.addEventListener('click', closeDeletePurokModal);

        function openPurokList() {
            if (!purokListModal) return;
            const puroks = @json($manageablePuroks);
            purokListContainer.innerHTML = '';
            if (puroks.length === 0) {
                purokListContainer.innerHTML = '<p class="text-sm text-text-light/70 dark:text-text-dark/70">No purok created yet.</p>';
                purokListModal.classList.remove('hidden');
                purokListModal.classList.add('flex');
                return;
            }
            puroks.forEach(purok => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between gap-3 p-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark hover:bg-primary/5';
                div.innerHTML = `
                    <p class="text-sm font-semibold text-text-light dark:text-text-dark">${escapeHtml(purok.name)}</p>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" class="open-edit-purok-btn p-1.5 rounded-lg text-primary hover:bg-primary/10" data-purok-id="${purok.id}" title="Edit purok">
                            <span class="material-symbols-outlined text-base">edit</span>
                        </button>
                        <button type="button" class="open-delete-purok-btn p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 dark:text-red-400" data-purok-id="${purok.id}" data-purok-name="${escapeHtml(purok.name)}" title="Delete purok">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                    </div>
                `;
                purokListContainer.appendChild(div);
            });

            purokListContainer.querySelectorAll('.open-edit-purok-btn').forEach((button) => {
                button.addEventListener('click', function() {
                    const purokId = Number(button.getAttribute('data-purok-id'));
                    openEditPurokModal(purokId);
                });
            });

            purokListContainer.querySelectorAll('.open-delete-purok-btn').forEach((button) => {
                button.addEventListener('click', function() {
                    const purokId = Number(button.getAttribute('data-purok-id'));
                    const purokName = button.getAttribute('data-purok-name') || '';
                    openDeletePurokModal(purokId, purokName);
                });
            });
            purokListModal.classList.remove('hidden');
            purokListModal.classList.add('flex');
        }
        function closePurokList() {
            if (purokListModal) { purokListModal.classList.add('hidden'); purokListModal.classList.remove('flex'); }
        }
        if (openPurokListModalBtn) openPurokListModalBtn.addEventListener('click', openPurokList);
        if (closePurokListModalBtn) closePurokListModalBtn.addEventListener('click', closePurokList);

        // ---------- Filter by Purok ----------
        if (purokFilter) {
            purokFilter.addEventListener('change', function() {
                const filterValue = this.value;
                workerRows.forEach(row => {
                    const purokLabels = (row.dataset.purokLabels || '').split('|').filter(Boolean);
                    let show = true;
                    if (filterValue === '') show = true;
                    else if (filterValue === 'unassigned') show = purokLabels.length === 0;
                    else show = purokLabels.includes(filterValue);
                    row.style.display = show ? '' : 'none';
                });
            });
        }

        // ---------- Preserve old form errors ----------
        @if ($errors->any() && old('worker_form'))
            openBhwModal();
        @endif
        @if ($errors->any() && old('purok_form'))
            openPurokModal();
        @endif
        @if ($errors->any() && old('worker_id'))
            openAssignModal(presetWorkerId || '', (presetPurokIds || []).map(String));
        @endif

        // ---------- Leaflet map initialization ----------
        let map = null;
        let drawnItems = null;
        let drawControl = null;
        let barangayLayer = null;

        function resetPurokMapView() {
            if (!map) {
                return;
            }

            if (hasValidBoundary && barangayBounds.isValid()) {
                map.fitBounds(barangayBounds, { padding: [20, 20] });
                return;
            }

            map.setView(mindoroCenter, mindoroDefaultZoom);
        }

        function destroyPurokMap() {
            const container = document.getElementById('purokBoundaryMap');
            resetFreeAreaCache();
            if (map) {
                map.remove();
                map = null;
                drawnItems = null;
                drawControl = null;
                barangayLayer = null;
            }
            if (container) {
                container.innerHTML = '';
                container.className = 'purok-map';
            }
        }

        function initPurokMap() {
            const container = document.getElementById('purokBoundaryMap');
            if (!container || !window.L) {
                return;
            }

            if (map) {
                map.invalidateSize();
                return;
            }

            map = L.map(container, {
                center: barangayCenter,
                zoom: hasValidBoundary ? 14 : mindoroDefaultZoom,
                zoomControl: true,
                attributionControl: true,
                minZoom: 3,
            });

            map.createPane('purokTakenPane');
            map.getPane('purokTakenPane').style.zIndex = 450;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            if (hasValidBoundary && parsedBarangayBoundary) {
                barangayLayer = L.geoJSON(parsedBarangayBoundary, {
                    style: {
                        color: '#2d7d2d',
                        weight: 4,
                        fillColor: '#86efac',
                        fillOpacity: 0.30,
                    },
                    interactive: false,
                }).addTo(map);

                if (barangayBounds.isValid()) {
                    map.fitBounds(barangayBounds, { padding: [20, 20] });
                }
            } else {
                const msg = document.createElement('div');
                msg.className = 'absolute left-1/2 top-1/2 z-[1000] -translate-x-1/2 -translate-y-1/2 rounded-lg bg-red-600/90 px-5 py-4 text-center text-sm font-semibold text-white shadow-lg';
                msg.innerHTML = 'No barangay boundary found.<br>Please ask the LGU admin to draw the boundary first.';
                container.style.position = 'relative';
                container.appendChild(msg);
                map.setView(mindoroCenter, mindoroDefaultZoom);
            }

            const purokTakenStyle = {
                color: '#111111',
                            weight: 2,
                fillColor: '#9ca3af',
                fillOpacity: 0.45,
            };

            existingPuroks.forEach((purok) => {
                const geojson = parseGeoJsonPayload(purok.boundary);
                if (!boundaryHasGeometry(geojson)) {
                    return;
                }

                L.geoJSON(geojson, {
                    pane: 'purokTakenPane',
                    style: purokTakenStyle,
                        interactive: false,
                        onEachFeature: function(feature, layer) {
                        setPurokOccupiedLabel(layer, purok.name);
                    },
                    }).addTo(map);
            });

            resetFreeAreaCache();

            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        shapeOptions: purokNewMapStyle,
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false,
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true,
                },
            });
            map.addControl(drawControl);
            registerBoundarySnapHandlers(map, parsedBarangayBoundary, { snapOccupied: true });

            map.on(L.Draw.Event.CREATED, function(event) {
                const draftLayer = event.layer;
                removeMapLayer(map, draftLayer);

                const clipResult = clipGeoJsonToFreeArea(draftLayer.toGeoJSON());

                if (!clipResult.ok || !clipResult.feature) {
                    showToast('This area is already taken by another purok. Draw in the remaining space.', 'error');
                    return;
                }

                const layer = createLayerFromTurfFeature(clipResult.feature, purokNewMapStyle);
                if (!layer) {
                    showToast('Could not apply the adjusted purok shape. Try drawing again.', 'error');
                    return;
                }

                snapPolygonToAllConstraints(layer, parsedBarangayBoundary);

                if (clipResult.adjusted) {
                    showToast('Shape adjusted to fit beside existing purok.', 'success');
                }

                drawnItems.clearLayers();
                drawnItems.addLayer(layer);
                setPurokNameLabel(layer, purokNameInput?.value || '');
                syncDraftPurokBoundaryInput();
            });

            map.on(L.Draw.Event.DELETED, function() {
                document.getElementById('purok_boundary_geojson').value = '';
            });

            map.on(L.Draw.Event.EDITED, function(event) {
                const nextLayers = [];

                event.layers.eachLayer(function(layer) {
                    const clipResult = clipGeoJsonToFreeArea(layer.toGeoJSON());
                    if (!clipResult.ok || !clipResult.feature) {
                        return;
                    }

                    const clippedLayer = createLayerFromTurfFeature(clipResult.feature, purokNewMapStyle) || layer;
                    if (clippedLayer !== layer) {
                        removeMapLayer(map, layer);
                    }

                    snapPolygonToAllConstraints(clippedLayer, parsedBarangayBoundary);
                    setPurokNameLabel(clippedLayer, purokNameInput?.value || '');
                    nextLayers.push(clippedLayer);
                });

                drawnItems.clearLayers();
                nextLayers.forEach((layer) => drawnItems.addLayer(layer));
                syncDraftPurokBoundaryInput();
            });

            setTimeout(() => map.invalidateSize(), 100);
            setTimeout(() => resetPurokMapView(), 250);
        }

        // ---------- Edit purok map ----------
        let editMap = null;
        let editDrawnItems = null;
        let editDrawControl = null;
        let editVertexLayer = null;
        let editingPurokId = null;
        let editingPurokSnapshot = null;

        const editPurokLayerStyle = {
            color: '#1d4ed8',
            weight: 4,
            fillColor: '#3b82f6',
            fillOpacity: 0.42,
        };

        function getOuterLatLngs(layer) {
            const latlngs = layer?.getLatLngs?.();
            if (!Array.isArray(latlngs) || latlngs.length === 0) {
                return [];
            }

            if (latlngs[0] instanceof L.LatLng || typeof latlngs[0]?.lat === 'number') {
                return latlngs.slice();
            }

            return (latlngs[0] || []).slice();
        }

        function setOuterLatLngs(layer, points) {
            if (!layer?.setLatLngs) {
                    return;
                }

            layer.setLatLngs(points);

            if (typeof layer.redraw === 'function') {
                layer.redraw();
            }
        }

        function finalizeEditPurokLayer(layer, excludePurokId) {
            if (!layer) {
                return null;
            }

            snapPolygonToAllConstraints(layer, parsedBarangayBoundary, excludePurokId);
            const clipResult = clipGeoJsonToFreeArea(layer.toGeoJSON(), excludePurokId);
            if (!clipResult.ok || !clipResult.feature) {
                return null;
            }

            setLayerFromTurfFeature(layer, clipResult.feature);
            setPurokNameLabel(layer, editPurokNameInput?.value || '');
            syncEditPurokBoundaryInput();
            return layer;
        }

        function removeEditPurokVertexHandles() {
            if (editVertexLayer && editMap) {
                editMap.removeLayer(editVertexLayer);
            }

            editVertexLayer = null;
        }

        function rebuildEditPurokVertexHandles(layer, excludePurokId) {
            if (!editMap || !layer) {
                                return;
                            }

            removeEditPurokVertexHandles();
            editVertexLayer = L.featureGroup().addTo(editMap);

            const cornerIcon = L.divIcon({
                className: 'edit-purok-corner-handle',
                iconSize: [22, 22],
                iconAnchor: [11, 11],
            });

            const midIcon = L.divIcon({
                className: 'edit-purok-mid-handle',
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });

            const buildHandles = function() {
                editVertexLayer.clearLayers();
                const points = getOuterLatLngs(layer);
                if (points.length < 3) {
                    return;
                }

                points.forEach((latlng, index) => {
                    const cornerMarker = L.marker(latlng, {
                        draggable: true,
                        icon: cornerIcon,
                        pane: 'purokEditPane',
                        zIndexOffset: 2500,
                    });

                    cornerMarker.on('drag', function() {
                        points[index] = cornerMarker.getLatLng();
                        setOuterLatLngs(layer, points);
                    });

                    cornerMarker.on('dragend', function() {
                        if (!finalizeEditPurokLayer(layer, excludePurokId)) {
                            showToast('Shape must stay in free space beside other purok.', 'error');
                        }
                        rebuildEditPurokVertexHandles(layer, excludePurokId);
                    });

                    editVertexLayer.addLayer(cornerMarker);

                    const nextPoint = points[(index + 1) % points.length];
                    const midLatLng = L.latLng(
                        (latlng.lat + nextPoint.lat) / 2,
                        (latlng.lng + nextPoint.lng) / 2
                    );

                    const midMarker = L.marker(midLatLng, {
                        draggable: true,
                        icon: midIcon,
                        pane: 'purokEditPane',
                        zIndexOffset: 2000,
                    });

                    midMarker.on('dragend', function() {
                        points.splice(index + 1, 0, midMarker.getLatLng());
                        setOuterLatLngs(layer, points);
                        if (!finalizeEditPurokLayer(layer, excludePurokId)) {
                            showToast('Shape must stay in free space beside other purok.', 'error');
                        }
                        rebuildEditPurokVertexHandles(layer, excludePurokId);
                    });

                    editVertexLayer.addLayer(midMarker);
                });
            };

            buildHandles();
        }

        function syncEditPurokBoundaryInput() {
            const input = document.getElementById('edit_purok_boundary_geojson');
            if (!input || !editDrawnItems) {
                return;
            }

            const layers = editDrawnItems.getLayers();
            input.value = layers.length === 0 ? '' : JSON.stringify(layers[0].toGeoJSON());
        }

        function removeEditPurokDrawControl() {
            if (editMap && editDrawControl) {
                editMap.removeControl(editDrawControl);
                editDrawControl = null;
            }
        }

        function installEditPurokDrawControl(options = {}) {
            const { allowDraw = false } = options;
            if (!editMap || !editDrawnItems) {
                return;
            }

            removeEditPurokDrawControl();

            editDrawControl = new L.Control.Draw({
                draw: {
                    polygon: allowDraw ? {
                        allowIntersection: false,
                        showArea: true,
                        shapeOptions: purokNewMapStyle,
                    } : false,
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false,
                },
                edit: {
                    featureGroup: editDrawnItems,
                    remove: allowDraw,
                },
            });
            editMap.addControl(editDrawControl);
        }

        function handleEditPurokDrawCreated(event) {
            const draftLayer = event.layer;
            removeMapLayer(editMap, draftLayer);

            const clipResult = clipGeoJsonToFreeArea(draftLayer.toGeoJSON(), editingPurokId);
            if (!clipResult.ok || !clipResult.feature) {
                showToast('This area is already taken by another purok. Draw in the remaining space.', 'error');
                return;
            }

            const layer = createLayerFromTurfFeature(clipResult.feature, editPurokLayerStyle);
            if (!layer) {
                showToast('Could not apply the adjusted purok shape. Try drawing again.', 'error');
                return;
            }

            snapPolygonToAllConstraints(layer, parsedBarangayBoundary, editingPurokId);

            if (clipResult.adjusted) {
                showToast('Shape adjusted to fit beside existing purok.', 'success');
            }

            editDrawnItems.clearLayers();
            editDrawnItems.addLayer(layer);
            setPurokNameLabel(layer, editPurokNameInput?.value || '');
            syncEditPurokBoundaryInput();
            rebuildEditPurokVertexHandles(layer, editingPurokId);
            installEditPurokDrawControl({ allowDraw: false });
        }

        function resetEditPurokAll() {
            if (!editMap || !editDrawnItems) {
                return;
            }

            if (editPurokNameInput && editingPurokSnapshot) {
                editPurokNameInput.value = capitalizePurokNameFirstLetter(editingPurokSnapshot.name);
            }

            removeEditPurokVertexHandles();
            editDrawnItems.clearLayers();
            syncEditPurokBoundaryInput();
            installEditPurokDrawControl({ allowDraw: true });

            if (hasValidBoundary && barangayBounds.isValid()) {
                editMap.fitBounds(barangayBounds, { padding: [20, 20] });
            }

            showToast('Purok reset. Draw a new shape on the map.', 'success');
        }

        function destroyEditPurokMap() {
            const container = document.getElementById('editPurokBoundaryMap');
            resetFreeAreaCache();
            removeEditPurokVertexHandles();

            if (editMap) {
                editMap.remove();
                editMap = null;
                editDrawnItems = null;
                editDrawControl = null;
            }
            if (container) {
                container.innerHTML = '';
                container.className = 'purok-map';
            }
            editingPurokId = null;
            editingPurokSnapshot = null;
        }

        function initEditPurokMap(purok) {
            const container = document.getElementById('editPurokBoundaryMap');
            if (!container || !window.L || !purok) {
                return;
            }

            if (editMap) {
                editMap.invalidateSize();
                return;
            }

            const purokBoundary = parseGeoJsonPayload(purok.boundary);
            if (!boundaryHasGeometry(purokBoundary)) {
                showToast('This purok has no boundary to edit.', 'error');
                return;
            }

            editMap = L.map(container, {
                center: barangayCenter,
                zoom: hasValidBoundary ? 14 : mindoroDefaultZoom,
                zoomControl: true,
                attributionControl: true,
                minZoom: 3,
            });

            editMap.createPane('purokTakenPane');
            editMap.getPane('purokTakenPane').style.zIndex = 450;
            editMap.createPane('purokEditPane');
            editMap.getPane('purokEditPane').style.zIndex = 500;

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(editMap);

            if (hasValidBoundary && parsedBarangayBoundary) {
                L.geoJSON(parsedBarangayBoundary, {
                    style: {
                        color: '#2d7d2d',
                        weight: 4,
                        fillColor: '#86efac',
                        fillOpacity: 0.30,
                    },
                    interactive: false,
                }).addTo(editMap);
            }

            const purokTakenStyle = {
                color: '#111111',
                weight: 2,
                fillColor: '#9ca3af',
                fillOpacity: 0.40,
            };

            existingPuroks.forEach((otherPurok) => {
                if (Number(otherPurok.id) === Number(purok.id)) {
                    return;
                }

                const geojson = parseGeoJsonPayload(otherPurok.boundary);
                if (!boundaryHasGeometry(geojson)) {
                    return;
                }

                L.geoJSON(geojson, {
                    pane: 'purokTakenPane',
                    style: purokTakenStyle,
                    interactive: false,
                    onEachFeature: function(feature, layer) {
                        setPurokOccupiedLabel(layer, otherPurok.name);
                    },
                }).addTo(editMap);
            });

            resetFreeAreaCache();

            editDrawnItems = new L.FeatureGroup();
            editMap.addLayer(editDrawnItems);

            L.geoJSON(purokBoundary, {
                pane: 'purokEditPane',
                style: editPurokLayerStyle,
                onEachFeature: function(feature, layer) {
                    editDrawnItems.addLayer(layer);
                    setPurokNameLabel(layer, editPurokNameInput?.value || purok.name);
                },
            });

            installEditPurokDrawControl({ allowDraw: false });
            registerBoundarySnapHandlers(editMap, parsedBarangayBoundary, {
                snapOccupied: false,
                excludePurokId: purok.id,
            });

            if (!editMap._editPurokDrawEventsBound) {
                editMap.on(L.Draw.Event.CREATED, handleEditPurokDrawCreated);
                editMap.on(L.Draw.Event.DELETED, function() {
                    removeEditPurokVertexHandles();
                    syncEditPurokBoundaryInput();
                    installEditPurokDrawControl({ allowDraw: true });
                });
                editMap._editPurokDrawEventsBound = true;
            }

            editMap.on(L.Draw.Event.EDITED, function(event) {
                event.layers.eachLayer(function(layer) {
                    finalizeEditPurokLayer(layer, purok.id);
                    rebuildEditPurokVertexHandles(layer, purok.id);
                });
            });

            syncEditPurokBoundaryInput();

            const editableLayer = editDrawnItems.getLayers()[0];
            if (editableLayer && editableLayer.getBounds().isValid()) {
                editMap.fitBounds(editableLayer.getBounds(), { padding: [60, 60], maxZoom: 18 });
            } else if (hasValidBoundary && barangayBounds.isValid()) {
                editMap.fitBounds(barangayBounds, { padding: [20, 20] });
            }

            setTimeout(() => {
                editMap?.invalidateSize();
                const layer = editDrawnItems.getLayers()[0];
                if (layer) {
                    rebuildEditPurokVertexHandles(layer, purok.id);
                    if (layer.getBounds().isValid()) {
                        editMap.fitBounds(layer.getBounds(), { padding: [60, 60], maxZoom: 18 });
                    }
                }
            }, 300);
        }

        function openEditPurokModal(purokId) {
            const purok = allPuroks.find((item) => Number(item.id) === Number(purokId));
            if (!editPurokModal || !purok) {
                showToast('Purok not found.', 'error');
                return;
            }

            destroyEditPurokMap();
            editingPurokId = purok.id;
            editingPurokSnapshot = {
                id: purok.id,
                name: purok.name,
                boundary: purok.boundary,
            };

            if (editPurokForm) {
                editPurokForm.action = `${purokUpdateBaseUrl}/${purok.id}/update`;
            }

            const editPurokIdInput = document.getElementById('editPurokId');
            if (editPurokIdInput) {
                editPurokIdInput.value = purok.id;
            }

            if (editPurokNameInput) {
                const usePreset = presetEditPurokId && Number(presetEditPurokId) === Number(purok.id) && presetEditPurokName;
                editPurokNameInput.value = capitalizePurokNameFirstLetter(usePreset ? presetEditPurokName : purok.name);
            }

            if (purokListModal) {
                purokListModal.classList.add('hidden');
                purokListModal.classList.remove('flex');
            }

            editPurokModal.classList.remove('hidden');
            editPurokModal.classList.add('flex');
            setTimeout(() => initEditPurokMap(purok), 75);
            setTimeout(() => editMap?.invalidateSize(), 250);
        }

        function closeEditPurokModal() {
            if (!editPurokModal) {
                return;
            }

            destroyEditPurokMap();
            editPurokModal.classList.add('hidden');
            editPurokModal.classList.remove('flex');
        }

        function cancelEditPurokModal() {
            closeEditPurokModal();
            openPurokList();
        }

        if (editPurokNameInput) {
            editPurokNameInput.addEventListener('input', function() {
                if (!editDrawnItems) {
                    return;
                }

                editDrawnItems.getLayers().forEach((layer) => {
                    setPurokNameLabel(layer, this.value);
                });
            });
        }

        if (editPurokForm) {
            editPurokForm.addEventListener('submit', function(event) {
                if (editPurokNameInput) {
                    editPurokNameInput.value = capitalizePurokNameFirstLetter(editPurokNameInput.value);
                }

                if (editDrawnItems && editingPurokId != null) {
                    const nextLayers = [];

                    editDrawnItems.getLayers().forEach((layer) => {
                        const clipResult = clipGeoJsonToFreeArea(layer.toGeoJSON(), editingPurokId);
                        if (!clipResult.ok || !clipResult.feature) {
                            return;
                        }

                        const clippedLayer = createLayerFromTurfFeature(clipResult.feature, {
                            color: '#4A90E2',
                            weight: 3,
                            fillColor: '#4A90E2',
                            fillOpacity: 0.25,
                        }) || layer;

                        if (clippedLayer !== layer) {
                            removeMapLayer(editMap, layer);
                        }

                        nextLayers.push(clippedLayer);
                    });

                    editDrawnItems.clearLayers();
                    nextLayers.forEach((layer) => editDrawnItems.addLayer(layer));
                    syncEditPurokBoundaryInput();
                }

                const boundaryValue = document.getElementById('edit_purok_boundary_geojson')?.value || '';

                if (!boundaryValue.trim()) {
                    event.preventDefault();
                    showToast('Purok boundary is missing.', 'error');
                    return;
                }

                const parsedBoundary = parseGeoJsonPayload(boundaryValue);
                if (hasValidBoundary && !isGeometryWithinBoundary(parsedBoundary, parsedBarangayBoundary)) {
                    event.preventDefault();
                    showToast('Purok boundary must stay inside your assigned barangay range.', 'error');
                    return;
                }

                if (geometryOverlapsOccupiedPuroks(parsedBoundary, editingPurokId)) {
                    event.preventDefault();
                    showToast('Purok cannot overlap another purok. Adjust the shape on the map.', 'error');
                }
            });
        }

        if (closeEditPurokModalBtn) {
            closeEditPurokModalBtn.addEventListener('click', closeEditPurokModal);
        }
        if (cancelEditPurokModalBtn) {
            cancelEditPurokModalBtn.addEventListener('click', cancelEditPurokModal);
        }
        if (resetEditPurokAllBtn) {
            resetEditPurokAllBtn.addEventListener('click', resetEditPurokAll);
        }

        if (purokNameInput) {
            purokNameInput.addEventListener('input', function() {
                if (!drawnItems) {
                    return;
                }

                drawnItems.getLayers().forEach((layer) => {
                    setPurokNameLabel(layer, this.value);
                });
            });
        }

        if (purokForm) {
            purokForm.addEventListener('submit', function(event) {
                if (purokNameInput) {
                    purokNameInput.value = capitalizePurokNameFirstLetter(purokNameInput.value);
                }

                if (drawnItems) {
                    const nextLayers = [];

                    drawnItems.getLayers().forEach((layer) => {
                        const clipResult = clipGeoJsonToFreeArea(layer.toGeoJSON());
                        if (!clipResult.ok || !clipResult.feature) {
                            return;
                        }

                        const clippedLayer = createLayerFromTurfFeature(clipResult.feature, purokNewMapStyle) || layer;
                        if (clippedLayer !== layer) {
                            removeMapLayer(map, layer);
                        }

                        nextLayers.push(clippedLayer);
                    });

                    drawnItems.clearLayers();
                    nextLayers.forEach((layer) => drawnItems.addLayer(layer));
                    syncDraftPurokBoundaryInput();
                }

                const boundaryValue = document.getElementById('purok_boundary_geojson')?.value || '';

                if (!boundaryValue.trim()) {
                    event.preventDefault();
                    showToast('Draw a purok boundary on the map first.', 'error');
                    return;
                }

                const parsedBoundary = parseGeoJsonPayload(boundaryValue);
                if (hasValidBoundary && !isGeometryWithinBoundary(parsedBoundary, parsedBarangayBoundary)) {
                    event.preventDefault();
                    showToast('Purok boundary must stay inside your assigned barangay range.', 'error');
                    return;
                }

                if (geometryOverlapsOccupiedPuroks(parsedBoundary)) {
                    event.preventDefault();
                    showToast('Purok cannot overlap an existing purok. Adjust the shape on the map.', 'error');
                }
            });
        }

        // ---------- Modal open/close for purok ----------
        function openPurokModal() {
            if (!purokModal) return;
            destroyPurokMap();
            purokModal.classList.remove('hidden');
            purokModal.classList.add('flex');
            setTimeout(initPurokMap, 75);
            setTimeout(() => map?.invalidateSize(), 250);
        }

        function closePurokModal() {
            if (!purokModal) return;
            destroyPurokMap();
            purokModal.classList.add('hidden');
            purokModal.classList.remove('flex');
        }

        if (openAddPurokModalBtn) {
            openAddPurokModalBtn.addEventListener('click', openPurokModal);
        }
        if (closeAddPurokModalBtn) {
            closeAddPurokModalBtn.addEventListener('click', closePurokModal);
        }
        if (cancelAddPurokModalBtn) {
            cancelAddPurokModalBtn.addEventListener('click', closePurokModal);
        }

        if (purokNameInput?.value) {
            purokNameInput.value = capitalizePurokNameFirstLetter(purokNameInput.value);
        }

        // If modal is already open (e.g., on form error), init map
        if (purokModal && !purokModal.classList.contains('hidden')) {
            setTimeout(initPurokMap, 300);
        }

        @if ($errors->any() && old('edit_purok_form'))
            openEditPurokModal({{ old('edit_purok_id') ?: 'null' }});
        @endif
    });
</script>
</body>
</html>