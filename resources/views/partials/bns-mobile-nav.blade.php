@php
    $isDashboard = request()->routeIs('bns.main');
    $isMonitoring = request()->routeIs('bns.monitoring');
    $isVitaminDistribution = request()->routeIs('bns.vitamin-distribution*');
    $isDeworming = request()->routeIs('bns.deworming*');
    $isProfile = request()->routeIs('bns.profile');
@endphp

<nav id="bnsMobileNav" class="bns-mobile-nav lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-border-light dark:border-border-dark bg-card-light/95 dark:bg-card-dark/95 backdrop-blur supports-[backdrop-filter]:bg-card-light/90 dark:supports-[backdrop-filter]:bg-card-dark/90" aria-label="Mobile navigation">
    <div class="grid grid-cols-5 gap-0.5 px-1.5 py-1.5">
        <a href="{{ route('bns.main') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isDashboard ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
            Home
        </a>
        <a href="{{ route('bns.monitoring') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isMonitoring ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isMonitoring) style="font-variation-settings: 'FILL' 1;" @endif>monitoring</span>
            Monitor
        </a>
        <a href="{{ route('bns.vitamin-distribution') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isVitaminDistribution ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isVitaminDistribution) style="font-variation-settings: 'FILL' 1;" @endif>nutrition</span>
            Vit A
        </a>
        <a href="{{ route('bns.deworming') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isDeworming ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isDeworming) style="font-variation-settings: 'FILL' 1;" @endif>medication</span>
            Deworm
        </a>
        <a href="{{ route('bns.profile') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isProfile ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isProfile) style="font-variation-settings: 'FILL' 1;" @endif>person</span>
            Profile
        </a>
    </div>
</nav>

@once
    <style>
        .bns-mobile-nav {
            padding-bottom: env(safe-area-inset-bottom, 0px);
            transition: transform 0.2s ease, opacity 0.2s ease, visibility 0.2s ease;
        }

        @media (max-width: 1023px) {
            body.bns-mobile-input-focused .bns-mobile-nav {
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
                    document.body.classList.remove('bns-mobile-input-focused');
                    return;
                }

                document.body.classList.toggle('bns-mobile-input-focused', isFocusableField(document.activeElement));
            }

            document.addEventListener('focusin', syncMobileNavForKeyboard, true);
            document.addEventListener('focusout', () => window.setTimeout(syncMobileNavForKeyboard, 80), true);
            mobileQuery.addEventListener('change', syncMobileNavForKeyboard);
        })();
    </script>
@endonce
