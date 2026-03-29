<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Resident Requests</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet"/>
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

    .dark .page-content::-webkit-scrollbar-track {
        background: #2c2c2c;
    }

    .dark .page-content::-webkit-scrollbar-thumb {
        background: #555;
    }

    .dark .page-content::-webkit-scrollbar-thumb:hover {
        background: #777;
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

    @include('sidebars.bhw')

    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Resident Requests</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Review requests, verify identities, and send instructions to residents.</p>
                    </div>
                    <a href="{{ route('bhw.residents') }}" class="inline-flex items-center gap-2 h-10 px-4 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-lg text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                        <span class="material-symbols-outlined text-lg">groups</span>
                        Residents List
                    </a>
                </header>

                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <form method="GET" action="{{ route('bhw.requests') }}" class="mb-5">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50">search</span>
                            <input
                                class="w-full h-10 pl-10 pr-4 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-text-light dark:text-text-dark placeholder:text-text-light/50 dark:placeholder:text-text-dark/50"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search resident request by name or email..."
                                type="text"
                            />
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border-light dark:border-border-dark">
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Resident</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Age</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Requested</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Barangay</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-text-light/70 dark:text-text-dark/70 text-xs font-medium uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingRequests as $pendingRequest)
                                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark align-top">
                                        <td class="px-4 py-3 text-sm">
                                            <p class="font-medium text-text-light dark:text-text-dark">{{ $pendingRequest->full_name }}</p>
                                            <p class="text-text-light/70 dark:text-text-dark/70">{{ $pendingRequest->user->email ?? 'No email' }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70">{{ $pendingRequest->age ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70">{{ $pendingRequest->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70">{{ $pendingRequest->barangay ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-accent-orange dark:text-yellow-300">Pending</span>
                                        </td>
                                        <td class="px-4 py-3 min-w-[340px]">
                                            <div class="flex gap-2 mb-2">
                                                <form method="POST" action="{{ route('bhw.requests.accept', $pendingRequest) }}" class="flex-1">
                                                    @csrf
                                                    <button type="submit" class="w-full h-10 bg-primary text-white rounded-lg text-sm font-semibold hover:opacity-90">Accept</button>
                                                </form>
                                                <form method="POST" action="{{ route('bhw.requests.reject', $pendingRequest) }}" class="flex-1">
                                                    @csrf
                                                    <button type="submit" class="w-full h-10 bg-accent-red text-white rounded-lg text-sm font-semibold hover:opacity-90">Reject</button>
                                                </form>
                                            </div>
                                            <form method="POST" action="{{ route('bhw.requests.message', ['residentRequest' => $pendingRequest, 'search' => $search]) }}" class="flex gap-2">
                                                @csrf
                                                <input
                                                    name="message"
                                                    required
                                                    maxlength="500"
                                                    class="flex-1 h-10 px-3 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                    placeholder="Message to resident for identity verification."
                                                    type="text"
                                                />
                                                <button type="submit" class="inline-flex items-center justify-center gap-1 h-10 px-3 rounded-lg border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                                                    <span class="material-symbols-outlined text-base">send</span>
                                                    Send
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="h-[72px] px-4 py-2 text-center">
                                            <div class="py-8">
                                                <span class="material-symbols-outlined text-4xl text-green-500 mb-2 block">check_circle</span>
                                                <p class="text-text-light/70 dark:text-text-dark/70 text-sm">All resident verification requests are already processed.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
</script>
</body>
</html>
