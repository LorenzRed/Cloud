<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - BHW Head Posts</title>
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

    .post-card {
        min-height: 17.5rem;
    }

    .post-card-text {
        overflow-wrap: anywhere;
        word-break: break-word;
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
@include('sidebars.bhw_head')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-head-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 overflow-hidden pt-3 px-4 pb-24 sm:px-5 lg:px-8 lg:pt-8 lg:pb-6">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto space-y-6">
                <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold">Posts</h1>
                        <p class="mt-1 text-sm text-black/70 dark:text-gray-400">Barangay-wide announcements you posted for all residents.</p>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5 items-stretch">
                    @forelse ($posts as $post)
                        @php
                            $eventStartTime = null;
                            $eventEndTime = null;
                            $isExpired = $post->isExpired();

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
                        <article class="post-card h-full overflow-hidden rounded-xl border {{ $isExpired ? 'border-gray-300 dark:border-gray-600 opacity-95' : 'border-[#c5daf5] dark:border-gray-700' }} bg-white dark:bg-gray-800 shadow-sm">
                            <div class="p-4 md:p-5 flex flex-col h-full min-h-0">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 shrink-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-[#4A90E2] dark:text-[#93c5fd]">
                                            Announcement
                                        </span>
                                        @if ($isExpired)
                                            <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                Expired
                                            </span>
                                        @elseif ($post->disabled_at)
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                                                Disabled
                                            </span>
                                        @endif
                                    </div>

                                    <span class="text-xs text-black/60 dark:text-gray-400 shrink-0">
                                        {{ $isExpired ? 'Expired' : 'Expires' }} {{ $post->expiresAt()?->format('M d, Y · h:i A') ?? ($post->event_date?->format('M d, Y') ?? 'TBA') }}
                                    </span>
                                </div>

                                <h2 class="post-card-text text-lg font-bold leading-snug text-[#0e1b13] dark:text-white line-clamp-2 min-h-[2.75rem]">{{ $post->title }}</h2>

                                <div class="mt-2 flex-1 min-h-[5.25rem]">
                                    @if ($post->description)
                                        <p class="post-card-text text-sm text-black dark:text-gray-200 leading-relaxed line-clamp-4">{{ $post->description }}</p>
                                    @else
                                        <p class="text-sm text-black/40 dark:text-gray-500 italic">No message</p>
                                    @endif
                                </div>

                                <div class="mt-auto pt-4 shrink-0 space-y-1.5 text-sm text-black/80 dark:text-gray-300">
                                    @if ($eventStartTime)
                                        <p class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-base text-[#4A90E2]">schedule</span>
                                            <span>
                                                {{ $eventStartTime }}
                                                @if ($eventEndTime)
                                                    - {{ $eventEndTime }}
                                                @endif
                                            </span>
                                        </p>
                                    @endif

                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 text-xs text-black/60 dark:text-gray-400 min-w-0">
                                            <span class="material-symbols-outlined text-sm text-[#4A90E2] shrink-0">edit_calendar</span>
                                            <span class="truncate">Posted {{ $post->created_at?->diffForHumans() }}</span>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#c5daf5] bg-[#f4f8fc] text-[#4A90E2] transition-colors hover:bg-[#e8f1fb]"
                                                data-open-edit-modal="{{ $post->id }}"
                                                aria-label="Edit announcement"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#d0021b] text-[#d0021b] transition-colors hover:bg-red-50"
                                                data-open-delete-modal="{{ $post->id }}"
                                                data-delete-title="{{ e($post->title) }}"
                                                aria-label="Delete post"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="editModal-{{ $post->id }}" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4" role="dialog" aria-modal="true" aria-labelledby="editModalTitle-{{ $post->id }}">
                                <div class="w-full sm:max-w-lg max-h-[90dvh] overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white dark:bg-gray-800 border border-[#c5daf5] dark:border-gray-700 shadow-2xl flex flex-col" data-edit-modal-panel>
                                    <div class="flex items-start justify-between gap-3 border-b border-[#c5daf5] dark:border-gray-700 px-4 sm:px-5 py-4 shrink-0">
                                        <div class="min-w-0 pr-2">
                                            <h3 id="editModalTitle-{{ $post->id }}" class="text-lg font-bold text-[#0e1b13] dark:text-white">Edit Announcement</h3>
                                            <p class="mt-0.5 text-sm text-black/70 dark:text-gray-400">Update details sent to residents.</p>
                                        </div>
                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#c5daf5] dark:border-gray-700 text-black/70 hover:bg-[#f4f8fc] dark:hover:bg-gray-700 shrink-0" data-close-edit-modal="{{ $post->id }}" aria-label="Close edit modal">
                                            <span class="material-symbols-outlined text-[20px]">close</span>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('bhw-head.posts.update', $post) }}" class="flex flex-col min-h-0 flex-1">
                                        @csrf
                                        @method('PUT')
                                        <div class="overflow-y-auto px-4 sm:px-5 py-4 space-y-3 flex-1" style="padding-bottom: max(1rem, env(safe-area-inset-bottom, 0px));">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">Title</label>
                                                <input type="text" name="title" value="{{ old('title', $post->title) }}" required class="w-full h-11 rounded-xl bg-[#f4f8fc] dark:bg-gray-900 border border-[#c5daf5] dark:border-gray-700 px-3 text-sm">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">What to announce</label>
                                                <textarea name="description" rows="5" class="w-full rounded-xl bg-[#f4f8fc] dark:bg-gray-900 border border-[#c5daf5] dark:border-gray-700 px-3 py-2.5 text-sm resize-y">{{ old('description', $post->description) }}</textarea>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">Expire on date</label>
                                                    <input type="date" name="event_date" value="{{ old('event_date', optional($post->event_date)->format('Y-m-d')) }}" required class="w-full h-11 rounded-xl bg-[#f4f8fc] dark:bg-gray-900 border border-[#c5daf5] dark:border-gray-700 px-3 text-sm">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-sm font-medium text-[#0e1b13] dark:text-gray-200">Expire at time</label>
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
                                                    <input type="time" name="end_time" value="{{ $editEndTime }}" required class="w-full h-11 rounded-xl bg-[#f4f8fc] dark:bg-gray-900 border border-[#c5daf5] dark:border-gray-700 px-3 text-sm">
                                                </div>
                                            </div>
                                            <input type="hidden" name="start_time" value="">
                                            <input type="hidden" name="location" value="">
                                        </div>
                                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-[#c5daf5] dark:border-gray-700 px-4 sm:px-5 py-3 shrink-0" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0px));">
                                            <button type="button" class="h-11 w-full sm:w-auto px-4 rounded-xl border border-[#c5daf5] dark:border-gray-700 text-sm font-semibold text-black/70 dark:text-gray-300" data-close-edit-modal="{{ $post->id }}">Cancel</button>
                                            <button type="submit" class="h-11 w-full sm:w-auto px-4 rounded-xl bg-[#4A90E2] text-white text-sm font-semibold hover:bg-[#3a7bc8]">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="md:col-span-2 rounded-xl border border-dashed border-[#c5daf5] dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-10 text-center">
                            <span class="material-symbols-outlined text-5xl text-[#4A90E2] dark:text-gray-400">campaign</span>
                            <p class="mt-3 text-base font-semibold">No announcements posted yet.</p>
                            <p class="mt-1 text-sm text-black/70 dark:text-gray-400">Use the Announcements page to post to all barangay residents.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>

    @include('partials.bhw-head-mobile-nav')
</div>

<div id="deletePostModal" class="fixed inset-0 z-[60] hidden items-end sm:items-center justify-center bg-black/50 px-0 sm:px-4" role="dialog" aria-modal="true" aria-labelledby="deletePostModalTitle">
    <div class="w-full sm:max-w-md overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white dark:bg-gray-800 border border-[#c5daf5] dark:border-gray-700 shadow-2xl">
        <div class="flex items-start justify-between gap-3 border-b border-[#c5daf5] dark:border-gray-700 px-4 sm:px-5 py-4">
            <div class="min-w-0 pr-2">
                <h3 id="deletePostModalTitle" class="text-lg font-bold text-red-600 dark:text-red-300">
                    <span class="material-symbols-outlined align-text-bottom mr-1">delete</span>
                    Delete Announcement
                </h3>
                <p class="mt-0.5 text-sm text-black/70 dark:text-gray-400">This action cannot be undone.</p>
            </div>
            <button type="button" id="closeDeletePostModalBtn" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#c5daf5] dark:border-gray-700 text-black/70 hover:bg-[#f4f8fc] dark:hover:bg-gray-700 shrink-0" aria-label="Close">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form id="deletePostForm" method="POST" action="" class="px-4 sm:px-5 py-4 space-y-4" style="padding-bottom: max(1rem, env(safe-area-inset-bottom, 0px));">
            @csrf
            @method('DELETE')
            <p class="text-sm leading-6 text-black/80 dark:text-gray-300">
                Are you sure you want to delete
                <span id="deletePostTitle" class="font-semibold text-[#0e1b13] dark:text-white"></span>?
            </p>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                <button type="button" id="cancelDeletePostModalBtn" class="h-11 w-full sm:w-auto px-4 rounded-xl border border-[#c5daf5] dark:border-gray-700 text-sm font-semibold text-black/70 dark:text-gray-300">Cancel</button>
                <button type="submit" class="h-11 w-full sm:w-auto px-4 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    const editModalTriggers = document.querySelectorAll('[data-open-edit-modal]');
    const editModalClosers = document.querySelectorAll('[data-close-edit-modal]');
    const deletePostModal = document.getElementById('deletePostModal');
    const deletePostForm = document.getElementById('deletePostForm');
    const deletePostTitle = document.getElementById('deletePostTitle');
    const closeDeletePostModalBtn = document.getElementById('closeDeletePostModalBtn');
    const cancelDeletePostModalBtn = document.getElementById('cancelDeletePostModalBtn');
    const deleteDestroyBase = @json(url('/bhw-head/posts'));

    function anyModalOpen() {
        return Boolean(
            document.querySelector('[id^="editModal-"]:not(.hidden)') ||
            (deletePostModal && ! deletePostModal.classList.contains('hidden'))
        );
    }

    function syncBodyScroll() {
        document.body.classList.toggle('overflow-hidden', anyModalOpen());
    }

    function openEditModal(postId) {
        const modal = document.getElementById(`editModal-${postId}`);
        if (! modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        syncBodyScroll();
    }

    function closeEditModal(postId) {
        const modal = document.getElementById(`editModal-${postId}`);
        if (! modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        syncBodyScroll();
    }

    function openDeleteModal(postId, title) {
        if (! deletePostModal || ! deletePostForm) return;
        deletePostForm.action = `${deleteDestroyBase}/${postId}`;
        if (deletePostTitle) {
            deletePostTitle.textContent = title || 'this announcement';
        }
        deletePostModal.classList.remove('hidden');
        deletePostModal.classList.add('flex');
        syncBodyScroll();
    }

    function closeDeleteModal() {
        if (! deletePostModal) return;
        deletePostModal.classList.add('hidden');
        deletePostModal.classList.remove('flex');
        syncBodyScroll();
    }

    editModalTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            openEditModal(trigger.getAttribute('data-open-edit-modal'));
        });
    });

    editModalClosers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            closeEditModal(trigger.getAttribute('data-close-edit-modal'));
        });
    });

    document.querySelectorAll('[id^="editModal-"]').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeEditModal(modal.id.replace('editModal-', ''));
            }
        });
    });

    document.querySelectorAll('[data-open-delete-modal]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            openDeleteModal(
                trigger.getAttribute('data-open-delete-modal'),
                trigger.getAttribute('data-delete-title')
            );
        });
    });

    closeDeletePostModalBtn?.addEventListener('click', closeDeleteModal);
    cancelDeletePostModalBtn?.addEventListener('click', closeDeleteModal);
    deletePostModal?.addEventListener('click', (event) => {
        if (event.target === deletePostModal) {
            closeDeleteModal();
        }
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
