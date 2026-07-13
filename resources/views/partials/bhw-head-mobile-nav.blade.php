@php
    $isDashboard = request()->routeIs('bhw-head.main');
    $isWorkers = request()->routeIs('bhw-head.bhw-workers*') || request()->routeIs('bhw-head.puroks*');
    $isReports = request()->routeIs('bhw-head.reports');
    $isProfile = request()->routeIs('bhw-head.my-profile') || request()->routeIs('bhw-head.settings*');
@endphp

<nav id="bhwHeadMobileNav" class="bhw-mobile-nav lg:hidden fixed inset-x-0 bottom-0 z-40 border-t border-border-light dark:border-border-dark bg-card-light/95 dark:bg-card-dark/95 backdrop-blur supports-[backdrop-filter]:bg-card-light/90 dark:supports-[backdrop-filter]:bg-card-dark/90" aria-label="Mobile navigation">
    <div class="grid grid-cols-4 gap-0.5 px-1.5 py-1.5">
        <a href="{{ route('bhw-head.main') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isDashboard ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
            Home
        </a>
        <a href="{{ route('bhw-head.bhw-workers') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isWorkers ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isWorkers) style="font-variation-settings: 'FILL' 1;" @endif>badge</span>
            Assign BHW
        </a>
        <a href="{{ route('bhw-head.reports') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isReports ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isReports) style="font-variation-settings: 'FILL' 1;" @endif>assessment</span>
            Reports
        </a>
        <a href="{{ route('bhw-head.my-profile') }}" class="flex flex-col items-center justify-center rounded-lg py-1.5 text-[10px] sm:text-[11px] font-semibold leading-tight {{ $isProfile ? 'text-primary bg-primary/10' : 'text-text-light/70 dark:text-text-dark/70' }}">
            <span class="material-symbols-outlined text-[20px]" @if ($isProfile) style="font-variation-settings: 'FILL' 1;" @endif>person</span>
            Profile
        </a>
    </div>
</nav>
