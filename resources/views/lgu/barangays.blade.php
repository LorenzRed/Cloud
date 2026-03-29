<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Barangays Management</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        .progress-bar {
            height: 8px;
            border-radius: 4px;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }

        .dark .progress-bar {
            background-color: #334155;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2A64F5",
                        "secondary": "#1ABC9C",
                        "accent-orange": "#F39C12",
                        "accent-red": "#E74C3C",
                        "background-light": "#F4F6F9",
                        "background-dark": "#1a1c23",
                        "text-light": "#34495E",
                        "text-dark": "#E0E0E0",
                        "border-light": "#EAECEF",
                        "border-dark": "#343841",
                    },
                    fontFamily: {
                        "display": ["Manrope", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex h-full w-full">
            @include('sidebars.lgu')

            <main class="flex-1 flex flex-col h-screen">
                <div class="flex-1 overflow-y-auto p-4 md:p-8">
                    <div class="flex flex-wrap justify-between gap-4 items-center mb-6">
                        <div class="flex flex-col gap-1">
                            <p class="text-text-light dark:text-text-dark text-3xl font-bold tracking-tight">Barangays Management</p>
                            <p class="text-text-light/70 dark:text-text-dark/70 text-base">Manage and monitor all barangays in your jurisdiction.</p>
                        </div>
                        <button id="toggle-add-form" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined text-lg">add</span>
                            <span class="text-sm font-medium">Add New Barangay</span>
                        </button>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div id="add-form-panel" class="hidden rounded-xl bg-white dark:bg-background-dark/40 border border-border-light dark:border-border-dark p-5 mb-6">
                        <h3 class="text-text-light dark:text-text-dark text-lg font-semibold mb-4">Add Barangay and Assign Captain</h3>

                        <form method="POST" action="{{ route('lgu.barangays.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium mb-1 text-text-light dark:text-text-dark">Barangay Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="Enter barangay name"
                                    class="w-full pl-3 pr-3 py-2 rounded-lg bg-white dark:bg-background-dark/40 border border-border-light dark:border-border-dark text-text-light dark:text-text-dark focus:outline-none focus:ring-2 focus:ring-primary/50" />
                            </div>

                            <div>
                                <label for="captain_user_id" class="block text-sm font-medium mb-1 text-text-light dark:text-text-dark">Assign Barangay Captain</label>
                                <select id="captain_user_id" name="captain_user_id"
                                    class="w-full pl-3 pr-3 py-2 rounded-lg bg-white dark:bg-background-dark/40 border border-border-light dark:border-border-dark text-text-light dark:text-text-dark focus:outline-none focus:ring-2 focus:ring-primary/50">
                                    <option value="">No assignment yet</option>
                                    @foreach ($availableCaptains as $captain)
                                        <option value="{{ $captain->id }}" @selected((string) old('captain_user_id') === (string) $captain->id)>{{ $captain->name }} ({{ $captain->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full md:w-auto flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors">
                                    <span class="material-symbols-outlined text-lg">check</span>
                                    <span class="text-sm font-medium">Save Barangay</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
                        @forelse ($barangays as $barangay)
                            <div class="rounded-xl bg-white dark:bg-background-dark/40 border border-border-light dark:border-border-dark overflow-hidden">
                                <div class="p-5">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-text-light dark:text-text-dark text-lg font-semibold">{{ $barangay->name }}</h3>
                                        <div class="flex gap-1">
                                            <button type="button" class="p-1 rounded text-text-light/70 dark:text-text-dark/70 hover:bg-black/5 dark:hover:bg-white/5">
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </button>
                                            <button type="button" class="p-1 rounded text-text-light/70 dark:text-text-dark/70 hover:bg-black/5 dark:hover:bg-white/5">
                                                <span class="material-symbols-outlined text-lg">more_vert</span>
                                            </button>
                                        </div>
                                    </div>

                                    <span class="inline-flex items-center bg-primary/10 text-primary text-xs font-medium px-2.5 py-0.5 rounded-full mb-4">Active</span>

                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="bg-primary/10 text-primary p-2 rounded-lg">
                                            <span class="material-symbols-outlined">account_box</span>
                                        </div>
                                        <div>
                                            <p class="text-text-light/70 dark:text-text-dark/70 text-sm">Assigned Captain</p>
                                            <p class="text-text-light dark:text-text-dark text-sm font-semibold">{{ $barangay->captain?->name ?? 'Not assigned' }}</p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-text-light/70 dark:text-text-dark/70">Captain Assignment</span>
                                                <span class="text-text-light dark:text-text-dark font-medium">{{ $barangay->captain ? '100%' : '0%' }}</span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress-fill bg-secondary" style="width: {{ $barangay->captain ? '100' : '0' }}%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-border-light dark:border-border-dark">
                                        <p class="text-text-light/70 dark:text-text-dark/70 text-xs">Created</p>
                                        <p class="text-text-light dark:text-text-dark text-sm font-semibold">{{ $barangay->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full rounded-xl bg-white dark:bg-background-dark/40 border border-border-light dark:border-border-dark p-8 text-center">
                                <p class="text-text-light/70 dark:text-text-dark/70 text-base">No barangays yet. Click "Add New Barangay" to create one.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const toggleAddFormButton = document.getElementById('toggle-add-form');
        const addFormPanel = document.getElementById('add-form-panel');

        toggleAddFormButton.addEventListener('click', () => {
            addFormPanel.classList.toggle('hidden');
        });

        @if ($errors->any())
            addFormPanel.classList.remove('hidden');
        @endif
    </script>
</body>
</html>
