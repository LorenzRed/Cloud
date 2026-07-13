<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - BHW Posts</title>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
<style>
    .material-symbols-outlined {
        font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24
    }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }

    .page-content {
        height: 100%;
        overflow-y: auto;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
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
@include('sidebars.bhw')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 overflow-hidden pt-3 px-4 pb-24 sm:px-5 lg:px-8 lg:pt-8 lg:pb-6">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto space-y-6">
                <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold">Posts</h1>
                        <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Latest announcements from your barangay.</p>
                    </div>
                </header>

                @if (session('status'))
                    <div data-auto-dismiss="3000" class="fixed top-4 right-4 z-50 w-[min(92vw,24rem)] rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg transition-all duration-300 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-200" role="status" aria-live="polite">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-base">check_circle</span>
                            <p class="flex-1">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div data-auto-dismiss="3000" class="fixed top-4 right-4 z-50 w-[min(92vw,24rem)] rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg transition-all duration-300 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200" role="alert" aria-live="assertive">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-base">error</span>
                            <div class="space-y-1">
                                <p class="font-semibold">Please fix the following:</p>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                    @forelse ($posts as $post)
                        @php
                            $eventStartTime = null;
                            $eventEndTime = null;

                            if ($post->start_time) {
                                try {
                                    $eventStartTime = \Illuminate\Support\Carbon::createFromFormat('H:i:s', $post->start_time)->format('h:i A');
                                } catch (\Throwable $e) {
                                    $eventStartTime = \Illuminate\Support\Carbon::parse($post->start_time)->format('h:i A');
                                }
                            }

                            if ($post->end_time) {
                                try {
                                    $eventEndTime = \Illuminate\Support\Carbon::createFromFormat('H:i:s', $post->end_time)->format('h:i A');
                                } catch (\Throwable $e) {
                                    $eventEndTime = \Illuminate\Support\Carbon::parse($post->end_time)->format('h:i A');
                                }
                            }
                        @endphp
                        <article class="overflow-hidden rounded-xl border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                            <div class="p-4 md:p-5 flex flex-col">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-[#1f7f4e] dark:text-[#8cefb5]">
                                            Announcement
                                        </span>

                                        @if ($post->disabled_at)
                                            <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-[#9a5a00] dark:bg-orange-900/30 dark:text-orange-200">
                                                Disabled from users
                                            </span>
                                        @endif
                                    </div>

                                    <span class="text-xs text-[#4e976b] dark:text-gray-400">
                                        Remove {{ $post->expiresAt()?->format('M d, Y · h:i A') ?? ($post->event_date?->format('M d, Y') ?? 'TBA') }}
                                    </span>
                                </div>
                                <h2 class="text-lg font-bold leading-tight text-[#0e1b13] dark:text-white">{{ $post->title }}</h2>

                                @if ($post->description)
                                    <p class="mt-2 text-sm text-[#376f50] dark:text-gray-300 line-clamp-3">{{ $post->description }}</p>
                                @endif

                                <div class="mt-4 space-y-1.5 text-sm text-[#376f50] dark:text-gray-300">
                                    @if ($eventStartTime)
                                        <p class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-base">schedule</span>
                                            <span>
                                                {{ $eventStartTime }}
                                                @if ($eventEndTime)
                                                    - {{ $eventEndTime }}
                                                @endif
                                            </span>
                                        </p>
                                    @endif
                                    @if ($post->location)
                                        <p class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-base">location_on</span>
                                            <span>{{ $post->location }}</span>
                                        </p>
                                    @endif

                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-xs text-[#4e976b] dark:text-gray-400">
                                            <span class="material-symbols-outlined text-sm">edit_calendar</span>
                                            <span>Posted {{ $post->created_at?->diffForHumans() }}</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#d0e7d9] bg-[#f6f8f7] text-[#1f7f4e] transition-colors hover:bg-[#e8f5ec]"
                                                data-open-edit-modal="{{ $post->id }}"
                                                aria-label="Edit announcement"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>

                                            <form method="POST" action="{{ route('bhw.posts.toggle-disable', $post) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full border {{ $post->disabled_at ? 'border-[#1f7f4e] text-[#1f7f4e] hover:bg-[#e8f5ec]' : 'border-[#F5A623] text-[#F5A623] hover:bg-orange-50' }} transition-colors"
                                                    aria-label="{{ $post->disabled_at ? 'Enable announcement' : 'Disable announcement' }}"
                                                >
                                                    <span class="material-symbols-outlined text-[18px]">{{ $post->disabled_at ? 'visibility' : 'visibility_off' }}</span>
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('bhw.posts.destroy', $post) }}" onsubmit="return confirm('Delete this announcement?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#d0021b] text-[#d0021b] transition-colors hover:bg-red-50"
                                                    aria-label="Delete post"
                                                >
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="editModal-{{ $post->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 px-3 py-4 sm:px-4 sm:py-6">
                                <div class="w-[94vw] max-w-[720px] rounded-2xl bg-white dark:bg-gray-800 shadow-2xl overflow-hidden">
                                    <div class="flex items-center justify-between gap-4 border-b border-[#d0e7d9] dark:border-gray-700 px-4 py-3">
                                        <div>
                                            <h3 class="text-lg font-bold text-[#0e1b13] dark:text-white">Edit Announcement</h3>
                                            <p class="text-sm text-[#4e976b] dark:text-gray-400">Update the announcement details sent to residents.</p>
                                        </div>
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#d0e7d9] text-[#4e976b] transition-colors hover:bg-[#f6f8f7]" data-close-edit-modal="{{ $post->id }}" aria-label="Close edit modal">
                                            <span class="material-symbols-outlined text-[20px]">close</span>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('bhw.posts.update', $post) }}" class="space-y-3 p-3 sm:p-4">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">Title</label>
                                            <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="w-full h-10 rounded-lg bg-[#f6f8f7] dark:bg-gray-900 border border-[#d0e7d9] dark:border-gray-700 px-3 text-sm">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">What to announce</label>
                                            <textarea name="description" rows="4" class="w-full rounded-lg bg-[#f6f8f7] dark:bg-gray-900 border border-[#d0e7d9] dark:border-gray-700 px-3 py-2 text-sm">{{ old('description', $post->description) }}</textarea>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">Remove on date</label>
                                                <input type="date" name="event_date" value="{{ old('event_date', optional($post->event_date)->format('Y-m-d')) }}" required class="w-full h-10 rounded-lg bg-[#f6f8f7] dark:bg-gray-900 border border-[#d0e7d9] dark:border-gray-700 px-3 text-sm">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">Remove at time</label>
                                                @php
                                                    $editEndTime = old('end_time');
                                                    if ($editEndTime === null && $post->end_time) {
                                                        try {
                                                            $editEndTime = \Illuminate\Support\Carbon::createFromFormat('H:i:s', $post->end_time)->format('H:i');
                                                        } catch (\Throwable $e) {
                                                            $editEndTime = \Illuminate\Support\Carbon::parse($post->end_time)->format('H:i');
                                                        }
                                                    }
                                                @endphp
                                                <input type="time" name="end_time" value="{{ $editEndTime }}" required class="w-full h-10 rounded-lg bg-[#f6f8f7] dark:bg-gray-900 border border-[#d0e7d9] dark:border-gray-700 px-3 text-sm">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">Venue / Location <span class="font-normal text-[#4e976b]">(optional)</span></label>
                                            <input type="text" name="location" value="{{ old('location', $post->location) }}" placeholder="Venue / location" class="w-full h-10 rounded-lg bg-[#f6f8f7] dark:bg-gray-900 border border-[#d0e7d9] dark:border-gray-700 px-3 text-sm">
                                        </div>
                                        <input type="hidden" name="start_time" value="">
                                        <div class="flex flex-wrap gap-2 pt-1">
                                            <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-[#0e1b13] text-sm font-semibold">Save changes</button>
                                            <button type="button" class="h-10 px-4 rounded-lg border border-[#d0e7d9] text-[#4e976b] text-sm font-semibold" data-close-edit-modal="{{ $post->id }}">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="md:col-span-2 rounded-xl border border-dashed border-[#d0e7d9] dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-10 text-center">
                            <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-400">campaign</span>
                            <p class="mt-3 text-base font-semibold">No announcements posted yet.</p>
                            <p class="mt-1 text-sm text-[#4e976b] dark:text-gray-400">Use the Announcements page to post a new announcement.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>

    @include('partials.bhw-mobile-nav')
</div>

<script>
    const editModalTriggers = document.querySelectorAll('[data-open-edit-modal]');
    const editModalClosers = document.querySelectorAll('[data-close-edit-modal]');

    editModalTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modalId = `editModal-${trigger.getAttribute('data-open-edit-modal')}`;
            const modal = document.getElementById(modalId);

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        });
    });

    editModalClosers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modalId = `editModal-${trigger.getAttribute('data-close-edit-modal')}`;
            const modal = document.getElementById(modalId);

            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });

    document.querySelectorAll('[data-auto-dismiss]').forEach((toast) => {
        const timeout = Number(toast.getAttribute('data-auto-dismiss')) || 3000;

        window.setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-[-0.5rem]');
            window.setTimeout(() => {
                toast.remove();
            }, 300);
        }, timeout);
    });
</script>
</body>
</html>
