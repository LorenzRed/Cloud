<!-- Sidebar (desktop) -->
<aside id="sidebar"
    class="hidden lg:flex fixed left-0 top-0 z-20 flex-col w-64 p-4 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark h-screen overflow-y-auto">
    @php
        $isDashboard = request()->routeIs('bhw-head.main');
        $isBhwWorkers = request()->routeIs('bhw-head.bhw-workers');
        $isReports = request()->routeIs('bhw-head.reports');
        $isMyProfile = request()->routeIs('bhw-head.my-profile');
        $isSettings = request()->routeIs('bhw-head.settings*');

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
                    <div class="flex items-center justify-center rounded-full size-10 bg-green-600 text-white text-sm font-bold">
                        {{ $initials }}
                    </div>
                @endif
                <div class="flex flex-col">
                    <h1 class="text-base font-semibold text-text-light dark:text-text-dark">{{ Auth::user()->name }}</h1>
                    <p class="text-sm text-primary/80 dark:text-primary/90">BHW Head</p>
                </div>
            </div>
            <a class="{{ $isDashboard ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw-head.main') }}">
                <span class="{{ $isDashboard ? $activeIconClass : $inactiveIconClass }}" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
                <p class="{{ $isDashboard ? $activeTextClass : $inactiveTextClass }}">Dashboard</p>
            </a>
            <a class="{{ $isBhwWorkers ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw-head.bhw-workers') }}">
                <span class="{{ $isBhwWorkers ? $activeIconClass : $inactiveIconClass }}" @if ($isBhwWorkers) style="font-variation-settings: 'FILL' 1;" @endif>badge</span>
                <p class="{{ $isBhwWorkers ? $activeTextClass : $inactiveTextClass }}">Assign BHW</p>
            </a>
            <a class="{{ $isReports ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw-head.reports') }}">
                <span class="{{ $isReports ? $activeIconClass : $inactiveIconClass }}" @if ($isReports) style="font-variation-settings: 'FILL' 1;" @endif>assessment</span>
                <p class="{{ $isReports ? $activeTextClass : $inactiveTextClass }}">Reports</p>
            </a>
        </div>
        <div class="flex flex-col gap-4">
            <a href="{{ route('bhw-head.my-profile') }}"
                class="{{ ($isMyProfile || $isSettings) ? $activeLinkClass : 'w-full nav-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors duration-200 cursor-pointer text-left' }}">
                <span class="{{ ($isMyProfile || $isSettings) ? $activeIconClass : 'material-symbols-outlined' }}" @if ($isMyProfile || $isSettings) style="font-variation-settings: 'FILL' 1;" @endif>person</span>
                <p class="{{ ($isMyProfile || $isSettings) ? $activeTextClass : 'text-sm font-medium' }}">Profile</p>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full nav-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors duration-200 cursor-pointer text-left">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="text-sm font-medium">Logout</p>
                </button>
            </form>
        </div>
    </div>
</aside>
