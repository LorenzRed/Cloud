<!DOCTYPE html>
<html class="light" lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <title>Bantay Kalusugan - Maintenance History</title>
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
        <main class="flex-1 px-4 sm:px-5 md:px-8 lg:px-7 pt-4 md:pt-7 pb-24 lg:pb-6 overflow-y-auto">
            <div class="mx-auto w-full max-w-xl md:max-w-none">
                <header class="mb-6 md:mb-7 flex items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold">Maintenance History</h1>
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Monitoring records from your BHW</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('users.profile.bhw-chat') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#d0e7d9] bg-white text-[#1f7f4e] shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" aria-label="Open BHW chat">
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

                <div class="space-y-3.5 md:space-y-4 lg:space-y-2">
                    @forelse ($monitoringCases as $monitoringCase)
                        @php
                            $visitCount = $monitoringCase->visits->count();
                            $latestVisit = $monitoringCase->visits->last();
                            $typeIcon = $monitoringCase->isPregnant()
                                ? 'pregnant_woman'
                                : ($monitoringCase->isSenior() ? 'elderly' : 'health_and_safety');
                            $familyRole = match (strtolower(trim((string) ($monitoringCase->member_source ?? '')))) {
                                'resident' => 'Resident',
                                'spouse' => 'Spouse',
                                'household_child', 'child' => 'Child',
                                'household_senior', 'senior' => 'Senior',
                                default => filled($monitoringCase->member_source)
                                    ? ucwords(str_replace('_', ' ', (string) $monitoringCase->member_source))
                                    : null,
                            };
                        @endphp
                        <article class="rounded-2xl border border-[#e7f3ec] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                            <div class="px-4 md:px-5 pt-4 md:pt-5 pb-3 md:pb-4">
                                <div class="flex items-center gap-3 md:gap-4">
                                    <span class="inline-flex size-12 md:size-14 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">
                                        <span class="material-symbols-outlined text-[26px] md:text-[30px]">{{ $typeIcon }}</span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h2 class="text-[15px] md:text-base font-bold text-[#0e1b13] dark:text-white leading-snug truncate">
                                                {{ $monitoringCase->member_name ?: 'Household member' }}
                                            </h2>
                                            <span class="shrink-0 rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $monitoringCase->is_active ? 'bg-[#f2fbf6] text-[#1f7f4e] dark:bg-gray-700 dark:text-[#8cefb5]' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ $monitoringCase->is_active ? 'Active' : 'Completed' }}
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-sm text-[#4e976b] dark:text-gray-400">
                                            <span class="font-semibold text-[#376f50] dark:text-gray-300">{{ $monitoringCase->typeLabel() }}</span>
                                            @if ($familyRole)
                                                <span class="mx-1.5 text-[#d0e7d9] dark:text-gray-600">·</span>
                                                <span>{{ $familyRole }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3.5 md:mt-4 grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-3">
                                    <div class="rounded-xl bg-[#f7fbf8] dark:bg-gray-700/40 px-3 md:px-4 py-2.5 md:py-3">
                                        <p class="text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">Visits</p>
                                        <p class="mt-0.5 text-sm md:text-base font-bold text-[#0e1b13] dark:text-white">{{ $visitCount }}</p>
                                    </div>
                                    <div class="rounded-xl bg-[#f7fbf8] dark:bg-gray-700/40 px-3 md:px-4 py-2.5 md:py-3 min-w-0 md:col-span-3">
                                        <p class="text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">{{ $monitoringCase->is_active ? 'Latest visit' : 'Last visit' }}</p>
                                        <p class="mt-0.5 text-sm md:text-base font-bold text-[#0e1b13] dark:text-white truncate">
                                            {{ $latestVisit?->visit_date?->format('M d, Y') ?: '—' }}
                                        </p>
                                    </div>
                                </div>

                                @if (filled($monitoringCase->issue_notes))
                                    <p class="mt-3 md:mt-4 text-sm md:text-[15px] leading-snug text-[#376f50] dark:text-gray-300 line-clamp-2 md:line-clamp-3">{{ $monitoringCase->issue_notes }}</p>
                                @endif

                                @if ($monitoringCase->isPregnant() && ($monitoringCase->first_period_date || $monitoringCase->last_period_date))
                                    <div class="mt-3 md:mt-4 grid grid-cols-2 gap-2 md:gap-3">
                                        <div class="rounded-xl bg-pink-50 dark:bg-pink-900/20 px-3 md:px-4 py-2.5 md:py-3">
                                            <p class="text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-pink-700 dark:text-pink-200">First period</p>
                                            <p class="mt-0.5 text-sm md:text-base font-bold text-pink-900 dark:text-pink-100 truncate">{{ $monitoringCase->first_period_date?->format('M d, Y') ?? '—' }}</p>
                                        </div>
                                        <div class="rounded-xl bg-pink-50 dark:bg-pink-900/20 px-3 md:px-4 py-2.5 md:py-3">
                                            <p class="text-[10px] md:text-[11px] font-semibold uppercase tracking-wide text-pink-700 dark:text-pink-200">Last period</p>
                                            <p class="mt-0.5 text-sm md:text-base font-bold text-pink-900 dark:text-pink-100 truncate">{{ $monitoringCase->last_period_date?->format('M d, Y') ?? '—' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($visitCount > 0)
                                <details class="group border-t border-[#e7f3ec] dark:border-gray-700">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 md:px-5 py-3.5 md:py-4 select-none [&::-webkit-details-marker]:hidden active:bg-[#f7fbf8] dark:active:bg-gray-700/30">
                                        <span class="flex items-center gap-2.5 min-w-0">
                                            <span class="inline-flex size-8 md:size-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">
                                                <span class="material-symbols-outlined text-[18px] md:text-[20px]">history</span>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block text-sm md:text-[15px] font-bold text-[#0e1b13] dark:text-white">Visit history</span>
                                                <span class="block text-xs md:text-sm text-[#4e976b] dark:text-gray-400">{{ $visitCount }} visit{{ $visitCount === 1 ? '' : 's' }} · tap to view</span>
                                            </span>
                                        </span>
                                        <span class="material-symbols-outlined shrink-0 text-[22px] text-[#4e976b] transition duration-200 group-open:rotate-180">expand_more</span>
                                    </summary>

                                    <div class="px-4 md:px-5 pb-4 md:pb-5 space-y-2 md:space-y-0 md:grid md:grid-cols-2 md:gap-3">
                                        @foreach ($monitoringCase->visits as $visit)
                                            @php
                                                $resolved = $visit->resolvedVitals($monitoringCase->visits);
                                                $visitLabel = (! $monitoringCase->is_active && $loop->last)
                                                    ? 'Last Visit'
                                                    : $resolved['visit_label'];
                                                $vitalRows = collect([
                                                    $resolved['height_cm'] !== null ? ['label' => 'Height', 'value' => $resolved['height_cm'].' cm'] : null,
                                                    $resolved['weight_kg'] !== null ? ['label' => 'Weight', 'value' => $resolved['weight_kg'].' kg'] : null,
                                                    $resolved['bmi_value'] !== null ? ['label' => 'BMI', 'value' => (string) $resolved['bmi_value']] : null,
                                                    $resolved['muac_cm'] !== null ? ['label' => 'MUAC', 'value' => $resolved['muac_cm'].' cm'] : null,
                                                    filled($resolved['anti_tetanus']) ? ['label' => 'Anti-tetanus', 'value' => (string) $resolved['anti_tetanus']] : null,
                                                    filled($resolved['blood_pressure']) ? ['label' => 'BP', 'value' => (string) $resolved['blood_pressure']] : null,
                                                ])->filter()->values();
                                            @endphp
                                            <div class="rounded-xl border border-[#e7f3ec] dark:border-gray-700 bg-[#fbfdfc] dark:bg-gray-800/80 px-3.5 md:px-4 py-3">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] md:text-[11px] font-bold uppercase tracking-wide text-sky-600 dark:text-sky-300">
                                                            {{ $visitLabel }}
                                                        </p>
                                                        <p class="mt-0.5 text-sm md:text-[15px] font-semibold text-[#0e1b13] dark:text-white leading-snug">
                                                            {{ $visit->visit_date?->format('M d, Y') ?: 'No date' }}
                                                        </p>
                                                    </div>
                                                    <span class="shrink-0 text-[11px] md:text-xs text-[#4e976b] dark:text-gray-400">
                                                        {{ $visit->created_at?->diffForHumans() }}
                                                    </span>
                                                </div>

                                                @if ($vitalRows->isNotEmpty())
                                                    <div class="mt-2.5 grid grid-cols-2 md:grid-cols-3 gap-2">
                                                        @foreach ($vitalRows as $vital)
                                                            <div class="rounded-lg bg-white dark:bg-gray-800 border border-[#e7f3ec] dark:border-gray-700 px-2.5 md:px-3 py-2 min-w-0">
                                                                <p class="text-[10px] font-semibold uppercase tracking-wide text-[#4e976b] dark:text-gray-400">{{ $vital['label'] }}</p>
                                                                <p class="mt-0.5 text-sm font-bold text-[#0e1b13] dark:text-white truncate">{{ $vital['value'] }}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if ($visit->notes)
                                                    <p class="mt-2.5 text-xs md:text-sm leading-relaxed text-[#4e976b] dark:text-gray-400 border-t border-[#e7f3ec] dark:border-gray-700 pt-2">
                                                        {{ $visit->notes }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @else
                                <div class="border-t border-[#e7f3ec] dark:border-gray-700 px-4 md:px-5 py-3.5">
                                    <p class="text-sm md:text-[15px] text-[#4e976b] dark:text-gray-400">No visits recorded yet for this member.</p>
                                </div>
                            @endif

                            <div class="flex items-center gap-1.5 border-t border-[#e7f3ec] dark:border-gray-700 px-4 md:px-5 py-2.5 md:py-3 text-xs md:text-sm text-[#4e976b] dark:text-gray-400">
                                <span class="material-symbols-outlined text-[15px]">schedule</span>
                                @if (! $monitoringCase->is_active && $monitoringCase->completed_at)
                                    <span>Completed {{ $monitoringCase->completed_at->diffForHumans() }}</span>
                                @else
                                    <span>Updated {{ $monitoringCase->updated_at?->diffForHumans() ?? '—' }}</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-800/70 px-6 py-14 text-center">
                            <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">
                                <span class="material-symbols-outlined text-4xl">health_and_safety</span>
                            </span>
                            <p class="mt-4 text-base font-semibold text-[#0e1b13] dark:text-white">No maintenance records yet</p>
                            <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Monitoring visits will show up here when your BHW adds them.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

        @include('partials.users-mobile-nav')
        @include('partials.users-chat-support-fab')
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
