<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Residents</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<style>
    .material-symbols-outlined {
        font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24
    }
    
    @media (max-width: 768px) {
        #sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            position: fixed;
            z-index: 40;
            height: 100vh;
        }
        
        #sidebar.active {
            transform: translateX(0);
        }
        
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
        }
        
        #overlay.active {
            display: block;
        }
        
        main {
            width: 100%;
        }
    }
    
    .page-content {
        height: 100%;
        overflow-y: auto;
    }
    
    .page-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .page-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .page-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .page-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    
    .nav-item.active {
        background-color: rgba(74, 144, 226, 0.2);
        color: #4A90E2;
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
<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    
    <div id="overlay" class=""></div>
    
    @include('sidebars.barangay_captain')
    
    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Residents</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">View resident profiles in your assigned barangay.</p>
                    </div>
                    <button id="openResidentsMapBtn" type="button" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap hover:opacity-90">
                        <span class="material-symbols-outlined text-base">map</span>
                        <span>View Residents Map</span>
                    </button>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    @php
                        $residents = $residents ?? collect();
                        $assignedBarangay = $assignedBarangay ?? null;
                    @endphp

                    @if (! $assignedBarangay)
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4 mb-4">
                            <p class="text-text-light/80 dark:text-text-dark/80">No barangay assigned yet. Resident access is unavailable until assignment.</p>
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row gap-3 mb-4">
                        <div class="relative flex-grow">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                            <input id="residentSearch" class="w-full h-10 pl-10 pr-4 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-text-light dark:text-text-dark placeholder:text-text-light/50 dark:placeholder:text-text-dark/50" placeholder="Search resident profile by name, contact, or address..." type="text"/>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-border-light dark:border-border-dark rounded-lg">
                        <table class="w-full" id="residentTable">
                            <thead>
                                <tr class="border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/60">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Resident</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Contact</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Household</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Location</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Health Status Summary</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($residents as $resident)
                                    @php
                                        $residentUser = $resident->user;
                                        $householdValue = trim((string) ($residentUser?->household_no ?? ''));
                                        $barangayValue = trim((string) ($resident->barangay ?? $residentUser?->barangay ?? ''));
                                        $municipalityValue = trim((string) ($residentUser?->municipality ?? ''));
                                        $provinceValue = trim((string) ($residentUser?->province ?? ''));

                                        $householdParts = array_values(array_filter([
                                            $householdValue !== '' ? $householdValue : null,
                                            $barangayValue !== '' ? $barangayValue : null,
                                            $municipalityValue !== '' ? $municipalityValue : null,
                                            $provinceValue !== '' ? $provinceValue : null,
                                        ]));
                                        $address = !empty($householdParts) ? implode(', ', $householdParts) : 'Unspecified household';

                                        $verificationAddress = trim((string) ($resident->address ?? ''));
                                        $mapLat = null;
                                        $mapLng = null;

                                        if (preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $verificationAddress, $matches)) {
                                            $mapLat = (float) $matches[1];
                                            $mapLng = (float) $matches[2];
                                        }

                                        $hasMapLocation = is_numeric($mapLat) && is_numeric($mapLng);
                                        $age = $resident->age;
                                        $healthTag = 'General monitoring';

                                        if (is_numeric($age) && (int) $age >= 60) {
                                            $healthTag = 'Senior citizen';
                                        } elseif (is_numeric($age) && (int) $age <= 5) {
                                            $healthTag = 'Early childhood';
                                        } elseif (is_numeric($age) && (int) $age < 18) {
                                            $healthTag = 'Adolescent';
                                        }
                                    @endphp
                                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-background-light/50 dark:hover:bg-background-dark/50 resident-row"
                                        data-search="{{ strtolower(($resident->full_name ?? '') . ' ' . ($resident->contact ?? '') . ' ' . $address) }}">
                                        <td class="px-4 py-3 text-sm">
                                            <p class="font-semibold text-text-light dark:text-text-dark">{{ $resident->full_name ?? 'N/A' }}</p>
                                            <p class="text-xs text-text-light/70 dark:text-text-dark/70">Age: {{ $resident->age ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-text-light/80 dark:text-text-dark/80">{{ $resident->contact ?? 'No contact' }}</td>
                                        <td class="px-4 py-3 text-sm text-text-light/80 dark:text-text-dark/80">{{ $address }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($hasMapLocation)
                                                <button
                                                    type="button"
                                                    class="view-location-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-primary/10 text-primary border border-primary/30 text-xs font-bold hover:bg-primary hover:text-white transition"
                                                    data-lat="{{ $mapLat }}"
                                                    data-lng="{{ $mapLng }}"
                                                    data-name="{{ $resident->full_name ?? 'Resident' }}"
                                                    data-label="{{ $address }}"
                                                >
                                                    <span class="material-symbols-outlined text-sm">location_on</span>
                                                    View Location
                                                </button>
                                            @else
                                                <span class="text-xs text-text-light/60 dark:text-text-dark/60">No location set</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">{{ $healthTag }}</span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent-orange/10 text-accent-orange">No clinical diagnosis encoded</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center text-sm text-text-light/70 dark:text-text-dark/70">
                                            No residents found for your assigned barangay.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<div id="residentLocationModal" class="hidden fixed inset-0 z-40 bg-black/50 p-4 md:p-8">
    <div class="mx-auto w-full max-w-3xl bg-card-light dark:bg-card-dark rounded-xl border border-border-light dark:border-border-dark overflow-hidden">
        <div class="px-4 py-3 border-b border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <h3 id="residentLocationTitle" class="font-bold">Resident Location</h3>
                <p id="residentLocationLabel" class="text-xs text-text-light/70 dark:text-text-dark/70"></p>
            </div>
            <button id="closeResidentLocationModal" type="button" class="inline-flex items-center justify-center size-8 rounded-md hover:bg-background-light dark:hover:bg-background-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div id="residentLocationMap" class="h-[420px]"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });
    
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    const residentSearch = document.getElementById('residentSearch');
    const residentRows = Array.from(document.querySelectorAll('.resident-row'));
    const residentLocationModal = document.getElementById('residentLocationModal');
    const residentLocationTitle = document.getElementById('residentLocationTitle');
    const residentLocationLabel = document.getElementById('residentLocationLabel');
    const closeResidentLocationModal = document.getElementById('closeResidentLocationModal');
    const openResidentsMapBtn = document.getElementById('openResidentsMapBtn');
    const viewLocationButtons = Array.from(document.querySelectorAll('.view-location-btn'));

    if (openResidentsMapBtn && viewLocationButtons.length === 0) {
        openResidentsMapBtn.disabled = true;
        openResidentsMapBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    let residentMap;
    let residentMarkersLayer;

    if (residentSearch) {
        residentSearch.addEventListener('input', (event) => {
            const term = (event.target.value || '').toLowerCase().trim();

            residentRows.forEach((row) => {
                const haystack = row.dataset.search || '';
                row.style.display = haystack.includes(term) ? '' : 'none';
            });
        });
    }

    function ensureResidentMap() {
        if (residentMap) {
            return;
        }

        residentMap = L.map('residentLocationMap').setView([14.5995, 120.9842], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(residentMap);
        residentMarkersLayer = L.layerGroup().addTo(residentMap);
    }

    function openResidentMap(lat, lng, name, label) {
        if (!residentLocationModal) {
            return;
        }

        residentLocationModal.classList.remove('hidden');
        residentLocationTitle.textContent = `${name} Location`;
        residentLocationLabel.textContent = label || '';

        setTimeout(() => {
            ensureResidentMap();
            residentMarkersLayer?.clearLayers();

            const marker = L.marker([lat, lng]).addTo(residentMarkersLayer);
            marker.bindPopup(`<strong>${name}</strong><br>${label || ''}`);

            residentMap.setView([lat, lng], 16);
            residentMap.invalidateSize();
        }, 120);
    }

    function openAllResidentsMap() {
        const points = viewLocationButtons.map((button) => {
            const lat = parseFloat(button.getAttribute('data-lat') || '');
            const lng = parseFloat(button.getAttribute('data-lng') || '');
            const name = button.getAttribute('data-name') || 'Resident';
            const label = button.getAttribute('data-label') || '';

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return null;
            }

            return { lat, lng, name, label };
        }).filter(Boolean);

        if (!points.length || !residentLocationModal) {
            return;
        }

        residentLocationModal.classList.remove('hidden');
        residentLocationTitle.textContent = 'Residents Map';
        residentLocationLabel.textContent = `${points.length} mapped resident${points.length > 1 ? 's' : ''}`;

        setTimeout(() => {
            ensureResidentMap();
            residentMarkersLayer?.clearLayers();

            const bounds = L.latLngBounds([]);

            points.forEach((point) => {
                const marker = L.marker([point.lat, point.lng]).addTo(residentMarkersLayer);
                marker.bindPopup(`<strong>${point.name}</strong><br>${point.label || ''}`);
                bounds.extend([point.lat, point.lng]);
            });

            if (bounds.isValid()) {
                residentMap.fitBounds(bounds.pad(0.2));
            }

            residentMap.invalidateSize();
        }, 120);
    }

    closeResidentLocationModal?.addEventListener('click', () => {
        residentLocationModal?.classList.add('hidden');
    });

    residentLocationModal?.addEventListener('click', (event) => {
        if (event.target === residentLocationModal) {
            residentLocationModal.classList.add('hidden');
        }
    });

    openResidentsMapBtn?.addEventListener('click', openAllResidentsMap);

    viewLocationButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const lat = parseFloat(button.getAttribute('data-lat') || '');
            const lng = parseFloat(button.getAttribute('data-lng') || '');
            const name = button.getAttribute('data-name') || 'Resident';
            const label = button.getAttribute('data-label') || '';

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }

            openResidentMap(lat, lng, name, label);
        });
    });
</script>
</body>
</html>
