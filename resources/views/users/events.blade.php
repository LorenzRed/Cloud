<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    @include('partials.submit-guard')
    @include('partials.users-swr')
    <title>Bantay Kalusugan - Barangay Events</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
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
                        primary: "#19e66b",
                        "background-light": "#f6f8f7",
                        "background-dark": "#112117",
                    },
                    fontFamily: {
                        display: ["Public Sans", "sans-serif"],
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
    @php
        $isDashboard = request()->routeIs('users.main');
        $isEvents = request()->routeIs('users.events');
        $isAnnouncements = request()->routeIs('users.announcements');
        $isProfile = request()->routeIs('users.profile');
        $isNotifications = request()->routeIs('users.notifications');
        $isMedicalRecords = request()->routeIs('users.medical-records');
        $verificationStatus = $verificationStatus ?? 'unverified';
        $canViewEvents = $verificationStatus === 'accepted';
    @endphp

    @include('sidebars.users')

    <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-3 sm:px-4 md:px-5 lg:px-6 pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            <div class="w-full max-w-none">
                <header class="mb-6 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold">Barangay Events</h1>
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">
                            @if ($barangayName)
                                Latest posted events from {{ $barangayName }}.
                            @else
                                Latest posted events from your barangay.
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('users.profile.chat-support') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#d0e7d9] bg-white text-[#1f7f4e] shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" aria-label="Open messages">
                            <span class="material-symbols-outlined text-[16px]">message</span>
                        </a>
                        <a href="{{ route('users.notifications') }}" class="relative inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#d0e7d9] bg-white text-[#1f7f4e] shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" aria-label="Notifications">
                            <span class="material-symbols-outlined text-[16px] {{ $isNotifications ? 'text-primary' : '' }}">notifications</span>
                            @if (($unreadInboxCount ?? 0) > 0)
                                <span class="absolute -top-1 -right-1 inline-flex min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-4 text-white">
                                    {{ ($unreadInboxCount ?? 0) > 99 ? '99+' : $unreadInboxCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                    @if ($canViewEvents)
                        @forelse ($events as $event)
                            @php
                                $isRegistered = in_array($event->id, $registeredEventIds ?? [], true);
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

                                $registrationCount = (int) ($event->registration_count ?? 0);
                                $isFull = $event->registration_limit !== null && $registrationCount >= (int) $event->registration_limit;
                            @endphp

                            <article class="overflow-hidden rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                                @if ($eventImage)
                                    <img src="{{ $eventImage }}" alt="{{ $event->title }}" class="h-44 w-full object-cover" />
                                @else
                                    <div class="flex h-44 w-full items-center justify-center bg-[#f2fbf6] dark:bg-gray-700/40 text-[#4e976b] dark:text-gray-400">
                                        <span class="material-symbols-outlined text-5xl">photo</span>
                                    </div>
                                @endif

                                <div class="p-4 md:p-5">
                                    <div class="mb-2 flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">
                                            {{ ucfirst($event->category ?? 'General') }}
                                        </span>
                                        <span class="text-xs text-[#4e976b] dark:text-gray-400">
                                            {{ $event->event_date?->format('M d, Y') ?? 'TBA' }}
                                        </span>
                                    </div>

                                    <h2 class="text-lg font-bold leading-tight text-[#0e1b13] dark:text-white">{{ $event->title }}</h2>

                                    @if ($event->description)
                                        <p class="mt-2 text-sm text-[#376f50] dark:text-gray-300 line-clamp-3">{{ $event->description }}</p>
                                    @endif

                                    <div class="mt-4 space-y-1.5 text-sm text-[#376f50] dark:text-gray-300">
                                        <p class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">schedule</span>
                                            <span>
                                                {{ $eventStartTime }}
                                                @if ($eventEndTime)
                                                    - {{ $eventEndTime }}
                                                @endif
                                            </span>
                                        </p>
                                        <p class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-sm">location_on</span>
                                            <span class="truncate">{{ $event->location ?: 'Location to be announced' }}</span>
                                        </p>

                                        @if ($event->registration_limit !== null)
                                            <div class="flex items-center justify-between gap-2 text-xs text-[#4e976b] dark:text-gray-400">
                                                <span class="flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-sm">group</span>
                                                    <span>{{ $registrationCount }} / {{ $event->registration_limit }} registered</span>
                                                </span>

                                                @if ($isFull && ! $isRegistered)
                                                    <span class="rounded-full bg-red-100 px-2 py-0.5 font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-200">Full</span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="mt-4 flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2 text-xs text-[#4e976b] dark:text-gray-400">
                                                <span class="material-symbols-outlined text-sm">edit_calendar</span>
                                                <span>Posted {{ $event->created_at?->diffForHumans() }}</span>
                                            </div>

                                            @if ($isRegistered)
                                                <button type="button" disabled class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-gray-200 text-gray-600 text-sm font-semibold">
                                                    <span class="material-symbols-outlined text-sm">check</span>
                                                    Registered
                                                </button>
                                            @elseif ($isFull)
                                                <button type="button" disabled class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-red-100 text-red-700 text-sm font-semibold dark:bg-red-900/30 dark:text-red-200">
                                                    <span class="material-symbols-outlined text-sm">block</span>
                                                    Full
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('users.events.register', $event) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-primary text-[#0e1b13] text-sm font-semibold">
                                                        <span class="material-symbols-outlined text-sm">how_to_reg</span>
                                                        Register Now
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="md:col-span-2 rounded-xl border border-dashed border-[#d0e7d9] dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-10 text-center">
                                <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-400">event_busy</span>
                                <p class="mt-3 text-base font-semibold">No posted barangay events yet.</p>
                                <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Check back again for upcoming activities from your barangay captain.</p>
                            </div>
                        @endforelse
                    @else
                        <div class="md:col-span-2 rounded-xl border border-dashed border-[#d0e7d9] dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-10 text-center">
                            <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-400">schedule</span>
                            <p class="mt-3 text-base font-semibold">Events will appear once your registration is approved.</p>
                            <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">You can still browse announcements while your account is pending.</p>
                        </div>
                    @endif
                </div>
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

    <script>
        (function () {
            const sidebar = document.getElementById('usersSidebar');
            const content = document.getElementById('usersContent');
            const toggleButton = document.getElementById('usersSidebarToggle');
            const hamburgerIcon = document.getElementById('hamburgerIcon');
            const closeIcon = document.getElementById('closeIcon');

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
