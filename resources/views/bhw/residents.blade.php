<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Patient Masterlist</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<style>
    .material-symbols-outlined {
        font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24
    }
    
    /* Sidebar responsive styles */
    @media (max-width: 768px) {
        #sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            position: fixed;
            z-index: 40;
            height: 100vh;
        }
        
        #sidebar.active {
            transform: translateX(0);
        }
        
        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
        }
        
        #overlay.active {
            display: block;
        }
        
        main {
            width: 100%;
        }
    }
    
    /* Page content styles */
    .page-content {
        height: 100%;
        overflow-y: auto;
    }
    
    /* Custom scrollbar */
    .page-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .page-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .page-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    .page-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .dark .page-content::-webkit-scrollbar-track {
        background: #2c2c2c;
    }
    
    .dark .page-content::-webkit-scrollbar-thumb {
        background: #555;
    }
    
    .dark .page-content::-webkit-scrollbar-thumb:hover {
        background: #777;
    }
    
    /* Ensure main content takes full height */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    
    /* Active navigation item styling */
    .nav-item.active {
        background-color: rgba(74, 144, 226, 0.2);
        color: #4A90E2;
    }
    
    /* Main layout fixes */
    .main-container {
        height: 100vh;
        display: flex;
    }
    
    main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }
    
    .modal.active {
        display: flex;
    }
    
    .modal-content {
        background-color: white;
        border-radius: 0.75rem;
        width: 90%;
        max-width: 1000px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .dark .modal-content {
        background-color: #2C2C2C;
        color: #E0E0E0;
    }
    
    /* Request item styles */
    .request-item {
        transition: all 0.2s ease;
    }
    
    .request-item:hover {
        background-color: rgba(74, 144, 226, 0.05);
    }
    
    .dark .request-item:hover {
        background-color: rgba(74, 144, 226, 0.1);
    }
    
    /* Tabs styling */
    .tab {
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .tab.active {
        color: #4A90E2;
        border-bottom-color: #4A90E2;
        font-weight: 600;
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2",
                    "secondary": "#50E3C2",
                    "accent-orange": "#F5A623",
                    "accent-red": "#D0021B",
                    "background-light": "#F4F4F4",
                    "background-dark": "#1A1A1A",
                    "text-light": "#4A4A4A",
                    "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF",
                    "card-dark": "#2C2C2C",
                    "border-light": "#EAEAEA",
                    "border-dark": "#3D3D3D",
                },
                fontFamily: {
                    "display": ["Manrope", "sans-serif"]
                },
                borderRadius: {
                    "DEFAULT": "0.5rem",
                    "lg": "0.75rem",
                    "xl": "1rem",
                    "full": "9999px"
                },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
<div class="main-container">
    <!-- Mobile menu button -->
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    
    <!-- Overlay for mobile -->
    <div id="overlay" class=""></div>
    
    @include('sidebars.bhw')
    
    <!-- Main content -->
    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Residents Masterlist</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">View and manage all residents in your community.</p>
                    </div>
                    <a href="{{ route('bhw.requests') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 bg-primary text-white rounded-lg text-sm font-bold leading-normal tracking-wide shadow-sm hover:opacity-90">
                        <span class="material-symbols-outlined text-lg">group_add</span>
                        <span class="truncate">Open Requests</span>
                    </a>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <div class="flex flex-col md:flex-row gap-4 mb-4">
                        <div class="relative flex-grow">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                            <input class="w-full h-10 pl-10 pr-4 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-text-light dark:text-text-dark placeholder:text-text-light/50 dark:placeholder:text-text-dark/50" placeholder="Search by name..." type="text"/>
                        </div>
                        <div class="flex gap-2">
                            <select class="h-10 w-full md:w-auto rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-text-light dark:text-text-dark">
                                <option>All Status</option>
                                <option>At Risk</option>
                                <option>Needs Follow-up</option>
                                <option>Normal</option>
                                <option>Inactive</option>
                            </select>
                            <button class="flex items-center justify-center gap-2 h-10 px-4 bg-transparent text-text-light/70 dark:text-text-dark/70 border border-border-light dark:border-border-dark rounded-lg text-sm font-medium hover:bg-background-light dark:hover:bg-background-dark">
                                <span class="material-symbols-outlined text-lg">filter_list</span>
                                <span>Filters</span>
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border-light dark:border-border-dark">
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Patient Name</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Age</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Last Consultation</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Health Status</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($acceptedResidents as $resident)
                                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark">
                                        <td class="h-[72px] px-4 py-2 text-text-light dark:text-text-dark text-sm font-medium">{{ $resident->full_name }}</td>
                                        <td class="h-[72px] px-4 py-2 text-text-light/70 dark:text-text-dark/70 text-sm">{{ $resident->age ?? 'N/A' }}</td>
                                        <td class="h-[72px] px-4 py-2 text-text-light/70 dark:text-text-dark/70 text-sm">{{ $resident->reviewed_at ? $resident->reviewed_at->format('M d, Y') : 'N/A' }}</td>
                                        <td class="h-[72px] px-4 py-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300">Accepted</span>
                                        </td>
                                        <td class="h-[72px] px-4 py-2 text-primary text-sm font-bold text-right">
                                            <a href="{{ route('bhw.residents.profile', $resident) }}" class="hover:underline">View Profile</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="h-[72px] px-4 py-2 text-center">
                                            <div class="py-8">
                                                <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">person_off</span>
                                                <p class="text-text-light/70 dark:text-text-dark/70 text-sm">No accepted residents yet. Review requests to add residents.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <nav aria-label="Pagination" class="flex items-center justify-between pt-4">
                        <div class="hidden sm:block">
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">
                                Showing
                                <span class="font-medium text-text-light dark:text-text-dark">1</span>
                                to
                                <span class="font-medium text-text-light dark:text-text-dark">5</span>
                                of
                                <span class="font-medium text-text-light dark:text-text-dark">42</span>
                                results
                            </p>
                        </div>
                        <div class="flex flex-1 justify-between sm:justify-end gap-2">
                            <a class="relative inline-flex items-center rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-2 text-sm font-medium text-text-light/70 dark:text-text-dark/70 hover:bg-background-light dark:hover:bg-background-dark" href="#">Previous</a>
                            <a class="relative inline-flex items-center rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-2 text-sm font-medium text-text-light/70 dark:text-text-dark/70 hover:bg-background-light dark:hover:bg-background-dark" href="#">Next</a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Toggle sidebar on mobile
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });
    
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
</script>
</body>
</html>