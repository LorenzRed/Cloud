<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <title>Bantay Kalusugan - Notifications</title>
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
        .notif-unread {
            background: #f2fbf6;
            border-color: rgba(25, 230, 107, 0.35);
        }
        .dark .notif-unread {
            background: rgba(25, 230, 107, 0.08);
            border-color: rgba(25, 230, 107, 0.25);
        }
        .notif-item.removing {
            opacity: 0;
            transform: translateX(12px);
            max-height: 0;
            margin: 0;
            padding-top: 0;
            padding-bottom: 0;
            border-width: 0;
            overflow: hidden;
            transition: all 0.28s ease;
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
                    fontFamily: { "display": ["Public Sans", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
    @php
        $isNotifications = request()->routeIs('users.notifications');
        $csrfToken = csrf_token();
        $hasRead = collect($notifications ?? [])->contains(fn ($n) => $n->read_at !== null);
    @endphp
    @include('sidebars.users')

    @if (session('status'))
        <div id="notif-flash" data-auto-dismiss="2000" class="fixed top-4 inset-x-4 z-[100] sm:left-auto sm:right-4 sm:max-w-md sm:ml-auto flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm text-emerald-900 shadow-lg transition-all duration-300 dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-100" role="status">
            <span class="material-symbols-outlined shrink-0 text-base sm:text-lg text-emerald-600 dark:text-emerald-400">check_circle</span>
            <p class="flex-1 pt-0.5">{{ session('status') }}</p>
            <button type="button" id="notif-flash-dismiss" class="shrink-0 rounded-lg p-1 text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/50" aria-label="Dismiss">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    @endif

    <div id="usersContent" class="flex min-h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200">
        <main class="flex-1 px-3 sm:px-5 md:px-6 lg:px-7 pt-3 sm:pt-4 md:pt-6 pb-24 md:pb-6 overflow-y-auto">
            <div class="w-full max-w-2xl mx-auto lg:max-w-none">
                <header class="mb-4 sm:mb-5">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <a href="{{ route('users.main') }}" class="inline-flex size-9 sm:size-10 shrink-0 items-center justify-center rounded-full text-[#1f7f4e] dark:text-gray-200 hover:bg-[#e7f3ec] dark:hover:bg-gray-800 transition-colors -ml-1" aria-label="Back to home">
                            <span class="material-symbols-outlined text-[26px] sm:text-[28px]">chevron_left</span>
                        </a>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold tracking-tight leading-tight">Notifications</h1>
                            <p class="mt-0.5 text-xs sm:text-sm text-[#4e976b] dark:text-gray-400 truncate">
                                @if (($unreadInboxCount ?? 0) > 0)
                                    <span class="font-semibold text-[#1f7f4e]">{{ $unreadInboxCount }} new</span>
                                @else
                                    You're all caught up
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if (($unreadInboxCount ?? 0) > 0)
                                <form method="POST" action="{{ route('users.notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="h-9 px-2.5 sm:px-3 rounded-full text-xs sm:text-sm font-semibold text-[#1f7f4e] dark:text-gray-200 hover:bg-[#e7f3ec] dark:hover:bg-gray-800 transition-colors whitespace-nowrap">
                                        Mark all
                                    </button>
                                </form>
                            @endif
                            @if ($hasRead)
                                <form method="POST" action="{{ route('users.notifications.clear-read') }}">
                                    @csrf
                                    <button type="submit" class="h-9 px-2.5 sm:px-3 rounded-full text-xs sm:text-sm font-semibold text-[#4e976b] dark:text-gray-400 hover:bg-[#e7f3ec] dark:hover:bg-gray-800 transition-colors whitespace-nowrap">
                                        Clear
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </header>

                <div id="notif-list" class="space-y-2 sm:space-y-2.5">
                    @forelse ($notifications as $notification)
                        @php
                            $rawTitle = (string) ($notification->title ?? '');
                            $rawMessage = trim((string) ($notification->message ?? ''));
                            $titleLower = strtolower($rawTitle);
                            $displayTitle = $rawTitle;
                            $displayMessage = $rawMessage;
                            $icon = 'notifications';
                            $iconBg = 'bg-primary/15 text-[#1f7f4e]';
                            $href = null;

                            if (str_contains($titleLower, 'transaction')) {
                                $displayTitle = 'Transaction received';
                                $displayMessage = 'Check the info in Transactions.';
                                $icon = 'medication';
                                $iconBg = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200';
                                $href = route('users.transaction-records');
                            } elseif (str_contains($titleLower, 'announcement')) {
                                $displayTitle = 'Announcement';
                                $displayMessage = 'Check announcement';
                                $icon = 'campaign';
                                $iconBg = 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200';
                                $href = route('users.announcements');
                            } elseif (str_contains($titleLower, 'maintenance') || str_contains($titleLower, 'monitoring')) {
                                $displayTitle = 'Maintenance received';
                                $displayMessage = 'Check the info in Maintenance.';
                                $icon = 'health_and_safety';
                                $iconBg = 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200';
                                $href = route('users.maintenance-records');
                            } elseif (str_contains($titleLower, 'immunization') || str_contains($titleLower, 'consultation')) {
                                $displayTitle = str_contains($titleLower, 'immunization') ? 'Immunization schedule' : 'Consultation';
                                $displayMessage = \Illuminate\Support\Str::limit($rawMessage, 90);
                                $icon = str_contains($titleLower, 'immunization') ? 'vaccines' : 'event';
                                $iconBg = 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-200';
                                $href = route('users.main');
                            } elseif (str_contains($titleLower, 'approved') || str_contains($titleLower, 'rejected')) {
                                $displayMessage = \Illuminate\Support\Str::limit($rawMessage, 90);
                                $icon = str_contains($titleLower, 'approved') ? 'verified' : 'cancel';
                                $iconBg = str_contains($titleLower, 'approved')
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200'
                                    : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200';
                            } else {
                                $displayMessage = \Illuminate\Support\Str::limit($rawMessage, 90);
                            }

                            $isUnread = is_null($notification->read_at);
                            $readUrl = route('users.notifications.read', $notification);
                            $deleteUrl = route('users.notifications.delete', $notification);
                        @endphp

                        <div
                            class="notif-item relative flex items-start gap-2.5 sm:gap-3 rounded-2xl border border-[#e7f3ec] dark:border-gray-700 bg-white dark:bg-gray-800 p-3 sm:p-3.5 shadow-sm {{ $isUnread ? 'notif-unread' : 'opacity-90' }}"
                            data-id="{{ $notification->id }}"
                            data-read-url="{{ $readUrl }}"
                            data-delete-url="{{ $deleteUrl }}"
                            data-href="{{ $href ?? '' }}"
                            data-unread="{{ $isUnread ? '1' : '0' }}"
                        >
                            <button type="button" class="notif-open min-w-0 flex-1 flex items-start gap-2.5 sm:gap-3 text-left active:opacity-80">
                                <span class="inline-flex size-9 sm:size-10 shrink-0 items-center justify-center rounded-xl {{ $iconBg }}">
                                    <span class="material-symbols-outlined text-[20px] sm:text-[22px]">{{ $icon }}</span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex items-center gap-1.5 flex-wrap">
                                            <p class="text-sm {{ $isUnread ? 'font-bold' : 'font-semibold' }} text-[#0e1b13] dark:text-white leading-snug">{{ $displayTitle }}</p>
                                            @if ($isUnread)
                                                <span class="notif-new-badge inline-flex items-center rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">New</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] sm:text-[11px] font-medium text-[#4e976b] dark:text-gray-400 whitespace-nowrap pt-0.5 shrink-0">{{ $notification->created_at?->diffForHumans(short: true) }}</span>
                                    </div>
                                    @if ($displayMessage !== '')
                                        <p class="mt-1 text-xs sm:text-sm {{ $isUnread ? 'font-medium text-[#1f7f4e]' : 'font-normal text-[#376f50]' }} dark:text-gray-300 leading-snug line-clamp-2">{{ $displayMessage }}</p>
                                    @endif
                                </div>
                            </button>
                            <button type="button" class="notif-delete shrink-0 inline-flex size-8 items-center justify-center rounded-full text-[#4e976b]/70 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" aria-label="Remove notification">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-800/70 px-6 py-14 text-center">
                            <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-[#1f7f4e]">
                                <span class="material-symbols-outlined text-4xl">notifications_none</span>
                            </span>
                            <p class="mt-4 text-base font-semibold text-[#0e1b13] dark:text-white">You're all caught up</p>
                            <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">New updates will show up here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

        @include('partials.users-mobile-nav')
    </div>

    <script>
        (function () {
            const csrfToken = @json($csrfToken);

            const flash = document.getElementById('notif-flash');
            if (flash) {
                const removeFlash = () => {
                    flash.style.opacity = '0';
                    flash.style.transform = 'translateY(-6px)';
                    window.setTimeout(() => flash.remove(), 300);
                };
                document.getElementById('notif-flash-dismiss')?.addEventListener('click', removeFlash);
                window.setTimeout(removeFlash, Number(flash.getAttribute('data-auto-dismiss')) || 2000);
            }

            async function postJson(url, method = 'POST') {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                return response.ok;
            }

            function removeItem(item) {
                item.classList.add('removing');
                window.setTimeout(() => {
                    item.remove();
                    const list = document.getElementById('notif-list');
                    if (list && !list.querySelector('.notif-item')) {
                        list.innerHTML = `
                            <div class="rounded-2xl border border-dashed border-[#d0e7d9] dark:border-gray-700 bg-white/70 dark:bg-gray-800/70 px-6 py-14 text-center">
                                <span class="inline-flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-[#1f7f4e]">
                                    <span class="material-symbols-outlined text-4xl">notifications_none</span>
                                </span>
                                <p class="mt-4 text-base font-semibold text-[#0e1b13] dark:text-white">You're all caught up</p>
                                <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">New updates will show up here.</p>
                            </div>
                        `;
                    }
                }, 280);
            }

            document.querySelectorAll('.notif-item').forEach((item) => {
                item.querySelector('.notif-open')?.addEventListener('click', async () => {
                    if (item.dataset.unread === '1') {
                        await postJson(item.dataset.readUrl);
                        item.dataset.unread = '0';
                        item.classList.remove('notif-unread');
                        item.querySelector('.notif-new-badge')?.remove();
                    }
                    const href = item.dataset.href;
                    if (href) {
                        window.location.href = href;
                        return;
                    }
                    // No linked page: remove after reading so it does not stay forever.
                    await postJson(item.dataset.deleteUrl, 'DELETE');
                    removeItem(item);
                });

                item.querySelector('.notif-delete')?.addEventListener('click', async (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    await postJson(item.dataset.deleteUrl, 'DELETE');
                    removeItem(item);
                });
            });

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
