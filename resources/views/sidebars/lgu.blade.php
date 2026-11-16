<!-- Sidebar -->
<aside id="sidebar"
    class="flex flex-col w-64 p-4 bg-card-light dark:bg-card-dark border-r border-border-light dark:border-border-dark h-screen">
    @php
        $navBaseClass = 'w-full flex items-center gap-3 px-3 py-2 rounded-lg transition-colors duration-200';
        $activeClass = 'bg-primary/10 dark:bg-primary/20';
        $inactiveClass = 'hover:bg-primary/10';
        $activeTextClass = 'text-primary';
        $inactiveTextClass = 'text-text-light/70 dark:text-text-dark/70';
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
                    <p class="text-sm text-primary/80 dark:text-primary/90">Local Government Unit</p>
                </div>
            </div>
            <a class="{{ $navBaseClass }} {{ request()->routeIs('lgu.main') ? $activeClass : $inactiveClass }}" href="{{ route('lgu.main') }}">
                <span class="material-symbols-outlined {{ request()->routeIs('lgu.main') ? $activeTextClass : $inactiveTextClass }}" style="font-variation-settings: 'FILL' 1;">dashboard</span>
                <p class="{{ request()->routeIs('lgu.main') ? $activeTextClass : $inactiveTextClass }} text-sm font-medium leading-normal">Dashboard</p>
            </a>
            <a class="{{ $navBaseClass }} {{ request()->routeIs('lgu.barangays') ? $activeClass : $inactiveClass }}" href="{{ route('lgu.barangays') }}">
                <span class="material-symbols-outlined {{ request()->routeIs('lgu.barangays') ? $activeTextClass : $inactiveTextClass }}" style="font-variation-settings: 'FILL' {{ request()->routeIs('lgu.barangays') ? 1 : 0 }};">location_city</span>
                <p class="{{ request()->routeIs('lgu.barangays') ? $activeTextClass : $inactiveTextClass }} text-sm font-medium leading-normal">Barangays</p>
            </a>
            <a class="{{ $navBaseClass }} {{ request()->routeIs('lgu.system-logs') ? $activeClass : $inactiveClass }}" href="{{ route('lgu.system-logs') }}">
                <span class="material-symbols-outlined {{ request()->routeIs('lgu.system-logs') ? $activeTextClass : $inactiveTextClass }}">history</span>
                <p class="text-sm font-medium">System Logs</p>
            </a>
            <a class="{{ $navBaseClass }} {{ request()->routeIs('lgu.deletion-log*') ? $activeClass : $inactiveClass }}" href="{{ route('lgu.deletion-log') }}">
                <span class="material-symbols-outlined">restore_from_trash</span>
                <p class="text-sm font-medium">Deletion Log</p>
            </a>
            <a class="{{ $navBaseClass }} {{ request()->routeIs('lgu.reports') ? $activeClass : $inactiveClass }}" href="{{ route('lgu.reports') }}">
                <span class="material-symbols-outlined {{ request()->routeIs('lgu.reports') ? $activeTextClass : $inactiveTextClass }}">bar_chart</span>
                <p class="{{ request()->routeIs('lgu.reports') ? $activeTextClass : $inactiveTextClass }} text-sm font-medium leading-normal">System Error Reports</p>
            </a>
        </div>
        <div class="flex flex-col gap-4 mt-6 pt-4 border-t border-border-light dark:border-border-dark">
            <a href="{{ route('lgu.main') }}" class="{{ $navBaseClass }} {{ $inactiveClass }} {{ $inactiveTextClass }}">
                <span class="material-symbols-outlined">settings</span>
                <p class="text-sm font-medium">Settings</p>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="{{ $navBaseClass }} {{ $inactiveClass }} {{ $inactiveTextClass }} cursor-pointer text-left"
                    data-page="logout">
                    <span class="material-symbols-outlined">logout</span>
                    <p class="text-sm font-medium">Logout</p>
                </button>
            </form>
        </div>
    </div>
</aside>
