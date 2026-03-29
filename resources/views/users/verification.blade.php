<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Resident Verification</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>

<body class="bg-gray-100 min-h-screen">
    @php
        $sourceName = trim((string) old('full_name', $residentRequest->full_name ?? Auth::user()->full_name ?? Auth::user()->name));
        $nameParts = collect(preg_split('/\s+/', $sourceName ?: '', -1, PREG_SPLIT_NO_EMPTY));
        $defaultFirstName = old('first_name', $nameParts->first() ?? '');
        $defaultLastName = old('last_name', $nameParts->count() > 1 ? $nameParts->last() : '');
        $middleToken = $nameParts->count() > 2 ? (string) $nameParts->slice(1, -1)->first() : '';
        $defaultMiddleInitial = old('middle_initial', $middleToken ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $middleToken), 0, 1)) : '');
        $trackedBarangay = old('barangay', $residentRequest->barangay ?? '');

        $storedGenderRaw = old('gender_display', Auth::user()->gender);
        $storedGender = in_array($storedGenderRaw, ['Male', 'Female'], true) ? $storedGenderRaw : '';
    @endphp

    <div class="max-w-2xl mx-auto py-10 px-4">
        <div class="bg-white rounded-xl shadow p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-gray-900">Resident Verification</h1>
            <p class="text-sm text-gray-600 mt-2">Fill out your details and send request to BHW for approval.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.verification.submit') }}" class="mt-6 space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="first_name">First Name</label>
                        <input id="first_name" name="first_name" type="text" value="{{ $defaultFirstName }}" placeholder="Enter first name" required @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div class="sm:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" type="text" value="{{ $defaultLastName }}" placeholder="Enter last name" required @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div class="sm:col-span-2 sm:max-w-[92px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="middle_initial">M.I.</label>
                        <input id="middle_initial" name="middle_initial" type="text" maxlength="1" value="{{ $defaultMiddleInitial }}" placeholder="M" @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="birthdate_display">Birthdate</label>
                        <input id="birthdate_display" name="birthdate_display" type="date" value="{{ old('birthdate_display', optional(Auth::user()->date_of_birth)->format('Y-m-d')) }}" @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="gender_select">Gender</label>
                        <select id="gender_select" name="gender_display" @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select gender</option>
                            <option value="Male" @selected($storedGender === 'Male')>Male</option>
                            <option value="Female" @selected($storedGender === 'Female')>Female</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="contact">Contact Number</label>
                    <input id="contact" name="contact" type="text" value="{{ old('contact', $residentRequest->contact ?? Auth::user()->contact_number) }}" placeholder="+091234567891" @disabled($requestSent ?? false)
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="rounded-xl border border-blue-100 bg-gradient-to-b from-blue-50/70 to-white p-4">
                    <label class="block text-sm font-semibold tracking-wide text-blue-900 mb-3 text-center">Barangay</label>

                    <p class="mb-3 text-center text-xs text-gray-600">
                        Please tap <span class="font-semibold text-blue-700">Track</span> and save your barangay before sending your verification request.
                    </p>

                    <div class="flex justify-center">
                        <button
                            id="openTrackModalBtn"
                            type="button"
                            @disabled($requestSent ?? false)
                            class="inline-flex items-center gap-2 px-6 h-11 rounded-full bg-blue-600 text-white text-sm font-semibold shadow-sm hover:bg-blue-700 hover:shadow transition disabled:bg-gray-400 disabled:cursor-not-allowed @if($trackedBarangay) hidden @endif"
                        >
                            <span class="material-symbols-outlined text-[18px]">my_location</span>
                            Track
                        </button>
                    </div>

                    <div id="savedBarangayText" class="mt-2 flex justify-center @if(!$trackedBarangay) hidden @endif">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold px-4 py-1.5 border border-blue-200">
                            <span class="material-symbols-outlined text-[16px]">place</span>
                            <span id="savedBarangayTextValue">{{ $trackedBarangay }}</span>
                        </span>
                    </div>

                    <input id="barangay" name="barangay" type="hidden" value="{{ $trackedBarangay }}" required />
                    <input id="address" name="address" type="hidden" value="{{ old('address') }}" />
                    <input id="full_name" name="full_name" type="hidden" value="{{ old('full_name', $sourceName) }}" required />
                </div>

                <div class="pt-4 mt-2 border-t border-gray-200">
                    @if ($requestSent ?? false)
                        <button type="button" disabled class="w-full bg-gray-400 text-white font-semibold py-2.5 rounded-lg cursor-not-allowed">
                            Request Sent
                        </button>
                    @else
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg">
                            Send Request
                        </button>
                    @endif
                </div>
            </form>

            @if ($requestSent ?? false)
                <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                    <a href="{{ route('users.main') }}" class="text-blue-600 hover:underline text-sm font-medium">Go Home</a>
                </div>
            @endif
        </div>
    </div>

    <div id="trackModal" class="hidden fixed inset-0 z-50 bg-black/50 p-4 flex items-center justify-center">
        <div class="w-full max-w-4xl bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Track Barangay</h2>
                    <p class="text-xs text-gray-600">Pick location on map, then save your barangay.</p>
                </div>
                <button id="closeTrackModalBtn" type="button" class="h-8 w-8 inline-flex items-center justify-center rounded-md hover:bg-gray-100">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3">
                <div class="md:col-span-1 p-4 border-b md:border-b-0 md:border-r border-gray-200 space-y-3 bg-gray-50">
                    <button id="useCurrentPositionBtn" type="button" @disabled($requestSent ?? false)
                        class="w-full h-10 rounded-lg bg-blue-600 text-white text-sm font-semibold inline-flex items-center justify-center gap-2 hover:bg-blue-700 disabled:bg-gray-400 disabled:text-gray-200 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-[18px]">my_location</span>
                        Use Current Position
                    </button>
                </div>
                <div class="md:col-span-2">
                    <div class="relative h-[420px]">
                        <div id="locationMap" class="h-[420px]"></div>
                        <div id="mapLoadingOverlay" class="hidden absolute inset-0 bg-black/35 backdrop-blur-[1px] flex items-center justify-center">
                            <div class="rounded-lg bg-white/95 px-4 py-3 border border-gray-200 shadow flex items-center gap-3">
                                <span class="inline-block h-5 w-5 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></span>
                                <p id="mapLoadingText" class="text-sm font-medium text-gray-700">Getting current location...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-end gap-2 bg-white">
                <button id="cancelTrackModalBtn" type="button" class="h-10 px-4 rounded-lg border border-gray-300 text-sm font-semibold">Cancel</button>
                <button id="saveTrackModalBtn" type="button" @disabled($requestSent ?? false)
                    class="h-10 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    Save Location
                </button>
            </div>
        </div>
    </div>

    <div id="noticeModal" class="hidden fixed inset-0 z-[60] bg-black/50 p-4">
        <div class="mx-auto mt-24 w-full max-w-sm rounded-xl bg-white border border-gray-200 shadow-xl">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 id="noticeTitle" class="text-base font-bold text-gray-900">Message</h3>
            </div>
            <div class="px-4 py-4">
                <p id="noticeMessage" class="text-sm text-gray-700"></p>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end">
                <button id="noticeOkBtn" type="button" class="h-9 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">OK</button>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="hidden fixed inset-0 z-[60] bg-black/50 p-4 flex items-center justify-center">
        <div class="w-full max-w-sm rounded-2xl bg-white border border-blue-100 shadow-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">verified</span>
                <h3 id="confirmTitle" class="text-base font-bold text-gray-900">Confirm</h3>
            </div>
            <div class="px-5 py-5">
                <p id="confirmMessage" class="text-sm text-gray-700 leading-relaxed"></p>
            </div>
            <div class="px-5 py-4 border-t border-blue-100 bg-white flex justify-end gap-2">
                <button id="confirmCancelBtn" type="button" class="h-10 px-4 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50">Cancel</button>
                <button id="confirmOkBtn" type="button" class="h-10 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-sm">Save</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const requestSent = @json((bool) ($requestSent ?? false));
        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const middleInitialInput = document.getElementById('middle_initial');
        const fullNameInput = document.getElementById('full_name');

        const genderSelect = document.getElementById('gender_select');

        const openTrackModalBtn = document.getElementById('openTrackModalBtn');
        const trackModal = document.getElementById('trackModal');
        const closeTrackModalBtn = document.getElementById('closeTrackModalBtn');
        const cancelTrackModalBtn = document.getElementById('cancelTrackModalBtn');
        const saveTrackModalBtn = document.getElementById('saveTrackModalBtn');
        const useCurrentPositionBtn = document.getElementById('useCurrentPositionBtn');
        const barangayInput = document.getElementById('barangay');
        const savedBarangayText = document.getElementById('savedBarangayText');
        const savedBarangayTextValue = document.getElementById('savedBarangayTextValue');
        const addressInput = document.getElementById('address');
        const noticeModal = document.getElementById('noticeModal');
        const noticeTitle = document.getElementById('noticeTitle');
        const noticeMessage = document.getElementById('noticeMessage');
        const noticeOkBtn = document.getElementById('noticeOkBtn');
        const confirmModal = document.getElementById('confirmModal');
        const confirmTitle = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        const confirmOkBtn = document.getElementById('confirmOkBtn');
        const mapLoadingOverlay = document.getElementById('mapLoadingOverlay');
        const mapLoadingText = document.getElementById('mapLoadingText');

        let map;
        let marker;
        let selectedLat = null;
        let selectedLng = null;
        let detectedBarangay = '';
        let confirmResolver = null;
        let hasUsedCurrentPosition = false;

        function setMapLoading(isLoading, text = 'Getting current location...') {
            if (!mapLoadingOverlay) {
                return;
            }

            if (mapLoadingText) {
                mapLoadingText.textContent = text;
            }

            mapLoadingOverlay.classList.toggle('hidden', !isLoading);
        }

        function showNotice(title, message) {
            if (!noticeModal) {
                return;
            }

            if (noticeTitle) {
                noticeTitle.textContent = title;
            }

            if (noticeMessage) {
                noticeMessage.textContent = message;
            }

            noticeModal.classList.remove('hidden');
        }

        function closeNotice() {
            noticeModal?.classList.add('hidden');
        }

        function showConfirm(title, message) {
            return new Promise((resolve) => {
                if (!confirmModal) {
                    resolve(false);
                    return;
                }

                if (confirmTitle) {
                    confirmTitle.textContent = title;
                }

                if (confirmMessage) {
                    confirmMessage.textContent = message;
                }

                confirmResolver = resolve;
                confirmModal.classList.remove('hidden');
            });
        }

        function resolveConfirm(value) {
            confirmModal?.classList.add('hidden');
            if (confirmResolver) {
                confirmResolver(value);
                confirmResolver = null;
            }
        }

        function updateHiddenFullName() {
            if (!fullNameInput) {
                return;
            }

            const firstName = (firstNameInput?.value || '').trim();
            const lastName = (lastNameInput?.value || '').trim();
            const middleInitialRaw = (middleInitialInput?.value || '').trim();
            const middleInitial = middleInitialRaw ? `${middleInitialRaw.charAt(0).toUpperCase()}.` : '';
            fullNameInput.value = [firstName, middleInitial, lastName].filter(Boolean).join(' ').trim();
        }

        function openTrackModal() {
            if (requestSent || !trackModal) {
                return;
            }

            trackModal.classList.remove('hidden');
            hasUsedCurrentPosition = false;
            if (saveTrackModalBtn) {
                saveTrackModalBtn.disabled = true;
            }

            setTimeout(() => {
                const lat = Number.isFinite(selectedLat) ? selectedLat : 14.5995;
                const lng = Number.isFinite(selectedLng) ? selectedLng : 120.9842;
                detectedBarangay = (barangayInput?.value || '').trim();

                if (!map) {
                    map = L.map('locationMap').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(map);

                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

                    marker.on('dragend', () => {
                        const pos = marker.getLatLng();
                        selectedLat = pos.lat;
                        selectedLng = pos.lng;
                        reverseGeocodeBarangay(selectedLat, selectedLng);
                    });

                    map.on('click', (event) => {
                        selectedLat = event.latlng.lat;
                        selectedLng = event.latlng.lng;
                        marker.setLatLng(event.latlng);
                        reverseGeocodeBarangay(selectedLat, selectedLng);
                    });
                } else {
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                    map.invalidateSize();
                }
            }, 120);
        }

        function closeTrackModal() {
            trackModal?.classList.add('hidden');
        }

        async function reverseGeocodeBarangay(lat, lng) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1`);
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const address = data?.address || {};
                const barangay = address.suburb || address.village || address.neighbourhood || address.quarter || address.hamlet || '';

                if (barangay) {
                    detectedBarangay = barangay;
                }
            } catch (error) {
            }
        }

        openTrackModalBtn?.addEventListener('click', openTrackModal);
        closeTrackModalBtn?.addEventListener('click', closeTrackModal);
        cancelTrackModalBtn?.addEventListener('click', closeTrackModal);
        noticeOkBtn?.addEventListener('click', closeNotice);
        confirmCancelBtn?.addEventListener('click', () => resolveConfirm(false));
        confirmOkBtn?.addEventListener('click', () => resolveConfirm(true));

        useCurrentPositionBtn?.addEventListener('click', () => {
            if (requestSent || !navigator.geolocation) {
                return;
            }

            setMapLoading(true, 'Getting current location...');
            useCurrentPositionBtn.disabled = true;

            navigator.geolocation.getCurrentPosition(async (position) => {
                selectedLat = position.coords.latitude;
                selectedLng = position.coords.longitude;
                hasUsedCurrentPosition = true;

                if (map && marker) {
                    map.setView([selectedLat, selectedLng], 16);
                    marker.setLatLng([selectedLat, selectedLng]);
                }

                await reverseGeocodeBarangay(selectedLat, selectedLng);
                setMapLoading(false);
                useCurrentPositionBtn.disabled = requestSent;
                if (saveTrackModalBtn) {
                    saveTrackModalBtn.disabled = requestSent ? true : false;
                }
            }, () => {
                setMapLoading(false, 'Unable to get current location.');
                useCurrentPositionBtn.disabled = requestSent;
                showNotice('Location Error', 'Unable to get current location. Please try again.');
            });
        });

        saveTrackModalBtn?.addEventListener('click', async () => {
            if (requestSent) {
                return;
            }

            if (!hasUsedCurrentPosition) {
                showNotice('Current Location Required', 'Please click Use Current Position first before saving.');
                return;
            }

            const shouldSave = await showConfirm('Confirm Save', 'Confirm saving this barangay location?');
            if (!shouldSave) {
                return;
            }

            const barangayValue = detectedBarangay.trim();
            if (!barangayValue) {
                showNotice('Missing Barangay', 'Please click Use Current Position to detect barangay.');
                return;
            }

            if (barangayInput) {
                barangayInput.value = barangayValue;
            }

            if (savedBarangayTextValue) {
                savedBarangayTextValue.textContent = barangayValue;
            }

            if (savedBarangayText) {
                savedBarangayText.classList.remove('hidden');
            }

            if (openTrackModalBtn) {
                openTrackModalBtn.classList.add('hidden');
            }

            if (addressInput && Number.isFinite(selectedLat) && Number.isFinite(selectedLng)) {
                addressInput.value = `${selectedLat.toFixed(7)}, ${selectedLng.toFixed(7)}`;
            }

            closeTrackModal();
        });

        firstNameInput?.addEventListener('input', updateHiddenFullName);
        lastNameInput?.addEventListener('input', updateHiddenFullName);
        middleInitialInput?.addEventListener('input', () => {
            if (middleInitialInput?.value) {
                middleInitialInput.value = middleInitialInput.value.replace(/[^A-Za-z]/g, '').slice(0, 1).toUpperCase();
            }
            updateHiddenFullName();
        });

        updateHiddenFullName();
    </script>
</body>

</html>
