<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
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
            $displayAddress = !empty($addressSegments) ? implode(', ', $addressSegments) : 'N/A';
        } else {
            $displayAddress = $user->barangay ?: 'N/A';
        }

        $profileRequiredFields = [
            'Full name' => $user->full_name,
            'Gender' => $user->gender,
            'Date of birth' => $user->date_of_birth,
            'Civil status' => $user->civil_status,
            'Contact number' => $user->contact_number,
            'Barangay' => $user->barangay,
        ];

        $missingProfileFields = [];
        foreach ($profileRequiredFields as $label => $value) {
            if (blank($value)) {
                $missingProfileFields[] = $label;
            }
        }

        $healthTips = [];

        if (!empty($missingProfileFields)) {
            $previewFields = array_slice($missingProfileFields, 0, 3);
            $moreCount = count($missingProfileFields) - count($previewFields);
            $fieldsText = implode(', ', $previewFields) . ($moreCount > 0 ? ' and '.$moreCount.' more' : '');

            $healthTips[] = 'Please complete your Profile Settings. Missing: '.$fieldsText.'.';
        }

        if ($status !== 'accepted') {
            $registrationTip = match ($status) {
                'pending' => 'Your account registration is still pending. Please wait for barangay review.',
                'rejected' => 'Your registration was not approved. Update your profile details and resubmit for review.',
                default => 'Your account is not registered yet. Complete your profile and submit your registration request.',
            };

            $healthTips[] = $registrationTip;
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
        $isEvents = request()->routeIs('users.events');
        $isAnnouncements = request()->routeIs('users.announcements');
        $isProfile = request()->routeIs('users.profile');
        $isNotifications = request()->routeIs('users.notifications');
        $isMedicalRecords = request()->routeIs('users.medical-records');

        $showHealthTip = !empty($healthTips);
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
                                <a href="{{ route('users.profile.chat-support') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#d0e7d9] bg-white text-[#1f7f4e] shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" aria-label="Open messages">
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
                    @if ($showHealthTip)
                    <div class="bg-gradient-to-br from-[#e7f3ec] to-white dark:from-gray-800 dark:to-gray-800 rounded-xl p-4 md:p-5 border border-[#d0e7d9] dark:border-gray-700">
                        <h3 class="font-bold mb-2">Health Tip</h3>
                        <ul class="space-y-2 text-sm text-[#376f50] dark:text-gray-300">
                            @foreach ($healthTips as $tip)
                                <li class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-base leading-5 text-[#1f7f4e] dark:text-[#8cefb5]">tips_and_updates</span>
                                    <span>{{ $tip }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

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
                                <p class="text-sm text-[#4e976b] dark:text-gray-400">Latest barangay events and announcements.</p>
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
                                    <div class="flex-shrink-0 w-full cursor-pointer">
                                        <div class="overflow-hidden rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm h-full flex flex-col">
                                            <div class="flex h-36 md:h-44 w-full items-center justify-center bg-[#f2fbf6] dark:bg-gray-700/40 text-[#4e976b] dark:text-gray-400">
                                                <span class="material-symbols-outlined text-5xl">campaign</span>
                                            </div>

                                            <div class="p-4 md:p-5 flex flex-col flex-1">
                                                <div class="flex items-start justify-between mb-3">
                                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-primary/15 text-[#1f7f4e] dark:bg-primary/30 dark:text-[#8cefb5]">Announcement</span>
                                                    <span class="text-2xl">📢</span>
                                                </div>

                                                <h4 class="font-bold text-lg mb-2 text-[#0e1b13] dark:text-white">{{ $announcement->title ?: 'Barangay Announcement' }}</h4>

                                                @if ($announcement->message)
                                                <p class="text-sm text-[#376f50] dark:text-gray-300 mb-3 line-clamp-2 flex-grow">{{ $announcement->message }}</p>
                                                @endif

                                                <div class="space-y-2 text-sm text-[#4e976b] dark:text-gray-400">
                                                    <div class="flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">calendar_today</span>
                                                        <span>{{ $announcement->created_at?->format('M d, Y') ?? 'TBA' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="material-symbols-outlined text-base">schedule</span>
                                                        <span>Posted {{ $announcement->created_at?->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                            @forelse (($upcomingConsultations ?? collect()) as $consultation)
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-[#f2fbf6] dark:bg-gray-700/50">
                                    <span class="material-symbols-outlined text-primary">event</span>
                                    <div class="flex-1">
                                        <p class="font-semibold">{{ $consultation->medical_checkup ?: 'General Consultation' }}</p>
                                        <p class="text-sm text-[#4e976b] dark:text-gray-400">{{ $consultation->scheduled_at?->format('M d, Y · h:i A') }}</p>
                                        @if ($consultation->note)
                                            <p class="text-xs text-[#4e976b] dark:text-gray-400 mt-1">{{ $consultation->note }}</p>
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
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Medical Summary</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700">
                                <p class="text-[#4e976b] dark:text-gray-400">Last Check-up</p>
                                <p class="font-semibold mt-1">{{ $lastCheckupLabel ?? 'N/A' }}</p>
                            </div>
                            @forelse (($medicalSummaryRecords ?? collect()) as $record)
                                <div class="p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700">
                                    <p class="text-[#4e976b] dark:text-gray-400">{{ $record->record_type }}</p>
                                    <p class="font-semibold mt-1">{{ $record->record_value }}</p>
                                </div>
                            @empty
                                <div class="sm:col-span-2 p-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700 text-[#4e976b] dark:text-gray-400">
                                    No medical records yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <nav class="lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-[#d0e7d9] bg-white/95 backdrop-blur dark:border-gray-700 dark:bg-gray-800/95" aria-label="Mobile navigation">
            <div class="grid grid-cols-5 gap-1 px-2 py-2">
                <a href="{{ route('users.main') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isDashboard ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">home</span>
                    Home
                </a>
                <a href="{{ route('users.events') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isEvents ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">event</span>
                    Events
                </a>
                <a href="{{ route('users.announcements') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isAnnouncements ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">campaign</span>
                    News
                </a>
                <a href="{{ route('users.medical-records') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isMedicalRecords ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">health_metrics</span>
                    Medical
                </a>
                <a href="{{ route('users.profile') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[11px] font-semibold {{ $isProfile ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
                    <span class="material-symbols-outlined text-[20px]">person</span>
                    Profile
                </a>
            </div>
        </nav>

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
                const consultations = {!! json_encode(($upcomingConsultations ?? collect())->map(fn($c) => [
                    'date' => $c->scheduled_at?->format('Y-m-d'),
                    'checkup' => $c->medical_checkup ?: 'General Consultation',
                    'time' => $c->scheduled_at?->format('h:i A'),
                    'note' => $c->note,
                ])->values()->all()) !!};

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
                                if (isMobile) {
                                    pin.className = 'absolute right-1 top-1 z-10 w-3 h-3 translate-x-1/2 -translate-y-1/2 rounded-full bg-red-500 border border-red-600 shadow-sm';
                                    pin.setAttribute('aria-hidden', 'true');
                                } else {
                                    pin.className = 'absolute right-6 top-1 z-10 inline-flex translate-x-1/2 -translate-y-1/2 items-center rounded-full bg-white px-1.5 py-0.5 text-[10px] font-bold text-black border border-gray-300 cursor-pointer select-none shadow-sm';
                                    pin.textContent = 'Checkup';
                                }
                                pin.title = dayConsultations.map(c => `${c.checkup} at ${c.time}`).join('\n');
                                
                                // Tooltip popup on hover/click
                                const showTooltip = () => {
                                    const existing = document.getElementById('consultationTooltip');
                                    if (existing) existing.remove();

                                    const tooltip = document.createElement('div');
                                    tooltip.id = 'consultationTooltip';
                                    tooltip.className = 'absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 z-50 bg-[#0e1b13] dark:bg-white text-white dark:text-[#0e1b13] text-xs font-semibold rounded-lg p-2 whitespace-nowrap';
                                    tooltip.innerHTML = dayConsultations.map(c => `<div>${c.checkup}<br><span class="text-[10px]">${c.time}</span>${c.note ? '<br><span class="opacity-75">' + c.note + '</span>' : ''}</div>`).join('');
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
