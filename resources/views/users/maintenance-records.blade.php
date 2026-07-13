<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <title>Bantay Kalusugan - Maintenance Records</title>
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
        $isNotifications = request()->routeIs('users.notifications');
    @endphp
    @include('sidebars.users')

    <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-4 sm:px-5 md:px-6 lg:px-7 pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            <div class="w-full max-w-none">
                <header class="mb-6 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold">Maintenance Records</h1>
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Household monitoring visits recorded by your Barangay Health Worker.</p>
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

                <div class="space-y-4 md:space-y-5">
                    @forelse ($monitoringCases as $monitoringCase)
                        <article class="bg-white dark:bg-gray-800 rounded-xl p-4 md:p-5 border border-[#e7f3ec] dark:border-gray-700 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">{{ $monitoringCase->typeLabel() }}</p>
                                    <h2 class="mt-1 text-lg font-bold text-[#0e1b13] dark:text-white">{{ $monitoringCase->member_name ?: 'Household member' }}</h2>
                                    @if (filled($monitoringCase->issue_notes))
                                        <p class="mt-2 text-sm text-[#376f50] dark:text-gray-300">{{ $monitoringCase->issue_notes }}</p>
                                    @endif
                                </div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold shrink-0 {{ $monitoringCase->is_active ? 'bg-primary/15 text-[#1f7f4e]' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $monitoringCase->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            @if ($monitoringCase->isPregnant() && ($monitoringCase->first_period_date || $monitoringCase->last_period_date))
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-[#376f50] dark:text-gray-300">
                                    @if ($monitoringCase->first_period_date)
                                        <p>First period: <span class="font-semibold">{{ $monitoringCase->first_period_date->format('M d, Y') }}</span></p>
                                    @endif
                                    @if ($monitoringCase->last_period_date)
                                        <p>Last period: <span class="font-semibold">{{ $monitoringCase->last_period_date->format('M d, Y') }}</span></p>
                                    @endif
                                </div>
                            @endif

                            @if ($monitoringCase->visits->isNotEmpty())
                                <div class="mt-4 space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">Visit history</p>
                                    @foreach ($monitoringCase->visits as $visit)
                                        <div class="rounded-lg border border-[#d0e7d9] dark:border-gray-700 p-3 text-sm">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="font-semibold text-[#0e1b13] dark:text-white">{{ $visit->visit_date?->format('M d, Y') ?: 'No date' }}</p>
                                                <p class="text-xs text-[#4e976b] dark:text-gray-400">{{ $visit->created_at?->diffForHumans() }}</p>
                                            </div>
                                            <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2 text-[#376f50] dark:text-gray-300">
                                                @if ($visit->height_cm !== null)
                                                    <span>Height: {{ $visit->height_cm }} cm</span>
                                                @endif
                                                @if ($visit->weight_kg !== null)
                                                    <span>Weight: {{ $visit->weight_kg }} kg</span>
                                                @endif
                                                @if ($visit->bmi_value !== null)
                                                    <span>BMI: {{ $visit->bmi_value }}</span>
                                                @endif
                                                @if ($visit->muac_cm !== null)
                                                    <span>MUAC: {{ $visit->muac_cm }} cm</span>
                                                @endif
                                                @if ($visit->anti_tetanus)
                                                    <span>Anti-tetanus: {{ $visit->anti_tetanus }}</span>
                                                @endif
                                                @if ($visit->blood_pressure)
                                                    <span>BP: {{ $visit->blood_pressure }}</span>
                                                @endif
                                            </div>
                                            @if ($visit->maintenance_medicines)
                                                <p class="mt-2 text-[#376f50] dark:text-gray-300"><span class="font-semibold">Maintenance:</span> {{ $visit->maintenance_medicines }}</p>
                                            @endif
                                            @if ($visit->notes)
                                                <p class="mt-2 text-[#4e976b] dark:text-gray-400">{{ $visit->notes }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-sm text-[#4e976b] dark:text-gray-400">No visits recorded yet for this member.</p>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-xl border border-[#d0e7d9] dark:border-gray-700 p-8 md:p-12 text-center bg-white dark:bg-gray-800 shadow-sm">
                            <div class="flex justify-center mb-4">
                                <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-500">health_and_safety</span>
                            </div>
                            <h3 class="text-lg font-bold text-[#0e1b13] dark:text-white mb-2">No Maintenance Records Yet</h3>
                            <p class="text-sm text-[#4e976b] dark:text-gray-400">Household monitoring and maintenance visit records will appear here once your BHW adds them.</p>
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
