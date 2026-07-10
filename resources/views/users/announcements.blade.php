<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Announcements</title>
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
                        <h1 class="text-2xl font-bold">Announcements</h1>
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Posted announcements.</p>
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
                    @forelse ($announcements as $announcement)
                        @php
                            $announcementImage = $announcement->image_url;
                            if ($announcementImage && !\Illuminate\Support\Str::startsWith($announcementImage, ['http://', 'https://', '/'])) {
                                $announcementImage = \Illuminate\Support\Facades\Storage::url($announcementImage);
                            }
                        @endphp
                        <article class="overflow-hidden rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                            @if ($announcementImage)
                                <img src="{{ $announcementImage }}" alt="{{ $announcement->title }}" class="h-44 w-full object-cover" />
                            @else
                                <div class="flex h-44 w-full items-center justify-center bg-[#f2fbf6] dark:bg-gray-700/40 text-[#4e976b] dark:text-gray-400">
                                    <span class="material-symbols-outlined text-5xl">campaign</span>
                                </div>
                            @endif

                            <div class="p-4 md:p-5">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">
                                        Announcement
                                    </span>
                                    <span class="text-xs text-[#4e976b] dark:text-gray-400">
                                        {{ $announcement->event_date?->format('M d, Y') ?? $announcement->created_at?->format('M d, Y') ?? 'TBA' }}
                                    </span>
                                </div>

                                <h2 class="text-lg font-bold leading-tight text-[#0e1b13] dark:text-white">{{ $announcement->title }}</h2>

                                @if ($announcement->description)
                                    <p class="mt-2 text-sm text-[#376f50] dark:text-gray-300 line-clamp-3">{{ $announcement->description }}</p>
                                @endif

                                <div class="mt-4 space-y-1.5 text-sm text-[#376f50] dark:text-gray-300">
                                    <p class="flex items-center gap-2 text-xs text-[#4e976b] dark:text-gray-400 pt-1">
                                        <span class="material-symbols-outlined text-base">edit_calendar</span>
                                        <span>Posted {{ $announcement->created_at?->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="md:col-span-2 rounded-xl border border-dashed border-[#d0e7d9] dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-10 text-center">
                            <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-400">campaign</span>
                            <p class="mt-3 text-base font-semibold">No announcements yet.</p>
                            <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Check back later for updates from your barangay and health workers.</p>
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
