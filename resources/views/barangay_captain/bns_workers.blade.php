<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Bantay Kalusugan - BNS Workers</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); transition: transform 0.3s ease-in-out; position: fixed; z-index: 40; height: 100vh; }
            #sidebar.active { transform: translateX(0); }
            #overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 30; }
            #overlay.active { display: block; }
            main { width: 100%; }
        }
        .page-content { height: 100%; overflow-y: auto; }
        .worker-list-scroll { max-height: 28rem; overflow-y: auto; }
        .toast-enter { animation: toast-in 180ms ease-out; }
        .toast-exit { animation: toast-out 180ms ease-in forwards; }
        @keyframes toast-in { from { transform: translateY(-8px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes toast-out { from { transform: translateY(0); opacity: 1; } to { transform: translateY(-8px); opacity: 0; } }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        .main-container { height: 100vh; display: flex; }
        main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#4A90E2", "secondary": "#50E3C2", "accent-orange": "#F5A623",
                        "accent-red": "#D0021B", "background-light": "#F4F4F4", "background-dark": "#1A1A1A",
                        "text-light": "#4A4A4A", "text-dark": "#E0E0E0", "card-light": "#FFFFFF",
                        "card-dark": "#2C2C2C", "border-light": "#EAEAEA", "border-dark": "#3D3D3D",
                    },
                    fontFamily: { "display": ["Manrope", "sans-serif"] },
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
    <div id="overlay"></div>

    @include('sidebars.barangay_captain')

    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-bold leading-tight">BNS Workers</h1>
                        <p class="text-base text-text-light/70 dark:text-text-dark/70">Manage Barangay Nutrition Scholar accounts for your barangay only.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($assignedBarangay)
                            <span class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-sm font-semibold bg-primary/10 text-primary">
                                <span class="material-symbols-outlined text-base">location_city</span>
                                {{ $assignedBarangay->name }}
                            </span>
                            <button type="button" id="openAddBnsModalBtn" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-secondary text-white text-sm font-bold hover:opacity-90">
                                <span class="material-symbols-outlined text-base">person_add</span> Add BNS Worker
                            </button>
                        @endif
                    </div>
                </header>

                @if (session('status'))
                    <div class="sr-only" id="toastData" data-toast-type="success" data-toast-message="{{ session('status') }}"></div>
                @endif
                @if ($errors->any())
                    <div class="sr-only" id="toastData" data-toast-type="error" data-toast-message="{{ $errors->first() }}"></div>
                @endif

                @if (! $assignedBarangay)
                    <div class="rounded-lg border border-border-light dark:border-border-dark p-4 mb-4 bg-card-light dark:bg-card-dark">
                        <p class="text-text-light/80 dark:text-text-dark/80">No barangay assigned yet. BNS worker management will be available once your barangay is assigned.</p>
                    </div>
                @endif

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 w-full space-y-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-bold">Worker Names</h2>
                        <div class="relative w-full sm:max-w-sm">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                            <input id="workerSearch" type="text" class="w-full h-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark pl-10 pr-3 text-sm" placeholder="Search worker name..." />
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-border-light dark:border-border-dark rounded-lg worker-list-scroll">
                        <table class="w-full">
                            <thead class="sticky top-0 z-10">
                                <tr class="border-b border-border-light dark:border-border-dark bg-background-light/60 dark:bg-background-dark/60">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Role</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Barangay</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Action</th>
                                </tr>
                            </thead>
                            <tbody id="workerTableBody">
                                @forelse ($bnsWorkers as $worker)
                                    <tr class="border-b border-border-light dark:border-border-dark worker-row" data-worker-name="{{ strtolower($worker->name) }}">
                                        <td class="px-4 py-3 text-sm font-semibold">{{ $worker->name }}</td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70">{{ $worker->email }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center rounded-full bg-accent-orange/15 px-2.5 py-0.5 text-xs font-semibold text-accent-orange">BNS</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $worker->barangay ?: 'Not assigned' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex items-center justify-center">
                                                <button type="button" class="open-kick-modal-btn inline-flex items-center justify-center rounded h-8 px-2 border border-red-300 bg-red-50 text-red-600 hover:bg-red-100 dark:border-red-800 dark:bg-red-950/50 dark:text-red-300 dark:hover:bg-red-950/70 transition"
                                                    data-worker-id="{{ $worker->id }}" data-worker-name="{{ $worker->name }}" title="Remove BNS permission">
                                                    <span class="material-symbols-outlined text-xs">person_remove</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-text-light/70 dark:text-text-dark/70">No BNS workers found for your barangay.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div id="bnsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-3">
            <div>
                <h2 class="text-xl font-bold">Add BNS Worker</h2>
                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Create a BNS account assigned to {{ $assignedBarangay?->name ?? 'your barangay' }}.</p>
            </div>
            <button type="button" id="closeAddBnsModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="{{ route('barangay-captain.bns-workers.store') }}" class="space-y-3 px-6 pt-3 pb-5">
            @csrf
            <input type="hidden" name="bns_form" value="1" />
            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 px-3" placeholder="bns@example.com" required/>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 pl-3 pr-12" placeholder="Enter a temporary password" required/>
                    <button id="togglePassword" type="button" class="absolute inset-y-0 right-0 flex items-center justify-center w-11 text-text-light/60 dark:text-text-dark/60 hover:text-primary" aria-label="Show password">
                        <span class="material-symbols-outlined text-xl" id="passwordEyeIcon">visibility</span>
                    </button>
                </div>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" class="w-full rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark h-11 pl-3 pr-12" placeholder="Re-enter the password" required/>
                    <button id="toggleConfirmPassword" type="button" class="absolute inset-y-0 right-0 flex items-center justify-center w-11 text-text-light/60 dark:text-text-dark/60 hover:text-primary" aria-label="Show password">
                        <span class="material-symbols-outlined text-xl" id="confirmPasswordEyeIcon">visibility</span>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="cancelAddBnsModalBtn" class="rounded-lg h-11 px-4 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-11 px-4 bg-secondary text-white text-sm font-bold hover:opacity-90">
                    <span class="material-symbols-outlined text-base">person_add</span> Create Worker
                </button>
            </div>
        </form>
    </div>
</div>

<div id="kickModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-lg rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl">
        <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark px-6 py-4">
            <div>
                <h2 class="text-xl font-bold text-red-600 dark:text-red-300"><span class="material-symbols-outlined align-text-bottom mr-2">person_remove</span> Remove Permission</h2>
                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">This action cannot be undone.</p>
            </div>
            <button type="button" id="closeKickModalBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form method="POST" action="{{ route('barangay-captain.bns-workers.kick') }}" class="space-y-4 px-6 py-5">
            @csrf
            <input type="hidden" name="worker_id" id="kickWorkerId" value="" />
            <p class="text-sm leading-6 text-text-light/80 dark:text-text-dark/80">Remove BNS permission from <span id="kickWorkerName" class="font-semibold text-text-light dark:text-text-dark"></span>? They will become a regular user and lose all BNS privileges.</p>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" id="cancelKickModalBtn" class="rounded-lg h-11 px-4 bg-gray-600 text-white text-sm font-semibold hover:bg-gray-700">Cancel</button>
                <button type="submit" class="rounded-lg h-9 px-3 bg-red-100 text-red-700 text-xs border border-red-300 hover:bg-red-200 dark:bg-red-950/50 dark:text-red-300 dark:border-red-800">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const bnsModal = document.getElementById('bnsModal');
        const kickModal = document.getElementById('kickModal');
        const openAddBnsModalBtn = document.getElementById('openAddBnsModalBtn');
        const closeAddBnsModalBtn = document.getElementById('closeAddBnsModalBtn');
        const cancelAddBnsModalBtn = document.getElementById('cancelAddBnsModalBtn');
        const closeKickModalBtn = document.getElementById('closeKickModalBtn');
        const cancelKickModalBtn = document.getElementById('cancelKickModalBtn');
        const workerSearch = document.getElementById('workerSearch');
        const workerRows = document.querySelectorAll('.worker-row');
        const openKickButtons = document.querySelectorAll('.open-kick-modal-btn');
        const kickWorkerId = document.getElementById('kickWorkerId');
        const kickWorkerName = document.getElementById('kickWorkerName');
        const toastData = document.getElementById('toastData');
        let toastTimer = null;

        if (menuToggle && sidebar && overlay) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }

        function openBnsModal() {
            if (!bnsModal) return;
            bnsModal.classList.remove('hidden');
            bnsModal.classList.add('flex');
        }

        function closeBnsModal() {
            if (!bnsModal) return;
            bnsModal.classList.add('hidden');
            bnsModal.classList.remove('flex');
        }

        function openKickModal(workerId, workerName) {
            if (!kickModal || !kickWorkerId) return;
            kickWorkerId.value = workerId;
            if (kickWorkerName) kickWorkerName.textContent = workerName || 'this worker';
            kickModal.classList.remove('hidden');
            kickModal.classList.add('flex');
        }

        function closeKickModal() {
            if (!kickModal) return;
            kickModal.classList.add('hidden');
            kickModal.classList.remove('flex');
        }

        openAddBnsModalBtn?.addEventListener('click', openBnsModal);
        closeAddBnsModalBtn?.addEventListener('click', closeBnsModal);
        cancelAddBnsModalBtn?.addEventListener('click', closeBnsModal);
        closeKickModalBtn?.addEventListener('click', closeKickModal);
        cancelKickModalBtn?.addEventListener('click', closeKickModal);

        openKickButtons.forEach((button) => {
            button.addEventListener('click', function () {
                openKickModal(button.dataset.workerId || '', button.dataset.workerName || '');
            });
        });

        workerSearch?.addEventListener('input', function () {
            const normalized = (this.value || '').trim().toLowerCase();
            workerRows.forEach((row) => {
                const rowName = row.dataset.workerName || '';
                row.style.display = rowName.includes(normalized) ? '' : 'none';
            });
        });

        function bindPasswordToggle(toggleBtn, input, iconEl) {
            if (!toggleBtn || !input) return;
            toggleBtn.addEventListener('click', () => {
                const isHidden = input.getAttribute('type') === 'password';
                input.setAttribute('type', isHidden ? 'text' : 'password');
                if (iconEl) iconEl.textContent = isHidden ? 'visibility_off' : 'visibility';
            });
        }

        bindPasswordToggle(document.getElementById('togglePassword'), document.getElementById('password'), document.getElementById('passwordEyeIcon'));
        bindPasswordToggle(document.getElementById('toggleConfirmPassword'), document.getElementById('password_confirmation'), document.getElementById('confirmPasswordEyeIcon'));

        function showToast(message, type) {
            if (!message) return;
            const existing = document.getElementById('floatingToast');
            if (existing) existing.remove();
            const toast = document.createElement('div');
            toast.id = 'floatingToast';
            toast.className = `toast-enter fixed top-4 right-4 z-[200] max-w-sm rounded-xl px-4 py-3 shadow-lg border ${type === 'error' ? 'border-red-200 bg-red-50 text-red-700 dark:bg-red-950/90 dark:text-red-200 dark:border-red-900' : 'border-green-200 bg-green-50 text-green-700 dark:bg-green-950/90 dark:text-green-200 dark:border-green-900'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = window.setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                window.setTimeout(() => toast.remove(), 180);
            }, 2500);
        }

        if (toastData) {
            showToast(toastData.dataset.toastMessage || '', toastData.dataset.toastType || 'success');
        }

        @if (old('bns_form'))
            openBnsModal();
        @endif
    });
</script>
</body>
</html>
