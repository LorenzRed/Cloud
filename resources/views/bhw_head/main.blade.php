<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - BHW Head Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
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
@include('partials.app-flash')
<div class="main-container">
    <!-- Mobile menu button -->
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    
    <!-- Overlay for mobile -->
    <div id="overlay" class=""></div>
    
    @include('sidebars.bhw_head')
    
    <!-- Main content -->
    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">BHW Head Dashboard</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Monitor health records across your assigned BHW team</p>
                    </div>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    @if (isset($assignedBarangay) && $assignedBarangay)
                        @php
                            $dashboardStats = $dashboardStats ?? [];
                            $recentHealthUpdates = $recentHealthUpdates ?? [];
                            $monthlyIllnessTrend = $monthlyIllnessTrend ?? [
                                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                                'values' => [0, 0, 0, 0, 0, 0],
                            ];
                            $vaccinationDistribution = $vaccinationDistribution ?? [
                                'vaccinated' => (int) ($dashboardStats['vaccinated_residents'] ?? 0),
                                'unvaccinated' => (int) ($dashboardStats['total_registered_residents'] ?? 0) - (int) ($dashboardStats['vaccinated_residents'] ?? 0),
                            ];
                            if (($vaccinationDistribution['unvaccinated'] ?? 0) < 0) {
                                $vaccinationDistribution['unvaccinated'] = 0;
                            }
                            $ageDistribution = $ageDistribution ?? [
                                'labels' => ['0-17', '18-35', '36-59', '60+'],
                                'values' => [0, 0, 0, 0],
                            ];
                            $statCards = [
                                ['label' => 'Total registered residents', 'key' => 'total_registered_residents'],
                                ['label' => 'Total households', 'key' => 'total_households'],
                                ['label' => 'Active illness cases', 'key' => 'active_illness_cases'],
                                ['label' => 'Malnutrition cases', 'key' => 'malnutrition_cases'],
                                ['label' => 'Vaccinated residents', 'key' => 'vaccinated_residents'],
                                ['label' => 'Pregnant women', 'key' => 'pregnant_women'],
                                ['label' => 'Senior citizens', 'key' => 'senior_citizens'],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4 md:col-span-2">
                                <p class="text-sm text-text-light/60 dark:text-text-dark/60 mb-1">Assigned Barangay</p>
                                <p class="text-2xl font-bold">{{ $assignedBarangay->name }}</p>
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">Team coverage for {{ number_format((int) ($managedBhwCount ?? 0)) }} BHW worker(s).</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                            @foreach ($statCards as $card)
                                <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                    <p class="text-sm text-text-light/60 dark:text-text-dark/60 mb-1">{{ $card['label'] }}</p>
                                    <p class="text-2xl font-bold">{{ number_format((int) ($dashboardStats[$card['key']] ?? 0)) }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4 xl:col-span-2">
                                <h2 class="text-xl font-bold mb-4">Line graph – Monthly illness trend</h2>
                                <div class="h-72">
                                    <canvas id="monthlyIllnessTrendChart"></canvas>
                                </div>
                            </div>

                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                <h2 class="text-xl font-bold mb-4">Pie chart – Vaccinated vs unvaccinated</h2>
                                <div class="h-72">
                                    <canvas id="vaccinationPieChart"></canvas>
                                </div>
                            </div>

                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                <h2 class="text-xl font-bold mb-4">Bar chart – Age distribution</h2>
                                <div class="h-72">
                                    <canvas id="ageDistributionBarChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-4">Recent health updates</h2>

                            @forelse ($recentHealthUpdates as $update)
                                @php
                                    $title = is_array($update) ? ($update['title'] ?? 'Health update') : ($update->title ?? 'Health update');
                                    $details = is_array($update)
                                        ? ($update['summary'] ?? $update['description'] ?? 'No details available.')
                                        : ($update->summary ?? $update->description ?? 'No details available.');
                                    $createdAt = is_array($update) ? ($update['created_at'] ?? null) : ($update->created_at ?? null);
                                @endphp

                                <div class="py-3 @if (! $loop->last) border-b border-border-light dark:border-border-dark @endif">
                                    <p class="font-semibold">{{ $title }}</p>
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">{{ $details }}</p>
                                    @if ($createdAt)
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">{{ $createdAt }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-text-light/70 dark:text-text-dark/70">No recent health updates available.</p>
                            @endforelse
                        </div>
                    @else
                        <div class="flex items-center justify-center h-96">
                            <p class="text-text-light/70 dark:text-text-dark/70 text-lg">No barangay assigned yet. Please contact LGU for assignment.</p>
                        </div>
                    @endif
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
    const monthlyIllnessTrend = @json($monthlyIllnessTrend ?? ['labels' => [], 'values' => []]);
    const vaccinationDistribution = @json($vaccinationDistribution ?? ['vaccinated' => 0, 'unvaccinated' => 0]);
    const ageDistribution = @json($ageDistribution ?? ['labels' => [], 'values' => []]);
    
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });
    
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    if (typeof Chart !== 'undefined') {
        const lineCanvas = document.getElementById('monthlyIllnessTrendChart');
        if (lineCanvas) {
            new Chart(lineCanvas, {
                type: 'line',
                data: {
                    labels: monthlyIllnessTrend.labels || [],
                    datasets: [{
                        label: 'Illness cases',
                        data: monthlyIllnessTrend.values || [],
                        borderColor: '#4A90E2',
                        backgroundColor: 'rgba(74, 144, 226, 0.15)',
                        tension: 0.3,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        }

        const pieCanvas = document.getElementById('vaccinationPieChart');
        if (pieCanvas) {
            new Chart(pieCanvas, {
                type: 'pie',
                data: {
                    labels: ['Vaccinated', 'Unvaccinated'],
                    datasets: [{
                        data: [vaccinationDistribution.vaccinated || 0, vaccinationDistribution.unvaccinated || 0],
                        backgroundColor: ['#50E3C2', '#D0021B'],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        }

        const barCanvas = document.getElementById('ageDistributionBarChart');
        if (barCanvas) {
            new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: ageDistribution.labels || [],
                    datasets: [{
                        label: 'Residents',
                        data: ageDistribution.values || [],
                        backgroundColor: '#F5A623',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        }
    }
</script>
</body>
</html>