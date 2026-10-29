<!DOCTYPE html>
<html class="light" lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8" />
    @include('partials.users-viewport')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Bantay Kalusugan - Barangay Health Worker</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <style>
        html, body { height: 100%; overflow: hidden; }
        #usersSidebar { transition: width 0.25s ease; }
        @media (min-width: 1024px) {
            #usersSidebar.is-collapsed { width: 5rem; }
            #usersSidebar.is-collapsed .sidebar-label, #usersSidebar.is-collapsed .sidebar-brand-text { display: none; }
            #usersSidebar.is-collapsed .sidebar-link, #usersSidebar.is-collapsed .sidebar-logout { justify-content: center; }
            #usersSidebar.is-collapsed .sidebar-header-inner { justify-content: center; }
        }

        .users-messenger-shell {
            min-height: 0;
            background: #fff;
        }
        .dark .users-messenger-shell {
            background: rgb(17 33 23);
        }
        .users-messenger-header {
            box-shadow: 0 1px 0 rgb(0 0 0 / 0.06);
        }
        .dark .users-messenger-header {
            box-shadow: 0 1px 0 rgb(255 255 255 / 0.06);
        }
        .users-messenger-chat-body .chat-bg {
            background-color: #eef2ef;
            background-image: none;
        }
        .dark .users-messenger-chat-body .chat-bg {
            background-color: rgb(15 28 20);
        }
        .users-messenger-chat-body .rbhw-chat-scroll {
            padding-top: 2.5rem !important;
            padding-bottom: 0.75rem !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .users-messenger-chat-body > .shrink-0 {
            background: #fff;
            border-top-color: #e7ede9 !important;
            padding: 0.5rem 0.625rem 0.625rem !important;
        }
        .dark .users-messenger-chat-body > .shrink-0 {
            background: rgb(17 33 23);
            border-top-color: rgb(55 65 81) !important;
        }
        .users-messenger-chat-body form {
            align-items: center;
            gap: 0.375rem;
            background: #f0f4f1;
            border-radius: 1.5rem;
            padding: 0.3125rem 0.375rem 0.3125rem 0.5rem;
        }
        .dark .users-messenger-chat-body form {
            background: rgb(31 41 55);
        }
        .users-messenger-chat-body .rbhw-media-btn {
            height: 2.25rem;
            width: 2.25rem;
            border: none !important;
            background: transparent !important;
            border-radius: 9999px;
        }
        .users-messenger-chat-body textarea {
            min-height: 2.25rem !important;
            max-height: 6.5rem;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }
        .users-messenger-chat-body textarea:focus {
            outline: none !important;
        }
        .users-messenger-chat-body button[type="submit"] {
            height: 2.375rem;
            width: 2.375rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        @media (max-width: 1023px) {
            .users-bhw-chat-page #usersSidebar { display: none; }
            .users-bhw-chat-page #usersMobileNav { display: none !important; }
            .users-bhw-chat-page #usersContent {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .users-bhw-chat-page .users-bhw-chat-main {
                padding: 0 !important;
            }
            .users-messenger-shell {
                min-height: 0;
                height: 100%;
            }
            .users-messenger-header {
                padding-top: max(0.5rem, env(safe-area-inset-top, 0px));
            }
            .users-messenger-chat-body > .shrink-0 {
                padding-bottom: calc(0.625rem + env(safe-area-inset-bottom, 0px)) !important;
            }
            .users-messenger-empty {
                min-height: 0;
                height: 100%;
                padding-bottom: env(safe-area-inset-bottom, 0px);
            }
        }

        @media (min-width: 1024px) {
            #usersSidebarToggle {
                z-index: 100;
            }
            .users-bhw-chat-page {
                background: #fff;
            }
            .dark .users-bhw-chat-page {
                background: rgb(17 33 23);
            }
            .users-bhw-chat-page #usersContent {
                background: #fff;
            }
            .dark .users-bhw-chat-page #usersContent {
                background: rgb(17 33 23);
            }
            .users-bhw-chat-page .users-bhw-chat-main {
                padding: 0 !important;
            }
            .users-messenger-shell,
            .users-messenger-empty {
                flex: 1;
                width: 100%;
                max-width: none;
                margin: 0;
                border: none;
                border-radius: 0;
                box-shadow: none;
                min-height: 100%;
            }
            .users-messenger-header {
                padding: 0.875rem 1.25rem;
            }
            .users-messenger-chat-body .rbhw-chat-scroll {
                padding-left: 1.25rem !important;
                padding-right: 1.25rem !important;
                padding-top: 3rem !important;
            }
            .users-messenger-chat-body > .shrink-0 {
                padding: 0.75rem 1.25rem 1rem !important;
            }
            .users-messenger-chat-body form {
                padding: 0.375rem 0.5rem 0.375rem 0.625rem;
            }
            .users-messenger-chat-body .rbhw-media-btn,
            .users-messenger-chat-body button[type="submit"] {
                height: 2.5rem;
                width: 2.5rem;
            }
            .users-messenger-chat-body textarea {
                min-height: 2.5rem !important;
                font-size: 0.9375rem;
            }
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#19e66b", "background-light": "#f6f8f7", "background-dark": "#112117" },
                    fontFamily: { "display": ["Public Sans", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="users-bhw-chat-page font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
@php
    $purokLabel = $user->purok?->name ?? $user->residentRequest?->purok?->name ?? 'Your purok';
    $userInitials = collect(explode(' ', $user->full_name ?: $user->name))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('') ?: 'Y';

    $previousUrl = url()->previous();
    $currentUrl = url()->current();
    $bhwChatBackUrl = route('users.main');
    if ($previousUrl && $previousUrl !== $currentUrl) {
        $previousPath = (string) (parse_url($previousUrl, PHP_URL_PATH) ?? '');
        if (str_starts_with($previousPath, '/users') && ! str_contains($previousPath, '/bhw-chat')) {
            $bhwChatBackUrl = $previousUrl;
        }
    }
@endphp
@include('sidebars.users')

<div id="usersContent" data-chat-keyboard-shell class="flex h-[100dvh] h-[100svh] lg:h-screen flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] transition-all duration-200 overflow-hidden">
    <main class="users-bhw-chat-main flex-1 overflow-hidden flex flex-col min-h-0">
        @if (! $bhw)
            <section class="users-messenger-empty relative flex-1 flex flex-col min-h-0">
                <header class="users-messenger-header shrink-0 flex items-center gap-2.5 px-2 lg:px-4 pb-2.5 lg:pb-3 bg-white dark:bg-gray-900 border-b border-[#e7f3ec] dark:border-gray-800">
                    <a href="{{ $bhwChatBackUrl }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-[#0e1b13] dark:text-white hover:bg-[#f0f4f1] dark:hover:bg-gray-800 transition shrink-0" aria-label="Go back">
                        <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                    </a>
                    <div class="min-w-0">
                        <p class="font-bold text-[15px] lg:text-base leading-tight text-[#0e1b13] dark:text-white">Barangay Health Worker</p>
                        <p class="text-xs text-[#4e976b] dark:text-gray-400 mt-0.5">Not assigned</p>
                    </div>
                </header>
                <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                    <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-500 mb-3">person_off</span>
                    <p class="font-bold text-lg">No health worker assigned yet</p>
                    <p class="text-sm text-[#4e976b] dark:text-gray-400 mt-2 max-w-md">A Barangay Health Worker will appear here once one is assigned to your purok.</p>
                </div>
            </section>
        @else
            <section class="users-messenger-shell flex-1 min-h-0 flex flex-col overflow-hidden">
                <header class="users-messenger-header shrink-0 flex items-center gap-2.5 px-2 lg:px-5 pb-2.5 lg:py-3 bg-white dark:bg-gray-900 border-b border-[#e7f3ec] dark:border-gray-800">
                    <a href="{{ $bhwChatBackUrl }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-[#0e1b13] dark:text-white hover:bg-[#f0f4f1] dark:hover:bg-gray-800 transition shrink-0" aria-label="Go back">
                        <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                    </a>
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="h-10 w-10 lg:h-11 lg:w-11 rounded-full bg-gradient-to-br from-primary/30 to-primary/10 flex items-center justify-center shrink-0 ring-2 ring-white dark:ring-gray-900 shadow-sm">
                            <span class="material-symbols-outlined text-[#1f7f4e] dark:text-primary text-[22px] lg:text-[24px]">local_hospital</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-[15px] lg:text-base leading-tight truncate text-[#0e1b13] dark:text-white">Barangay Health Worker</p>
                            <p class="text-xs lg:text-sm text-[#4e976b] dark:text-gray-400 truncate flex items-center gap-1.5 mt-0.5">
                                <span class="inline-block h-2 w-2 rounded-full bg-primary shrink-0"></span>
                                <span>{{ $purokLabel }}</span>
                            </p>
                        </div>
                    </div>
                </header>

                <div class="users-messenger-chat-body flex flex-col flex-1 min-h-0">
                    @include('partials.resident-bhw-chat', [
                        'messages' => $messages,
                        'messageStoreUrl' => route('users.profile.bhw-chat.message'),
                        'messageActionUrl' => fn ($id) => route('users.profile.bhw-chat.message.update', ['residentBhwMessage' => $id]),
                        'messageReactionUrl' => fn ($id) => route('users.profile.bhw-chat.message.reaction', ['residentBhwMessage' => $id]),
                        'myInitials' => $userInitials,
                        'peerInitials' => 'BH',
                        'peerName' => 'Barangay Health Worker',
                        'peerLabel' => 'Barangay Health Worker',
                        'theme' => 'users',
                        'hidePeerAvatar' => true,
                        'unreadMessageIds' => $unreadBhwMessageIds ?? [],
                        'showMessengerIntro' => true,
                        'composerPlaceholder' => 'Aa',
                        'emptyTitle' => $purokLabel,
                        'emptySubtitle' => 'Message your Barangay Health Worker about health concerns or follow-ups.',
                        'chatMessagesId' => 'usersBhwChatMessages',
                        'chatFormId' => 'usersBhwChatForm',
                        'chatInputId' => 'usersBhwChatInput',
                        'chatSendBtnId' => 'usersBhwChatSendBtn',
                    ])
                </div>
            </section>
        @endif
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

        applySidebarState(localStorage.getItem(storageKey) === '1');

        toggleButton.addEventListener('click', () => {
            const nextCollapsed = !sidebar.classList.contains('is-collapsed');
            applySidebarState(nextCollapsed);
            localStorage.setItem(storageKey, nextCollapsed ? '1' : '0');
        });
    })();
</script>
@include('partials.mobile-chat-keyboard')
</body>
</html>
