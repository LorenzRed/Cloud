<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - BHW Dashboard</title>
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

    .page-content {
        height: 100%;
        overflow-y: auto;
    }

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

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

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
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('partials.app-flash')
<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>

    <div id="overlay" class=""></div>

    @include('sidebars.bhw')

    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">BHW Dashboard</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Community health monitoring overview</p>
                    </div>
                </header>

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    @php
                        $bhwStats = $bhwStats ?? [];
                        $monthlyIllnessTrend = $monthlyIllnessTrend ?? [
                            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            'values' => [0, 0, 0, 0, 0, 0],
                        ];
                        $vaccinationCoverage = $vaccinationCoverage ?? [
                            'vaccinated' => 0,
                            'pending' => 0,
                        ];
                        $commonIllnesses = $commonIllnesses ?? [
                            'labels' => ['Flu', 'Cough/Cold', 'Diarrhea', 'Hypertension'],
                            'values' => [0, 0, 0, 0],
                        ];

                        $kpiCards = [
                            ['label' => 'Total residents assigned', 'key' => 'total_residents_assigned'],
                            ['label' => 'Active illness cases', 'key' => 'active_illness_cases'],
                            ['label' => 'Recovered cases', 'key' => 'recovered_cases'],
                            ['label' => 'Vaccinated residents', 'key' => 'vaccinated_residents'],
                            ['label' => 'Pending vaccinations', 'key' => 'pending_vaccinations'],
                            ['label' => 'Pregnant women monitored', 'key' => 'pregnant_women_monitored'],
                            ['label' => 'Senior citizens monitored', 'key' => 'senior_citizens_monitored'],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                        @foreach ($kpiCards as $card)
                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                <p class="text-sm text-text-light/60 dark:text-text-dark/60 mb-1">{{ $card['label'] }}</p>
                                <p class="text-2xl font-bold">{{ number_format((int) ($bhwStats[$card['key']] ?? 0)) }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4 xl:col-span-2">
                            <h2 class="text-xl font-bold mb-4">Line graph – Monthly illness trend</h2>
                            <div class="h-72">
                                <canvas id="monthlyIllnessTrendChart"></canvas>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-4">Pie chart – Vaccination coverage</h2>
                            <div class="h-72">
                                <canvas id="vaccinationCoveragePieChart"></canvas>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-4">Bar chart – Common illnesses</h2>
                            <div class="h-72">
                                <canvas id="commonIllnessesBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const monthlyIllnessTrend = @json($monthlyIllnessTrend ?? ['labels' => [], 'values' => []]);
    const vaccinationCoverage = @json($vaccinationCoverage ?? ['vaccinated' => 0, 'pending' => 0]);
    const commonIllnesses = @json($commonIllnesses ?? ['labels' => [], 'values' => []]);

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
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        }

        const pieCanvas = document.getElementById('vaccinationCoveragePieChart');
        if (pieCanvas) {
            new Chart(pieCanvas, {
                type: 'pie',
                data: {
                    labels: ['Vaccinated', 'Pending'],
                    datasets: [{
                        data: [vaccinationCoverage.vaccinated || 0, vaccinationCoverage.pending || 0],
                        backgroundColor: ['#50E3C2', '#F5A623'],
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        }

        const barCanvas = document.getElementById('commonIllnessesBarChart');
        if (barCanvas) {
            new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: commonIllnesses.labels || [],
                    datasets: [{
                        label: 'Cases',
                        data: commonIllnesses.values || [],
                        backgroundColor: '#D0021B',
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        }
    }
</script>
</body>
</html>
