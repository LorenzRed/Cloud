@php
    $isDashboard = request()->routeIs('barangay-captain.main') || request()->routeIs('brgcaptain.main');
    $isBhw = request()->routeIs('barangay-captain.bhw-workers*') || request()->routeIs('barangay-captain.puroks*');
    $isBns = request()->routeIs('barangay-captain.bns-workers*');
    $isReports = request()->routeIs('barangay-captain.reports*');
    $isProfile = request()->routeIs('barangay-captain.my-profile') || request()->routeIs('barangay-captain.settings*') || request()->routeIs('barangay-captain.all-records');
@endphp

<nav id="captainMobileNav" class="captain-mobile-nav lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-border-light dark:border-border-dark bg-card-light/95 dark:bg-card-dark/95 backdrop-blur supports-[backdrop-filter]:bg-card-light/90 dark:supports-[backdrop-filter]:bg-card-dark/90" aria-label="Mobile navigation" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <div class="grid grid-cols-5 gap-0.5 px-1 py-1.5">
        <a href="{{ route('barangay-captain.main') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold leading-tight {{ $isDashboard ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
            Home
        </a>
        <a href="{{ route('barangay-captain.bhw-workers') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold leading-tight {{ $isBhw ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isBhw) style="font-variation-settings: 'FILL' 1;" @endif>badge</span>
            BHW
        </a>
        <a href="{{ route('barangay-captain.bns-workers') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold leading-tight {{ $isBns ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isBns) style="font-variation-settings: 'FILL' 1;" @endif>nutrition</span>
            BNS
        </a>
        <a href="{{ route('barangay-captain.reports') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold leading-tight {{ $isReports ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isReports) style="font-variation-settings: 'FILL' 1;" @endif>assessment</span>
            Reports
        </a>
        <a href="{{ route('barangay-captain.my-profile') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] font-semibold leading-tight {{ $isProfile ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isProfile) style="font-variation-settings: 'FILL' 1;" @endif>person</span>
            Profile
        </a>
    </div>
</nav>
