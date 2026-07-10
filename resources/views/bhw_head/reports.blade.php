<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Reports</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
    
    .nav-item.active {
        background-color: rgba(74, 144, 226, 0.2);
        color: #4A90E2;
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
<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    
    <div id="overlay" class=""></div>
    
    @include('sidebars.bhw_head')
    
    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Reports</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Health reports for residents covered by your BHW team</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="printReportBtn" type="button" class="inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold hover:opacity-90">
                            <span class="material-symbols-outlined text-[18px]">print</span>
                            Print
                        </button>
                    </div>
                </header>

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    @php
                        $reportData = $reportData ?? [];
                        $monthly = $reportData['monthly_barangay_health_report'] ?? [];
                        $malnutrition = $reportData['malnutrition_summary'] ?? [];
                        $vaccination = $reportData['vaccination_coverage_report'] ?? [];
                        $maternal = $reportData['maternal_summary'] ?? [];
                        $illness = $reportData['illness_case_summary'] ?? [];
                        $programs = $reportData['health_program_status_report'] ?? [];

                        $malnutritionIdentified = (int) ($malnutrition['identified_cases'] ?? 0);
                        $malnutritionMonitoring = (int) ($malnutrition['under_monitoring'] ?? 0);

                        $vaccinationVaccinated = (int) ($vaccination['vaccinated'] ?? 0);
                        $vaccinationUnvaccinated = (int) ($vaccination['unvaccinated'] ?? 0);

                        $maternalPregnantWomen = (int) ($maternal['pregnant_women'] ?? 0);
                        $maternalPrenatalMonitoring = (int) ($maternal['prenatal_monitoring'] ?? 0);
                        $maternalHighRisk = (int) ($maternal['high_risk_cases'] ?? 0);

                        $illnessActive = (int) ($illness['active_cases'] ?? 0);
                        $illnessResolved = (int) ($illness['resolved_cases'] ?? 0);

                        $monthlyTotalResidents = (int) ($monthly['total_residents'] ?? 0);
                        $monthlyTotalHouseholds = (int) ($monthly['total_households'] ?? 0);
                        $monthlySeniorCitizens = (int) ($monthly['senior_citizens'] ?? 0);
                    @endphp

                    <div id="reportContent" class="space-y-4">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Monthly Barangay Health Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Total residents</p>
                                    <p class="text-2xl font-bold" id="monthlyTotalResidents">{{ number_format($monthlyTotalResidents) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Total households</p>
                                    <p class="text-2xl font-bold" id="monthlyTotalHouseholds">{{ number_format($monthlyTotalHouseholds) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Senior citizens</p>
                                    <p class="text-2xl font-bold" id="monthlySeniorCitizens">{{ number_format($monthlySeniorCitizens) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Malnutrition Summary</h2>
                            <div class="h-64">
                                <canvas id="malnutritionChart" aria-label="Malnutrition summary chart"></canvas>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Identified cases</p>
                                    <p class="text-2xl font-bold" id="malnutritionIdentified">{{ number_format($malnutritionIdentified) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Under monitoring</p>
                                    <p class="text-2xl font-bold" id="malnutritionMonitoring">{{ number_format($malnutritionMonitoring) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Vaccination Coverage Report</h2>
                            <div class="h-64">
                                <canvas id="vaccinationChart" aria-label="Vaccination coverage chart"></canvas>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Vaccinated residents</p>
                                    <p class="text-2xl font-bold" id="vaccinationVaccinated">{{ number_format($vaccinationVaccinated) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Unvaccinated residents</p>
                                    <p class="text-2xl font-bold" id="vaccinationUnvaccinated">{{ number_format($vaccinationUnvaccinated) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Maternal Summary</h2>
                            <div class="h-64">
                                <canvas id="maternalChart" aria-label="Maternal summary chart"></canvas>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Pregnant women</p>
                                    <p class="text-2xl font-bold" id="maternalPregnantWomen">{{ number_format($maternalPregnantWomen) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Prenatal monitoring</p>
                                    <p class="text-2xl font-bold" id="maternalPrenatalMonitoring">{{ number_format($maternalPrenatalMonitoring) }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 mt-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">High-risk cases</p>
                                    <p class="text-2xl font-bold" id="maternalHighRisk">{{ number_format($maternalHighRisk) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Illness Case Summary</h2>
                            <div class="h-64">
                                <canvas id="illnessChart" aria-label="Illness case summary chart"></canvas>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Active cases</p>
                                    <p class="text-2xl font-bold" id="illnessActive">{{ number_format($illnessActive) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Resolved cases</p>
                                    <p class="text-2xl font-bold" id="illnessResolved">{{ number_format($illnessResolved) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Health Program Status Report</h2>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-border-light dark:border-border-dark">
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Program</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($programs as $program)
                                            <tr class="border-b border-border-light dark:border-border-dark">
                                                <td class="px-3 py-2 text-sm">{{ $program['program'] ?? 'N/A' }}</td>
                                                <td class="px-3 py-2 text-sm">{{ $program['status'] ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="px-3 py-3 text-sm text-text-light/70 dark:text-text-dark/70">No health program records found for the selected period.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    const chartTextColor = document.documentElement.classList.contains('dark') ? '#E0E0E0' : '#4A4A4A';
    const chartGridColor = document.documentElement.classList.contains('dark') ? 'rgba(224, 224, 224, 0.16)' : 'rgba(74, 74, 74, 0.12)';

    const reportSeries = {
        malnutrition: {
            identified: {{ $malnutritionIdentified }},
            monitoring: {{ $malnutritionMonitoring }},
        },
        vaccination: {
            vaccinated: {{ $vaccinationVaccinated }},
            unvaccinated: {{ $vaccinationUnvaccinated }},
        },
        illness: {
            active: {{ $illnessActive }},
            resolved: {{ $illnessResolved }},
        },
        maternal: {
            pregnantWomen: {{ $maternalPregnantWomen }},
            prenatalMonitoring: {{ $maternalPrenatalMonitoring }},
            highRisk: {{ $maternalHighRisk }},
        },
    };

    const sharedScaleOptions = {
        beginAtZero: true,
        ticks: { color: chartTextColor },
        grid: { color: chartGridColor },
    };

    const sharedPlugins = {
        legend: {
            labels: {
                color: chartTextColor,
                boxWidth: 14,
            },
        },
    };

    const malnutritionCanvas = document.getElementById('malnutritionChart');
    if (malnutritionCanvas) {
        new Chart(malnutritionCanvas, {
            type: 'bar',
            data: {
                labels: ['Identified Cases', 'Under Monitoring'],
                datasets: [{
                    label: 'Malnutrition Summary',
                    data: [reportSeries.malnutrition.identified, reportSeries.malnutrition.monitoring],
                    backgroundColor: ['#D0021B', '#50E3C2'],
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: sharedPlugins,
                scales: { y: sharedScaleOptions, x: { ticks: { color: chartTextColor }, grid: { display: false } } },
            },
        });
    }

    const vaccinationCanvas = document.getElementById('vaccinationChart');
    if (vaccinationCanvas) {
        new Chart(vaccinationCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Vaccinated', 'Unvaccinated'],
                datasets: [{
                    label: 'Vaccination Coverage',
                    data: [reportSeries.vaccination.vaccinated, reportSeries.vaccination.unvaccinated],
                    backgroundColor: ['#50E3C2', '#F5A623'],
                    borderColor: ['#3ac7a8', '#de941f'],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: sharedPlugins,
            },
        });
    }

    const illnessCanvas = document.getElementById('illnessChart');
    if (illnessCanvas) {
        new Chart(illnessCanvas, {
            type: 'bar',
            data: {
                labels: ['Active Cases', 'Resolved Cases'],
                datasets: [{
                    label: 'Illness Case Summary',
                    data: [reportSeries.illness.active, reportSeries.illness.resolved],
                    backgroundColor: ['#D0021B', '#50E3C2'],
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: sharedPlugins,
                scales: { y: sharedScaleOptions, x: { ticks: { color: chartTextColor }, grid: { display: false } } },
            },
        });
    }

    const maternalCanvas = document.getElementById('maternalChart');
    if (maternalCanvas) {
        new Chart(maternalCanvas, {
            type: 'bar',
            data: {
                labels: ['Pregnant Women', 'Prenatal Monitoring', 'High-Risk Cases'],
                datasets: [{
                    label: 'Maternal Summary',
                    data: [reportSeries.maternal.pregnantWomen, reportSeries.maternal.prenatalMonitoring, reportSeries.maternal.highRisk],
                    backgroundColor: ['#4A90E2', '#50E3C2', '#D0021B'],
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: sharedPlugins,
                scales: { y: sharedScaleOptions, x: { ticks: { color: chartTextColor }, grid: { display: false } } },
            },
        });
    }

    const printReportBtn = document.getElementById('printReportBtn');
    if (printReportBtn) {
        printReportBtn.addEventListener('click', () => {
            window.print();
        });
    }
</script>
</body>
</html>
