<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Household Visits</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                        <h1 class="text-3xl font-bold leading-tight">Household Visits</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Record home visits, observations, remarks, and high-risk flags.</p>
                    </div>
                </header>

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Add household visit record</h2>
                    <form id="visitForm" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        <input id="householdName" type="text" required placeholder="Household / beneficiary name" class="h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        <input id="visitDate" type="date" required class="h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        <input id="visitObserver" type="text" required placeholder="Recorded by" class="h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        <textarea id="visitObservations" required rows="3" placeholder="Add observations" class="md:col-span-2 xl:col-span-3 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 py-2"></textarea>
                        <textarea id="visitRemarks" rows="2" placeholder="Upload remarks" class="md:col-span-2 xl:col-span-3 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 py-2"></textarea>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input id="isHighRisk" type="checkbox" class="rounded border-border-light dark:border-border-dark text-accent-red focus:ring-accent-red">
                            <span>Flag high-risk case</span>
                        </label>
                        <div class="xl:col-span-3">
                            <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Save household visit</button>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                        <h2 class="text-xl font-bold mb-4">Household Visit Records</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-border-light dark:border-border-dark">
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Visit date</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Household</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Observations</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Remarks</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Risk</th>
                                    </tr>
                                </thead>
                                <tbody id="visitTableBody">
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No household visit records yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                        <h2 class="text-xl font-bold mb-4">High-Risk Cases</h2>
                        <div id="highRiskList" class="space-y-3">
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">No high-risk cases flagged.</p>
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

    const visitForm = document.getElementById('visitForm');
    const visitTableBody = document.getElementById('visitTableBody');
    const highRiskList = document.getElementById('highRiskList');

    const visitRecords = [];

    function renderVisitRecords() {
        if (!visitRecords.length) {
            visitTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No household visit records yet.</td>
                </tr>
            `;
            return;
        }

        visitTableBody.innerHTML = visitRecords.map((record) => `
            <tr class="border-b border-border-light dark:border-border-dark">
                <td class="px-3 py-2 text-sm">${record.visitDate}</td>
                <td class="px-3 py-2 text-sm">${record.householdName}</td>
                <td class="px-3 py-2 text-sm">${record.observations}</td>
                <td class="px-3 py-2 text-sm">${record.remarks || '—'}</td>
                <td class="px-3 py-2 text-sm">
                    ${record.highRisk
                        ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-red/10 text-accent-red">High-risk</span>'
                        : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary/15 text-secondary">Regular</span>'}
                </td>
            </tr>
        `).join('');
    }

    function renderHighRiskList() {
        const highRiskCases = visitRecords.filter((record) => record.highRisk);

        if (!highRiskCases.length) {
            highRiskList.innerHTML = '<p class="text-sm text-text-light/70 dark:text-text-dark/70">No high-risk cases flagged.</p>';
            return;
        }

        highRiskList.innerHTML = highRiskCases.map((record) => `
            <div class="rounded-lg border border-accent-red/30 p-3 bg-accent-red/5">
                <p class="font-semibold">${record.householdName}</p>
                <p class="text-sm">Visit date: ${record.visitDate}</p>
                <p class="text-sm text-text-light/80 dark:text-text-dark/80">Observation: ${record.observations}</p>
                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Recorded by ${record.observer}</p>
            </div>
        `).join('');
    }

    visitForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const householdName = document.getElementById('householdName').value.trim();
        const visitDate = document.getElementById('visitDate').value;
        const observer = document.getElementById('visitObserver').value.trim();
        const observations = document.getElementById('visitObservations').value.trim();
        const remarks = document.getElementById('visitRemarks').value.trim();
        const highRisk = document.getElementById('isHighRisk').checked;

        if (!householdName || !visitDate || !observer || !observations) {
            return;
        }

        visitRecords.unshift({
            householdName,
            visitDate,
            observer,
            observations,
            remarks,
            highRisk,
        });

        visitForm.reset();
        renderVisitRecords();
        renderHighRiskList();
    });
</script>
</body>
</html>
