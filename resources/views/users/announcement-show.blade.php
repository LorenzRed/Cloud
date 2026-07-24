<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <title>{{ $announcement->title }} - Bantay Kalusugan</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
        #usersSidebar { transition: width 0.25s ease; }

        @media (min-width: 1024px) {
            #usersSidebar.is-collapsed { width: 5rem; }
            #usersSidebar.is-collapsed .sidebar-label,
            #usersSidebar.is-collapsed .sidebar-brand-text { display: none; }
            #usersSidebar.is-collapsed .sidebar-link,
            #usersSidebar.is-collapsed .sidebar-logout { justify-content: center; }
            #usersSidebar.is-collapsed .sidebar-header-inner { justify-content: center; }
        }

        .announcement-body {
            white-space: pre-wrap;
            word-break: break-word;
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
        $announcementImage = $announcement->image_url;
        if ($announcementImage && !\Illuminate\Support\Str::startsWith($announcementImage, ['http://', 'https://', '/'])) {
            $announcementImage = \Illuminate\Support\Facades\Storage::url($announcementImage);
        }

        $barangayLabel = $announcement->barangay?->name
            ?? $barangay?->name
            ?? $residentRequest?->barangay
            ?? 'Barangay';

        $scopeLabel = $announcement->isBarangayWide() ? 'Barangay-wide post' : 'Purok announcement';
        $postedBy = $announcement->createdBy?->name;
    @endphp

    @include('sidebars.users')

    <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            <div class="mx-auto w-full max-w-2xl lg:mx-0 lg:max-w-none">
                    <div class="mb-4 flex items-center justify-between gap-3">
                    <a href="{{ route('users.announcements') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-[#1f7f4e] dark:text-gray-200 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        Back
                    </a>
                    <div class="flex items-center gap-2">
                        @if ($announcement->isExpired())
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-800 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white dark:bg-gray-900">
                                Expired
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">
                            <span class="material-symbols-outlined text-[14px]">campaign</span>
                            Announcement
                        </span>
                    </div>
                </div>

                <article class="overflow-hidden rounded-2xl border border-[#e7f3ec] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    @if ($announcementImage)
                        <div class="relative h-48 sm:h-56 w-full overflow-hidden bg-[#f2fbf6] dark:bg-gray-700/40">
                            <img src="{{ $announcementImage }}" alt="{{ $announcement->title }}" class="h-full w-full object-cover" />
                        </div>
                    @else
                        <div class="relative flex h-36 sm:h-44 w-full items-end overflow-hidden bg-gradient-to-br from-[#1f7f4e] via-[#19a85a] to-[#0e1b13] px-5 pb-5">
                            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #fff 0, transparent 40%), radial-gradient(circle at 80% 0%, #8cefb5 0, transparent 35%);"></div>
                            <span class="relative material-symbols-outlined text-white/90 text-5xl">campaign</span>
                        </div>
                    @endif

                    <div class="p-5 sm:p-6 space-y-5">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-[#f2fbf6] dark:bg-gray-700/50 px-2.5 py-1 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">
                                    <span class="material-symbols-outlined text-[14px]">location_on</span>
                                    {{ $barangayLabel }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-[#f2fbf6] dark:bg-gray-700/50 px-2.5 py-1 text-xs font-semibold text-[#376f50] dark:text-gray-300">
                                    <span class="material-symbols-outlined text-[14px]">{{ $announcement->isBarangayWide() ? 'apartment' : 'home_pin' }}</span>
                                    {{ $scopeLabel }}
                                </span>
                            </div>

                            <h1 class="text-2xl sm:text-[1.75rem] font-bold tracking-tight leading-snug text-[#0e1b13] dark:text-white">
                                {{ $announcement->title ?: 'Barangay Announcement' }}
                            </h1>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-[#4e976b] dark:text-gray-400 border-y border-[#e7f3ec] dark:border-gray-700 py-3">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">schedule</span>
                                Posted {{ $announcement->created_at?->format('M d, Y · h:i A') ?? '—' }}
                            </span>
                            @if ($announcement->expiresAt())
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">event</span>
                                    Until {{ $announcement->expiresAt()->format('M d, Y · h:i A') }}
                                </span>
                            @endif
                            @if ($postedBy)
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">person</span>
                                    {{ $postedBy }}
                                </span>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400 mb-2">Full message</p>
                            @if (filled($announcement->description))
                                <p class="announcement-body text-[15px] sm:text-base leading-relaxed text-[#0e1b13] dark:text-gray-200">{{ $announcement->description }}</p>
                            @else
                                <p class="text-sm text-[#4e976b] dark:text-gray-400 italic">No additional message was provided for this post.</p>
                            @endif
                        </div>

                        @if (filled($announcement->location))
                            <div class="rounded-xl bg-[#f8fcf9] dark:bg-gray-700/40 px-4 py-3 flex items-start gap-3">
                                <span class="material-symbols-outlined text-[#1f7f4e] text-[20px] mt-0.5">place</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#4e976b] dark:text-gray-400">Location</p>
                                    <p class="text-sm font-medium text-[#0e1b13] dark:text-white mt-0.5">{{ $announcement->location }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
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
                    content.classList.remove('lg:ml-64', 'lg:w-[calc(100%-16rem)]');
                    content.classList.add('lg:ml-20', 'lg:w-[calc(100%-5rem)]');
                    toggleButton.style.left = '5rem';
                    hamburgerIcon.style.transform = 'scale(1) rotate(0deg)';
                    hamburgerIcon.style.opacity = '1';
                    closeIcon.style.transform = 'scale(0) rotate(-90deg)';
                    closeIcon.style.opacity = '0';
                } else {
                    sidebar.classList.remove('is-collapsed');
                    content.classList.remove('lg:ml-20', 'lg:w-[calc(100%-5rem)]');
                    content.classList.add('lg:ml-64', 'lg:w-[calc(100%-16rem)]');
                    toggleButton.style.left = '16rem';
                    hamburgerIcon.style.transform = 'scale(0) rotate(90deg)';
                    hamburgerIcon.style.opacity = '0';
                    closeIcon.style.transform = 'scale(1) rotate(0deg)';
                    closeIcon.style.opacity = '1';
                }
            }

            applySidebarState(localStorage.getItem(storageKey) === '1');

            toggleButton.addEventListener('click', () => {
                const nextCollapsed = !sidebar.classList.contains('is-collapsed');
                applySidebarState(nextCollapsed);
                localStorage.setItem(storageKey, nextCollapsed ? '1' : '0');
            });
        })();
    </script>
</body>
</html>
