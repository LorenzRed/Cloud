<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Programs</title>
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
                        <h1 class="text-3xl font-bold leading-tight">Nutrition Programs</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Manage feeding programs and track impact outcomes.</p>
                    </div>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3 mb-6" id="impactSummaryCards">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-3">
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Enrolled beneficiaries</p>
                            <p class="text-2xl font-bold" id="summaryEnrolled">0</p>
                        </div>
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-3">
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Attendance records</p>
                            <p class="text-2xl font-bold" id="summaryAttendance">0</p>
                        </div>
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-3">
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Food distribution logs</p>
                            <p class="text-2xl font-bold" id="summaryDistributions">0</p>
                        </div>
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-3">
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Improving cases</p>
                            <p class="text-2xl font-bold" id="summaryImproving">0</p>
                        </div>
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-3">
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Recovered cases</p>
                            <p class="text-2xl font-bold" id="summaryRecovered">0</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Enroll beneficiaries in feeding programs</h2>
                            <form id="enrollForm" class="space-y-3">
                                <input id="beneficiaryName" type="text" required placeholder="Beneficiary name" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input id="beneficiaryAge" type="number" min="0" required placeholder="Age" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                    <select id="beneficiaryProgram" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                        <option value="">Select feeding program</option>
                                        <option>Supplementary Feeding</option>
                                        <option>Micronutrient Support</option>
                                        <option>Pregnant Women Nutrition Package</option>
                                    </select>
                                </div>
                                <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Enroll beneficiary</button>
                            </form>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Track attendance</h2>
                            <form id="attendanceForm" class="space-y-3">
                                <input id="attendanceName" type="text" required placeholder="Beneficiary name" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input id="attendanceDate" type="date" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                    <select id="attendanceStatus" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                        <option value="">Attendance status</option>
                                        <option>Present</option>
                                        <option>Absent</option>
                                    </select>
                                </div>
                                <button type="submit" class="h-10 px-4 rounded-lg bg-secondary text-white text-sm font-semibold">Record attendance</button>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Record food distribution</h2>
                            <form id="distributionForm" class="space-y-3">
                                <input id="distributionName" type="text" required placeholder="Beneficiary name" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input id="distributionDate" type="date" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                    <input id="foodPackage" type="text" required placeholder="Food package / item" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                </div>
                                <button type="submit" class="h-10 px-4 rounded-lg bg-accent-orange text-white text-sm font-semibold">Record distribution</button>
                            </form>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Monitor improvement progress</h2>
                            <form id="progressForm" class="space-y-3">
                                <input id="progressName" type="text" required placeholder="Beneficiary name" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input id="progressDate" type="date" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                    <select id="progressStatus" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                        <option value="">Progress status</option>
                                        <option>Improving</option>
                                        <option>No Change</option>
                                        <option>Needs Follow-up</option>
                                    </select>
                                </div>
                                <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Save progress</button>
                            </form>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border-light dark:border-border-dark p-4 mb-6">
                        <h2 class="text-lg font-bold mb-3">Mark recovered cases</h2>
                        <form id="recoverForm" class="flex flex-col md:flex-row gap-2">
                            <input id="recoverName" type="text" required placeholder="Beneficiary name" class="flex-1 h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                            <button type="submit" class="h-10 px-4 rounded-lg bg-secondary text-white text-sm font-semibold">Mark recovered</button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                        <h2 class="text-lg font-bold mb-3">Impact tracking log</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-border-light dark:border-border-dark">
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Date</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Beneficiary</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Action</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Details</th>
                                    </tr>
                                </thead>
                                <tbody id="impactLogTableBody">
                                    <tr>
                                        <td colspan="4" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No impact records yet.</td>
                                    </tr>
                                </tbody>
                            </table>
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

    const enrolledBeneficiaries = [];
    const attendanceLogs = [];
    const distributionLogs = [];
    const progressLogs = [];
    const recoveredCases = [];
    const impactLogs = [];

    const summaryEnrolled = document.getElementById('summaryEnrolled');
    const summaryAttendance = document.getElementById('summaryAttendance');
    const summaryDistributions = document.getElementById('summaryDistributions');
    const summaryImproving = document.getElementById('summaryImproving');
    const summaryRecovered = document.getElementById('summaryRecovered');
    const impactLogTableBody = document.getElementById('impactLogTableBody');

    function refreshSummary() {
        summaryEnrolled.textContent = enrolledBeneficiaries.length;
        summaryAttendance.textContent = attendanceLogs.length;
        summaryDistributions.textContent = distributionLogs.length;
        summaryImproving.textContent = progressLogs.filter((item) => item.progressStatus === 'Improving').length;
        summaryRecovered.textContent = recoveredCases.length;
    }

    function renderImpactLogs() {
        if (!impactLogs.length) {
            impactLogTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No impact records yet.</td>
                </tr>
            `;
            return;
        }

        impactLogTableBody.innerHTML = impactLogs.map((item) => `
            <tr class="border-b border-border-light dark:border-border-dark">
                <td class="px-3 py-2 text-sm">${item.date}</td>
                <td class="px-3 py-2 text-sm">${item.name}</td>
                <td class="px-3 py-2 text-sm">${item.action}</td>
                <td class="px-3 py-2 text-sm">${item.details}</td>
            </tr>
        `).join('');
    }

    function addImpactLog(name, action, details) {
        impactLogs.unshift({
            date: new Date().toLocaleDateString(),
            name,
            action,
            details,
        });

        renderImpactLogs();
        refreshSummary();
    }

    document.getElementById('enrollForm').addEventListener('submit', (event) => {
        event.preventDefault();
        const name = document.getElementById('beneficiaryName').value.trim();
        const age = document.getElementById('beneficiaryAge').value;
        const program = document.getElementById('beneficiaryProgram').value;

        if (!name || !age || !program) {
            return;
        }

        enrolledBeneficiaries.unshift({ name, age, program });
        addImpactLog(name, 'Enrollment', `Program: ${program}; Age: ${age}`);
        event.target.reset();
    });

    document.getElementById('attendanceForm').addEventListener('submit', (event) => {
        event.preventDefault();
        const name = document.getElementById('attendanceName').value.trim();
        const date = document.getElementById('attendanceDate').value;
        const status = document.getElementById('attendanceStatus').value;

        if (!name || !date || !status) {
            return;
        }

        attendanceLogs.unshift({ name, date, status });
        addImpactLog(name, 'Attendance', `${status} on ${date}`);
        event.target.reset();
    });

    document.getElementById('distributionForm').addEventListener('submit', (event) => {
        event.preventDefault();
        const name = document.getElementById('distributionName').value.trim();
        const date = document.getElementById('distributionDate').value;
        const foodPackage = document.getElementById('foodPackage').value.trim();

        if (!name || !date || !foodPackage) {
            return;
        }

        distributionLogs.unshift({ name, date, foodPackage });
        addImpactLog(name, 'Food Distribution', `${foodPackage} on ${date}`);
        event.target.reset();
    });

    document.getElementById('progressForm').addEventListener('submit', (event) => {
        event.preventDefault();
        const name = document.getElementById('progressName').value.trim();
        const date = document.getElementById('progressDate').value;
        const progressStatus = document.getElementById('progressStatus').value;

        if (!name || !date || !progressStatus) {
            return;
        }

        progressLogs.unshift({ name, date, progressStatus });
        addImpactLog(name, 'Progress Monitoring', `${progressStatus} on ${date}`);
        event.target.reset();
    });

    document.getElementById('recoverForm').addEventListener('submit', (event) => {
        event.preventDefault();
        const name = document.getElementById('recoverName').value.trim();

        if (!name) {
            return;
        }

        recoveredCases.unshift({ name, date: new Date().toLocaleDateString() });
        addImpactLog(name, 'Recovered Case', 'Marked as recovered');
        event.target.reset();
    });

    refreshSummary();
    renderImpactLogs();
</script>
</body>
</html>
