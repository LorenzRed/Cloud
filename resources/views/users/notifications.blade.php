<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Notifications</title>
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
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 py-4 md:py-6 overflow-y-auto">
            <div class="w-full max-w-none">
            <header class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Notifications</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('users.main') }}" class="inline-flex items-center gap-1 rounded-lg border border-[#d0e7d9] dark:border-gray-700 px-3 py-2 text-sm font-semibold text-[#1f7f4e] dark:text-gray-200">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back
                    </a>
                </div>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-[#d0e7d9] dark:border-gray-700 shadow-sm divide-y divide-[#e7f3ec] dark:divide-gray-700">
                @forelse ($notifications as $notification)
                    <div class="p-4 {{ is_null($notification->read_at) ? 'bg-[#f2fbf6] dark:bg-gray-700/30' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-[#0e1b13] dark:text-white">{{ $notification->title }}</p>
                                <p class="text-sm text-[#4e976b] dark:text-gray-300 mt-1">{{ $notification->message }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-[#4e976b] dark:text-gray-400">{{ $notification->created_at?->format('M d, Y h:i A') }}</p>
                                @if (is_null($notification->read_at))
                                    <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">New</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-400">notifications</span>
                        <p class="mt-3 text-sm text-[#4e976b] dark:text-gray-400">No notifications in your inbox yet.</p>
                    </div>
                @endforelse
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
