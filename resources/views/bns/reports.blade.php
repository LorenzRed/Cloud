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
    
    @include('sidebars.bns')
    
    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Reports</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Program reports and performance analytics for Kapitan or LGU submission.</p>
                    </div>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    @php
                        $reportData = $reportData ?? [];
                        $monthly = $reportData['monthly_nutrition_report'] ?? [];
                        $malnutrition = $reportData['malnutrition_case_report'] ?? [];
                        $feeding = $reportData['feeding_program_summary'] ?? [];
                        $growth = $reportData['child_growth_progress_report'] ?? [];
                    @endphp

                    <form method="GET" action="{{ route('bns.reports') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
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
                            <a href="{{ route('bns.reports') }}" class="h-10 px-4 rounded-lg border border-border-light dark:border-border-dark text-sm font-semibold inline-flex items-center">Reset</a>
                            <button type="button" id="exportPdfBtn" class="h-10 px-4 rounded-lg bg-secondary text-white text-sm font-semibold">Export to PDF</button>
                        </div>
                    </form>

                    <div id="reportContent" class="space-y-4">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Monthly Nutrition Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Total children monitored</p>
                                    <p class="text-2xl font-bold" id="monthlyChildren">{{ number_format((int) ($monthly['total_children'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Households covered</p>
                                    <p class="text-2xl font-bold" id="monthlyHouseholds">{{ number_format((int) ($monthly['households_covered'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Active monitoring</p>
                                    <p class="text-2xl font-bold" id="monthlyMonitoring">{{ number_format((int) ($monthly['active_monitoring'] ?? 0)) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Malnutrition Case Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Underweight cases</p>
                                    <p class="text-2xl font-bold" id="malnutritionUnderweight">{{ number_format((int) ($malnutrition['underweight_cases'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Severely underweight cases</p>
                                    <p class="text-2xl font-bold" id="malnutritionSevere">{{ number_format((int) ($malnutrition['severely_underweight_cases'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Recovered cases</p>
                                    <p class="text-2xl font-bold" id="malnutritionRecovered">{{ number_format((int) ($malnutrition['recovered_cases'] ?? 0)) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Feeding Program Summary</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Enrolled beneficiaries</p>
                                    <p class="text-2xl font-bold" id="feedingEnrolled">{{ number_format((int) ($feeding['enrolled_beneficiaries'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Attendance rate</p>
                                    <p class="text-2xl font-bold" id="feedingAttendance">{{ number_format((int) ($feeding['attendance_rate'] ?? 0)) }}%</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Food distributions</p>
                                    <p class="text-2xl font-bold" id="feedingDistributions">{{ number_format((int) ($feeding['food_distributions'] ?? 0)) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-xl font-bold mb-3">Child Growth Progress Report</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Improving</p>
                                    <p class="text-2xl font-bold" id="growthImproving">{{ number_format((int) ($growth['improving'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">No change</p>
                                    <p class="text-2xl font-bold" id="growthNoChange">{{ number_format((int) ($growth['no_change'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-lg bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Needs follow-up</p>
                                    <p class="text-2xl font-bold" id="growthFollowUp">{{ number_format((int) ($growth['needs_follow_up'] ?? 0)) }}</p>
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

            line('BNS Nutrition Reports', 16);
            line('For submission to Kapitan/LGU', 24);
            line('Date filter: ' + fromDate + ' to ' + toDate, 32);

            line('Monthly Nutrition Report', 44);
            line('Total children monitored: ' + document.getElementById('monthlyChildren').innerText, 52);
            line('Households covered: ' + document.getElementById('monthlyHouseholds').innerText, 58);
            line('Active monitoring: ' + document.getElementById('monthlyMonitoring').innerText, 64);

            line('Malnutrition Case Report', 76);
            line('Underweight cases: ' + document.getElementById('malnutritionUnderweight').innerText, 84);
            line('Severely underweight cases: ' + document.getElementById('malnutritionSevere').innerText, 90);
            line('Recovered cases: ' + document.getElementById('malnutritionRecovered').innerText, 96);

            line('Feeding Program Summary', 108);
            line('Enrolled beneficiaries: ' + document.getElementById('feedingEnrolled').innerText, 116);
            line('Attendance rate: ' + document.getElementById('feedingAttendance').innerText, 122);
            line('Food distributions: ' + document.getElementById('feedingDistributions').innerText, 128);

            line('Child Growth Progress Report', 140);
            line('Improving: ' + document.getElementById('growthImproving').innerText, 148);
            line('No change: ' + document.getElementById('growthNoChange').innerText, 154);
            line('Needs follow-up: ' + document.getElementById('growthFollowUp').innerText, 160);

            doc.save('bns-nutrition-reports.pdf');
        });
    }
</script>
</body>
</html>
