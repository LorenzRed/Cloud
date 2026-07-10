<!-- Sidebar -->
<aside id="sidebar"
    class="flex flex-col w-64 p-4 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark h-full">
    @php
        $isDashboard = request()->routeIs('barangay-captain.main') || request()->routeIs('brgcaptain.main');
        $isResidents = request()->routeIs('barangay-captain.residents');
        $isReports = request()->routeIs('barangay-captain.reports');
        $isBhwWorkers = request()->routeIs('barangay-captain.bhw-workers');
        $isBnsWorkers = request()->routeIs('barangay-captain.bns-workers');
        $isSettings = request()->routeIs('barangay-captain.settings');

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
                    <p class="text-sm text-primary/80 dark:text-primary/90">Barangay Captain</p>
                </div>
            </div>
            <a class="{{ $isDashboard ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('barangay-captain.main') }}">
                <span class="{{ $isDashboard ? $activeIconClass : $inactiveIconClass }}" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
                <p class="{{ $isDashboard ? $activeTextClass : $inactiveTextClass }}">Dashboard</p>
            </a>
            <a class="{{ $isBhwWorkers ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('barangay-captain.bhw-workers') }}">
                <span class="{{ $isBhwWorkers ? $activeIconClass : $inactiveIconClass }}" @if ($isBhwWorkers) style="font-variation-settings: 'FILL' 1;" @endif>badge</span>
                <p class="{{ $isBhwWorkers ? $activeTextClass : $inactiveTextClass }}">BHW Workers</p>
            </a>
            <a class="{{ $isBnsWorkers ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('barangay-captain.bns-workers') }}">
                <span class="{{ $isBnsWorkers ? $activeIconClass : $inactiveIconClass }}" @if ($isBnsWorkers) style="font-variation-settings: 'FILL' 1;" @endif>nutrition</span>
                <p class="{{ $isBnsWorkers ? $activeTextClass : $inactiveTextClass }}">BNS Workers</p>
            </a>
            <a class="{{ $isResidents ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('barangay-captain.residents') }}">
                <span class="{{ $isResidents ? $activeIconClass : $inactiveIconClass }}" @if ($isResidents) style="font-variation-settings: 'FILL' 1;" @endif>groups</span>
                <p class="{{ $isResidents ? $activeTextClass : $inactiveTextClass }}">Residents</p>
            </a>
            <a class="{{ $isReports ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('barangay-captain.reports') }}">
                <span class="{{ $isReports ? $activeIconClass : $inactiveIconClass }}" @if ($isReports) style="font-variation-settings: 'FILL' 1;" @endif>assessment</span>
                <p class="{{ $isReports ? $activeTextClass : $inactiveTextClass }}">Reports</p>
            </a>
        </div>
        <div class="flex flex-col gap-4">
            <a href="{{ route('barangay-captain.settings') }}"
                class="{{ $isSettings ? $activeLinkClass : 'w-full nav-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors duration-200 cursor-pointer text-left' }}"
                data-page="settings"
                aria-label="Open settings">
                <span class="{{ $isSettings ? $activeIconClass : 'material-symbols-outlined' }}" @if ($isSettings) style="font-variation-settings: 'FILL' 1;" @endif>settings</span>
                <p class="{{ $isSettings ? $activeTextClass : 'text-sm font-medium' }}">Settings</p>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full nav-item flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors duration-200 cursor-pointer text-left"
                    data-page="logout">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="text-sm font-medium">Logout</p>
                </button>
            </form>
        </div>
    </div>
</aside>
