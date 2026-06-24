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

    @include('sidebars.bhw')

    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Reports</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Generate community health reports for BHW submission.</p>
                    </div>
                    <div class="flex flex-wrap items-end gap-3">
                        <form method="GET" action="{{ route('bhw.reports') }}" class="relative flex flex-wrap items-end gap-3" autocomplete="off">
                            <div class="flex flex-col gap-1">
                                <label for="period" class="text-sm font-medium text-text-light/70 dark:text-text-dark/70">Filter period</label>
                                <select id="period" name="period" class="h-10 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-card-dark px-3 text-sm">
                                    <option value="weekly" @selected(($period ?? 'monthly') === 'weekly')>Weekly</option>
                                    <option value="monthly" @selected(($period ?? 'monthly') === 'monthly')>Monthly</option>
                                    <option value="custom" @selected(($period ?? 'monthly') === 'custom')>Custom range</option>
                                </select>
                            </div>

                            <div id="customDateRange" class="absolute left-0 top-full z-20 mt-2 flex flex-wrap items-end gap-3 rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-3 shadow-sm {{ ($period ?? 'monthly') === 'custom' ? '' : 'hidden' }}">
                                <div class="flex flex-col gap-1">
                                    <label for="from_date" class="text-sm font-medium text-text-light/70 dark:text-text-dark/70">From</label>
                                    <input id="from_date" name="from_date" type="date" value="{{ $fromDate ?? '' }}" class="h-10 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-card-dark px-3 text-sm">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label for="to_date" class="text-sm font-medium text-text-light/70 dark:text-text-dark/70">To</label>
                                    <input id="to_date" name="to_date" type="date" value="{{ $toDate ?? '' }}" class="h-10 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-card-dark px-3 text-sm">
                                </div>
                            </div>

                            <button type="submit" class="inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-card-dark text-white dark:bg-card-light dark:text-text-light text-sm font-semibold hover:opacity-90">
                                Apply
                            </button>
                        </form>

                        <button id="printReportBtn" type="button" class="inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold hover:opacity-90">
                            <span class="material-symbols-outlined text-[18px]">print</span>
                            Print
                        </button>
                    </div>
                </header>

                @php
                    $reportData = $reportData ?? [];
                    $selectedPeriod = $period ?? 'monthly';
                    $periodLabel = match ($selectedPeriod) {
                        'weekly' => 'Weekly',
                        'custom' => 'Custom range',
                        default => 'Monthly',
                    };
                    $illness = $reportData['monthly_illness_report'] ?? [];
                    $vaccination = $reportData['vaccination_report'] ?? [];
                    $maternal = $reportData['maternal_health_report'] ?? [];
                    $senior = $reportData['senior_citizen_monitoring_report'] ?? [];

                    $illnessActive = (int) ($illness['active_cases'] ?? 0);
                    $illnessRecovered = (int) ($illness['recovered_cases'] ?? 0);
                    $illnessVisited = (int) ($illness['households_visited'] ?? 0);

                    $vaccinationDone = (int) ($vaccination['vaccinated'] ?? 0);
                    $vaccinationPending = (int) ($vaccination['pending'] ?? 0);
                    $vaccinationRate = (int) ($vaccination['coverage_rate'] ?? 0);

                    $maternalMonitored = (int) ($maternal['pregnant_women_monitored'] ?? 0);
                    $maternalPrenatal = (int) ($maternal['prenatal_visits_completed'] ?? 0);
                    $maternalHighRisk = (int) ($maternal['high_risk_cases'] ?? 0);

                    $seniorMonitored = (int) ($senior['seniors_monitored'] ?? 0);
                    $seniorStable = (int) ($senior['stable_condition'] ?? 0);
                    $seniorFollowUp = (int) ($senior['needs_follow_up'] ?? 0);
                @endphp

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <div id="reportContent" class="space-y-4">
                        <div class="flex flex-wrap items-center gap-2 text-sm text-text-light/70 dark:text-text-dark/70">
                            <span class="font-medium text-text-light dark:text-text-dark">Active filter:</span>
                            <span class="rounded-full bg-background-light dark:bg-background-dark px-3 py-1">{{ $periodLabel }}</span>
                            <span class="rounded-full bg-background-light dark:bg-background-dark px-3 py-1">{{ $fromDate ?? 'N/A' }} to {{ $toDate ?? 'N/A' }}</span>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                <h2 class="text-xl font-bold mb-1">Illness Report</h2>
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mb-3">Active, recovered, and household visit totals.</p>
                                <div class="h-72">
                                    <canvas id="illnessChart" aria-label="Illness chart"></canvas>
                                </div>
                            </div>

                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                <h2 class="text-xl font-bold mb-1">Maternal Health Report</h2>
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mb-3">Monitoring, prenatal visits, and high-risk cases.</p>
                                <div class="h-72">
                                    <canvas id="maternalChart" aria-label="Maternal health chart"></canvas>
                                </div>
                            </div>

                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                <h2 class="text-xl font-bold mb-1">Senior Citizen Monitoring Report</h2>
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mb-3">Senior monitoring, stable cases, and follow-ups.</p>
                                <div class="h-72">
                                    <canvas id="seniorChart" aria-label="Senior monitoring chart"></canvas>
                                </div>
                            </div>

                            <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                                <h2 class="text-xl font-bold mb-1">Vaccination Report</h2>
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mb-3">Vaccinated vs pending residents and coverage rate.</p>
                                <div class="h-72">
                                    <canvas id="vaccinationChart" aria-label="Vaccination chart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Illness active</p>
                                <p class="text-2xl font-bold" id="illnessActive">{{ number_format($illnessActive) }}</p>
                            </div>
                            <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Vaccination coverage</p>
                                <p class="text-2xl font-bold" id="vaccinationRate">{{ number_format($vaccinationRate) }}%</p>
                            </div>
                            <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Maternal monitored</p>
                                <p class="text-2xl font-bold" id="maternalMonitored">{{ number_format($maternalMonitored) }}</p>
                            </div>
                            <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Senior follow-ups</p>
                                <p class="text-2xl font-bold" id="seniorFollowUp">{{ number_format($seniorFollowUp) }}</p>
                            </div>
                        </div>

                        <div class="hidden">
                            <span id="illnessRecovered">{{ number_format($illnessRecovered) }}</span>
                            <span id="illnessVisited">{{ number_format($illnessVisited) }}</span>
                            <span id="vaccinationDone">{{ number_format($vaccinationDone) }}</span>
                            <span id="vaccinationPending">{{ number_format($vaccinationPending) }}</span>
                            <span id="maternalPrenatal">{{ number_format($maternalPrenatal) }}</span>
                            <span id="maternalHighRisk">{{ number_format($maternalHighRisk) }}</span>
                            <span id="seniorMonitored">{{ number_format($seniorMonitored) }}</span>
                            <span id="seniorStable">{{ number_format($seniorStable) }}</span>
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
    const periodSelect = document.getElementById('period');
    const customDateRange = document.getElementById('customDateRange');
    const initialPeriod = @json($selectedPeriod ?? 'monthly');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    if (periodSelect && customDateRange) {
        periodSelect.value = initialPeriod;

        const toggleCustomRange = () => {
            const isCustom = periodSelect.value === 'custom';

            customDateRange.classList.toggle('hidden', !isCustom);
            customDateRange.querySelectorAll('input').forEach((input) => {
                input.disabled = !isCustom;
            });
        };

        periodSelect.addEventListener('change', toggleCustomRange);
        toggleCustomRange();
    }

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    const chartTextColor = document.documentElement.classList.contains('dark') ? '#E0E0E0' : '#4A4A4A';
    const chartGridColor = document.documentElement.classList.contains('dark') ? 'rgba(224, 224, 224, 0.16)' : 'rgba(74, 74, 74, 0.12)';

    const reportSeries = {
        illness: {
            active: {{ $illnessActive }},
            recovered: {{ $illnessRecovered }},
            visited: {{ $illnessVisited }},
        },
        vaccination: {
            done: {{ $vaccinationDone }},
            pending: {{ $vaccinationPending }},
            rate: {{ $vaccinationRate }},
        },
        maternal: {
            monitored: {{ $maternalMonitored }},
            prenatal: {{ $maternalPrenatal }},
            highRisk: {{ $maternalHighRisk }},
        },
        senior: {
            monitored: {{ $seniorMonitored }},
            stable: {{ $seniorStable }},
            followUp: {{ $seniorFollowUp }},
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

    const illnessCanvas = document.getElementById('illnessChart');
    if (illnessCanvas) {
        new Chart(illnessCanvas, {
            type: 'bar',
            data: {
                labels: ['Active', 'Recovered', 'Households Visited'],
                datasets: [{
                    label: 'Illness Metrics',
                    data: [reportSeries.illness.active, reportSeries.illness.recovered, reportSeries.illness.visited],
                    backgroundColor: ['#D0021B', '#50E3C2', '#4A90E2'],
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
                labels: ['Vaccinated', 'Pending'],
                datasets: [{
                    label: 'Vaccination Status',
                    data: [reportSeries.vaccination.done, reportSeries.vaccination.pending],
                    backgroundColor: ['#50E3C2', '#F5A623'],
                    borderColor: ['#3ac7a8', '#de941f'],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    ...sharedPlugins,
                    title: {
                        display: true,
                        text: 'Coverage Rate: ' + reportSeries.vaccination.rate + '%',
                        color: chartTextColor,
                    },
                },
            },
        });
    }

    const maternalCanvas = document.getElementById('maternalChart');
    if (maternalCanvas) {
        new Chart(maternalCanvas, {
            type: 'bar',
            data: {
                labels: ['Monitored', 'Prenatal Visits', 'High-Risk'],
                datasets: [{
                    label: 'Maternal Health Metrics',
                    data: [reportSeries.maternal.monitored, reportSeries.maternal.prenatal, reportSeries.maternal.highRisk],
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

    const seniorCanvas = document.getElementById('seniorChart');
    if (seniorCanvas) {
        new Chart(seniorCanvas, {
            type: 'pie',
            data: {
                labels: ['Stable', 'Needs Follow-Up', 'Total Monitored'],
                datasets: [{
                    label: 'Senior Monitoring Metrics',
                    data: [reportSeries.senior.stable, reportSeries.senior.followUp, reportSeries.senior.monitored],
                    backgroundColor: ['#50E3C2', '#F5A623', '#4A90E2'],
                    borderColor: ['#3ac7a8', '#de941f', '#3678c7'],
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
            line('Date filter: {{ $periodLabel }}', 24);
            line('Range: ' + fromDate + ' to ' + toDate, 30);

            line('Monthly Illness Report', 42);
            line('Active cases: ' + document.getElementById('illnessActive').innerText, 50);
            line('Recovered cases: ' + document.getElementById('illnessRecovered').innerText, 56);
            line('Households visited: ' + document.getElementById('illnessVisited').innerText, 62);

            line('Vaccination Report', 74);
            line('Vaccinated residents: ' + document.getElementById('vaccinationDone').innerText, 82);
            line('Pending vaccinations: ' + document.getElementById('vaccinationPending').innerText, 88);
            line('Coverage rate: ' + document.getElementById('vaccinationRate').innerText, 94);

            line('Maternal Health Report', 106);
            line('Pregnant women monitored: ' + document.getElementById('maternalMonitored').innerText, 114);
            line('Prenatal visits completed: ' + document.getElementById('maternalPrenatal').innerText, 120);
            line('High-risk cases: ' + document.getElementById('maternalHighRisk').innerText, 126);

            line('Senior Citizen Monitoring Report', 138);
            line('Seniors monitored: ' + document.getElementById('seniorMonitored').innerText, 146);
            line('Stable condition: ' + document.getElementById('seniorStable').innerText, 152);
            line('Needs follow-up: ' + document.getElementById('seniorFollowUp').innerText, 158);

            doc.save('bhw-reports.pdf');
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
