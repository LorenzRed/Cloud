<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Reports</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

    @include('sidebars.bhw')

    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Reports</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Generate community health reports for BHW submission.</p>
                    </div>
                </header>

                @php
                    $reportData = $reportData ?? [];
                    $illness = $reportData['monthly_illness_report'] ?? [];
                    $vaccination = $reportData['vaccination_report'] ?? [];
                    $maternal = $reportData['maternal_health_report'] ?? [];
                    $senior = $reportData['senior_citizen_monitoring_report'] ?? [];
                @endphp

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <form method="GET" action="{{ route('bhw.reports') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-1">From date</label>
                            <input type="date" name="from_date" value="{{ $fromDate ?? '' }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">To date</label>
                            <input type="date" name="to_date" value="{{ $toDate ?? '' }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>
                        <div class="md:col-span-2 flex items-end gap-2">
                            <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Date filter</button>
                            <a href="{{ route('bhw.reports') }}" class="h-10 px-4 rounded-lg border border-border-light dark:border-border-dark text-sm font-semibold inline-flex items-center">Reset</a>
                            <button type="button" id="exportPdfBtn" class="h-10 px-4 rounded-lg bg-secondary text-white text-sm font-semibold">Export to PDF</button>
                        </div>
                    </form>

                    <div id="reportContent" class="space-y-4">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Monthly Illness Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Active cases</p>
                                    <p class="text-2xl font-bold" id="illnessActive">{{ number_format((int) ($illness['active_cases'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Recovered cases</p>
                                    <p class="text-2xl font-bold" id="illnessRecovered">{{ number_format((int) ($illness['recovered_cases'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Households visited</p>
                                    <p class="text-2xl font-bold" id="illnessVisited">{{ number_format((int) ($illness['households_visited'] ?? 0)) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Vaccination Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Vaccinated residents</p>
                                    <p class="text-2xl font-bold" id="vaccinationDone">{{ number_format((int) ($vaccination['vaccinated'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Pending vaccinations</p>
                                    <p class="text-2xl font-bold" id="vaccinationPending">{{ number_format((int) ($vaccination['pending'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Coverage rate</p>
                                    <p class="text-2xl font-bold" id="vaccinationRate">{{ number_format((int) ($vaccination['coverage_rate'] ?? 0)) }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Maternal Health Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Pregnant women monitored</p>
                                    <p class="text-2xl font-bold" id="maternalMonitored">{{ number_format((int) ($maternal['pregnant_women_monitored'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Prenatal visits completed</p>
                                    <p class="text-2xl font-bold" id="maternalPrenatal">{{ number_format((int) ($maternal['prenatal_visits_completed'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">High-risk cases</p>
                                    <p class="text-2xl font-bold" id="maternalHighRisk">{{ number_format((int) ($maternal['high_risk_cases'] ?? 0)) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Senior Citizen Monitoring Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Seniors monitored</p>
                                    <p class="text-2xl font-bold" id="seniorMonitored">{{ number_format((int) ($senior['seniors_monitored'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Stable condition</p>
                                    <p class="text-2xl font-bold" id="seniorStable">{{ number_format((int) ($senior['stable_condition'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Needs follow-up</p>
                                    <p class="text-2xl font-bold" id="seniorFollowUp">{{ number_format((int) ($senior['needs_follow_up'] ?? 0)) }}</p>
                                </div>
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

    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', () => {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const line = (text, y) => doc.text(String(text), 14, y);
            const fromDate = "{{ $fromDate ?? '' }}" || 'N/A';
            const toDate = "{{ $toDate ?? '' }}" || 'N/A';

            line('BHW Reports', 16);
            line('Date filter: ' + fromDate + ' to ' + toDate, 24);

            line('Monthly Illness Report', 36);
            line('Active cases: ' + document.getElementById('illnessActive').innerText, 44);
            line('Recovered cases: ' + document.getElementById('illnessRecovered').innerText, 50);
            line('Households visited: ' + document.getElementById('illnessVisited').innerText, 56);

            line('Vaccination Report', 68);
            line('Vaccinated residents: ' + document.getElementById('vaccinationDone').innerText, 76);
            line('Pending vaccinations: ' + document.getElementById('vaccinationPending').innerText, 82);
            line('Coverage rate: ' + document.getElementById('vaccinationRate').innerText, 88);

            line('Maternal Health Report', 100);
            line('Pregnant women monitored: ' + document.getElementById('maternalMonitored').innerText, 108);
            line('Prenatal visits completed: ' + document.getElementById('maternalPrenatal').innerText, 114);
            line('High-risk cases: ' + document.getElementById('maternalHighRisk').innerText, 120);

            line('Senior Citizen Monitoring Report', 132);
            line('Seniors monitored: ' + document.getElementById('seniorMonitored').innerText, 140);
            line('Stable condition: ' + document.getElementById('seniorStable').innerText, 146);
            line('Needs follow-up: ' + document.getElementById('seniorFollowUp').innerText, 152);

            doc.save('bhw-reports.pdf');
        });
    }
</script>
</body>
</html>
