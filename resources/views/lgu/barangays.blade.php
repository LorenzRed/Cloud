<!DOCTYPE html>
<html class="light" lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    @include('partials.submit-guard')
    <title>Bantay Kalusugan - Barangays Management</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        .leaflet-control-attribution {
            display: none !important;
        }

        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }

        .dark .progress-bar {
            background-color: #334155;
        }

        .toast-enter {
            animation: toast-in 180ms ease-out;
        }

        .toast-exit {
            animation: toast-out 180ms ease-in forwards;
        }

        @keyframes toast-in {
            from { transform: translateY(-8px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes toast-out {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(-8px); opacity: 0; }
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2A64F5",
                        "secondary": "#1ABC9C",
                        "accent-orange": "#F39C12",
                        "accent-red": "#E74C3C",
                        "background-light": "#F4F6F9",
                        "background-dark": "#1a1c23",
                        "text-light": "#34495E",
                        "text-dark": "#E0E0E0",
                        "border-light": "#EAECEF",
                        "border-dark": "#343841",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>
    @include('partials.lgu-responsive-styles')
</head>
<body class="lgu-layout font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex h-full w-full">
            @include('sidebars.lgu')

            <main class="lgu-barangays-main flex-1 flex flex-col h-screen">
                <div class="flex-1 overflow-y-auto p-4 md:p-8">
                    <div class="flex flex-wrap justify-between gap-4 items-center mb-6">
                        <div class="flex flex-col gap-1">
                            <p class="text-text-light dark:text-text-dark text-3xl font-bold tracking-tight">Barangays Management</p>
                            <p class="text-text-light/70 dark:text-text-dark/70 text-base">Manage and monitor all barangays in your jurisdiction.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button" id="openBarangayMapModalBtn" class="inline-flex items-center gap-2 bg-accent-orange text-white px-4 py-2 rounded-lg hover:opacity-90 transition-colors">
                                <span class="material-symbols-outlined text-lg">map</span>
                                <span class="text-sm font-medium">Barangay Map</span>
                            </button>
                            <button type="button" id="openAddCaptainModalBtn" class="inline-flex items-center gap-2 bg-secondary text-white px-4 py-2 rounded-lg hover:opacity-90 transition-colors">
                                <span class="material-symbols-outlined text-lg">person_add</span>
                                <span class="text-sm font-medium">Add Brg Captain</span>
                            </button>
                            <button type="button" id="toggle-add-form" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined text-lg">location_on</span>
                                <span class="text-sm font-medium">Add New Barangay</span>
                            </button>
                        </div>
                    </div>

                    @if (session('status'))
                        <div class="sr-only" id="toastData" data-toast-type="success" data-toast-message="{{ session('status') }}"></div>
                    @endif

                    @if ($errors->any())
                        <div class="sr-only" id="toastData" data-toast-type="error" data-toast-message="{{ $errors->first() }}"></div>
                    @endif

                    <div id="barangayModal" class="fixed inset-0 z-40 hidden items-center justify-center bg-black/50 px-4 py-6">
                        <div class="w-full max-w-6xl h-[calc(100vh-3rem)] overflow-hidden rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl flex flex-col">
                            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4 bg-white dark:bg-background-dark z-10">
                                <div>
                                    <h3 id="barangayFormTitle" class="text-text-light dark:text-text-dark text-lg font-semibold">Add Barangay Boundary</h3>
                                    <p id="barangayFormSubtitle" class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Draw the barangay boundary on the map and save it here.</p>
                                </div>
                                <button type="button" id="closeBarangayModalBtn" class="p-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>

                            <div class="grid flex-1 min-h-0 gap-0 lg:grid-cols-[minmax(0,1.8fr)_minmax(320px,1fr)]">
                                <div class="border-b lg:border-b-0 lg:border-r border-border-light dark:border-border-dark min-h-0">
                                    <div id="barangayBoundaryMap" class="h-full min-h-[45vh] lg:min-h-0 w-full"></div>
                                </div>

                                <div class="p-6 flex flex-col min-h-0 overflow-y-auto">
                                    <form id="barangayForm" method="POST" action="{{ route('lgu.barangays.store') }}" class="flex flex-col gap-4">
                                        @csrf
                                        <input id="barangay_id" name="barangay_id" type="hidden" value="{{ old('barangay_id') }}" />
                                        <input id="boundary_geojson" name="boundary_geojson" type="hidden" value="{{ old('boundary_geojson') }}" required />

                                        <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 space-y-4 relative">
                                            <div class="relative">
                                                <label for="barangayMapSearchInput" class="text-xs uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Search Location</label>
                                                <div class="relative mt-2">
                                                    <input id="barangayMapSearchInput" type="text" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-background-dark px-3 pr-10 text-sm" placeholder="Search a location, address, or place..." autocomplete="off" />
                                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-light/40 dark:text-text-dark/40 text-base">search</span>
                                                </div>
                                                <div id="barangayMapSearchSuggestions" class="hidden absolute left-0 right-0 top-full z-20 mt-2 max-h-56 overflow-y-auto rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-background-dark shadow-xl"></div>
                                            </div>
                                            <p id="barangayMapSearchHint" class="text-xs text-text-light/60 dark:text-text-dark/60">Type to see suggestions, then pick one or press Enter.</p>

                                            <div>
                                                <label for="name" class="block text-xs uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Barangay Name</label>
                                                <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="Enter barangay name"
                                                    class="mt-2 w-full h-11 px-3 rounded-lg bg-white dark:bg-background-dark border border-border-light dark:border-border-dark text-text-light dark:text-text-dark text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                                            </div>
                                        </div>

                                        <button id="barangaySubmitBtn" type="submit" class="inline-flex w-full items-center justify-center gap-2 h-11 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                            <span id="barangaySubmitIcon" class="material-symbols-outlined text-lg">check</span>
                                            <span id="barangaySubmitText" class="text-sm font-medium">Save Barangay</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border-light dark:border-border-dark bg-white dark:bg-background-dark/40 shadow-sm overflow-hidden">
                        <div class="flex flex-col gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-text-light dark:text-text-dark">Barangays List</h2>
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Search barangays and manage their assigned captains from one place.</p>
                            </div>
                            <div class="relative w-full sm:max-w-sm">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                                <input id="barangaySearchInput" type="text" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark pl-10 pr-3 text-sm" placeholder="Search barangay or captain..." autocomplete="off" />
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-border-light dark:border-border-dark bg-background-light/70 dark:bg-background-dark/70">
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Barangay</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Assigned Captain</th>
                                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="barangayTableBody" class="divide-y divide-border-light dark:divide-border-dark">
                                    @forelse ($barangays as $barangay)
                                        <tr class="barangay-row" data-barangay-name="{{ strtolower($barangay->name) }}" data-captain-name="{{ strtolower($barangay->captain?->name ?? '') }}">
                                            <td class="px-5 py-4">
                                                <div class="flex flex-col gap-1">
                                                    <p class="text-sm font-semibold text-text-light dark:text-text-dark">{{ $barangay->name }}</p>
                                                    <p class="text-xs text-text-light/60 dark:text-text-dark/60">Created {{ $barangay->created_at->format('M d, Y') }}</p>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="bg-primary/10 text-primary p-2 rounded-lg shrink-0">
                                                        <span class="material-symbols-outlined text-base">badge</span>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold text-text-light dark:text-text-dark">{{ $barangay->captain?->name ?? 'Not assigned' }}</p>
                                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $barangay->captain?->email ?? 'No captain assigned yet' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button
                                                        type="button"
                                                        class="open-captain-management-btn inline-flex items-center justify-center rounded-lg h-9 w-9 border border-primary/20 bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors"
                                                        data-barangay-id="{{ $barangay->id }}"
                                                        data-barangay-name="{{ $barangay->name }}"
                                                        data-barangay-captain="{{ $barangay->captain?->name ?? '' }}"
                                                        title="Manage captain"
                                                    >
                                                        <span class="material-symbols-outlined text-base">edit</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-5 py-10 text-center text-sm text-text-light/70 dark:text-text-dark/70">No barangays yet. Click "Add New Barangay" to create one.</td>
                                        </tr>
                                    @endforelse
                                    <tr id="barangayListEmptyState" class="hidden">
                                        <td colspan="3" class="px-5 py-10 text-center text-sm text-text-light/70 dark:text-text-dark/70">No barangays found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="addCaptainModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl">
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
                <div>
                    <h2 class="text-xl font-bold text-text-light dark:text-text-dark">Add Barangay Captain</h2>
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Create a captain account with email and password. Assign them to a barangay from the table.</p>
                </div>
                <button type="button" id="closeAddCaptainModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('lgu.barangays.captains.store') }}" class="space-y-4 px-6 pt-2 pb-5">
                @csrf
                <div>
                    <label for="captain_email" class="block text-sm font-medium mb-1 text-text-light dark:text-text-dark">Email</label>
                    <input id="captain_email" name="captain_email" type="email" value="{{ old('captain_email') }}" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3 @error('captain_email') border-red-500 @enderror" placeholder="captain@example.com" required />
                    @error('captain_email')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="captain_password" class="block text-sm font-medium mb-1 text-text-light dark:text-text-dark">Password</label>
                    <input id="captain_password" name="captain_password" type="password" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3 @error('captain_password') border-red-500 @enderror" placeholder="Enter a temporary password" required />
                    @error('captain_password')
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="captain_password_confirmation" class="block text-sm font-medium mb-1 text-text-light dark:text-text-dark">Confirm Password</label>
                    <input id="captain_password_confirmation" name="captain_password_confirmation" type="password" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3" placeholder="Re-enter the temporary password" required />
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" id="cancelAddCaptainModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-secondary text-white text-sm font-bold hover:opacity-90">
                        <span class="material-symbols-outlined text-base">person_add</span>
                        Create Captain
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteBarangayModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl">
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
                <div>
                    <h2 class="text-xl font-bold text-text-light dark:text-text-dark">Delete Barangay</h2>
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">This will remove the barangay record permanently.</p>
                </div>
                <button type="button" id="closeDeleteBarangayModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" id="deleteBarangayForm" action="{{ route('lgu.barangays.destroy', ['barangay' => 0]) }}" class="space-y-4 px-6 py-5">
                @csrf
                @method('DELETE')
                <p class="text-sm leading-6 text-text-light/80 dark:text-text-dark/80">
                    Delete <span id="deleteBarangayName" class="font-semibold text-text-light dark:text-text-dark"></span>? Associated captain assignment will be cleared.
                </p>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="rounded-lg h-11 px-4 bg-red-100 text-red-700 text-sm font-semibold border border-red-300 hover:bg-red-200">
                        Delete
                    </button>
                    <button type="button" id="cancelDeleteBarangayModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="captainManagementModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6">
        <div class="w-full max-w-5xl h-[calc(100vh-3rem)] overflow-hidden rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl flex flex-col">
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4 sticky top-0 bg-white dark:bg-background-dark z-10">
                <div>
                    <h2 id="captainManagementTitle" class="text-xl font-bold text-text-light dark:text-text-dark">Manage Barangay Captains</h2>
                    <p id="captainManagementSubtitle" class="text-sm text-text-light/70 dark:text-text-dark/70">Choose a captain card to assign or remove the position.</p>
                </div>
                <button type="button" id="closeCaptainManagementModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 flex flex-col min-h-0 gap-4">
                <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 space-y-3">
                    <div class="relative">
                        <label for="captainManagementSearch" class="text-xs uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Search Captains</label>
                        <div class="relative mt-2">
                            <input id="captainManagementSearch" type="text" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-background-dark px-3 pr-10 text-sm" placeholder="Type a captain name or email..." autocomplete="off" />
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-light/40 dark:text-text-dark/40 text-base">search</span>
                        </div>
                    </div>
                    <p id="captainManagementHint" class="text-xs text-text-light/60 dark:text-text-dark/60">Click a card action to assign the captain to the selected barangay or remove the current position.</p>
                </div>

                <form id="captainManagementForm" method="POST" action="{{ route('lgu.barangays.captains.assignment') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="captain_user_id" id="captainManagementUserId" value="" />
                    <input type="hidden" name="captain_barangay_id" id="captainManagementBarangayId" value="" />
                    <input type="hidden" name="captain_action" id="captainManagementAction" value="assign" />
                </form>

                <div class="flex-1 min-h-0 overflow-y-auto">
                    <div id="captainManagementCards" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"></div>
                    <div id="captainManagementEmptyState" class="hidden rounded-xl border border-dashed border-border-light dark:border-border-dark p-6 text-center text-sm text-text-light/70 dark:text-text-dark/70">
                        No captains found.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="unassignCaptainModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl">
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
                <div>
                    <h2 class="text-xl font-bold text-text-light dark:text-text-dark">Confirm Unassign</h2>
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">This will clear the selected barangay assignment.</p>
                </div>
                <button type="button" id="closeUnassignCaptainModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="space-y-4 px-6 py-5">
                <p class="text-sm leading-6 text-text-light/80 dark:text-text-dark/80">
                    Unassign <span id="unassignCaptainName" class="font-semibold text-text-light dark:text-text-dark"></span> from <span id="unassignCaptainBarangay" class="font-semibold text-text-light dark:text-text-dark"></span>?
                </p>
                <p class="text-xs text-text-light/60 dark:text-text-dark/60">
                    The captain account will remain active. Only the barangay assignment is removed.
                </p>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" id="cancelUnassignCaptainModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                        Cancel
                    </button>
                    <button type="button" id="confirmUnassignCaptainBtn" class="rounded-lg h-11 px-4 bg-amber-100 text-amber-700 text-sm font-semibold border border-amber-300 hover:bg-amber-200">
                        <span id="unassignCaptainAction">Unassign</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="changeCaptainPasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="changeCaptainPasswordTitle">
        <div class="w-full max-w-md max-h-[90dvh] overflow-hidden rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl flex flex-col">
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4 shrink-0">
                <div>
                    <h2 id="changeCaptainPasswordTitle" class="text-lg font-bold text-text-light dark:text-text-dark">Change Password</h2>
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-0.5">Set a new password for <span id="passwordCaptainName" class="font-semibold text-text-light dark:text-text-dark"></span>.</p>
                </div>
                <button type="button" id="closeChangeCaptainPasswordModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('lgu.barangays.captains.password') }}" class="flex flex-col min-h-0 flex-1">
                @csrf
                <input type="hidden" name="captain_user_id" id="passwordCaptainId" value="" />
                <input type="hidden" name="password_form" value="1" />
                <div class="overflow-y-auto px-6 py-4 space-y-3 flex-1">
                    @if (($errors->has('password') || $errors->has('password_confirmation') || $errors->has('captain_user_id')) && old('password_form'))
                        <div class="rounded-lg border border-accent-red/40 bg-accent-red/10 px-4 py-3 text-sm text-accent-red">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->get('password') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                @foreach ($errors->get('password_confirmation') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                                @foreach ($errors->get('captain_user_id') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium mb-1" for="captain_password_change">New Password</label>
                        <input id="captain_password_change" type="password" name="password" required autocomplete="new-password" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="captain_password_confirmation_change">Confirm Password</label>
                        <input id="captain_password_confirmation_change" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-3 text-sm">
                    </div>
                    <p class="text-xs text-text-light/60 dark:text-text-dark/60">Use at least 8 characters with letters and numbers.</p>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-border-light dark:border-border-dark px-6 py-3 shrink-0">
                    <button type="button" id="cancelChangeCaptainPasswordModalBtn" class="h-11 w-full sm:w-auto px-4 rounded-lg border border-border-light dark:border-border-dark text-sm font-semibold">Cancel</button>
                    <button type="submit" class="h-11 w-full sm:w-auto px-4 rounded-lg bg-primary text-white text-sm font-bold hover:opacity-90">Save Password</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteCaptainModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl">
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
                <div>
                    <h2 class="text-xl font-bold text-text-light dark:text-text-dark">Delete Captain Account</h2>
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">This will permanently remove the captain account.</p>
                </div>
                <button type="button" id="closeDeleteCaptainModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="deleteCaptainForm" method="POST" action="" class="space-y-4 px-6 py-5">
                @csrf
                @method('DELETE')
                <p class="text-sm leading-6 text-text-light/80 dark:text-text-dark/80">
                    Delete <span id="deleteCaptainName" class="font-semibold text-text-light dark:text-text-dark"></span>? This will also unassign the captain from any barangay.
                </p>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="rounded-lg h-11 px-4 bg-red-100 text-red-700 text-sm font-semibold border border-red-300 hover:bg-red-200">
                        Delete
                    </button>
                    <button type="button" id="cancelDeleteCaptainModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="barangayOverviewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4 py-6">
        <div class="w-full max-w-6xl h-[calc(100vh-3rem)] overflow-hidden rounded-2xl bg-white dark:bg-background-dark border border-border-light dark:border-border-dark shadow-2xl flex flex-col">
            <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4 sticky top-0 bg-white dark:bg-background-dark z-10">
                <div>
                    <h2 class="text-xl font-bold text-text-light dark:text-text-dark">Mindoro Barangay Map</h2>
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Browse, select, edit, or delete barangay boundaries in Oriental Mindoro.</p>
                </div>
                <button type="button" id="closeBarangayOverviewModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="grid flex-1 min-h-0 gap-0 lg:grid-cols-[minmax(0,1.8fr)_minmax(320px,1fr)]">
                <div class="border-b lg:border-b-0 lg:border-r border-border-light dark:border-border-dark">
                    <div id="barangayOverviewMap" class="h-[70vh] w-full"></div>
                </div>
                <div class="p-6 space-y-4 flex flex-col min-h-0">
                    <div class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 space-y-3 relative">
                        <div class="relative">
                            <label for="barangayOverviewSearch" class="text-xs uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Search Barangays</label>
                            <div class="relative mt-2">
                                <input id="barangayOverviewSearch" type="text" class="w-full h-11 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-background-dark px-3 pr-10 text-sm" placeholder="Type a barangay name..." autocomplete="off" />
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-text-light/40 dark:text-text-dark/40 text-base">search</span>
                            </div>
                            <div id="barangayOverviewSearchResults" class="hidden absolute left-0 right-0 top-full z-20 mt-2 max-h-56 overflow-y-auto rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-background-dark shadow-xl"></div>
                        </div>
                        <p id="barangayOverviewSearchHint" class="text-xs text-text-light/60 dark:text-text-dark/60">Type to filter the list below.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="editSelectedBarangayBtn" class="inline-flex items-center gap-2 rounded-lg bg-primary text-white px-4 py-2 text-sm font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <span class="material-symbols-outlined text-base">edit</span>
                            Edit Selected
                        </button>
                        <button type="button" id="deleteSelectedBarangayBtn" class="inline-flex items-center gap-2 rounded-lg bg-red-100 text-red-700 border border-red-300 px-4 py-2 text-sm font-semibold hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <span class="material-symbols-outlined text-base">delete</span>
                            Delete Selected
                        </button>
                    </div>

                    <div class="rounded-xl border border-border-light dark:border-border-dark overflow-hidden flex flex-col min-h-0 flex-1">
                        <div class="border-b border-border-light dark:border-border-dark px-4 py-3 bg-background-light dark:bg-background-dark">
                            <p class="text-sm font-semibold text-text-light dark:text-text-dark">Barangays</p>
                        </div>
                        <div id="barangayOverviewList" class="flex-1 overflow-y-auto divide-y divide-border-light dark:divide-border-dark"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $barangayMapPayload = ($barangays ?? collect())->map(function ($barangay) {
            return [
                'id' => $barangay->id,
                'name' => $barangay->name,
                'boundary' => $barangay->boundary_geojson,
                'captain' => $barangay->captain?->name,
            ];
        })->values();

        $captainManagementPayload = ($captains ?? collect())->map(function ($captain) {
            return [
                'id' => $captain->id,
                'name' => $captain->name,
                'email' => $captain->email,
                'barangay_id' => $captain->ledBarangay?->id,
                'barangay_name' => $captain->ledBarangay?->name,
            ];
        })->values();
    @endphp

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        const openAddCaptainModalBtn = document.getElementById('openAddCaptainModalBtn');
        const openBarangayMapModalBtn = document.getElementById('openBarangayMapModalBtn');
        const toggleAddFormButton = document.getElementById('toggle-add-form');
        const barangayModal = document.getElementById('barangayModal');
        const closeBarangayModalBtn = document.getElementById('closeBarangayModalBtn');
        const barangayForm = document.getElementById('barangayForm');
        const barangayFormTitle = document.getElementById('barangayFormTitle');
        const barangayFormSubtitle = document.getElementById('barangayFormSubtitle');
        const barangayIdInput = document.getElementById('barangay_id');
        const barangayNameInput = document.getElementById('name');
        const boundaryGeoJsonInput = document.getElementById('boundary_geojson');
        const barangayBoundaryMapContainer = document.getElementById('barangayBoundaryMap');
        const barangayMapSearchInput = document.getElementById('barangayMapSearchInput');
        const barangayMapSearchSuggestions = document.getElementById('barangayMapSearchSuggestions');
        const barangaySubmitText = document.getElementById('barangaySubmitText');
        const barangaySubmitIcon = document.getElementById('barangaySubmitIcon');
        const addCaptainModal = document.getElementById('addCaptainModal');
        const closeAddCaptainModalBtn = document.getElementById('closeAddCaptainModalBtn');
        const cancelAddCaptainModalBtn = document.getElementById('cancelAddCaptainModalBtn');
        const deleteBarangayModal = document.getElementById('deleteBarangayModal');
        const deleteBarangayForm = document.getElementById('deleteBarangayForm');
        const deleteBarangayName = document.getElementById('deleteBarangayName');
        const closeDeleteBarangayModalBtn = document.getElementById('closeDeleteBarangayModalBtn');
        const cancelDeleteBarangayModalBtn = document.getElementById('cancelDeleteBarangayModalBtn');
        const barangayOverviewModal = document.getElementById('barangayOverviewModal');
        const closeBarangayOverviewModalBtn = document.getElementById('closeBarangayOverviewModalBtn');
        const barangayOverviewMapContainer = document.getElementById('barangayOverviewMap');
        const barangayOverviewList = document.getElementById('barangayOverviewList');
        const barangayOverviewSearch = document.getElementById('barangayOverviewSearch');
        const barangayOverviewSearchResults = document.getElementById('barangayOverviewSearchResults');
        const barangayOverviewName = document.getElementById('barangayOverviewName');
        const barangayOverviewMeta = document.getElementById('barangayOverviewMeta');
        const barangaySearchInput = document.getElementById('barangaySearchInput');
        const barangayTableBody = document.getElementById('barangayTableBody');
        const barangayListEmptyState = document.getElementById('barangayListEmptyState');
        const captainManagementModal = document.getElementById('captainManagementModal');
        const closeCaptainManagementModalBtn = document.getElementById('closeCaptainManagementModalBtn');
        const captainManagementTitle = document.getElementById('captainManagementTitle');
        const captainManagementSubtitle = document.getElementById('captainManagementSubtitle');
        const captainManagementSearch = document.getElementById('captainManagementSearch');
        const captainManagementCards = document.getElementById('captainManagementCards');
        const captainManagementEmptyState = document.getElementById('captainManagementEmptyState');
        const captainManagementForm = document.getElementById('captainManagementForm');
        const captainManagementUserId = document.getElementById('captainManagementUserId');
        const captainManagementBarangayId = document.getElementById('captainManagementBarangayId');
        const captainManagementAction = document.getElementById('captainManagementAction');
        const unassignCaptainModal = document.getElementById('unassignCaptainModal');
        const closeUnassignCaptainModalBtn = document.getElementById('closeUnassignCaptainModalBtn');
        const cancelUnassignCaptainModalBtn = document.getElementById('cancelUnassignCaptainModalBtn');
        const confirmUnassignCaptainBtn = document.getElementById('confirmUnassignCaptainBtn');
        const unassignCaptainName = document.getElementById('unassignCaptainName');
        const unassignCaptainBarangay = document.getElementById('unassignCaptainBarangay');
        const unassignCaptainAction = document.getElementById('unassignCaptainAction');
        let pendingUnassignCaptain = null;
        const changeCaptainPasswordModal = document.getElementById('changeCaptainPasswordModal');
        const passwordCaptainId = document.getElementById('passwordCaptainId');
        const passwordCaptainName = document.getElementById('passwordCaptainName');
        const captainPasswordInput = document.getElementById('captain_password_change');
        const captainPasswordConfirmationInput = document.getElementById('captain_password_confirmation_change');
        const closeChangeCaptainPasswordModalBtn = document.getElementById('closeChangeCaptainPasswordModalBtn');
        const cancelChangeCaptainPasswordModalBtn = document.getElementById('cancelChangeCaptainPasswordModalBtn');
        const shouldOpenCaptainPasswordModal = @json(old('password_form') && ($errors->has('password') || $errors->has('password_confirmation') || $errors->has('captain_user_id')));
        const deleteCaptainModal = document.getElementById('deleteCaptainModal');
        const closeDeleteCaptainModalBtn = document.getElementById('closeDeleteCaptainModalBtn');
        const cancelDeleteCaptainModalBtn = document.getElementById('cancelDeleteCaptainModalBtn');
        const deleteCaptainForm = document.getElementById('deleteCaptainForm');
        const deleteCaptainName = document.getElementById('deleteCaptainName');
        const deleteCaptainButtons = document.querySelectorAll('.captain-management-delete-btn');
        const toastData = document.getElementById('toastData');
        const editSelectedBarangayBtn = document.getElementById('editSelectedBarangayBtn');
        const deleteSelectedBarangayBtn = document.getElementById('deleteSelectedBarangayBtn');
        const openCaptainManagementButtons = document.querySelectorAll('.open-captain-management-btn');
        const deleteBarangayButtons = document.querySelectorAll('.delete-barangay-btn');
        const initialBoundary = @json(old('boundary_geojson'));
        const initialBarangayId = @json(old('barangay_id'));
        const barangaysWithBoundaries = @json($barangayMapPayload);
        const captainsWithBarangays = @json($captainManagementPayload);
        const boundaryColors = ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#ec4899'];
        const mindoroBounds = L.latLngBounds([
            [12.35, 120.35],
            [13.65, 121.45],
        ]);
        const mindoroCenter = [12.95, 120.90];
        const mindoroDefaultZoom = 9;

        function resetOverviewMapToMindoro() {
            if (barangayOverviewMap) {
                barangayOverviewMap.setView(mindoroCenter, mindoroDefaultZoom);
            }
        }

        function resetBoundaryMapToMindoro() {
            if (barangayBoundaryMap) {
                barangayBoundaryMap.setView(mindoroCenter, mindoroDefaultZoom);
            }

            if (searchMarker && barangayBoundaryMap) {
                barangayBoundaryMap.removeLayer(searchMarker);
                searchMarker = null;
            }

            barangayMapSearchSuggestions?.classList.add('hidden');
            if (barangayMapSearchSuggestions) {
                barangayMapSearchSuggestions.innerHTML = '';
            }
        }

        let barangayBoundaryMap = null;
        let barangayBoundaryGroup = null;
        let barangayOverviewMap = null;
        let barangayOverviewGroup = null;
        let barangayOverviewLayers = [];
        let visibleBarangayIds = new Set();
        let searchMarker = null;
        let selectedBarangay = null;
        let selectedCaptainManagementBarangay = null;
        let searchSuggestionsTimer = null;
        let barangayBoundaryMapState = null;
        let barangayOverviewMapState = null;

        function openModal(modalElement) {
            if (!modalElement) {
                return;
            }

            modalElement.classList.remove('hidden');
            modalElement.classList.add('flex');
        }

        function closeModal(modalElement) {
            if (!modalElement) {
                return;
            }

            modalElement.classList.add('hidden');
            modalElement.classList.remove('flex');
        }

        let toastTimer = null;

        function showToast(message, type) {
            if (!message) {
                return;
            }

            const existing = document.getElementById('floatingToast');
            if (existing) {
                existing.remove();
            }

            const toast = document.createElement('div');
            toast.id = 'floatingToast';
            toast.className = `toast-enter fixed top-4 right-4 z-[200] max-w-sm rounded-xl px-4 py-3 shadow-lg border ${type === 'error' ? 'border-red-200 bg-red-50 text-red-700 dark:bg-red-950/90 dark:text-red-200 dark:border-red-900' : 'border-green-200 bg-green-50 text-green-700 dark:bg-green-950/90 dark:text-green-200 dark:border-green-900'}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            if (toastTimer) {
                clearTimeout(toastTimer);
            }

            toastTimer = window.setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                window.setTimeout(() => toast.remove(), 180);
            }, 2500);
        }

        function openBarangayModal() {
            openModal(barangayModal);

            if (barangayBoundaryMap && !barangayIdInput?.value) {
                resetBoundaryMapToMindoro();
            }

            setTimeout(() => barangayBoundaryMap?.invalidateSize(), 50);
            setTimeout(() => barangayBoundaryMap?.invalidateSize(), 250);
        }

        function closeBarangayModal() {
            barangayBoundaryMapState = null;

            if (searchMarker && barangayBoundaryMap) {
                barangayBoundaryMap.removeLayer(searchMarker);
                searchMarker = null;
            }

            if (barangayMapSearchInput) {
                barangayMapSearchInput.value = '';
            }
            barangayMapSearchSuggestions?.classList.add('hidden');
            if (barangayMapSearchSuggestions) {
                barangayMapSearchSuggestions.innerHTML = '';
            }

            if (barangayBoundaryMap) {
                barangayBoundaryMap.remove();
                barangayBoundaryMap = null;
                barangayBoundaryGroup = null;
            }

            closeModal(barangayModal);
        }

        function focusBarangayBoundaryMap() {
            if (!barangayBoundaryMap || !barangayBoundaryGroup) {
                return;
            }

            const bounds = barangayBoundaryGroup.getBounds();
            if (bounds && bounds.isValid()) {
                barangayBoundaryMap.fitBounds(bounds.pad(0.18));
            }
        }

        function openBarangayOverviewModal() {
            renderBarangayOverviewMap();
            setSelectedBarangay(null);
            if (barangayOverviewSearch) {
                barangayOverviewSearch.value = '';
            }
            resetOverviewMapToMindoro();
            openModal(barangayOverviewModal);
            setTimeout(() => barangayOverviewMap?.invalidateSize(), 50);
        }

        function closeBarangayOverviewModal() {
            barangayOverviewMapState = null;
            selectedBarangay = null;

            if (barangayOverviewSearch) {
                barangayOverviewSearch.value = '';
            }

            if (barangayOverviewSearchResults) {
                barangayOverviewSearchResults.classList.add('hidden');
                barangayOverviewSearchResults.innerHTML = '';
            }

            if (barangayOverviewMap) {
                barangayOverviewMap.remove();
                barangayOverviewMap = null;
                barangayOverviewGroup = null;
                barangayOverviewLayers = [];
            }

            closeModal(barangayOverviewModal);
        }

        function filterBarangayTable(query) {
            if (!barangayTableBody) {
                return;
            }

            const normalized = (query || '').trim().toLowerCase();
            let visibleCount = 0;

            barangayTableBody.querySelectorAll('.barangay-row').forEach((row) => {
                const barangayName = row.getAttribute('data-barangay-name') || '';
                const captainName = row.getAttribute('data-captain-name') || '';
                const matches = normalized.length === 0 || barangayName.includes(normalized) || captainName.includes(normalized);

                row.classList.toggle('hidden', !matches);

                if (matches) {
                    visibleCount += 1;
                }
            });

            if (barangayListEmptyState) {
                barangayListEmptyState.classList.toggle('hidden', visibleCount > 0 || normalized.length === 0);
            }
        }

        function renderCaptainManagementCards(query = '') {
            if (!captainManagementCards || !selectedCaptainManagementBarangay) {
                return;
            }

            const normalized = (query || '').trim().toLowerCase();
            const filteredCaptains = captainsWithBarangays.filter((captain) => {
                const searchable = [captain.name, captain.email, captain.barangay_name || 'not assigned'].join(' ').toLowerCase();
                return normalized.length === 0 || searchable.includes(normalized);
            });

            captainManagementCards.innerHTML = filteredCaptains.map((captain) => {
                const isAssignedHere = Number(captain.barangay_id || 0) === Number(selectedCaptainManagementBarangay.id);
                const isAssignedElsewhere = !isAssignedHere && Boolean(captain.barangay_id);
                const currentPositionLabel = captain.barangay_name || 'No position assigned';

                return `
                    <div class="rounded-xl border border-border-light dark:border-border-dark bg-white dark:bg-background-dark p-4 shadow-sm captain-card" data-captain-id="${captain.id}" data-captain-search="${escapeHtml([captain.name, captain.email, currentPositionLabel].join(' ')).toLowerCase()}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-base font-semibold text-text-light dark:text-text-dark">${escapeHtml(captain.name)}</p>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">${escapeHtml(captain.email)}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${isAssignedHere ? 'bg-secondary/10 text-secondary' : 'bg-primary/10 text-primary'}">
                                ${escapeHtml(isAssignedHere ? 'Assigned here' : (isAssignedElsewhere ? 'Assigned elsewhere' : 'Available'))}
                            </span>
                        </div>

                        <div class="mt-4 rounded-lg bg-background-light dark:bg-background-dark px-3 py-2 text-xs text-text-light/70 dark:text-text-dark/70">
                            Current position: <span class="font-semibold text-text-light dark:text-text-dark">${escapeHtml(currentPositionLabel)}</span>
                        </div>

                        <div class="mt-4 flex flex-nowrap gap-2 overflow-x-auto pb-1">
                            <button type="button" class="captain-management-action-btn inline-flex items-center gap-2 rounded-lg px-3 h-9 text-sm font-semibold bg-primary text-white hover:opacity-90" data-captain-id="${captain.id}" data-action="assign" ${isAssignedHere ? 'disabled' : ''}>
                                <span class="material-symbols-outlined text-sm">assignment_ind</span>
                                Assign Position
                            </button>
                            <button type="button" class="captain-management-action-btn inline-flex items-center gap-2 rounded-lg px-3 h-9 text-sm font-semibold border border-amber-300 bg-amber-100 text-amber-700 hover:bg-amber-200" data-captain-id="${captain.id}" data-captain-name="${escapeHtml(captain.name)}" data-action="unassign" ${!isAssignedHere && !isAssignedElsewhere ? 'disabled' : ''}>
                                <span class="material-symbols-outlined text-sm">do_not_disturb_on</span>
                                Unassign
                            </button>
                            <button type="button" class="open-captain-password-modal-btn inline-flex items-center gap-2 rounded-lg px-3 h-9 text-sm font-semibold border border-primary/30 bg-primary/10 text-primary hover:bg-primary/15" data-captain-id="${captain.id}" data-captain-name="${escapeHtml(captain.name)}" title="Change password" aria-label="Change password">
                                <span class="material-symbols-outlined text-sm">lock_reset</span>
                                Password
                            </button>
                            <button type="button" class="captain-management-delete-btn inline-flex items-center gap-2 rounded-lg px-3 h-9 text-sm font-semibold border border-red-300 bg-red-100 text-red-700 hover:bg-red-200" data-captain-id="${captain.id}" data-captain-name="${escapeHtml(captain.name)}">
                                <span class="material-symbols-outlined text-sm">remove</span>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            if (captainManagementEmptyState) {
                captainManagementEmptyState.classList.toggle('hidden', filteredCaptains.length > 0);
            }
        }

        function openCaptainManagementModal(barangay) {
            if (!barangay) {
                return;
            }

            selectedCaptainManagementBarangay = barangay;

            if (captainManagementTitle) {
                captainManagementTitle.textContent = `Manage Captains - ${barangay.name}`;
            }

            if (captainManagementSubtitle) {
                captainManagementSubtitle.textContent = barangay.captain
                    ? `Current captain: ${barangay.captain}. Choose another captain card or remove the current position.`
                    : 'Choose a captain card to assign the position to this barangay.';
            }

            if (captainManagementBarangayId) {
                captainManagementBarangayId.value = barangay.id;
            }

            if (captainManagementSearch) {
                captainManagementSearch.value = '';
            }

            renderCaptainManagementCards('');
            openModal(captainManagementModal);

            setTimeout(() => captainManagementSearch?.focus(), 50);
        }

        function closeCaptainManagementModal() {
            selectedCaptainManagementBarangay = null;

            if (captainManagementSearch) {
                captainManagementSearch.value = '';
            }

            if (captainManagementCards) {
                captainManagementCards.innerHTML = '';
            }

            if (captainManagementEmptyState) {
                captainManagementEmptyState.classList.add('hidden');
            }

            closeModal(captainManagementModal);
        }

        function openDeleteCaptainModal(captainId, captainName) {
            if (!deleteCaptainModal || !deleteCaptainForm || !deleteCaptainName) {
                return;
            }

            deleteCaptainForm.action = "{{ url('/lgu/barangays/captains') }}/" + captainId;
            deleteCaptainName.textContent = captainName || 'this captain';
            openModal(deleteCaptainModal);
        }

        function closeDeleteCaptainModal() {
            closeModal(deleteCaptainModal);
        }

        function openUnassignCaptainModal(captainId, captainName) {
            if (!unassignCaptainModal || !unassignCaptainName || !unassignCaptainBarangay || !unassignCaptainAction) {
                return;
            }

            pendingUnassignCaptain = {
                captainId: String(captainId || ''),
                captainName: captainName || 'this captain',
            };

            unassignCaptainName.textContent = pendingUnassignCaptain.captainName;
            unassignCaptainBarangay.textContent = selectedCaptainManagementBarangay?.name || 'the selected barangay';
            unassignCaptainAction.textContent = `Unassign ${pendingUnassignCaptain.captainName}`;

            openModal(unassignCaptainModal);
        }

        function closeUnassignCaptainModal() {
            pendingUnassignCaptain = null;
            closeModal(unassignCaptainModal);
        }

        function openChangeCaptainPasswordModal(captainId, captainName) {
            if (!changeCaptainPasswordModal || !passwordCaptainId) {
                return;
            }

            passwordCaptainId.value = captainId || '';

            if (passwordCaptainName) {
                passwordCaptainName.textContent = captainName || 'this captain';
            }

            if (captainPasswordInput) {
                captainPasswordInput.value = '';
            }

            if (captainPasswordConfirmationInput) {
                captainPasswordConfirmationInput.value = '';
            }

            openModal(changeCaptainPasswordModal);

            setTimeout(() => captainPasswordInput?.focus(), 50);
        }

        function closeChangeCaptainPasswordModal() {
            closeModal(changeCaptainPasswordModal);
        }

        function confirmUnassignCaptain() {
            if (!pendingUnassignCaptain || !captainManagementForm || !captainManagementUserId || !captainManagementBarangayId || !captainManagementAction || !selectedCaptainManagementBarangay) {
                return;
            }

            captainManagementUserId.value = pendingUnassignCaptain.captainId;
            captainManagementBarangayId.value = selectedCaptainManagementBarangay.id;
            captainManagementAction.value = 'unassign';
            captainManagementForm.submit();
        }

        function submitCaptainManagementAction(captainId, action) {
            if (!captainManagementForm || !captainManagementUserId || !captainManagementBarangayId || !captainManagementAction || !selectedCaptainManagementBarangay) {
                return;
            }

            if (action === 'unassign') {
                openUnassignCaptainModal(captainId, 'this captain');
                return;
            }

            captainManagementUserId.value = captainId;
            captainManagementBarangayId.value = selectedCaptainManagementBarangay.id;
            captainManagementAction.value = action;
            captainManagementForm.submit();
        }

        function getOverviewStyle(index, isSelected = false) {
            const color = boundaryColors[index % boundaryColors.length];

            return {
                color: isSelected ? '#0f172a' : color,
                fillColor: color,
                fillOpacity: isSelected ? 0.48 : 0.24,
                weight: isSelected ? 4 : 2,
            };
        }

        function escapeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        function setSelectedBarangay(barangay) {
            selectedBarangay = barangay || null;

            if (barangayOverviewName) {
                barangayOverviewName.textContent = barangay ? barangay.name : 'Click a highlighted barangay';
            }

            if (barangayOverviewMeta) {
                barangayOverviewMeta.textContent = barangay
                    ? `Captain: ${barangay.captain || 'Not assigned'} • Click edit to resize or rename it.`
                    : 'The boundary will highlight when selected.';
            }

            if (editSelectedBarangayBtn) {
                editSelectedBarangayBtn.disabled = !barangay;
            }

            if (deleteSelectedBarangayBtn) {
                deleteSelectedBarangayBtn.disabled = !barangay;
            }

            barangayOverviewLayers.forEach((entry, index) => {
                const isSelected = barangay && entry.barangay.id === barangay.id;
                entry.layer.setStyle(getOverviewStyle(index, isSelected));

                if (isSelected) {
                    entry.layer.bringToFront();
                    if (barangayOverviewMap) {
                        barangayOverviewMap.fitBounds(entry.layer.getBounds().pad(0.18));
                    }
                }
            });
        }

        function openSelectedBarangayForEdit() {
            if (!selectedBarangay) {
                return;
            }

            closeModal(barangayOverviewModal);
            setFormToEditMode(selectedBarangay.id, selectedBarangay.name, selectedBarangay.boundary);
            openBarangayModal();
        }

        function openSelectedBarangayForDelete() {
            if (!selectedBarangay) {
                return;
            }

            if (deleteBarangayForm) {
                deleteBarangayForm.action = "{{ url('/lgu/barangays') }}/" + selectedBarangay.id;
            }

            if (deleteBarangayName) {
                deleteBarangayName.textContent = selectedBarangay.name;
            }

            closeModal(barangayOverviewModal);
            openModal(deleteBarangayModal);
        }

        async function fetchBarangaySuggestions(query) {
            const normalized = (query || '').trim();

            if (!barangayMapSearchSuggestions) {
                return [];
            }

            if (normalized.length < 2) {
                barangayMapSearchSuggestions.classList.add('hidden');
                barangayMapSearchSuggestions.innerHTML = '';

                if (!normalized) {
                    resetBoundaryMapToMindoro();
                }

                return [];
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(normalized)}&limit=5`);
                if (!response.ok) {
                    return [];
                }

                const results = await response.json();
                if (!Array.isArray(results) || results.length === 0) {
                    barangayMapSearchSuggestions.classList.add('hidden');
                    barangayMapSearchSuggestions.innerHTML = '<div class="px-3 py-2 text-sm text-text-light/60 dark:text-text-dark/60">No suggestions found.</div>';
                    return [];
                }

                barangayMapSearchSuggestions.innerHTML = results.map((result, index) => `
                    <button type="button" class="barangay-suggestion-btn w-full text-left px-3 py-2 border-b last:border-b-0 border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark" data-suggestion-index="${index}">
                        <div class="text-sm font-medium text-text-light dark:text-text-dark">${escapeHtml(result.display_name || normalized)}</div>
                    </button>
                `).join('');
                barangayMapSearchSuggestions.classList.remove('hidden');

                const suggestionButtons = barangayMapSearchSuggestions.querySelectorAll('.barangay-suggestion-btn');
                suggestionButtons.forEach((button, index) => {
                    button.addEventListener('click', () => {
                        const result = results[index];
                        barangayMapSearchInput.value = result.display_name || normalized;
                        barangayMapSearchSuggestions.classList.add('hidden');
                        barangayMapSearchSuggestions.innerHTML = '';
                        searchBarangayLocation(result.display_name || normalized);
                    });
                });

                return results;
            } catch (error) {
                return [];
            }
        }

        function renderBoundaryGeoJson(boundary) {
            if (!barangayBoundaryGroup) {
                return;
            }

            barangayBoundaryGroup.clearLayers();

            if (!boundary) {
                syncBoundaryInput();
                return;
            }

            try {
                const parsedBoundary = typeof boundary === 'string' ? JSON.parse(boundary) : boundary;

                L.geoJSON(parsedBoundary, {
                    style: {
                        color: '#22c55e',
                        fillColor: '#86efac',
                        fillOpacity: 0.35,
                    },
                }).eachLayer((layer) => barangayBoundaryGroup.addLayer(layer));
                syncBoundaryInput();
            } catch (error) {
                boundaryGeoJsonInput.value = '';
            }
        }

        function setFormToCreateMode() {
            if (!barangayForm || !barangayFormTitle || !barangayFormSubtitle || !barangaySubmitText || !barangaySubmitIcon || !barangayIdInput || !barangayNameInput) {
                return;
            }

            barangayForm.action = "{{ route('lgu.barangays.store') }}";
            barangayIdInput.value = '';
            barangayNameInput.value = '';
            barangayFormTitle.textContent = 'Add Barangay Boundary';
            barangayFormSubtitle.textContent = 'Draw the barangay boundary on the map and save it here.';
            barangaySubmitText.textContent = 'Save Barangay';
            barangaySubmitIcon.textContent = 'check';
            clearBoundaryLayer();
            if (barangayMapSearchInput) {
                barangayMapSearchInput.value = '';
            }
            resetBoundaryMapToMindoro();
            barangayMapSearchSuggestions?.classList.add('hidden');
            if (barangayMapSearchSuggestions) {
                barangayMapSearchSuggestions.innerHTML = '';
            }
        }

        function setFormToEditMode(barangayId, barangayName, barangayBoundary) {
            if (!barangayForm || !barangayFormTitle || !barangayFormSubtitle || !barangaySubmitText || !barangaySubmitIcon || !barangayIdInput || !barangayNameInput) {
                return;
            }

            ensureBoundaryMap();
            barangayForm.action = "{{ route('lgu.barangays.store') }}";
            barangayIdInput.value = barangayId || '';
            barangayNameInput.value = barangayName || '';
            barangayFormTitle.textContent = 'Edit Barangay Boundary';
            barangayFormSubtitle.textContent = 'Update the barangay name or redraw its boundary before saving.';
            barangaySubmitText.textContent = 'Update Barangay';
            barangaySubmitIcon.textContent = 'save';
            renderBoundaryGeoJson(barangayBoundary);
            if (barangayMapSearchInput) {
                barangayMapSearchInput.value = '';
            }
            barangayMapSearchSuggestions?.classList.add('hidden');
            if (barangayMapSearchSuggestions) {
                barangayMapSearchSuggestions.innerHTML = '';
            }

            openBarangayModal();
            setTimeout(() => {
                barangayBoundaryMap?.invalidateSize();
                focusBarangayBoundaryMap();
            }, 75);
        }

        function getBoundaryStyle(index) {
            const color = boundaryColors[index % boundaryColors.length];

            return {
                color,
                fillColor: color,
                fillOpacity: 0.24,
                weight: 2,
            };
        }

        function syncBoundaryInput() {
            if (!boundaryGeoJsonInput || !barangayBoundaryGroup) {
                return;
            }

            if (barangayBoundaryGroup.getLayers().length === 0) {
                boundaryGeoJsonInput.value = '';
                return;
            }

            boundaryGeoJsonInput.value = JSON.stringify(barangayBoundaryGroup.toGeoJSON());
        }

        function clearBoundaryLayer() {
            barangayBoundaryGroup?.clearLayers();
            syncBoundaryInput();
        }

        async function searchBarangayLocation(queryOverride = '') {
            if (!barangayBoundaryMap || !barangayMapSearchInput) {
                return;
            }

            const query = (queryOverride || barangayMapSearchInput.value).trim();
            if (!query) {
                return;
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(query)}&limit=1`);
                if (!response.ok) {
                    return;
                }

                const results = await response.json();
                if (!Array.isArray(results) || results.length === 0) {
                    return;
                }

                const result = results[0];
                const lat = Number(result.lat);
                const lng = Number(result.lon);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }

                barangayBoundaryMap.setView([lat, lng], 16);

                if (searchMarker) {
                    searchMarker.setLatLng([lat, lng]);
                } else {
                    searchMarker = L.marker([lat, lng], {
                        keyboard: false,
                    }).addTo(barangayBoundaryMap);
                }

                searchMarker.bindPopup(result.display_name || query).openPopup();
            } catch (error) {
            }
        }

        function renderBarangayOverviewMap() {
            if (barangayOverviewMap || !barangayOverviewMapContainer || !window.L) {
                return;
            }

            barangayOverviewMap = L.map('barangayOverviewMap', {
                minZoom: 3,
                attributionControl: false,
            }).setView(mindoroCenter, mindoroDefaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(barangayOverviewMap);

            barangayOverviewGroup = new L.FeatureGroup();
            barangayOverviewMap.addLayer(barangayOverviewGroup);

            barangayOverviewLayers = [];
            visibleBarangayIds = new Set(barangaysWithBoundaries.map((barangay) => Number(barangay.id)));

            barangaysWithBoundaries.forEach((barangay, index) => {
                if (!barangay.boundary) {
                    return;
                }

                const layer = L.geoJSON(barangay.boundary, {
                    style: getOverviewStyle(index, false),
                    onEachFeature: (feature, featureLayer) => {
                        featureLayer.on('click', () => {
                            setSelectedBarangay(barangay);
                        });
                        featureLayer.bindTooltip(barangay.name, { sticky: true });
                    },
                });

                layer.addTo(barangayOverviewMap);
                barangayOverviewLayers.push({ barangay, layer });
            });

            const overviewList = barangayOverviewList;

            if (overviewList) {
                overviewList.innerHTML = barangaysWithBoundaries.map((barangay, index) => `
                    <button type="button" class="overview-list-item w-full text-left px-4 py-3 hover:bg-background-light dark:hover:bg-background-dark border-l-4 border-transparent" data-barangay-id="${barangay.id}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-text-light dark:text-text-dark">${barangay.name}</p>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Captain: ${barangay.captain || 'Not assigned'}</p>
                            </div>
                            <span class="material-symbols-outlined text-text-light/50 dark:text-text-dark/50">chevron_right</span>
                        </div>
                    </button>
                `).join('');

                overviewList.querySelectorAll('.overview-list-item').forEach((button) => {
                    button.addEventListener('click', () => {
                        const barangayId = Number(button.getAttribute('data-barangay-id'));
                        const barangay = barangaysWithBoundaries.find((item) => Number(item.id) === barangayId);
                        const visibleLayer = barangayOverviewLayers.find((entry) => Number(entry.barangay.id) === barangayId);

                        if (barangay) {
                            setSelectedBarangay(barangay);
                            if (barangayOverviewMap && visibleLayer?.layer) {
                                barangayOverviewMap.fitBounds(visibleLayer.layer.getBounds().pad(0.18));
                            }
                        }
                    });
                });
            }

            updateBarangayOverviewFilter('');
        }

        function updateBarangayOverviewFilter(query) {
            const normalized = (query || '').trim().toLowerCase();

            if (barangayOverviewSearchResults) {
                barangayOverviewSearchResults.innerHTML = '';
                barangayOverviewSearchResults.classList.toggle('hidden', normalized.length === 0);
            }

            if (!normalized) {
                setSelectedBarangay(null);
                resetOverviewMapToMindoro();
            }

            const visibleItems = barangaysWithBoundaries.filter((barangay) => barangay.name.toLowerCase().includes(normalized));
            visibleBarangayIds = new Set(visibleItems.map((barangay) => Number(barangay.id)));

            barangayOverviewList?.querySelectorAll('.overview-list-item').forEach((button) => {
                const barangayId = Number(button.getAttribute('data-barangay-id'));
                const isVisible = visibleBarangayIds.has(barangayId);
                button.style.display = isVisible ? '' : 'none';
            });

            if (barangayOverviewList) {
                const existingEmptyState = barangayOverviewList.querySelector('[data-empty-state="barangays"]');
                const hasVisibleRows = Array.from(barangayOverviewList.querySelectorAll('.overview-list-item')).some((button) => button.style.display !== 'none');

                if (normalized && !hasVisibleRows) {
                    if (!existingEmptyState) {
                        const emptyState = document.createElement('div');
                        emptyState.setAttribute('data-empty-state', 'barangays');
                        emptyState.className = 'px-4 py-6 text-center text-sm text-text-light/60 dark:text-text-dark/60';
                        emptyState.textContent = 'No barangays found.';
                        barangayOverviewList.appendChild(emptyState);
                    }
                } else if (existingEmptyState) {
                    existingEmptyState.remove();
                }
            }

            barangayOverviewLayers.forEach((entry, index) => {
                const matchesFilter = visibleBarangayIds.has(Number(entry.barangay.id));
                entry.layer.setStyle({
                    ...getOverviewStyle(index, selectedBarangay && Number(selectedBarangay.id) === Number(entry.barangay.id)),
                    fillOpacity: matchesFilter ? (selectedBarangay && Number(selectedBarangay.id) === Number(entry.barangay.id) ? 0.48 : 0.24) : 0.05,
                    opacity: matchesFilter ? 1 : 0.25,
                });
                if (!matchesFilter) {
                    entry.layer.bringToBack();
                }
            });

            if (!normalized) {
                return;
            }

            if (barangayOverviewSearchResults) {
                const suggestions = visibleItems.slice(0, 5);

                if (suggestions.length === 0) {
                    barangayOverviewSearchResults.innerHTML = '<div class="px-3 py-2 text-sm text-text-light/60 dark:text-text-dark/60">No barangays found.</div>';
                } else {
                    barangayOverviewSearchResults.innerHTML = suggestions.map((barangay) => `
                        <button type="button" class="overview-search-result-btn w-full text-left px-3 py-2 border-b last:border-b-0 border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark" data-barangay-id="${barangay.id}">
                            <div class="text-sm font-medium text-text-light dark:text-text-dark">${escapeHtml(barangay.name)}</div>
                        </button>
                    `).join('');

                    barangayOverviewSearchResults.querySelectorAll('.overview-search-result-btn').forEach((button) => {
                        button.addEventListener('click', () => {
                            const barangayId = Number(button.getAttribute('data-barangay-id'));
                            const barangay = barangaysWithBoundaries.find((item) => Number(item.id) === barangayId);

                            if (barangay) {
                                setSelectedBarangay(barangay);
                                if (barangayOverviewSearch) {
                                    barangayOverviewSearch.value = barangay.name;
                                }
                                barangayOverviewSearchResults.classList.add('hidden');
                            }
                        });
                    });
                }
            }
        }

        function ensureBoundaryMap() {
            if (barangayBoundaryMap || !barangayBoundaryMapContainer || !window.L) {
                return;
            }

            barangayBoundaryMap = L.map('barangayBoundaryMap', {
                minZoom: 3,
                attributionControl: false,
            }).setView(mindoroCenter, mindoroDefaultZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(barangayBoundaryMap);

            barangayBoundaryGroup = new L.FeatureGroup();
            barangayBoundaryMap.addLayer(barangayBoundaryGroup);

            barangayBoundaryMap.addControl(new L.Control.Draw({
                edit: {
                    featureGroup: barangayBoundaryGroup,
                    remove: true,
                },
                draw: {
                    polygon: {
                        allowIntersection: false,
                        shapeOptions: {
                            color: '#22c55e',
                            fillColor: '#86efac',
                            fillOpacity: 0.35,
                        },
                    },
                    rectangle: false,
                    circle: false,
                    marker: false,
                    polyline: false,
                    circlemarker: false,
                },
            }));

            barangayBoundaryMap.on(L.Draw.Event.CREATED, (event) => {
                clearBoundaryLayer();
                barangayBoundaryGroup.addLayer(event.layer);
                syncBoundaryInput();
            });

            barangayBoundaryMap.on(L.Draw.Event.EDITED, syncBoundaryInput);
            barangayBoundaryMap.on(L.Draw.Event.DELETED, syncBoundaryInput);

            barangaysWithBoundaries.forEach((barangay, index) => {
                if (!barangay.boundary) {
                    return;
                }

                L.geoJSON(barangay.boundary, {
                    style: getBoundaryStyle(index),
                    onEachFeature: (feature, layer) => {
                        layer.bindTooltip(barangay.name, { sticky: true });
                    },
                }).addTo(barangayBoundaryMap);
            });

            if (initialBoundary) {
                renderBoundaryGeoJson(initialBoundary);
            }

            setTimeout(() => barangayBoundaryMap?.invalidateSize(), 100);
        }

        openAddCaptainModalBtn?.addEventListener('click', () => openModal(addCaptainModal));
        openBarangayMapModalBtn?.addEventListener('click', openBarangayOverviewModal);
        closeBarangayOverviewModalBtn?.addEventListener('click', closeBarangayOverviewModal);
        closeBarangayModalBtn?.addEventListener('click', closeBarangayModal);
        editSelectedBarangayBtn?.addEventListener('click', openSelectedBarangayForEdit);
        deleteSelectedBarangayBtn?.addEventListener('click', openSelectedBarangayForDelete);
        closeAddCaptainModalBtn?.addEventListener('click', () => closeModal(addCaptainModal));
        cancelAddCaptainModalBtn?.addEventListener('click', () => closeModal(addCaptainModal));
        closeCaptainManagementModalBtn?.addEventListener('click', closeCaptainManagementModal);
        closeUnassignCaptainModalBtn?.addEventListener('click', closeUnassignCaptainModal);
        cancelUnassignCaptainModalBtn?.addEventListener('click', closeUnassignCaptainModal);
        confirmUnassignCaptainBtn?.addEventListener('click', confirmUnassignCaptain);
        closeChangeCaptainPasswordModalBtn?.addEventListener('click', closeChangeCaptainPasswordModal);
        cancelChangeCaptainPasswordModalBtn?.addEventListener('click', closeChangeCaptainPasswordModal);
        closeDeleteCaptainModalBtn?.addEventListener('click', closeDeleteCaptainModal);
        cancelDeleteCaptainModalBtn?.addEventListener('click', closeDeleteCaptainModal);

        deleteBarangayButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const barangayId = button.getAttribute('data-barangay-id') || '';
                const barangayName = button.getAttribute('data-barangay-name') || '';

                if (deleteBarangayForm) {
                    deleteBarangayForm.action = "{{ url('/lgu/barangays') }}/" + barangayId;
                }

                if (deleteBarangayName) {
                    deleteBarangayName.textContent = barangayName;
                }

                openModal(deleteBarangayModal);
            });
        });

        openCaptainManagementButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const barangayId = Number(button.getAttribute('data-barangay-id') || 0);
                const barangayName = button.getAttribute('data-barangay-name') || '';
                const barangayCaptain = button.getAttribute('data-barangay-captain') || '';
                const barangay = {
                    id: barangayId,
                    name: barangayName,
                    captain: barangayCaptain,
                };

                openCaptainManagementModal(barangay);
            });
        });

        closeDeleteBarangayModalBtn?.addEventListener('click', () => closeModal(deleteBarangayModal));
        cancelDeleteBarangayModalBtn?.addEventListener('click', () => closeModal(deleteBarangayModal));

        barangaySearchInput?.addEventListener('input', () => {
            filterBarangayTable(barangaySearchInput.value);
        });

        captainManagementSearch?.addEventListener('input', () => {
            renderCaptainManagementCards(captainManagementSearch.value);
        });

        captainManagementCards?.addEventListener('click', (event) => {
            const actionButton = event.target.closest('.captain-management-action-btn');
            const passwordButton = event.target.closest('.open-captain-password-modal-btn');
            const deleteButton = event.target.closest('.captain-management-delete-btn');

            if (actionButton) {
                const captainId = actionButton.getAttribute('data-captain-id');
                const action = actionButton.getAttribute('data-action');
                const captainName = actionButton.getAttribute('data-captain-name') || 'this captain';

                if (captainId && action) {
                    if (action === 'unassign') {
                        openUnassignCaptainModal(captainId, captainName);
                    } else {
                        submitCaptainManagementAction(captainId, action);
                    }
                }
            }

            if (passwordButton) {
                openChangeCaptainPasswordModal(passwordButton.getAttribute('data-captain-id') || '', passwordButton.getAttribute('data-captain-name') || 'this captain');
            }

            if (deleteButton) {
                openDeleteCaptainModal(deleteButton.getAttribute('data-captain-id') || '', deleteButton.getAttribute('data-captain-name') || 'this captain');
            }
        });

        barangayModal?.addEventListener('click', (event) => {
            if (event.target === barangayModal) {
                closeBarangayModal();
            }
        });

        captainManagementModal?.addEventListener('click', (event) => {
            if (event.target === captainManagementModal) {
                closeCaptainManagementModal();
            }
        });

        unassignCaptainModal?.addEventListener('click', (event) => {
            if (event.target === unassignCaptainModal) {
                closeUnassignCaptainModal();
            }
        });

        deleteCaptainModal?.addEventListener('click', (event) => {
            if (event.target === deleteCaptainModal) {
                closeDeleteCaptainModal();
            }
        });

        changeCaptainPasswordModal?.addEventListener('click', (event) => {
            if (event.target === changeCaptainPasswordModal) {
                closeChangeCaptainPasswordModal();
            }
        });

        if (shouldOpenCaptainPasswordModal) {
            const oldCaptainId = @json(old('captain_user_id'));
            const oldCaptainBtn = oldCaptainId
                ? document.querySelector(`.open-captain-password-modal-btn[data-captain-id="${oldCaptainId}"]`)
                : null;

            openChangeCaptainPasswordModal(
                oldCaptainId || '',
                oldCaptainBtn?.dataset.captainName || 'this captain'
            );
        }

        barangayOverviewModal?.addEventListener('click', (event) => {
            if (event.target === barangayOverviewModal) {
                closeBarangayOverviewModal();
            }
        });

        if (toastData) {
            showToast(toastData.getAttribute('data-toast-message') || '', toastData.getAttribute('data-toast-type') || 'success');
        }

        barangayMapSearchInput?.addEventListener('input', () => {
            const query = (barangayMapSearchInput.value || '').trim();

            if (!query) {
                resetBoundaryMapToMindoro();
                return;
            }

            window.clearTimeout(searchSuggestionsTimer);
            searchSuggestionsTimer = window.setTimeout(() => {
                fetchBarangaySuggestions(barangayMapSearchInput.value);
            }, 220);
        });

        barangayMapSearchInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                barangayMapSearchSuggestions?.classList.add('hidden');
                searchBarangayLocation();
            }
        });

        barangayOverviewSearch?.addEventListener('input', () => {
            updateBarangayOverviewFilter(barangayOverviewSearch.value);
        });

        barangayOverviewSearch?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                const firstVisible = barangaysWithBoundaries.find((barangay) => barangay.name.toLowerCase().includes((barangayOverviewSearch.value || '').trim().toLowerCase()));

                if (firstVisible) {
                    setSelectedBarangay(firstVisible);
                    const visibleLayer = barangayOverviewLayers.find((entry) => Number(entry.barangay.id) === Number(firstVisible.id));
                    if (visibleLayer?.layer && barangayOverviewMap) {
                        barangayOverviewMap.fitBounds(visibleLayer.layer.getBounds().pad(0.18));
                    }
                    barangayOverviewSearchResults?.classList.add('hidden');
                }
            }
        });

        document.addEventListener('click', (event) => {
            if (barangayMapSearchSuggestions && !barangayMapSearchSuggestions.contains(event.target) && event.target !== barangayMapSearchInput) {
                barangayMapSearchSuggestions.classList.add('hidden');
            }

            if (barangayOverviewSearchResults && !barangayOverviewSearchResults.contains(event.target) && event.target !== barangayOverviewSearch) {
                barangayOverviewSearchResults.classList.add('hidden');
            }
        });

        filterBarangayTable(barangaySearchInput?.value || '');

        @if (old('captain_email') || session('reopen_captain_modal'))
            openModal(addCaptainModal);
        @endif

        toggleAddFormButton?.addEventListener('click', () => {
            setFormToCreateMode();
            ensureBoundaryMap();
            openBarangayModal();
        });

        if (initialBarangayId) {
            ensureBoundaryMap();
            setFormToEditMode(initialBarangayId, barangayNameInput?.value || '', boundaryGeoJsonInput?.value || '');
            openBarangayModal();
        }

        @if ($errors->any() && ! old('captain_email') && ! session('reopen_captain_modal'))
            ensureBoundaryMap();
            openBarangayModal();
        @endif

        if (barangayModal) {
            barangayModal.addEventListener('click', function(event) {
                if (event.target === barangayModal) {
                    closeBarangayModal();
                }
            });
        }

        if (barangayOverviewModal) {
            barangayOverviewModal.addEventListener('click', function(event) {
                if (event.target === barangayOverviewModal) {
                    closeBarangayOverviewModal();
                }
            });
        }

        if (captainManagementModal) {
            captainManagementModal.addEventListener('click', function(event) {
                if (event.target === captainManagementModal) {
                    closeCaptainManagementModal();
                }
            });
        }
    </script>
@include('partials.lgu-mobile-nav')
</body>
</html>
