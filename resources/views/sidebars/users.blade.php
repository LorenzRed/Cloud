<style>
    #usersSidebar {
        transition: width 0.25s ease;
    }

    @media (min-width: 1024px) {
        #usersSidebar.is-collapsed {
            width: 5rem;
        }

        #usersSidebar.is-collapsed .sidebar-label,
        #usersSidebar.is-collapsed .sidebar-brand-text {
            display: none;
        }

        #usersSidebar.is-collapsed .sidebar-link,
        #usersSidebar.is-collapsed .sidebar-logout {
            justify-content: center;
        }

        #usersSidebar.is-collapsed .sidebar-header-inner {
            justify-content: center;
        }

        #usersSidebar:not(.is-collapsed) #hamburgerIcon {
            opacity: 0;
            transform: scale(0) rotate(90deg);
        }

        #usersSidebar:not(.is-collapsed) #closeIcon {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }

        #usersSidebar.is-collapsed #hamburgerIcon {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }

        #usersSidebar.is-collapsed #closeIcon {
            opacity: 0;
            transform: scale(0) rotate(-90deg);
        }
    }
</style>

<aside id="usersSidebar" class="hidden lg:flex fixed left-0 top-0 h-screen w-64 shrink-0 overflow-y-auto overflow-x-hidden bg-white dark:bg-gray-800 border-r border-[#e7f3ec] dark:border-gray-700 flex-col z-20">
    @php
        $isDashboard = request()->routeIs('users.main');
        $isAnnouncements = request()->routeIs('users.announcements');
        $isProfile = request()->routeIs('users.profile');
        $isNotifications = request()->routeIs('users.notifications');
        $isMaintenanceRecords = request()->routeIs('users.maintenance-records');
        $isTransactionRecords = request()->routeIs('users.transaction-records');

        $activeLinkClass = 'flex items-center gap-3 rounded-lg px-3 py-2.5 bg-primary/15 text-[#0e1b13] dark:text-white font-semibold';
        $inactiveLinkClass = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-[#376f50] dark:text-gray-300 hover:bg-[#f2fbf6] dark:hover:bg-gray-700 transition-colors';
    @endphp

    <div class="px-4 py-5 border-b border-[#e7f3ec] dark:border-gray-700">
        <div class="sidebar-header-inner flex items-center gap-2 text-[#0e1b13] dark:text-white">
            <div class="flex items-center gap-3 min-w-0">
            <img src="{{ asset('logo/logo.png') }}" alt="Bantay Kalusugan logo" class="h-8 w-auto object-contain" />
            <div class="sidebar-brand-text">
                <h2 class="text-base font-bold leading-tight">Bantay Kalusugan</h2>
                <p class="text-xs text-[#4e976b] dark:text-gray-400">Resident Portal</p>
            </div>
            </div>
        </div>
    </div>

    <button id="usersSidebarToggle" type="button" class="fixed top-6 inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#d0e7d9] dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-bold hover:bg-[#f2fbf6] dark:hover:bg-gray-700 transition-all duration-300 shadow-sm z-30 overflow-hidden" style="left: 16rem; transform: translateX(-40%);" aria-label="Toggle sidebar">
        <!-- Hamburger icon (3 lines) -->
        <svg id="hamburgerIcon" class="w-5 h-5" style="transition: all 400ms cubic-bezier(0.34, 1.56, 0.64, 1); transform: scale(1) rotate(0deg); opacity: 1;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <!-- X icon -->
        <svg id="closeIcon" class="w-5 h-5 absolute" style="transition: all 400ms cubic-bezier(0.34, 1.56, 0.64, 1); transform: scale(0) rotate(-90deg); opacity: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <nav class="flex-1 px-4 py-5 space-y-2">
        <a href="{{ route('users.main') }}" class="sidebar-link {{ $isDashboard ? $activeLinkClass : $inactiveLinkClass }}">
            <span class="material-symbols-outlined text-[20px]">home</span>
            <span class="sidebar-label">Home</span>
        </a>
        <a href="{{ route('users.announcements') }}" class="sidebar-link {{ $isAnnouncements ? $activeLinkClass : $inactiveLinkClass }}">
            <span class="material-symbols-outlined text-[20px]">campaign</span>
            <span class="sidebar-label">Announcements</span>
        </a>
        <a href="{{ route('users.transaction-records') }}" class="sidebar-link {{ $isTransactionRecords ? $activeLinkClass : $inactiveLinkClass }}">
            <span class="material-symbols-outlined text-[20px]">medication</span>
            <span class="sidebar-label">Transactions</span>
        </a>
        <a href="{{ route('users.maintenance-records') }}" class="sidebar-link {{ $isMaintenanceRecords ? $activeLinkClass : $inactiveLinkClass }}">
            <span class="material-symbols-outlined text-[20px]">health_and_safety</span>
            <span class="sidebar-label">Maintenance</span>
        </a>
        <a href="{{ route('users.profile') }}" class="sidebar-link {{ $isProfile ? $activeLinkClass : $inactiveLinkClass }}">
            <span class="material-symbols-outlined text-[20px]">person</span>
            <span class="sidebar-label">Profile</span>
        </a>
        <a href="{{ route('users.notifications') }}" class="sidebar-link {{ $isNotifications ? $activeLinkClass : $inactiveLinkClass }}">
            <span class="material-symbols-outlined text-[20px]">notifications</span>
            <span class="sidebar-label">Notifications</span>
        </a>
    </nav>
</aside>
