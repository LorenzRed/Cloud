<!-- Sidebar -->
<aside id="sidebar"
    class="flex flex-col w-64 p-4 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark h-full">
    @php
        $isDashboard = request()->routeIs('bhw.main');
        $isResidents = request()->routeIs('bhw.residents');
        $isRequests = request()->routeIs('bhw.requests');
        $isReports = request()->routeIs('bhw.reports');
        $isEvents = request()->routeIs('bhw.events');
        $isPosts = request()->routeIs('bhw.posts');
        $isSettings = request()->routeIs('bhw.settings');

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
                    <p class="text-sm text-primary/80 dark:text-primary/90">Barangay Health Worker</p>
                </div>
            </div>
            <a class="{{ $isDashboard ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw.main') }}">
                <span class="{{ $isDashboard ? $activeIconClass : $inactiveIconClass }}" @if ($isDashboard) style="font-variation-settings: 'FILL' 1;" @endif>dashboard</span>
                <p class="{{ $isDashboard ? $activeTextClass : $inactiveTextClass }}">Dashboard</p>
            </a>
            <a class="{{ $isResidents ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw.residents') }}">
                <span class="{{ $isResidents ? $activeIconClass : $inactiveIconClass }}" @if ($isResidents) style="font-variation-settings: 'FILL' 1;" @endif>groups</span>
                <p class="{{ $isResidents ? $activeTextClass : $inactiveTextClass }}">Residents</p>
            </a>
            <a class="{{ $isRequests ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw.requests') }}">
                <span class="{{ $isRequests ? $activeIconClass : $inactiveIconClass }}" @if ($isRequests) style="font-variation-settings: 'FILL' 1;" @endif>group_add</span>
                <p class="{{ $isRequests ? $activeTextClass : $inactiveTextClass }}">Requests</p>
                @if (($pendingRequestCount ?? 0) > 0)
                    <span class="ml-auto inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-accent-red text-white text-[10px] font-bold leading-none">
                        {{ $pendingRequestCount }}
                    </span>
                @endif
            </a>
            <a class="{{ $isReports ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw.reports') }}">
                <span class="{{ $isReports ? $activeIconClass : $inactiveIconClass }}" @if ($isReports) style="font-variation-settings: 'FILL' 1;" @endif>assessment</span>
                <p class="{{ $isReports ? $activeTextClass : $inactiveTextClass }}">Reports</p>
            </a>
            <a class="{{ $isEvents ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw.events') }}">
                <span class="{{ $isEvents ? $activeIconClass : $inactiveIconClass }}" @if ($isEvents) style="font-variation-settings: 'FILL' 1;" @endif>event</span>
                <p class="{{ $isEvents ? $activeTextClass : $inactiveTextClass }}">Events</p>
            </a>
            <a class="{{ $isPosts ? $activeLinkClass : $inactiveLinkClass }}" href="{{ route('bhw.posts') }}">
                <span class="{{ $isPosts ? $activeIconClass : $inactiveIconClass }}" @if ($isPosts) style="font-variation-settings: 'FILL' 1;" @endif>sticky_note_2</span>
                <p class="{{ $isPosts ? $activeTextClass : $inactiveTextClass }}">Posts</p>
            </a>
        </div>
        <div class="flex flex-col gap-4">
            <a href="{{ route('bhw.settings') }}"
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