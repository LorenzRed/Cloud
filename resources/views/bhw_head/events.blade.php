<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - BHW Head Announcements</title>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    .page-content { height: 100%; overflow-y: auto; width: 100%; scrollbar-width: thin; -webkit-overflow-scrolling: touch; }

    @media (min-width: 769px) {
        .page-content { padding: 0; }
        .page-panel { border-radius: 0; min-height: 100%; }
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#4A90E2",
                    "background-light": "#F4F4F4",
                    "background-dark": "#1A1A1A",
                    "text-light": "#4A4A4A",
                    "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF",
                    "card-dark": "#2C2C2C",
                    "border-light": "#EAEAEA",
                    "border-dark": "#3D3D3D",
                },
                fontFamily: { display: ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('sidebars.bhw_head')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-head-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 overflow-hidden pb-24 lg:pb-0 w-full min-w-0">
        <div class="page-content">
            <div class="page-panel w-full bg-card-light dark:bg-card-dark border-y border-border-light dark:border-border-dark p-5 sm:p-6 lg:p-8">
                    <h1 class="text-2xl sm:text-3xl font-bold mb-2">Announcements</h1>
                    <p class="mb-6 pb-4 border-b border-border-light dark:border-border-dark text-sm text-black/70 dark:text-text-dark/70">Posts go to all accepted residents in your barangay (every purok).</p>
                    <form id="announcementForm" method="POST" action="{{ route('bhw-head.posts.store') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="type" value="announcement">
                        <input type="hidden" name="start_time" value="">
                        <input type="hidden" name="location" value="">

                        <div>
                            <label for="announcementTitle" class="mb-2 block text-sm font-semibold">Title</label>
                            <input id="announcementTitle" name="title" type="text" required maxlength="255" placeholder="Short title for all barangay residents" class="w-full h-12 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 text-base focus:border-primary focus:ring-primary/20">
                        </div>

                        <div>
                            <label for="announcementMessage" class="mb-2 block text-sm font-semibold">What to announce</label>
                            <textarea id="announcementMessage" name="description" required rows="7" maxlength="2000" placeholder="Write the message you want all residents to read..." class="w-full min-h-[190px] lg:min-h-[270px] rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 py-3 text-base leading-relaxed resize-y focus:border-primary focus:ring-primary/20"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="announcementDate" class="mb-2 block text-sm font-semibold">Expire on date</label>
                                <input id="announcementDate" name="event_date" type="date" required value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" class="w-full h-12 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 text-base focus:border-primary focus:ring-primary/20">
                            </div>
                            <div>
                                <label for="announcementEndTime" class="mb-2 block text-sm font-semibold">Expire at time</label>
                                <input id="announcementEndTime" name="end_time" type="time" required value="{{ now()->addHour()->format('H:i') }}" class="w-full h-12 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark px-4 text-base focus:border-primary focus:ring-primary/20">
                            </div>
                        </div>
                        <p class="text-xs text-black/60 dark:text-text-dark/60 -mt-2">After this date and time, the post is marked expired. Residents can still see it for 1 week on Announcements, then it is hidden from them. What's New only shows active posts.</p>
                        <div class="flex justify-end">
                            <button id="announcementSubmitBtn" type="submit" class="inline-flex h-12 w-full sm:w-auto sm:min-w-[160px] items-center justify-center gap-2 rounded-xl bg-primary px-8 text-sm font-bold text-white hover:opacity-90 disabled:opacity-60 transition-opacity">
                                <span class="material-symbols-outlined text-base">send</span>
                                Post
                            </button>
                        </div>
                    </form>
            </div>
        </div>
    </main>

    @include('partials.bhw-head-mobile-nav')
</div>

<script>
    const announcementForm = document.getElementById('announcementForm');
    const announcementSubmitBtn = document.getElementById('announcementSubmitBtn');
    const toastContainer = document.createElement('div');
    toastContainer.className = 'fixed top-4 right-4 z-50 flex w-[min(92vw,22rem)] flex-col gap-3';
    document.body.appendChild(toastContainer);

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const ok = type === 'success';
        toast.className = `rounded-xl border px-4 py-3 text-sm shadow-lg ${ok ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'}`;
        toast.innerHTML = `<div class="flex items-start gap-2"><span class="material-symbols-outlined text-base">${ok ? 'check_circle' : 'error'}</span><span>${escapeHtml(message)}</span></div>`;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    announcementForm?.addEventListener('submit', (event) => {
        event.preventDefault();

        const title = document.getElementById('announcementTitle')?.value.trim();
        const message = document.getElementById('announcementMessage')?.value.trim();

        if (!title || !message) {
            showToast('Please fill in title, message, date, and time.', 'error');
            return;
        }

        const dateInput = document.getElementById('announcementDate');
        const timeInput = document.getElementById('announcementEndTime');
        if (!dateInput?.value || !timeInput?.value) {
            showToast('Please set the expire date and time.', 'error');
            return;
        }

        announcementSubmitBtn.disabled = true;

        fetch(@json(route('bhw-head.posts.store')), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: new FormData(announcementForm),
        })
            .then(async (response) => {
                const data = await response.json().catch(() => null);
                if (!response.ok) {
                    throw new Error(data?.message || 'Unable to post');
                }
                return data;
            })
            .then((data) => {
                if (data?.success) {
                    announcementForm.reset();
                    const dateInput = document.getElementById('announcementDate');
                    const timeInput = document.getElementById('announcementEndTime');
                    if (dateInput) {
                        dateInput.value = @json(now()->toDateString());
                    }
                    if (timeInput) {
                        const nextHour = new Date();
                        nextHour.setHours(nextHour.getHours() + 1);
                        timeInput.value = String(nextHour.getHours()).padStart(2, '0') + ':' + String(nextHour.getMinutes()).padStart(2, '0');
                    }
                    showToast('Posted successfully.');
                }
            })
            .catch((error) => showToast(error.message, 'error'))
            .finally(() => {
                announcementSubmitBtn.disabled = false;
            });
    });
</script>
</body>
</html>
