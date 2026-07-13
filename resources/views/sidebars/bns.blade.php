<!-- Sidebar (desktop) -->
<aside id="bnsSidebar"
    class="hidden lg:flex fixed left-0 top-0 z-20 flex-col w-64 p-4 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark h-screen overflow-y-auto">
    @php
        $isDashboard = request()->routeIs('bns.main');
        $isMonitoring = request()->routeIs('bns.monitoring');
        $isVitaminDistribution = request()->routeIs('bns.vitamin-distribution*');
        $isDeworming = request()->routeIs('bns.deworming*');
        $isReports = request()->routeIs('bns.reports');
        $isSettings = request()->routeIs('bns.settings*');
        $isProfile = request()->routeIs('bns.profile');

        $activeLinkClass = 'flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 dark:bg-primary/20';
        $inactiveLinkClass = 'flex items-center gap-3 px-3 py-2 rounded hover:bg-primary/10 transition-colors';
        $activeIconClass = 'material-symbols-outlined text-primary';
        $inactiveIconClass = 'material-symbols-outlined text-text-light/70 dark:text-text-dark/70';
        $activeTextClass = 'text-primary text-sm font-bold leading-normal';
        $inactiveTextClass = 'text-text-light/70 dark:text-text-dark/70 text-sm font-medium leading-normal';
    @endphp
    <div class="flex items-center gap-2 mb-8">
        <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="w-7 h-7"/>
        <h1 class="text-xl font-bold text-text-light dark:text-text-dark">Bantay Kalusugan</h1>
    </div>
    <div class="flex flex-col justify-between flex-1">
        <div class="flex flex-col gap-2">
            <div class="flex gap-3 items-center border-b border-border-light dark:border-border-dark pb-4 mb-4">
                @php
                    $emailName = strstr(Auth::user()->email ?? '', '@', true) ?: (Auth::user()->name ?? 'U');
                    $lettersOnly = preg_replace('/[^a-zA-Z]/', '', $emailName) ?: 'U';
                    $initials = strtoupper(substr($lettersOnly, 0, 2));
                    $profilePicturePath = Auth::user()->profile_picture_path;
                @endphp
                @if ($profilePicturePath)
                    <img
                        src="{{ route('profile.picture', ['user' => Auth::id(), 'v' => optional(Auth::user()->updated_at)->timestamp]) }}"
                        alt="Profile picture"
                        class="size-10 rounded-full object-cover border border-border-light dark:border-border-dark"
                    />
                @else
                    <div class="flex items-center justify-center rounded-full size-10 bg-green-600 text-white text-sm font-bold" data-alt="Profile initials">
                        {{ $initials }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <h1 class="text-base font-semibold text-text-light dark:text-text-dark">{{ Auth::user()->name }}</h1>
                    <p class="text-sm text-primary/80 dark:text-primary/90">Barangay Nutrition Scheme</p>
                </div>
            </div>
            <a class="{{ $isDashboard ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bns.main') }}">
                <span class="{{ $isDashboard ? $activeIconClass : $inactiveIconClass }}" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
                <p class="{{ $isDashboard ? $activeTextClass : $inactiveTextClass }}">Dashboard</p>
            </a>
            <a class="{{ $isMonitoring ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bns.monitoring') }}">
                <span class="{{ $isMonitoring ? $activeIconClass : $inactiveIconClass }}" @if ($isMonitoring) style="font-variation-settings: 'FILL' 1;" @endif>monitoring</span>
                <p class="{{ $isMonitoring ? $activeTextClass : $inactiveTextClass }}">Monitoring</p>
            </a>
            <a class="{{ $isVitaminDistribution ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bns.vitamin-distribution') }}">
                <span class="{{ $isVitaminDistribution ? $activeIconClass : $inactiveIconClass }}" @if ($isVitaminDistribution) style="font-variation-settings: 'FILL' 1;" @endif>nutrition</span>
                <p class="{{ $isVitaminDistribution ? $activeTextClass : $inactiveTextClass }}">Vitamin Distribution</p>
            </a>
            <a class="{{ $isDeworming ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bns.deworming') }}">
                <span class="{{ $isDeworming ? $activeIconClass : $inactiveIconClass }}" @if ($isDeworming) style="font-variation-settings: 'FILL' 1;" @endif>medication</span>
                <p class="{{ $isDeworming ? $activeTextClass : $inactiveTextClass }}">Deworming</p>
            </a>
            <a class="{{ $isReports ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bns.reports') }}">
                <span class="{{ $isReports ? $activeIconClass : $inactiveIconClass }}" @if ($isReports) style="font-variation-settings: 'FILL' 1;" @endif>assessment</span>
                <p class="{{ $isReports ? $activeTextClass : $inactiveTextClass }}">Reports</p>
            </a>
        </div>
        <div class="flex flex-col gap-4">
            <a href="{{ route('bns.profile') }}"
                class="{{ ($isProfile || $isSettings) ? $activeLinkClass : 'w-full nav-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors duration-200 cursor-pointer text-left' }}"
                data-page="profile"
                aria-label="Open profile">
                <span class="{{ ($isProfile || $isSettings) ? $activeIconClass : 'material-symbols-outlined' }}" @if ($isProfile || $isSettings) style="font-variation-settings: 'FILL' 1;" @endif>person</span>
                <p class="{{ ($isProfile || $isSettings) ? $activeTextClass : 'text-sm font-medium' }}">Profile</p>
            </a>
        </div>
    </div>
</aside>
