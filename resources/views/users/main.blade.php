<!DOCTYPE html>
<html class="light" lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    @include('partials.submit-guard')
    @include('partials.users-skeleton-styles')
    @include('partials.users-swr')
    <title>Bantay Kalusugan - Resident Portal</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24;
        }

        .dropdown-container {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            width: 190px;
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-6px);
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
            overflow: hidden;
        }

        .dark .dropdown-menu {
            background-color: #1f2937;
        }

        .dropdown-container:focus-within .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            width: 100%;
            text-decoration: none;
            color: #374151;
            transition: background-color 0.2s;
        }

        .dark .dropdown-item {
            color: #d1d5db;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: #f3f4f6;
            outline: none;
        }

        .dark .dropdown-item:hover,
        .dark .dropdown-item:focus {
            background-color: #374151;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
        }

        .dark .dropdown-divider {
            background-color: #4b5563;
        }

        #usersSidebar {
            transition: width 0.25s ease;
        }

        @media (min-width: 1024px) {
            #usersSidebar.is-collapsed {
                width: 5rem;
            }

            #usersSidebar.is-collapsed .sidebar-label,
            #usersSidebar.is-collapsed .sidebar-brand-text {
                display: none;
            }

            #usersSidebar.is-collapsed .sidebar-link,
            #usersSidebar.is-collapsed .sidebar-logout {
                justify-content: center;
            }

            #usersSidebar.is-collapsed .sidebar-header-inner {
                justify-content: center;
            }
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#19e66b",
                        "background-light": "#f6f8f7",
                        "background-dark": "#112117",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
    @php
        $user = Auth::user();
        $displayName = $user->full_name ?: $user->name;
        $emailLocal = strstr($user->email, '@', true) ?: $user->email;
        $initial = strtoupper(substr($displayName ?: $emailLocal, 0, 1));
        $residentId = 'RES-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
        $profilePicturePath = $user->profile_picture_path;
        $status = $verificationStatus ?? 'unverified';
        $statusLabel = match ($status) {
            'accepted' => 'Registered',
            'pending' => 'Registration Pending',
            'rejected' => 'Not Registered',
            default => 'Not Registered',
        };
        $statusIcon = match ($status) {
            'accepted' => 'check_circle',
            'pending' => 'schedule',
            'rejected' => 'cancel',
            default => 'cancel',
        };
        $statusClass = match ($status) {
            'accepted' => 'text-green-700 dark:text-green-300',
            'pending' => 'text-yellow-700 dark:text-yellow-300',
            'rejected' => 'text-red-700 dark:text-red-300',
            default => 'text-red-700 dark:text-red-300',
        };

        $computedAge = $user->date_of_birth ? \Illuminate\Support\Carbon::parse($user->date_of_birth)->age : null;
        $displayAge = $computedAge ?? 'N/A';

        $municipality = filled($user->municipality) ? trim((string) $user->municipality) : null;
        $province = filled($user->province) ? trim((string) $user->province) : null;

        $hasDetailedAddress = filled($user->household_no) || filled($municipality) || filled($province);

        if ($hasDetailedAddress) {
            $addressSegments = array_values(array_filter([
                filled($user->household_no) ? $user->household_no : null,
                $user->barangay ?: null,
                $municipality,
                $province,
            ]));
            $displayAddress = ! empty($addressSegments) ? implode(', ', $addressSegments) : 'N/A';
        } else {
            $displayAddress = $user->barangay ?: 'N/A';
        }

        $calendarBaseDate = now();

        $calendarMonthStart = $calendarBaseDate->copy()->startOfMonth();
        $calendarDaysInMonth = $calendarBaseDate->daysInMonth;
        $calendarStartOffset = $calendarMonthStart->dayOfWeek;
        $calendarSelectedDay = $calendarBaseDate->day;
        $calendarCells = array_merge(
            array_fill(0, $calendarStartOffset, null),
            range(1, $calendarDaysInMonth)
        );
        $calendarTrailingCells = (7 - (count($calendarCells) % 7)) % 7;

        if ($calendarTrailingCells > 0) {
            $calendarCells = array_merge($calendarCells, array_fill(0, $calendarTrailingCells, null));
        }

        $calendarWeeks = array_chunk($calendarCells, 7);

        $isDashboard = request()->routeIs('users.main');
        $isAnnouncements = request()->routeIs('users.announcements');
        $isProfile = request()->routeIs('users.profile');
        $isNotifications = request()->routeIs('users.notifications');
        $isMedicalRecords = request()->routeIs('users.medical-records');

        $weatherBarangay = filled($residentRequest?->barangay)
            ? trim((string) $residentRequest->barangay)
            : (filled($user->barangay) ? trim((string) $user->barangay) : null);
        $weatherPurok = filled($residentRequest?->purok?->name)
            ? trim((string) $residentRequest->purok->name)
            : $user->householdPurokLabel();
        $weatherPlace = collect([
            $weatherPurok,
            $weatherBarangay,
            $municipality ?: 'Victoria',
            $province ?: 'Oriental Mindoro',
        ])->filter()->implode(', ');

        // Victoria, Oriental Mindoro (municipality center) — used when no verification pin/boundary exists.
        $weatherLat = 13.1733849;
        $weatherLon = 121.2787746;

        $verificationAddress = trim((string) ($residentRequest?->address ?? ''));
        if ($verificationAddress !== '' && preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $verificationAddress, $weatherCoordMatch)) {
            $weatherLat = (float) $weatherCoordMatch[1];
            $weatherLon = (float) $weatherCoordMatch[2];
        } else {
            $purokCentroid = \App\Support\GeoBoundary::centroid($residentRequest?->purok?->boundary_geojson);
            $barangayModel = $weatherBarangay
                ? \App\Models\Barangay::query()->where('name', $weatherBarangay)->first()
                : null;
            $barangayCentroid = \App\Support\GeoBoundary::centroid($barangayModel?->boundary_geojson);

            if ($purokCentroid) {
                $weatherLat = $purokCentroid['lat'];
                $weatherLon = $purokCentroid['lng'];
            } elseif ($barangayCentroid) {
                $weatherLat = $barangayCentroid['lat'];
                $weatherLon = $barangayCentroid['lng'];
            }
        }

        $consultationCalendarItems = collect($upcomingConsultations ?? [])
            ->map(static function ($consultation) {
                return [
                    'date' => $consultation->scheduled_at?->format('Y-m-d'),
                    'checkup' => $consultation->medical_checkup ?: 'General Consultation',
                    'time' => $consultation->scheduled_at?->format('h:i A'),
                    'note' => $consultation->note,
                    'type' => 'consultation',
                ];
            })
            ->merge(collect($upcomingImmunizations ?? [])->map(static function ($event) {
                return [
                    'date' => $event->event_date?->format('Y-m-d'),
                    'checkup' => $event->title ?: 'Immunization consultation',
                    'time' => null,
                    'note' => $event->description,
                    'type' => 'immunization',
                ];
            }))
            ->values()
            ->all();

        $registrationBanner = match ($status) {
            'rejected' => [
                'tone' => 'red',
                'title' => 'Registration not approved',
                'message' => 'Your previous request was rejected. Update your details and resubmit for review.',
                'action' => 'Resubmit registration',
                'route' => 'users.verification',
            ],
            'pending' => [
                'tone' => 'amber',
                'title' => 'Registration pending',
                'message' => 'Your account registration is still under barangay review. Please wait for approval.',
                'action' => null,
                'route' => null,
            ],
            'accepted' => null,
            default => [
                'tone' => 'amber',
                'title' => 'Complete your registration',
                'message' => 'Your account is not registered yet. Submit your verification request to continue.',
                'action' => 'Submit registration',
                'route' => 'users.verification',
            ],
        };
    @endphp

    @if (session('profile_update_status') || session('profile_update_error'))
        @php
            $isUpdateError = (bool) session('profile_update_error');
            $toastMessage = session('profile_update_error') ?: session('profile_update_status');
        @endphp
        <div id="profileUpdateToast" class="fixed top-4 right-4 z-50 w-[min(92vw,22rem)] rounded-xl border px-4 py-3 text-sm font-semibold shadow-lg transition-all duration-300 {{ $isUpdateError ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200' : 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200' }}" role="status" aria-live="polite">
            {{ $toastMessage }}
        </div>
    @endif

    <div class="relative min-h-screen w-full overflow-x-hidden">
        @include('sidebars.users')

        <!-- Mobile fixed buttons removed: message and notifications will remain beside profile -->

        <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            @php
                $carouselEvents = $events ?? collect();
                $carouselAnnouncements = $announcements ?? collect();
                $totalCarouselSlides = $carouselEvents->count() + $carouselAnnouncements->count();
            @endphp

            <div class="w-full max-w-none grid grid-cols-1 lg:grid-cols-12 gap-2 md:gap-2 lg:gap-2">
                <section class="lg:col-span-12 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                                <a href="{{ route('users.profile') }}" class="inline-flex rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/70 shrink-0" aria-label="Go to profile">
                                    @if ($profilePicturePath)
                                        <img
                                            src="{{ route('profile.picture', ['user' => $user->id, 'v' => optional($user->updated_at)->timestamp]) }}"
                                            alt="Profile picture"
                                            class="size-10 rounded-full object-cover border border-[#d0e7d9] dark:border-gray-700"
                                        />
                                    @else
                                        <div class="size-10 rounded-full bg-primary/15 text-primary flex items-center justify-center text-sm font-bold border border-[#d0e7d9] dark:border-gray-700">{{ $initial }}</div>
                                    @endif
                                </a>
                                <a href="{{ route('users.profile') }}" class="flex flex-col justify-center text-left">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $statusClass }}">
                                            <span class="material-symbols-outlined text-sm">{{ $statusIcon }}</span>
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    <p class="text-lg font-bold text-[#0e1b13] dark:text-white -mt-1">{{ $displayName }}</p>
                                </a>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('users.profile.bhw-chat') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#d0e7d9] bg-white text-[#1f7f4e] shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" aria-label="Open BHW chat">
                                    <span class="material-symbols-outlined text-[18px]">message</span>
                                </a>
                                <a href="{{ route('users.notifications') }}" class="relative inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#d0e7f0] bg-white text-[#1f7f4e] shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" aria-label="Notifications">
                                    <span class="material-symbols-outlined text-[18px] {{ $isNotifications ? 'text-primary' : '' }}">notifications</span>
                                    @if (($unreadInboxCount ?? 0) > 0)
                                        <span class="absolute -top-1 -right-1 inline-flex min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-4 text-white">
                                            {{ ($unreadInboxCount ?? 0) > 99 ? '99+' : $unreadInboxCount }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        </div>

                </section>

                <section class="lg:col-span-12 space-y-4 md:space-y-5">
                    @if ($registrationBanner)
                        @php
                            $bannerTone = $registrationBanner['tone'];
                            $bannerClasses = $bannerTone === 'red'
                                ? 'border-red-200 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-900/30 dark:text-red-100'
                                : 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-100';
                            $buttonClasses = $bannerTone === 'red'
                                ? 'bg-red-600 hover:bg-red-700 text-white'
                                : 'bg-amber-600 hover:bg-amber-700 text-white';
                        @endphp
                        <div class="rounded-xl border p-4 md:p-5 {{ $bannerClasses }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold">{{ $registrationBanner['title'] }}</p>
                                    <p class="mt-1 text-sm opacity-90">{{ $registrationBanner['message'] }}</p>
                                </div>
                                @if (! empty($registrationBanner['action']) && ! empty($registrationBanner['route']))
                                    <a href="{{ route($registrationBanner['route']) }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-lg px-4 text-sm font-semibold {{ $buttonClasses }}">
                                        {{ $registrationBanner['action'] }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div
                        id="weatherCard"
                        class="bg-gradient-to-br from-[#e7f3ec] to-white dark:from-gray-800 dark:to-gray-800 rounded-xl p-4 md:p-5 border border-[#d0e7d9] dark:border-gray-700"
                        data-place="{{ $weatherPlace }}"
                        data-lat="{{ $weatherLat }}"
                        data-lon="{{ $weatherLon }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">Weather</p>
                                <h3 class="font-bold mt-1 text-[#0e1b13] dark:text-white">At your registered location</h3>
                                <p id="weatherClock" class="mt-1 text-sm font-semibold text-[#1f7f4e] dark:text-[#8cefb5]" aria-live="polite">—</p>
                            </div>
                            <span class="material-symbols-outlined text-[28px] text-[#1f7f4e] dark:text-[#8cefb5]" id="weatherIcon">partly_cloudy_day</span>
                        </div>

                        <div id="weatherLoading" class="mt-3" aria-hidden="true">
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="bk-skeleton bk-skeleton-block h-9 w-20"></div>
                                <div class="bk-skeleton bk-skeleton-line w-28"></div>
                            </div>
                            <div class="bk-skeleton bk-skeleton-line mt-3 w-2/3 max-w-xs"></div>
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-900/40 px-3 py-2">
                                    <div class="bk-skeleton bk-skeleton-line w-12 mb-2"></div>
                                    <div class="bk-skeleton bk-skeleton-line w-10 h-4"></div>
                                </div>
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-900/40 px-3 py-2">
                                    <div class="bk-skeleton bk-skeleton-line w-16 mb-2"></div>
                                    <div class="bk-skeleton bk-skeleton-line w-10 h-4"></div>
                                </div>
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-900/40 px-3 py-2">
                                    <div class="bk-skeleton bk-skeleton-line w-12 mb-2"></div>
                                    <div class="bk-skeleton bk-skeleton-line w-14 h-4"></div>
                                </div>
                            </div>
                        </div>

                        <div id="weatherContent" class="hidden mt-3">
                            <div class="flex flex-wrap items-end gap-3">
                                <p id="weatherTemp" class="text-3xl font-bold text-[#0e1b13] dark:text-white leading-none">—</p>
                                <p id="weatherCondition" class="text-sm font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">—</p>
                            </div>
                            <p id="weatherPlace" class="mt-2 text-sm text-[#376f50] dark:text-gray-300">—</p>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-xs text-[#4e976b] dark:text-gray-400">
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-900/40 px-3 py-2">
                                    <p class="uppercase tracking-wide">Time</p>
                                    <p id="weatherTime" class="mt-1 font-semibold text-[#0e1b13] dark:text-white tabular-nums">—</p>
                                </div>
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-900/40 px-3 py-2">
                                    <p class="uppercase tracking-wide">Humidity</p>
                                    <p id="weatherHumidity" class="mt-1 font-semibold text-[#0e1b13] dark:text-white">—</p>
                                </div>
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-900/40 px-3 py-2">
                                    <p class="uppercase tracking-wide">Wind</p>
                                    <p id="weatherWind" class="mt-1 font-semibold text-[#0e1b13] dark:text-white">—</p>
                                </div>
                            </div>
                        </div>

                        <div id="weatherError" class="hidden mt-3 text-sm text-red-700 dark:text-red-300"></div>
                    </div>

                    <script>
                        (function () {
                            const card = document.getElementById('weatherCard');
                            if (!card) {
                                return;
                            }

                            const placeLabel = card.dataset.place || 'Victoria, Oriental Mindoro';
                            const savedLat = Number.parseFloat(card.dataset.lat || '');
                            const savedLon = Number.parseFloat(card.dataset.lon || '');
                            const hasSavedCoords = Number.isFinite(savedLat) && Number.isFinite(savedLon);
                            const loadingEl = document.getElementById('weatherLoading');
                            const contentEl = document.getElementById('weatherContent');
                            const errorEl = document.getElementById('weatherError');
                            const iconEl = document.getElementById('weatherIcon');

                            const weatherCodeMap = {
                                0: { label: 'Clear sky', icon: 'sunny' },
                                1: { label: 'Mainly clear', icon: 'partly_cloudy_day' },
                                2: { label: 'Partly cloudy', icon: 'partly_cloudy_day' },
                                3: { label: 'Overcast', icon: 'cloud' },
                                45: { label: 'Foggy', icon: 'foggy' },
                                48: { label: 'Rime fog', icon: 'foggy' },
                                51: { label: 'Light drizzle', icon: 'rainy_light' },
                                53: { label: 'Drizzle', icon: 'rainy' },
                                55: { label: 'Heavy drizzle', icon: 'rainy' },
                                61: { label: 'Light rain', icon: 'rainy_light' },
                                63: { label: 'Rain', icon: 'rainy' },
                                65: { label: 'Heavy rain', icon: 'rainy_heavy' },
                                71: { label: 'Light snow', icon: 'weather_snowy' },
                                73: { label: 'Snow', icon: 'weather_snowy' },
                                75: { label: 'Heavy snow', icon: 'weather_snowy' },
                                80: { label: 'Rain showers', icon: 'rainy' },
                                81: { label: 'Rain showers', icon: 'rainy' },
                                82: { label: 'Violent showers', icon: 'rainy_heavy' },
                                95: { label: 'Thunderstorm', icon: 'thunderstorm' },
                                96: { label: 'Thunderstorm with hail', icon: 'thunderstorm' },
                                99: { label: 'Thunderstorm with hail', icon: 'thunderstorm' },
                            };

                            function setLoading() {
                                loadingEl?.classList.remove('hidden');
                                contentEl?.classList.add('hidden');
                                errorEl?.classList.add('hidden');
                            }

                            function setError(message) {
                                if (errorEl) {
                                    errorEl.textContent = message;
                                    errorEl.classList.remove('hidden');
                                }
                                loadingEl?.classList.add('hidden');
                                contentEl?.classList.add('hidden');
                            }

                            const clockEl = document.getElementById('weatherClock');
                            const timeEl = document.getElementById('weatherTime');
                            let weatherTimeZone = 'Asia/Manila';

                            function formatWeatherClock(date = new Date()) {
                                try {
                                    return new Intl.DateTimeFormat('en-PH', {
                                        timeZone: weatherTimeZone,
                                        weekday: 'short',
                                        month: 'short',
                                        day: 'numeric',
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        hour12: true,
                                    }).format(date);
                                } catch (error) {
                                    return date.toLocaleString('en-PH', {
                                        weekday: 'short',
                                        month: 'short',
                                        day: 'numeric',
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        hour12: true,
                                    });
                                }
                            }

                            function formatWeatherTime(date = new Date()) {
                                try {
                                    return new Intl.DateTimeFormat('en-PH', {
                                        timeZone: weatherTimeZone,
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        hour12: true,
                                    }).format(date);
                                } catch (error) {
                                    return date.toLocaleTimeString('en-PH', {
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        hour12: true,
                                    });
                                }
                            }

                            function tickWeatherClock() {
                                const now = new Date();
                                if (clockEl) {
                                    clockEl.textContent = formatWeatherClock(now);
                                }
                                if (timeEl && !contentEl?.classList.contains('hidden')) {
                                    timeEl.textContent = formatWeatherTime(now);
                                }
                            }

                            function renderWeather(payload) {
                                const code = Number(payload.weatherCode ?? 0);
                                const meta = weatherCodeMap[code] || { label: 'Weather update', icon: 'partly_cloudy_day' };

                                if (payload.timezone) {
                                    weatherTimeZone = payload.timezone;
                                }

                                document.getElementById('weatherTemp').textContent = `${Math.round(payload.temperature)}°C`;
                                document.getElementById('weatherCondition').textContent = meta.label;
                                document.getElementById('weatherPlace').textContent = payload.place;
                                document.getElementById('weatherHumidity').textContent = `${Math.round(payload.humidity)}%`;
                                document.getElementById('weatherWind').textContent = `${Math.round(payload.wind)} km/h`;
                                if (iconEl) {
                                    iconEl.textContent = meta.icon;
                                }

                                loadingEl?.classList.add('hidden');
                                errorEl?.classList.add('hidden');
                                contentEl?.classList.remove('hidden');
                                tickWeatherClock();
                            }

                            async function fetchWeather(lat, lon) {
                                const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&timezone=Asia%2FManila`;
                                const response = await fetch(url);
                                if (!response.ok) {
                                    throw new Error('Weather service unavailable.');
                                }
                                const data = await response.json();
                                const current = data.current || {};

                                renderWeather({
                                    temperature: current.temperature_2m ?? 0,
                                    humidity: current.relative_humidity_2m ?? 0,
                                    wind: current.wind_speed_10m ?? 0,
                                    weatherCode: current.weather_code ?? 0,
                                    place: placeLabel,
                                    timezone: data.timezone || 'Asia/Manila',
                                    observationTime: current.time || null,
                                });
                            }

                            async function start() {
                                setLoading();
                                tickWeatherClock();

                                try {
                                    if (!hasSavedCoords) {
                                        throw new Error('Registered location coordinates are missing.');
                                    }

                                    await fetchWeather(savedLat, savedLon);
                                } catch (error) {
                                    setError(error.message || 'Unable to load weather for your registered location.');
                                }
                            }

                            tickWeatherClock();
                            setInterval(tickWeatherClock, 30000);
                            start();
                        })();
                    </script>

                    <script>
                        let currentSlide = 0;
                        const totalSlides = {{ $totalCarouselSlides }};
                        let autoSlideTimer;

                        function startAutoSlide() {
                            if (totalSlides <= 1) {
                                return;
                            }

                            clearInterval(autoSlideTimer);
                            autoSlideTimer = setInterval(() => scrollCarousel(1), 4500);
                        }

                        function scrollCarousel(direction) {
                            const carousel = document.getElementById('eventsCarousel');
                            if (!carousel || totalSlides <= 0) {
                                return;
                            }

                            currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
                            updateCarousel();
                            startAutoSlide();
                        }

                        function goToSlide(index) {
                            if (totalSlides <= 0) {
                                return;
                            }

                            currentSlide = index;
                            updateCarousel();
                            startAutoSlide();
                        }

                        function updateCarousel() {
                            const carousel = document.getElementById('eventsCarousel');
                            if (!carousel) {
                                return;
                            }

                            carousel.style.transform = `translateX(-${currentSlide * 100}%)`;

                            document.querySelectorAll('.carousel-indicator').forEach((btn, index) => {
                                if (index === currentSlide) {
                                    btn.classList.remove('w-2', 'bg-primary/30');
                                    btn.classList.add('w-8', 'bg-primary');
                                } else {
                                    btn.classList.remove('w-8', 'bg-primary');
                                    btn.classList.add('w-2', 'bg-primary/30');
                                }
                            });
                        }

                        const carouselWrapper = document.getElementById('eventsCarouselWrapper');
                        // Initialize carousel state and auto-play
                        try {
                            updateCarousel();
                        } catch (e) {
                            // ignore if functions not available
                        }

                        if (carouselWrapper && totalSlides > 1) {
                            carouselWrapper.addEventListener('mouseenter', () => clearInterval(autoSlideTimer));
                            carouselWrapper.addEventListener('mouseleave', startAutoSlide);
                            startAutoSlide();
                        }
                    </script>
                </section>

                @if ($totalCarouselSlides > 0)
                <section class="lg:col-span-12">
                    <div class="w-full bg-white dark:bg-gray-800 rounded-xl p-4 md:p-5 border border-[#e7f3ec] dark:border-gray-700 shadow-sm">
                        <div class="flex items-center justify-between mb-4 gap-3">
                            <div>
                                <h3 class="text-lg font-bold tracking-tight">What's new</h3>
                                <p class="text-sm text-[#4e976b] dark:text-gray-400">Latest barangay announcements.</p>
                            </div>
                            <span class="hidden md:inline-flex items-center rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">
                                Live Updates
                            </span>
                        </div>

                        <div id="eventsCarouselWrapper" class="relative">
                            <div class="overflow-hidden">
                                <div class="flex transition-transform duration-300 ease-out" id="eventsCarousel">
                                    @foreach ($carouselEvents as $event)
                                    @php
                                        $eventImage = $event->image_url;

                                        if ($eventImage && !\Illuminate\Support\Str::startsWith($eventImage, ['http://', 'https://', '/'])) {
                                            $eventImage = \Illuminate\Support\Facades\Storage::url($eventImage);
                                        }

                                        $eventStartTime = null;
                                        $eventEndTime = null;

                                        if ($event->start_time) {
                                            try {
                                                $eventStartTime = \Illuminate\Support\Carbon::createFromFormat('H:i:s', $event->start_time)->format('h:i A');
                                            } catch (\Throwable $e) {
                                                $eventStartTime = \Illuminate\Support\Carbon::parse($event->start_time)->format('h:i A');
                                            }
                                        }

                                        if ($event->end_time) {
                                            try {
                                                $eventEndTime = \Illuminate\Support\Carbon::createFromFormat('H:i:s', $event->end_time)->format('h:i A');
                                            } catch (\Throwable $e) {
                                                $eventEndTime = \Illuminate\Support\Carbon::parse($event->end_time)->format('h:i A');
                                            }
                                        }
                                    @endphp
                                    <div class="flex-shrink-0 w-full cursor-pointer">
                                        <div class="overflow-hidden rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm h-full flex flex-col">
                                            @if ($eventImage)
                                                <div class="h-36 md:h-44 w-full overflow-hidden bg-[#f2fbf6] dark:bg-gray-700/40">
                                                    <img src="{{ $eventImage }}" alt="{{ $event->title }}" class="h-full w-full object-cover object-center" />
                                                </div>
                                            @else
                                                <div class="flex h-36 md:h-44 w-full items-center justify-center bg-[#f2fbf6] dark:bg-gray-700/40 text-[#4e976b] dark:text-gray-400">
                                                    <span class="material-symbols-outlined text-5xl">photo</span>
                                                </div>
                                            @endif

                                            <div class="p-4 md:p-5 flex flex-col flex-1">
                                                <div class="flex items-start justify-between mb-3">
                                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-primary/15 text-[#1f7f4e] dark:bg-primary/30 dark:text-[#8cefb5]">
                                                        {{ ucfirst($event->category) }}
                                                    </span>
                                                    <span class="text-2xl">📅</span>
                                                </div>

                                                <h4 class="font-bold text-lg mb-2 text-[#0e1b13] dark:text-white">{{ $event->title }}</h4>

                                                @if ($event->description)
                                                <p class="text-sm text-[#376f50] dark:text-gray-300 mb-3 line-clamp-2 flex-grow">{{ $event->description }}</p>
                                                @endif

                                                <div class="space-y-2 text-sm text-[#4e976b] dark:text-gray-400">
                                                    @if ($event->event_date)
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="flex items-center gap-2 text-xs text-[#4e976b] dark:text-gray-400">
                                                            <span class="material-symbols-outlined text-base">calendar_today</span>
                                                            <span>{{ $event->event_date->format('M d, Y') }}</span>
                                                        </div>

                                                        @if ($event->registration_limit !== null)
                                                            <div class="flex items-center gap-1.5 text-xs text-[#4e976b] dark:text-gray-400">
                                                                <span class="material-symbols-outlined text-sm">group</span>
                                                                <span>Limit: {{ $event->registration_limit }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @endif

                                                    @if ($event->start_time)
                                                    <div class="flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">schedule</span>
                                                        <span>{{ $eventStartTime ?? 'TBA' }}@if ($eventEndTime) - {{ $eventEndTime }}@endif</span>
                                                    </div>
                                                    @endif

                                                    @if ($event->location)
                                                    <div class="flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">location_on</span>
                                                        <span>{{ $event->location }}</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                    @foreach ($carouselAnnouncements as $announcement)
                                    @php
                                        $isAnnouncementExpired = $announcement->isExpired();
                                    @endphp
                                    <a href="{{ route('users.announcements.show', $announcement) }}" class="flex-shrink-0 w-full block cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 rounded-xl">
                                        <div class="overflow-hidden rounded-xl border {{ $isAnnouncementExpired ? 'border-gray-200 dark:border-gray-700' : 'border-[#d0e7d9] dark:border-gray-700' }} bg-white dark:bg-gray-800 shadow-sm h-full flex flex-col transition hover:border-primary/40 hover:shadow-md active:scale-[0.99]">
                                            <div class="relative flex h-36 md:h-44 w-full items-center justify-center {{ $isAnnouncementExpired ? 'bg-gray-100 dark:bg-gray-700/40 text-gray-400' : 'bg-[#f2fbf6] dark:bg-gray-700/40 text-[#4e976b] dark:text-gray-400' }}">
                                                <span class="material-symbols-outlined text-5xl">campaign</span>
                                                @if ($isAnnouncementExpired)
                                                    <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-gray-800/90 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white dark:bg-gray-900/90">
                                                        Expired
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="p-4 md:p-5 flex flex-col flex-1">
                                                <div class="flex items-start justify-between mb-3 gap-2">
                                                    <div class="flex flex-wrap items-center gap-2 min-w-0">
                                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $isAnnouncementExpired ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' : 'bg-primary/15 text-[#1f7f4e] dark:bg-primary/30 dark:text-[#8cefb5]' }}">Announcement</span>
                                                        @if ($isAnnouncementExpired)
                                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200">Expired</span>
                                                        @endif
                                                    </div>
                                                    <span class="inline-flex items-center gap-0.5 text-xs font-semibold {{ $isAnnouncementExpired ? 'text-gray-500' : 'text-[#1f7f4e]' }} shrink-0">
                                                        {{ $isAnnouncementExpired ? 'View' : 'Read' }}
                                                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                                                    </span>
                                                </div>

                                                <h4 class="font-bold text-lg mb-2 text-[#0e1b13] dark:text-white">{{ $announcement->title ?: 'Barangay Announcement' }}</h4>

                                                @if ($announcement->description)
                                                <p class="text-sm text-black dark:text-gray-200 mb-3 line-clamp-2 flex-grow">{{ $announcement->description }}</p>
                                                @endif

                                                <div class="space-y-2 text-sm text-[#4e976b] dark:text-gray-400">
                                                    <div class="flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">calendar_today</span>
                                                        <span>{{ $isAnnouncementExpired ? 'Expired' : 'Expires' }} {{ $announcement->expiresAt()?->format('M d, Y · h:i A') ?? $announcement->event_date?->format('M d, Y') ?? 'TBA' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">schedule</span>
                                                        <span>Posted {{ $announcement->created_at?->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>

                            @if ($totalCarouselSlides > 1)
                            <div class="mt-4 flex items-center justify-center gap-2" aria-label="Carousel navigation dots">
                                @for ($i = 0; $i < $totalCarouselSlides; $i++)
                                <button onclick="goToSlide({{ $i }})" class="carousel-indicator h-2 rounded-full transition-all duration-300 {{ $i === 0 ? 'bg-primary w-8' : 'bg-primary/30 w-2' }} dark:bg-opacity-70" aria-label="Go to slide {{ $i + 1 }}"></button>
                                @endfor
                            </div>
                            @endif
                        </div>
                    </div>
                </section>
                @endif

                <section class="lg:col-span-12 space-y-4 md:space-y-5 pt-6 md:pt-8 border-t border-[#d0e7d9] dark:border-gray-700">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-5 shadow-sm border border-[#e7f3ec] dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Upcoming Schedule</h3>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary/20 text-[#0e1b13] dark:text-white">This Month</span>
                        </div>
                        <div class="space-y-3">
                            @forelse (($upcomingSchedule ?? collect()) as $item)
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-[#f2fbf6] dark:bg-gray-700/50">
                                    <span class="material-symbols-outlined text-primary">{{ ($item['type'] ?? '') === 'immunization' ? 'vaccines' : 'event' }}</span>
                                    <div class="flex-1">
                                        <p class="font-semibold">{{ $item['title'] ?? 'Schedule' }}</p>
                                        <p class="text-sm text-[#4e976b] dark:text-gray-400">{{ $item['when_label'] ?? '—' }}</p>
                                        @if (! empty($item['note']))
                                            <p class="text-xs text-[#4e976b] dark:text-gray-400 mt-1">{{ $item['note'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 p-4 text-sm text-[#4e976b] dark:text-gray-400">
                                    No upcoming appointments yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-5 border border-[#e7f3ec] dark:border-gray-700 shadow-sm" data-calendar-card="true" data-initial-year="{{ $calendarBaseDate->year }}" data-initial-month="{{ $calendarBaseDate->month }}" data-selected-day="{{ $calendarSelectedDay }}">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <p class="text-sm font-semibold text-[#4e976b] dark:text-gray-400">Calendar</p>
                                <div class="flex items-center gap-2">
                                    <button id="calendarPrev" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-[#f2fbf6] text-[#1f7f4e] hover:bg-[#e7f3ec] dark:bg-gray-700/60 dark:text-gray-200 dark:hover:bg-gray-700" aria-label="Previous month">&lt;</button>
                                    <p id="calendarMonthLabel" class="text-sm font-bold text-[#0e1b13] dark:text-white min-w-[110px] text-center">{{ $calendarBaseDate->format('F Y') }}</p>
                                    <button id="calendarNext" type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-[#f2fbf6] text-[#1f7f4e] hover:bg-[#e7f3ec] dark:bg-gray-700/60 dark:text-gray-200 dark:hover:bg-gray-700" aria-label="Next month">&gt;</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-7 gap-2 text-center text-xs font-semibold text-[#4e976b] dark:text-gray-400 mb-2">
                                <span>Sun</span>
                                <span>Mon</span>
                                <span>Tue</span>
                                <span>Wed</span>
                                <span>Thu</span>
                                <span>Fri</span>
                                <span>Sat</span>
                            </div>

                            <div id="calendarGrid" class="space-y-2">
                                @foreach ($calendarWeeks as $week)
                                    <div class="grid grid-cols-7 gap-2">
                                        @foreach ($week as $day)
                                            @if ($day)
                                                <button
                                                    type="button"
                                                    class="h-10 rounded-lg text-sm font-semibold transition-colors {{ $day === $calendarSelectedDay ? 'bg-primary text-[#0e1b13]' : 'bg-[#f2fbf6] text-[#1f7f4e] hover:bg-[#e7f3ec] dark:bg-gray-700/50 dark:text-gray-200 dark:hover:bg-gray-700' }}"
                                                    aria-label="Day {{ $day }}"
                                                >
                                                    {{ $day }}
                                                </button>
                                            @else
                                                <span class="h-10"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-5 shadow-sm border border-[#e7f3ec] dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4 gap-3">
                            <div>
                                <h3 class="text-lg font-bold tracking-tight">Transaction History</h3>
                                <p class="text-xs text-[#4e976b] dark:text-gray-400 mt-0.5">Recent medicine records</p>
                            </div>
                            <a href="{{ route('users.transaction-records') }}" class="inline-flex items-center gap-0.5 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5] shrink-0 group">
                                <span class="group-hover:underline">View all</span>
                                <span class="material-symbols-outlined text-[16px] no-underline">chevron_right</span>
                            </a>
                        </div>
                        <div class="space-y-2.5">
                            @forelse (($transactionHistory ?? collect()) as $transaction)
                                @php
                                    $homeMeds = $transaction->medicationEntries();
                                @endphp
                                <a href="{{ route('users.transaction-records') }}" class="flex items-start gap-3 rounded-xl border border-[#e7f3ec] dark:border-gray-700 bg-[#fbfdfc] dark:bg-gray-800/80 p-3 transition active:scale-[0.99] hover:border-primary/40">
                                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                        <span class="material-symbols-outlined text-[20px]">medication</span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-[#0e1b13] dark:text-white leading-snug">{{ $transaction->recipientLabel() }}</p>
                                                <p class="text-[11px] text-[#4e976b] dark:text-gray-400 mt-0.5">
                                                    {{ $transaction->transaction_date?->format('M d, Y') ?: 'No date' }}
                                                    <span class="mx-1 text-[#d0e7d9] dark:text-gray-600">·</span>
                                                    {{ $transaction->familyRoleLabel() }}
                                                </p>
                                            </div>
                                            @if (filled($transaction->gender))
                                                <span class="inline-flex items-center rounded-lg bg-primary/15 px-2 py-0.5 text-[10px] font-semibold text-[#1f7f4e] shrink-0">{{ $transaction->gender }}</span>
                                            @endif
                                        </div>
                                        @if ($homeMeds !== [])
                                            <p class="mt-1.5 text-xs text-[#376f50] dark:text-gray-300 line-clamp-2">
                                                {{ collect($homeMeds)->map(fn ($med) => $med['label'].' · '.$med['pcs'].' pc'.($med['pcs'] === 1 ? '' : 's'))->implode(', ') }}
                                            </p>
                                        @else
                                            <p class="mt-1.5 text-xs text-[#4e976b] dark:text-gray-400">No medicines listed</p>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-xl border border-dashed border-[#d0e7d9] dark:border-gray-700 px-4 py-8 text-center">
                                    <span class="material-symbols-outlined text-3xl text-[#4e976b] dark:text-gray-400">medication</span>
                                    <p class="mt-2 text-sm text-[#4e976b] dark:text-gray-400">No medicine transactions yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-5 shadow-sm border border-[#e7f3ec] dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4 gap-3">
                            <div>
                                <h3 class="text-lg font-bold tracking-tight">Maintenance History</h3>
                                <p class="text-xs text-[#4e976b] dark:text-gray-400 mt-0.5">Recent monitoring records</p>
                            </div>
                            <a href="{{ route('users.maintenance-records') }}" class="inline-flex items-center gap-0.5 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5] shrink-0 group">
                                <span class="group-hover:underline">View all</span>
                                <span class="material-symbols-outlined text-[16px] no-underline">chevron_right</span>
                            </a>
                        </div>
                        <div class="space-y-2.5">
                            @forelse (($maintenanceHistory ?? collect()) as $monitoringCase)
                                @php
                                    $latestVisit = $monitoringCase->visits->first();
                                    $typeIcon = $monitoringCase->isPregnant()
                                        ? 'pregnant_woman'
                                        : ($monitoringCase->isSenior() ? 'elderly' : 'health_and_safety');
                                    $visitCount = (int) ($monitoringCase->visits_count ?? $monitoringCase->visits->count());
                                @endphp
                                <a href="{{ route('users.maintenance-records') }}" class="flex items-start gap-3 rounded-xl border border-[#e7f3ec] dark:border-gray-700 bg-[#fbfdfc] dark:bg-gray-800/80 p-3 transition active:scale-[0.99] hover:border-primary/40">
                                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">
                                        <span class="material-symbols-outlined text-[20px]">{{ $typeIcon }}</span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-[#0e1b13] dark:text-white leading-snug">{{ $monitoringCase->member_name ?: 'Household member' }}</p>
                                                <p class="text-[11px] text-[#4e976b] dark:text-gray-400 mt-0.5">
                                                    {{ $monitoringCase->typeLabel() }}
                                                    <span class="mx-1 text-[#d0e7d9] dark:text-gray-600">·</span>
                                                    {{ $visitCount }} visit{{ $visitCount === 1 ? '' : 's' }}
                                                </p>
                                            </div>
                                            <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[10px] font-semibold shrink-0 {{ $monitoringCase->is_active ? 'bg-primary/15 text-[#1f7f4e]' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ $monitoringCase->is_active ? 'Active' : 'Done' }}
                                            </span>
                                        </div>
                                        @if ($latestVisit)
                                            <p class="mt-1.5 text-xs text-[#376f50] dark:text-gray-300">
                                                {{ $monitoringCase->is_active ? 'Latest visit' : 'Last visit' }} {{ $latestVisit->visit_date?->format('M d, Y') ?: '—' }}
                                            </p>
                                        @else
                                            <p class="mt-1.5 text-xs text-[#4e976b] dark:text-gray-400">No visits recorded yet</p>
                                        @endif
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-xl border border-dashed border-[#d0e7d9] dark:border-gray-700 px-4 py-8 text-center">
                                    <span class="material-symbols-outlined text-3xl text-[#4e976b] dark:text-gray-400">health_and_safety</span>
                                    <p class="mt-2 text-sm text-[#4e976b] dark:text-gray-400">No maintenance records yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @include('partials.users-mobile-nav')
        @include('partials.users-chat-support-fab')

        </div>
    </div>

    <script>
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, "", window.location.href);
        };

        (function () {
            const profileUpdateToast = document.getElementById('profileUpdateToast');
            if (profileUpdateToast) {
                setTimeout(() => {
                    profileUpdateToast.classList.add('opacity-0', 'translate-y-1');
                }, 3000);

                setTimeout(() => {
                    profileUpdateToast.remove();
                }, 3400);
            }

            const sidebar = document.getElementById('usersSidebar');
            const content = document.getElementById('usersContent');
            const toggleButton = document.getElementById('usersSidebarToggle');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            const closeIcon = document.getElementById('closeIcon');
            const calendarCard = document.querySelector('[data-calendar-card="true"]');
            const calendarPrev = document.getElementById('calendarPrev');
            const calendarNext = document.getElementById('calendarNext');
            const calendarMonthLabel = document.getElementById('calendarMonthLabel');
            const calendarGrid = document.getElementById('calendarGrid');

            if (calendarCard && calendarPrev && calendarNext && calendarMonthLabel && calendarGrid) {
                let viewYear = parseInt(calendarCard.dataset.initialYear || '', 10);
                let viewMonth = parseInt(calendarCard.dataset.initialMonth || '', 10) - 1;
                const now = new Date();

                // Build consultation date map
                const consultations = @json($consultationCalendarItems);
                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

                const consultationMap = {};
                consultations.forEach(c => {
                    if (!consultationMap[c.date]) {
                        consultationMap[c.date] = [];
                    }
                    consultationMap[c.date].push(c);
                });

                function daysInMonth(year, monthIndex) {
                    return new Date(year, monthIndex + 1, 0).getDate();
                }

                function renderCalendar() {
                    const firstDay = new Date(viewYear, viewMonth, 1).getDay();
                    const monthDays = daysInMonth(viewYear, viewMonth);
                    const monthName = new Date(viewYear, viewMonth, 1).toLocaleString('en-US', { month: 'long' });

                    calendarMonthLabel.textContent = `${monthName} ${viewYear}`;
                    calendarGrid.innerHTML = '';

                    const cells = [];
                    for (let i = 0; i < firstDay; i += 1) {
                        cells.push(null);
                    }

                    for (let day = 1; day <= monthDays; day += 1) {
                        cells.push(day);
                    }

                    while (cells.length % 7 !== 0) {
                        cells.push(null);
                    }

                    for (let i = 0; i < cells.length; i += 7) {
                        const week = cells.slice(i, i + 7);
                        const row = document.createElement('div');
                        row.className = 'grid grid-cols-7 gap-2';

                        week.forEach((day) => {
                            if (day === null) {
                                const empty = document.createElement('span');
                                empty.className = 'h-10';
                                row.appendChild(empty);
                                return;
                            }

                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.setAttribute('aria-label', `Day ${day}`);

                            const isToday = day === now.getDate() && viewYear === now.getFullYear() && viewMonth === now.getMonth();
                            const isActive = isToday;

                            btn.className = `h-10 rounded-lg text-sm font-semibold transition-colors relative ${isActive
                                ? 'bg-primary text-[#0e1b13]'
                                : 'bg-[#f2fbf6] text-[#1f7f4e] hover:bg-[#e7f3ec] dark:bg-gray-700/50 dark:text-gray-200 dark:hover:bg-gray-700'}`;
                            btn.textContent = String(day);

                            // Add consultation pin if exists
                            const dateStr = `${viewYear}-${String(viewMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                            const dayConsultations = consultationMap[dateStr];

                            if (dayConsultations && dayConsultations.length > 0) {
                                const pin = document.createElement('span');
                                const isMobile = window.matchMedia('(max-width: 1023px)').matches;
                                const hasImmunization = dayConsultations.some((c) => c.type === 'immunization');
                                if (isMobile) {
                                    pin.className = 'absolute right-1 top-1 z-10 w-3 h-3 translate-x-1/2 -translate-y-1/2 rounded-full bg-red-500 border border-red-600 shadow-sm';
                                    pin.setAttribute('aria-hidden', 'true');
                                } else {
                                    pin.className = 'absolute right-6 top-1 z-10 inline-flex translate-x-1/2 -translate-y-1/2 items-center rounded-full bg-white px-1.5 py-0.5 text-[10px] font-bold text-black border border-gray-300 cursor-pointer select-none shadow-sm';
                                    pin.textContent = hasImmunization && dayConsultations.every((c) => c.type === 'immunization')
                                        ? 'Immunization'
                                        : 'Checkup';
                                }
                                pin.title = dayConsultations.map((c) => {
                                    const when = c.time ? ` at ${c.time}` : '';
                                    return `${c.checkup}${when}`;
                                }).join('\n');
                                
                                // Tooltip popup on hover/click
                                const showTooltip = () => {
                                    const existing = document.getElementById('consultationTooltip');
                                    if (existing) existing.remove();

                                    const tooltip = document.createElement('div');
                                    tooltip.id = 'consultationTooltip';
                                    tooltip.className = 'absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 z-50 bg-[#0e1b13] dark:bg-white text-white dark:text-[#0e1b13] text-xs font-semibold rounded-lg p-2 whitespace-nowrap';
                                    tooltip.innerHTML = dayConsultations.map((c) => {
                                        const noteHtml = c.note
                                            ? `<br><span class="opacity-75">${escapeHtml(c.note)}</span>`
                                            : '';
                                        const timeLabel = c.time
                                            ? escapeHtml(c.time)
                                            : 'Date only';
                                        return `<div>${escapeHtml(c.checkup)}<br><span class="text-[10px]">${timeLabel}</span>${noteHtml}</div>`;
                                    }).join('');
                                    btn.parentNode.style.position = 'relative';
                                    btn.appendChild(tooltip);
                                };

                                const hideTooltip = () => {
                                    const tooltip = document.getElementById('consultationTooltip');
                                    if (tooltip) tooltip.remove();
                                };

                                pin.addEventListener('mouseenter', showTooltip);
                                pin.addEventListener('mouseleave', hideTooltip);
                                pin.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    showTooltip();
                                });

                                btn.style.position = 'relative';
                                btn.appendChild(pin);
                            }
                            row.appendChild(btn);
                        });

                        calendarGrid.appendChild(row);
                    }
                }

                calendarPrev.addEventListener('click', () => {
                    viewMonth -= 1;
                    if (viewMonth < 0) {
                        viewMonth = 11;
                        viewYear -= 1;
                    }
                    renderCalendar();
                });

                calendarNext.addEventListener('click', () => {
                    viewMonth += 1;
                    if (viewMonth > 11) {
                        viewMonth = 0;
                        viewYear += 1;
                    }
                    renderCalendar();
                });

                // Close tooltip when clicking elsewhere
                document.addEventListener('click', (e) => {
                    if (e.target.id !== 'consultationTooltip' && !e.target.closest('[class*="animate-pulse"]')) {
                        const tooltip = document.getElementById('consultationTooltip');
                        if (tooltip) tooltip.remove();
                    }
                });

                renderCalendar();
            }

            if (!sidebar || !content || !toggleButton || !hamburgerIcon || !closeIcon) {
                return;
            }

            const storageKey = 'usersSidebarCollapsed';

            function applySidebarState(collapsed) {
                if (collapsed) {
                    sidebar.classList.add('is-collapsed');
                    content.classList.remove('lg:ml-64');
                    content.classList.remove('lg:w-[calc(100%-16rem)]');
                    content.classList.add('lg:ml-20');
                    content.classList.add('lg:w-[calc(100%-5rem)]');
                    toggleButton.style.left = '5rem';
                    // Show hamburger icon, hide X
                    hamburgerIcon.style.transform = 'scale(1) rotate(0deg)';
                    hamburgerIcon.style.opacity = '1';
                    closeIcon.style.transform = 'scale(0) rotate(-90deg)';
                    closeIcon.style.opacity = '0';
                } else {
                    sidebar.classList.remove('is-collapsed');
                    content.classList.remove('lg:ml-20');
                    content.classList.remove('lg:w-[calc(100%-5rem)]');
                    content.classList.add('lg:ml-64');
                    content.classList.add('lg:w-[calc(100%-16rem)]');
                    toggleButton.style.left = '16rem';
                    // Show X icon, hide hamburger
                    hamburgerIcon.style.transform = 'scale(0) rotate(90deg)';
                    hamburgerIcon.style.opacity = '0';
                    closeIcon.style.transform = 'scale(1) rotate(0deg)';
                    closeIcon.style.opacity = '1';
                }
            }

            const isCollapsed = localStorage.getItem(storageKey) === '1';
            applySidebarState(isCollapsed);

            toggleButton.addEventListener('click', () => {
                const nextCollapsed = !sidebar.classList.contains('is-collapsed');
                applySidebarState(nextCollapsed);
                localStorage.setItem(storageKey, nextCollapsed ? '1' : '0');
            });
        })();
    </script>
</body>
</html>
