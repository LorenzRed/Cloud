@php
    $isDashboard = request()->routeIs('users.main');
    $isAnnouncements = request()->routeIs('users.announcements*');
    $isProfile = request()->routeIs('users.profile*');
    $isTransactionRecords = request()->routeIs('users.transaction-records');
    $isMaintenanceRecords = request()->routeIs('users.maintenance-records');
@endphp

<nav id="usersMobileNav" class="users-mobile-nav lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-[#d0e7d9] bg-white/95 backdrop-blur dark:border-gray-700 dark:bg-gray-800/95" aria-label="Mobile navigation">
    <div class="grid grid-cols-5 gap-0.5 px-1 py-2">
        <a href="{{ route('users.main') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold {{ $isDashboard ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
            <span class="material-symbols-outlined text-[20px]">home</span>
            Home
        </a>
        <a href="{{ route('users.announcements') }}" class="relative flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold {{ $isAnnouncements ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
            <span class="relative material-symbols-outlined text-[20px]">
                campaign
                @if (($unreadAnnouncementCount ?? 0) > 0)
                    <span class="absolute -top-0.5 -right-1 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800" aria-hidden="true"></span>
                @endif
            </span>
            Announce
        </a>
        <a href="{{ route('users.transaction-records') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold {{ $isTransactionRecords ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
            <span class="material-symbols-outlined text-[20px]">medication</span>
            Transact
        </a>
        <a href="{{ route('users.maintenance-records') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold {{ $isMaintenanceRecords ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
            <span class="material-symbols-outlined text-[20px]">health_and_safety</span>
            Maintain
        </a>
        <a href="{{ route('users.profile') }}" class="relative flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold {{ $isProfile ? 'text-[#0e1b13] dark:text-white bg-primary/15' : 'text-[#4e976b] dark:text-gray-300' }}">
            <span class="relative material-symbols-outlined text-[20px]">
                person
                @if (($unreadBhwChatCount ?? 0) > 0)
                    <span class="absolute -top-0.5 -right-1 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800" aria-hidden="true"></span>
                @endif
            </span>
            Profile
        </a>
    </div>
</nav>

@once
    <style>
        .users-mobile-nav {
            padding-bottom: env(safe-area-inset-bottom, 0px);
            transition: transform 0.2s ease, opacity 0.2s ease, visibility 0.2s ease;
        }

        @media (max-width: 1023px) {
            body.users-mobile-input-focused .users-mobile-nav {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(100%);
            }
        }
    </style>
    <script>
        (function () {
            const mobileQuery = window.matchMedia('(max-width: 1023px)');
            const focusableSelector = 'input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea, select, [contenteditable="true"]';

            function isFocusableField(element) {
                return Boolean(element && element.matches && element.matches(focusableSelector));
            }

            function syncMobileNavForKeyboard() {
                if (!mobileQuery.matches) {
                    document.body.classList.remove('users-mobile-input-focused');
                    return;
                }

                document.body.classList.toggle('users-mobile-input-focused', isFocusableField(document.activeElement));
            }

            document.addEventListener('focusin', syncMobileNavForKeyboard, true);
            document.addEventListener('focusout', () => window.setTimeout(syncMobileNavForKeyboard, 80), true);
            mobileQuery.addEventListener('change', syncMobileNavForKeyboard);
        })();
    </script>
@endonce
