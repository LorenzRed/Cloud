<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Bantay Kalusugan - BHW Workers</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); transition: transform 0.3s ease-in-out; position: fixed; z-index: 40; height: 100vh; }
            #sidebar.active { transform: translateX(0); }
            #overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 30; }
            #overlay.active { display: block; }
            main { width: 100%; }
        }
        .page-content { height: 100%; overflow-y: auto; }
        .worker-list-scroll { max-height: 28rem; overflow-y: auto; }
        .purok-map { height: 26rem; width: 100%; }
        .purok-option.is-selected > div { border-color: rgb(74 144 226 / 1); background-color: rgb(74 144 226 / 0.10); box-shadow: 0 0 0 1px rgb(74 144 226 / 0.18); }
        .toast-enter { animation: toast-in 180ms ease-out; }
        .toast-exit { animation: toast-out 180ms ease-in forwards; }
        @keyframes toast-in { from { transform: translateY(-8px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateY(0); opacity: 1; } to { transform: translateY(-8px); opacity: 0; } }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        .main-container { height: 100vh; display: flex; }
        main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .leaflet-control-container { z-index: 10; }
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
<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    <div id="overlay"></div>

    @include('sidebars.barangay_captain')

    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-bold leading-tight">BHW Workers</h1>
                        <p class="text-base text-text-light/70 dark:text-text-dark/70">View BHW worker names and manage puroks for your barangay.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($assignedBarangay)
                            <span class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-sm font-semibold bg-primary/10 text-primary">
                                <span class="material-symbols-outlined text-base">location_city</span>
                                {{ $assignedBarangay->name }}
                            </span>
                            <button type="button" id="openPurokListModalBtn" class="rounded-lg h-11 px-3 bg-gray-600 text-white hover:bg-gray-700" title="List Puroks">
                                <span class="material-symbols-outlined text-base">list</span>
                            </button>
                            <button type="button" id="openAddPurokModalBtn" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold hover:opacity-90">
                                <span class="material-symbols-outlined text-base">add_location_alt</span> Create Purok
                            </button>
                            <button type="button" id="openAddBhwModalBtn" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-secondary text-white text-sm font-bold hover:opacity-90">
                                <span class="material-symbols-outlined text-base">person_add</span> Add BHW Worker
                            </button>
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
                    <div class="rounded-lg border border-border-light dark:border-border-dark p-4 mb-4 bg-card-light dark:bg-card-dark">
                        <p class="text-text-light/80 dark:text-text-dark/80">No barangay assigned yet. BHW Workers and purok setup will be available once your barangay is assigned.</p>
                    </div>
                @endif

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 w-full space-y-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-bold">Worker Names</h2>
                        <div class="flex flex-col gap-3 w-full sm:ml-auto sm:flex-row sm:items-center sm:w-auto sm:max-w-2xl">
                            <select id="purokFilter" class="h-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm">
                                <option value="">All Workers</option>
                                <option value="unassigned">Not Assigned</option>
                                @foreach ($puroks as $purok)
                                    <option value="{{ $purok->name }}">{{ $purok->name }}</option>
                                @endforeach
                            </select>
                            <div class="relative w-full sm:max-w-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                                <input id="workerSearch" type="text" class="w-full h-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark pl-10 pr-3 text-sm" placeholder="Search worker name..." />
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-border-light dark:border-border-dark rounded-lg worker-list-scroll">
                        <table class="w-full">
                            <thead class="sticky top-0 z-10">
                                <tr class="border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/60">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Assigned Purok</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Action</th>
                                </tr>
                            </thead>
                            <tbody id="workerTableBody">
                                @forelse ($bhwWorkers as $worker)
                                    @php $currentPurok = $puroks->firstWhere('name', $worker->household_no); @endphp
                                    <tr class="border-b border-border-light dark:border-border-dark worker-row" data-worker-name="{{ strtolower($worker->name) }}">
                                        <td class="px-4 py-3 text-sm font-semibold">{{ $worker->name }}</td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70">{{ $worker->email }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $worker->household_no ?: 'Not assigned' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex items-center justify-center gap-2">
                                                <button type="button" class="open-assign-modal-btn inline-flex items-center gap-2 rounded-lg h-10 px-3 bg-primary text-white text-sm font-bold hover:opacity-90"
                                                    data-worker-id="{{ $worker->id }}" data-worker-name="{{ $worker->name }}"
                                                    data-current-purok-id="{{ $currentPurok?->id }}" data-current-purok-name="{{ $currentPurok?->name }}">
                                                    <span class="material-symbols-outlined text-sm">assignment_ind</span> Assign Purok
                                                </button>
                                                <button type="button" class="open-kick-modal-btn inline-flex items-center justify-center rounded h-8 px-2 border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/50 dark:text-red-300 dark:hover:bg-red-950/70 transition"
                                                    data-worker-id="{{ $worker->id }}" data-worker-name="{{ $worker->name }}" title="Remove BHW permission">
                                                    <span class="material-symbols-outlined text-xs">person_remove</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-text-light/70 dark:text-text-dark/70">No BHW workers found for your barangay.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modals (same as before) -->
<div id="bhwModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <div><h2 class="text-xl font-bold">Add BHW Worker</h2><p class="text-sm text-text-light/70 dark:text-text-dark/70">Create a BHW account and assign it to your barangay.</p></div>
            <button type="button" id="closeAddBhwModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="{{ route('barangay-captain.bhw-workers.store') }}" class="space-y-4 px-6 py-5">
            @csrf
            <input type="hidden" name="worker_form" value="1" />
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
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="cancelAddBhwModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-secondary text-white text-sm font-bold hover:opacity-90"><span class="material-symbols-outlined text-base">person_add</span> Create Worker</button>
            </div>
        </form>
    </div>
</div>

<div id="purokModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-5xl max-h-[calc(100vh-3rem)] overflow-y-auto rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <div><h2 class="text-xl font-bold">Create Purok</h2><p class="text-sm text-text-light/70 dark:text-text-dark/70">Draw a purok range inside your assigned barangay only.</p></div>
            <button type="button" id="closeAddPurokModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="rounded-xl border border-border-light dark:border-border-dark overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3">
                    <div><p class="text-sm font-semibold text-text-light dark:text-text-dark">Assigned barangay boundary</p><p class="text-xs text-text-light/70 dark:text-text-dark/70">The map starts on your assigned barangay and cannot pan outside it.</p></div>
                </div>
                <div class="border-b border-border-light dark:border-border-dark bg-white dark:bg-card-dark px-4 py-3">
                    <div class="flex flex-col gap-2">
                        <div class="relative">
                            <input id="purokMapSearchInput" type="text" class="w-full h-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 pr-10 text-sm" placeholder="Search a place inside your barangay..." autocomplete="off" />
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-light/40 dark:text-text-dark/40 text-base">search</span>
                        </div>
                        <div id="purokMapSearchSuggestions" class="hidden max-h-52 overflow-y-auto rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-background-dark shadow-lg"></div>
                    </div>
                    <p class="mt-2 text-xs text-text-light/60 dark:text-text-dark/60">Draw the custom purok boundary after you find the area.</p>
                </div>
                <div id="purokBoundaryMap" class="purok-map"></div>
            </div>

            <form method="POST" action="{{ route('barangay-captain.puroks.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="purok_form" value="1" />
                <input id="purok_boundary_geojson" name="boundary_geojson" type="hidden" value="{{ old('boundary_geojson') }}" required />
                <div><label for="purokName" class="block text-sm font-medium mb-1">Purok name</label><input id="purokName" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3" placeholder="e.g., Purok 1" required/></div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" id="cancelAddPurokModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">Cancel</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold hover:opacity-90"><span class="material-symbols-outlined text-base">add</span> Create Purok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="assignModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-3xl rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <div><h2 class="text-xl font-bold">Assign Purok</h2><p id="assignModalSubtitle" class="text-sm text-text-light/70 dark:text-text-dark/70">Select a purok for the chosen BHW worker.</p></div>
            <button type="button" id="closeAssignModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="px-6 pt-2 pb-5 space-y-2">
            <form id="assignWorkerForm" method="POST" action="{{ route('barangay-captain.bhw-workers.assign') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="worker_id" id="assignWorkerId" value="" />
                <input type="hidden" name="purok_id" id="assignPurokId" value="{{ old('purok_id') }}" />
                <input type="hidden" name="purok_name" id="assignPurokName" value="{{ old('purok_name') }}" />
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                    <input id="purokSearch" type="text" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark pl-10 pr-3 text-sm" placeholder="Search purok..." />
                </div>
                <div class="max-h-[28rem] overflow-y-auto border border-border-light dark:border-border-dark rounded-lg">
                    <div id="purokOptions" class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3">
                        @forelse ($puroks as $purok)
                            <button type="button" class="purok-option text-left cursor-pointer" data-purok-id="{{ $purok->id }}" data-purok-name="{{ strtolower($purok->name) }}" data-purok-label="{{ $purok->name }}">
                                <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 transition"><p class="font-semibold">{{ $purok->name }}</p></div>
                            </button>
                        @empty
                            <div class="col-span-full rounded-xl border border-dashed border-border-light dark:border-border-dark p-4 text-sm text-text-light/70 dark:text-text-dark/70">No puroks available. Create one first.</div>
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

<div id="kickModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
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

<div id="purokListModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <h2 class="text-xl font-bold"><span class="material-symbols-outlined align-text-bottom mr-2">list</span> Puroks</h2>
            <button type="button" id="closePurokListModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="px-6 py-5 space-y-3 max-h-96 overflow-y-auto" id="purokListContainer"></div>
    </div>
</div>

<!-- Leaflet JS & Draw JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ---------- Data from backend ----------
        const barangayBoundaryGeoJSON = @json($assignedBarangay ? $assignedBarangay->boundary_geojson : null);

        @php
            $puroksWithBoundary = $puroks->filter(function($p) {
                return !is_null($p->boundary_geojson);
            })->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'boundary' => $p->boundary_geojson,
                ];
            })->values();
        @endphp
        const existingPuroks = @json($puroksWithBoundary);

        console.log('Barangay boundary:', barangayBoundaryGeoJSON);
        console.log('Existing puroks:', existingPuroks);

        let barangayBounds = null;
        let barangayCenter = null;
        let barangayGeoJSONLayerData = null;
        let hasValidBoundary = false;

        // Parse boundary
        if (barangayBoundaryGeoJSON) {
            try {
                const geojson = typeof barangayBoundaryGeoJSON === 'string' ? JSON.parse(barangayBoundaryGeoJSON) : barangayBoundaryGeoJSON;
                // Check for Polygon or MultiPolygon
                let coords = null;
                if (geojson.type === 'Polygon' && geojson.coordinates && geojson.coordinates.length > 0) {
                    barangayGeoJSONLayerData = geojson;
                    coords = geojson.coordinates[0];
                    hasValidBoundary = true;
                } else if (geojson.type === 'MultiPolygon' && geojson.coordinates && geojson.coordinates.length > 0) {
                    barangayGeoJSONLayerData = geojson;
                    coords = geojson.coordinates[0][0]; // first polygon's exterior ring
                    hasValidBoundary = true;
                }
                if (coords) {
                    let minLat = Infinity, maxLat = -Infinity, minLng = Infinity, maxLng = -Infinity;
                    coords.forEach(([lng, lat]) => {
                        if (lat < minLat) minLat = lat;
                        if (lat > maxLat) maxLat = lat;
                        if (lng < minLng) minLng = lng;
                        if (lng > maxLng) maxLng = lng;
                    });
                    barangayBounds = [[minLat, minLng], [maxLat, maxLng]];
                    barangayCenter = [(minLat + maxLat) / 2, (minLng + maxLng) / 2];
                }
            } catch (e) {
                console.warn('Error parsing barangay boundary GeoJSON:', e);
            }
        }

        // If no boundary, we'll show a message but still allow drawing puroks with a default view
        if (!hasValidBoundary) {
            // Set a default center (Mindoro region) with a reasonable zoom
            barangayCenter = [12.95, 120.90];
            barangayBounds = [[12.35, 120.35], [13.65, 121.45]];
            console.warn('No valid barangay boundary found.');
        }

        // ---------- UI Logic (modals, toggles, etc.) ----------
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
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
        const passwordEyeOpen = document.getElementById('passwordEyeOpen');
        const passwordEyeClosed = document.getElementById('passwordEyeClosed');
        const assignModalSubtitle = document.getElementById('assignModalSubtitle');
        const assignWorkerId = document.getElementById('assignWorkerId');
        const assignPurokId = document.getElementById('assignPurokId');
        const assignPurokName = document.getElementById('assignPurokName');
        const kickWorkerId = document.getElementById('kickWorkerId');
        const kickWorkerName = document.getElementById('kickWorkerName');
        const kickModalSubtitle = document.getElementById('kickModalSubtitle');
        const purokOptions = document.querySelectorAll('.purok-option');
        const workerRows = document.querySelectorAll('.worker-row');
        const openAssignButtons = document.querySelectorAll('.open-assign-modal-btn');
        const openKickButtons = document.querySelectorAll('.open-kick-modal-btn');
        const presetWorkerId = @json(old('worker_id'));
        const presetPurokId = @json(old('purok_id'));
        const presetPurokName = @json(old('purok_name'));
        const toastData = document.getElementById('toastData');
        let toastTimer = null;

        // ---------- Modal functions (all) ----------
        function openBhwModal() { if (bhwModal) { bhwModal.classList.remove('hidden'); bhwModal.classList.add('flex'); } }
        function closeBhwModal() { if (bhwModal) { bhwModal.classList.add('hidden'); bhwModal.classList.remove('flex'); } }
        if (openAddBhwModalBtn) openAddBhwModalBtn.addEventListener('click', openBhwModal);
        if (closeAddBhwModalBtn) closeAddBhwModalBtn.addEventListener('click', closeBhwModal);
        if (cancelAddBhwModalBtn) cancelAddBhwModalBtn.addEventListener('click', closeBhwModal);
        if (bhwModal) bhwModal.addEventListener('click', function(e) { if (e.target === bhwModal) closeBhwModal(); });

        function openAssignModal(workerId, currentPurokId, currentPurokName) {
            if (!assignModal || !assignWorkerId) return;
            assignWorkerId.value = workerId;
            assignModalSubtitle.textContent = 'Select a purok for the chosen BHW worker. Click the selected purok again to clear it.';
            assignModal.classList.remove('hidden');
            assignModal.classList.add('flex');
            clearSelectedPurok();
            if (currentPurokId) {
                const presetOption = document.querySelector(`.purok-option[data-purok-id="${currentPurokId}"]`);
                if (presetOption) {
                    presetOption.classList.add('is-selected');
                    if (assignPurokId) assignPurokId.value = currentPurokId;
                    if (assignPurokName) assignPurokName.value = currentPurokName || '';
                }
            }
        }
        function closeAssignModal() { if (assignModal) { assignModal.classList.add('hidden'); assignModal.classList.remove('flex'); } }
        if (closeAssignModalBtn) closeAssignModalBtn.addEventListener('click', closeAssignModal);
        if (cancelAssignModalBtn) cancelAssignModalBtn.addEventListener('click', closeAssignModal);
        if (assignModal) assignModal.addEventListener('click', function(e) { if (e.target === assignModal) closeAssignModal(); });

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
        if (kickModal) kickModal.addEventListener('click', function(e) { if (e.target === kickModal) closeKickModal(); });

        // ---------- Assign buttons ----------
        openAssignButtons.forEach(button => {
            button.addEventListener('click', function() {
                const workerId = button.dataset.workerId || '';
                const currentPurokId = button.dataset.currentPurokId || '';
                const currentPurokName = button.dataset.currentPurokName || '';
                openAssignModal(workerId, currentPurokId, currentPurokName);
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
            if (assignPurokId) assignPurokId.value = '';
            if (assignPurokName) assignPurokName.value = '';
        };
        purokOptions.forEach(option => {
            option.addEventListener('click', function() {
                const purokId = option.dataset.purokId || '';
                const purokName = option.dataset.purokLabel || '';
                const isSelected = option.classList.contains('is-selected');
                clearSelectedPurok();
                if (isSelected) return;
                option.classList.add('is-selected');
                if (assignPurokId) assignPurokId.value = purokId;
                if (assignPurokName) assignPurokName.value = purokName;
            });
        });

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
                passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                const nowVisible = passwordInput.getAttribute('type') === 'text';
                togglePassword.setAttribute('aria-pressed', nowVisible ? 'true' : 'false');
                togglePassword.setAttribute('aria-label', nowVisible ? 'Hide password' : 'Show password');
                passwordEyeOpen.classList.toggle('hidden', nowVisible);
                passwordEyeClosed.classList.toggle('hidden', !nowVisible);
            });
        }

        // ---------- Purok List Modal ----------
        const purokListModal = document.getElementById('purokListModal');
        const openPurokListModalBtn = document.getElementById('openPurokListModalBtn');
        const closePurokListModalBtn = document.getElementById('closePurokListModalBtn');
        const purokListContainer = document.getElementById('purokListContainer');
        const purokFilter = document.getElementById('purokFilter');

        function openPurokList() {
            if (!purokListModal) return;
            const puroks = @json($puroks);
            purokListContainer.innerHTML = '';
            if (puroks.length === 0) {
                purokListContainer.innerHTML = '<p class="text-sm text-text-light/70 dark:text-text-dark/70">No puroks created yet.</p>';
                purokListModal.classList.remove('hidden');
                purokListModal.classList.add('flex');
                return;
            }
            puroks.forEach(purok => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between p-3 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark hover:bg-primary/5';
                div.innerHTML = `
                    <p class="text-sm font-semibold text-text-light dark:text-text-dark">${purok.name}</p>
                    <form method="POST" action="/barangay-captain/puroks/${purok.id}/delete" class="inline" onsubmit="return confirm('Delete this purok?');">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 dark:text-red-400">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                    </form>
                `;
                purokListContainer.appendChild(div);
            });
            purokListModal.classList.remove('hidden');
            purokListModal.classList.add('flex');
        }
        function closePurokList() {
            if (purokListModal) { purokListModal.classList.add('hidden'); purokListModal.classList.remove('flex'); }
        }
        if (openPurokListModalBtn) openPurokListModalBtn.addEventListener('click', openPurokList);
        if (closePurokListModalBtn) closePurokListModalBtn.addEventListener('click', closePurokList);
        if (purokListModal) purokListModal.addEventListener('click', function(e) { if (e.target === purokListModal) closePurokList(); });

        // ---------- Filter by Purok ----------
        if (purokFilter) {
            purokFilter.addEventListener('change', function() {
                const filterValue = this.value;
                workerRows.forEach(row => {
                    const purokCell = row.querySelector('td:nth-child(3)');
                    const purokText = purokCell?.textContent?.trim() || '';
                    let show = true;
                    if (filterValue === '') show = true;
                    else if (filterValue === 'unassigned') show = purokText === 'Not assigned';
                    else show = purokText === filterValue;
                    row.style.display = show ? '' : 'none';
                });
            });
        }

        // ---------- Sidebar toggle ----------
        if (menuToggle && sidebar && overlay) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
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
            openAssignModal(presetWorkerId || '', presetPurokId || '', presetPurokName || '');
        @endif

        // ---------- Leaflet map initialization ----------
        let map = null;
        let drawnItems = null;
        let drawControl = null;
        let barangayLayer = null;

        function initPurokMap() {
            const container = document.getElementById('purokBoundaryMap');
            if (!container) return;
            if (map) {
                map.invalidateSize();
                return;
            }

            // Create map with maxBounds
            const mapOptions = {
                center: barangayCenter,
                zoom: hasValidBoundary ? 14 : 10, // zoom out if no boundary
                zoomControl: true,
                attributionControl: true,
                maxBounds: barangayBounds || undefined,
                maxBoundsViscosity: 1.0,
            };
            map = L.map(container, mapOptions);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // ---------- Draw barangay boundary (highlight) ----------
            if (hasValidBoundary && barangayGeoJSONLayerData) {
                barangayLayer = L.geoJSON(barangayGeoJSONLayerData, {
                    style: {
                        color: '#2d7d2d',
                        weight: 4,
                        fillColor: '#86efac',
                        fillOpacity: 0.30,
                        dashArray: null,
                    },
                    interactive: false,
                }).addTo(map);
                console.log('Barangay boundary added to map');
                // Zoom to the boundary
                if (barangayBounds) {
                    map.fitBounds(barangayBounds, { padding: [20, 20] });
                }
            } else {
                console.warn('No barangay boundary to display');
                // Show a prominent message on the map
                const msg = document.createElement('div');
                msg.style.position = 'absolute';
                msg.style.top = '50%';
                msg.style.left = '50%';
                msg.style.transform = 'translate(-50%, -50%)';
                msg.style.background = 'rgba(255, 0, 0, 0.85)';
                msg.style.color = 'white';
                msg.style.padding = '15px 25px';
                msg.style.borderRadius = '8px';
                msg.style.zIndex = '1000';
                msg.style.textAlign = 'center';
                msg.style.fontSize = '16px';
                msg.style.fontWeight = 'bold';
                msg.style.boxShadow = '0 4px 12px rgba(0,0,0,0.3)';
                msg.innerHTML = '⚠️ No barangay boundary found.<br>Please ask the admin to draw the boundary.';
                map.getContainer().style.position = 'relative';
                map.getContainer().appendChild(msg);
            }

            // ---------- Draw existing purok boundaries ----------
            const purokColors = ['#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#ec4899', '#06b6d4'];
            existingPuroks.forEach((purok, index) => {
                if (!purok.boundary) return;
                try {
                    const geojson = typeof purok.boundary === 'string' ? JSON.parse(purok.boundary) : purok.boundary;
                    const color = purokColors[index % purokColors.length];
                    L.geoJSON(geojson, {
                        style: {
                            color: color,
                            weight: 2,
                            fillColor: color,
                            fillOpacity: 0.15,
                        },
                        interactive: false,
                        onEachFeature: function(feature, layer) {
                            layer.bindTooltip(purok.name, { sticky: true });
                        }
                    }).addTo(map);
                } catch (e) {
                    console.warn('Invalid purok boundary for ' + purok.name, e);
                }
            });

            // ---------- Draw control ----------
            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        shapeOptions: { color: '#4A90E2', weight: 2 }
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });
            map.addControl(drawControl);

            map.on(L.Draw.Event.CREATED, function(event) {
                const layer = event.layer;
                drawnItems.clearLayers();
                drawnItems.addLayer(layer);
                const geojson = layer.toGeoJSON();
                document.getElementById('purok_boundary_geojson').value = JSON.stringify(geojson);
            });
            map.on(L.Draw.Event.DELETED, function() {
                document.getElementById('purok_boundary_geojson').value = '';
            });
            map.on(L.Draw.Event.EDITED, function() {
                const layers = drawnItems.getLayers();
                if (layers.length === 0) {
                    document.getElementById('purok_boundary_geojson').value = '';
                } else {
                    const geojson = layers[0].toGeoJSON();
                    document.getElementById('purok_boundary_geojson').value = JSON.stringify(geojson);
                }
            });

            setTimeout(() => map.invalidateSize(), 200);
        }

        // ---------- Search (Nominatim) ----------
        const searchInput = document.getElementById('purokMapSearchInput');
        const suggestionsContainer = document.getElementById('purokMapSearchSuggestions');
        let searchTimeout = null;

        if (searchInput && suggestionsContainer) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                clearTimeout(searchTimeout);
                if (query.length < 3) {
                    suggestionsContainer.classList.add('hidden');
                    return;
                }
                searchTimeout = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
                        .then(res => res.json())
                        .then(data => {
                            suggestionsContainer.innerHTML = '';
                            if (data.length === 0) {
                                suggestionsContainer.classList.add('hidden');
                                return;
                            }
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-2 hover:bg-primary/10 cursor-pointer text-sm';
                                div.textContent = item.display_name;
                                div.addEventListener('click', function() {
                                    const lat = parseFloat(item.lat);
                                    const lon = parseFloat(item.lon);
                                    if (map) {
                                        map.setView([lat, lon], 16);
                                        L.marker([lat, lon]).addTo(map);
                                    }
                                    suggestionsContainer.classList.add('hidden');
                                    searchInput.value = item.display_name;
                                });
                                suggestionsContainer.appendChild(div);
                            });
                            suggestionsContainer.classList.remove('hidden');
                        })
                        .catch(() => suggestionsContainer.classList.add('hidden'));
                }, 400);
            });
            searchInput.addEventListener('blur', function() {
                setTimeout(() => suggestionsContainer.classList.add('hidden'), 300);
            });
        }

        // ---------- Modal open/close for purok ----------
        function openPurokModal() {
            if (!purokModal) return;
            purokModal.classList.remove('hidden');
            purokModal.classList.add('flex');
            setTimeout(initPurokMap, 200);
        }

        function closePurokModal() {
            if (!purokModal) return;
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
        if (purokModal) {
            purokModal.addEventListener('click', function(event) {
                if (event.target === purokModal) closePurokModal();
            });
        }

        // If modal is already open (e.g., on form error), init map
        if (purokModal && !purokModal.classList.contains('hidden')) {
            setTimeout(initPurokMap, 300);
        }
    });
</script>
</body>
</html>