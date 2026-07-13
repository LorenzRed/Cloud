@php
    $isDashboard = request()->routeIs('bhw.main');
    $isResidents = request()->routeIs('bhw.residents*');
    $isRequests = request()->routeIs('bhw.requests*');
    $isAnnouncements = request()->routeIs('bhw.announcements') || request()->routeIs('bhw.posts');
    $isProfile = request()->routeIs('bhw.my-profile') || request()->routeIs('bhw.settings*');
@endphp

<nav id="bhwMobileNav" class="bhw-mobile-nav lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-border-light dark:border-border-dark bg-card-light/95 dark:bg-card-dark/95 backdrop-blur supports-[backdrop-filter]:bg-card-light/90 dark:supports-[backdrop-filter]:bg-card-dark/90" aria-label="Mobile navigation">
    <div class="grid grid-cols-5 gap-0.5 px-1.5 py-1.5">
        <a href="{{ route('bhw.main') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isDashboard ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
            Home
        </a>
        <a href="{{ route('bhw.residents') }}" class="relative flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isResidents ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isResidents) style="font-variation-settings: 'FILL' 1;" @endif>groups</span>
            Residents
            @if (($residentMessageUnreadCount ?? 0) > 0)
                <span class="absolute top-1 right-2 h-2 w-2 rounded-full bg-accent-red" aria-hidden="true"></span>
            @endif
        </a>
        <a href="{{ route('bhw.requests') }}" class="relative flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isRequests ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isRequests) style="font-variation-settings: 'FILL' 1;" @endif>group_add</span>
            Requests
            @if (($pendingRequestCount ?? 0) > 0)
                <span class="absolute top-0.5 right-1.5 inline-flex min-w-[16px] h-4 items-center justify-center rounded-full bg-accent-red px-1 text-[9px] font-bold text-white">{{ ($pendingRequestCount ?? 0) > 9 ? '9+' : $pendingRequestCount }}</span>
            @endif
        </a>
        <a href="{{ route('bhw.announcements') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isAnnouncements ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isAnnouncements) style="font-variation-settings: 'FILL' 1;" @endif>campaign</span>
            News
        </a>
        <a href="{{ route('bhw.my-profile') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isProfile ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isProfile) style="font-variation-settings: 'FILL' 1;" @endif>person</span>
            Profile
        </a>
    </div>
</nav>

@once
    <style>
        .bhw-mobile-nav {
            padding-bottom: env(safe-area-inset-bottom, 0px);
            transition: transform 0.2s ease, opacity 0.2s ease, visibility 0.2s ease;
        }

        @media (max-width: 1023px) {
            body.bhw-mobile-input-focused .bhw-mobile-nav {
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
                    document.body.classList.remove('bhw-mobile-input-focused');
                    return;
                }

                document.body.classList.toggle('bhw-mobile-input-focused', isFocusableField(document.activeElement));
            }

            document.addEventListener('focusin', syncMobileNavForKeyboard, true);
            document.addEventListener('focusout', () => window.setTimeout(syncMobileNavForKeyboard, 80), true);
            mobileQuery.addEventListener('change', syncMobileNavForKeyboard);
        })();
    </script>
@endonce
