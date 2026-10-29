<!DOCTYPE html>
<html class="light" lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <title>Bantay Kalusugan - Medical Records</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
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
                        "primary": "#19e66b",
                        "background-light": "#f6f8f7",
                        "background-dark": "#112117",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
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
    @endphp
    @include('sidebars.users')

    <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            <div class="w-full max-w-none">
                <header class="mb-6 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold">Medical Records</h1>
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Medical history and checkup records.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('users.profile.bhw-chat') }}" class="relative inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#d0e7d9] bg-white text-[#1f7f4e] shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" aria-label="Open BHW chat">
                            <span class="material-symbols-outlined text-[16px]">message</span>
                            @if (($unreadBhwChatCount ?? 0) > 0)
                                <span class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-red-600 ring-2 ring-white dark:ring-gray-800" aria-label="Unread BHW chat messages"></span>
                            @endif
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

                <div class="space-y-4 md:space-y-5">
                    @forelse ($medicalRecords as $record)
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-5 border border-[#e7f3ec] dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4 flex-1">
                                    <div class="flex-shrink-0">
                                        @if ($record->record_type === 'blood_pressure')
                                            <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-red-100 dark:bg-red-900/30">
                                                <span class="material-symbols-outlined text-red-600 dark:text-red-300">favorite</span>
                                            </div>
                                        @elseif ($record->record_type === 'temperature')
                                            <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-orange-100 dark:bg-orange-900/30">
                                                <span class="material-symbols-outlined text-orange-600 dark:text-orange-300">thermometer</span>
                                            </div>
                                        @elseif ($record->record_type === 'weight')
                                            <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-300">scale</span>
                                            </div>
                                        @elseif ($record->record_type === 'height')
                                            <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                                                <span class="material-symbols-outlined text-purple-600 dark:text-purple-300">straighten</span>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900/30">
                                                <span class="material-symbols-outlined text-green-600 dark:text-green-300">check_circle</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-lg font-bold text-[#0e1b13] dark:text-white">
                                                @if ($record->record_type === 'blood_pressure')
                                                    Blood Pressure
                                                @elseif ($record->record_type === 'temperature')
                                                    Temperature
                                                @elseif ($record->record_type === 'weight')
                                                    Weight
                                                @elseif ($record->record_type === 'height')
                                                    Height
                                                @else
                                                    {{ ucwords(str_replace('_', ' ', $record->record_type)) }}
                                                @endif
                                            </h3>
                                            <span class="inline-flex items-center rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">
                                                {{ $record->record_value }}
                                            </span>
                                        </div>

                                        @if ($record->note)
                                            <p class="text-sm text-[#376f50] dark:text-gray-300 mt-2">{{ $record->note }}</p>
                                        @endif

                                        <div class="flex items-center gap-3 mt-3 text-xs text-[#4e976b] dark:text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">calendar_today</span>
                                                {{ $record->created_at->format('M d, Y') }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">schedule</span>
                                                {{ $record->created_at->format('h:i A') }}
                                            </span>

                                            @if ($record->recorder)
                                                <span class="flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm">person</span>
                                                    Recorded by {{ $record->recorder->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-[#d0e7d9] dark:border-gray-700 p-8 md:p-12 text-center bg-white dark:bg-gray-800 shadow-sm">
                            <div class="flex justify-center mb-4">
                                <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-500">file_open</span>
                            </div>
                            <h3 class="text-lg font-bold text-[#0e1b13] dark:text-white mb-2">No Medical Records Yet</h3>
                            <p class="text-sm text-[#4e976b] dark:text-gray-400">Your medical records will appear here once they are recorded by a Barangay Health Worker.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

        @include('partials.users-mobile-nav')
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
