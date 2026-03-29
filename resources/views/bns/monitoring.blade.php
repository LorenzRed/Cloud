<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Child Monitoring</title>
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
                        <h1 class="text-3xl font-bold leading-tight">Child Monitoring</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Add child profiles and track growth history with automatic nutrition classification.</p>
                    </div>
                </header>

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Add child profile</h2>
                    <form id="childProfileForm" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        <input id="childName" type="text" required placeholder="Child name" class="h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        <input id="childAge" type="number" min="0" max="5" required placeholder="Age (0-5)" class="h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        <input id="childWeight" type="number" min="0.1" step="0.1" required placeholder="Weight (kg)" class="h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        <input id="childHeight" type="number" min="1" step="0.1" required placeholder="Height (cm)" class="h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        <input id="childBmi" type="text" readonly placeholder="BMI (auto-computed)" class="h-10 rounded-lg bg-background-light/70 dark:bg-background-dark/70 border border-border-light dark:border-border-dark px-3">
                        <input id="childStatus" type="text" readonly placeholder="Nutrition status (auto)" class="h-10 rounded-lg bg-background-light/70 dark:bg-background-dark/70 border border-border-light dark:border-border-dark px-3">
                        <div class="xl:col-span-3">
                            <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Save child profile</button>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                        <h2 class="text-xl font-bold mb-4">Child Profiles</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-border-light dark:border-border-dark">
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Age</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">BMI</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="profilesTableBody">
                                    <tr>
                                        <td colspan="4" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No child profiles yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                        <h2 class="text-xl font-bold mb-4">Growth Tracking History</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-border-light dark:border-border-dark">
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Date</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Child</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Weight</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Height</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">BMI</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    <tr>
                                        <td colspan="6" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No growth records yet.</td>
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

    const childProfileForm = document.getElementById('childProfileForm');
    const childName = document.getElementById('childName');
    const childAge = document.getElementById('childAge');
    const childWeight = document.getElementById('childWeight');
    const childHeight = document.getElementById('childHeight');
    const childBmi = document.getElementById('childBmi');
    const childStatus = document.getElementById('childStatus');

    const profilesTableBody = document.getElementById('profilesTableBody');
    const historyTableBody = document.getElementById('historyTableBody');

    const childProfiles = [];
    const growthHistory = [];

    function computeBMI(weightKg, heightCm) {
        const heightM = heightCm / 100;
        if (!heightM || heightM <= 0) {
            return 0;
        }

        return weightKg / (heightM * heightM);
    }

    function classifyNutritionStatus(bmi) {
        if (bmi < 16) {
            return 'Severely underweight';
        }

        if (bmi < 18.5) {
            return 'Underweight';
        }

        if (bmi <= 24.9) {
            return 'Normal';
        }

        return 'Overweight';
    }

    function refreshComputedFields() {
        const weightValue = Number(childWeight.value);
        const heightValue = Number(childHeight.value);

        if (!weightValue || !heightValue) {
            childBmi.value = '';
            childStatus.value = '';
            return;
        }

        const bmi = computeBMI(weightValue, heightValue);
        childBmi.value = bmi.toFixed(2);
        childStatus.value = classifyNutritionStatus(bmi);
    }

    function statusBadgeClass(status) {
        if (status === 'Severely underweight') {
            return 'bg-accent-red/10 text-accent-red';
        }

        if (status === 'Underweight') {
            return 'bg-accent-orange/10 text-accent-orange';
        }

        if (status === 'Normal') {
            return 'bg-secondary/15 text-secondary';
        }

        return 'bg-primary/10 text-primary';
    }

    function renderProfiles() {
        if (!childProfiles.length) {
            profilesTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No child profiles yet.</td>
                </tr>
            `;
            return;
        }

        profilesTableBody.innerHTML = childProfiles.map((profile) => `
            <tr class="border-b border-border-light dark:border-border-dark">
                <td class="px-3 py-2 text-sm">${profile.name}</td>
                <td class="px-3 py-2 text-sm">${profile.age}</td>
                <td class="px-3 py-2 text-sm">${profile.bmi.toFixed(2)}</td>
                <td class="px-3 py-2 text-sm">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${statusBadgeClass(profile.status)}">${profile.status}</span>
                </td>
            </tr>
        `).join('');
    }

    function renderHistory() {
        if (!growthHistory.length) {
            historyTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-3 py-4 text-sm text-text-light/70 dark:text-text-dark/70">No growth records yet.</td>
                </tr>
            `;
            return;
        }

        historyTableBody.innerHTML = growthHistory.map((item) => `
            <tr class="border-b border-border-light dark:border-border-dark">
                <td class="px-3 py-2 text-sm">${item.date}</td>
                <td class="px-3 py-2 text-sm">${item.name}</td>
                <td class="px-3 py-2 text-sm">${item.weight} kg</td>
                <td class="px-3 py-2 text-sm">${item.height} cm</td>
                <td class="px-3 py-2 text-sm">${item.bmi.toFixed(2)}</td>
                <td class="px-3 py-2 text-sm">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${statusBadgeClass(item.status)}">${item.status}</span>
                </td>
            </tr>
        `).join('');
    }

    childWeight.addEventListener('input', refreshComputedFields);
    childHeight.addEventListener('input', refreshComputedFields);

    childProfileForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const name = childName.value.trim();
        const age = Number(childAge.value);
        const weight = Number(childWeight.value);
        const height = Number(childHeight.value);

        if (!name || Number.isNaN(age) || Number.isNaN(weight) || Number.isNaN(height)) {
            return;
        }

        const bmi = computeBMI(weight, height);
        const status = classifyNutritionStatus(bmi);

        const profileIndex = childProfiles.findIndex((item) => item.name.toLowerCase() === name.toLowerCase());
        const profile = { name, age, bmi, status };

        if (profileIndex >= 0) {
            childProfiles[profileIndex] = profile;
        } else {
            childProfiles.unshift(profile);
        }

        growthHistory.unshift({
            date: new Date().toLocaleDateString(),
            name,
            weight: weight.toFixed(1),
            height: height.toFixed(1),
            bmi,
            status,
        });

        renderProfiles();
        renderHistory();

        childProfileForm.reset();
        childBmi.value = '';
        childStatus.value = '';
    });
</script>
</body>
</html>
