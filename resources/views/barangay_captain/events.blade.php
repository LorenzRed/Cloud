<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
@include('partials.submit-guard')
<title>Bantay Kalusugan - Events</title>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.captain-shell-styles')
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
@include('sidebars.barangay_captain')

<div id="captainContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.captain-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-4 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Events</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Manage community health events and activities</p>
                    </div>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Post Barangay Health Announcements</h2>
                            <form id="announcementForm" class="space-y-3">
                                <input id="announcementTitle" type="text" required placeholder="Announcement title" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <textarea id="announcementMessage" required rows="3" placeholder="Announcement details" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 py-2"></textarea>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Post announcement</button>
                                </div>
                            </form>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Schedule Health Events</h2>
                            <form id="eventForm" method="POST" action="{{ route('barangay-captain.events.store') }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <input id="eventName" type="text" required placeholder="Event name" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <input id="eventDate" type="date" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                    <input id="eventTime" type="time" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                </div>
                                <input id="eventEndTime" type="time" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3" placeholder="End time">
                                <input id="eventVenue" type="text" required placeholder="Venue" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <input id="eventImage" type="file" accept="image/*" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 py-2 text-sm">
                                <button type="submit" class="h-10 px-4 rounded-lg bg-secondary text-white text-sm font-semibold">Schedule event</button>
                            </form>
                            <p id="eventStatus" class="mt-2 text-sm"></p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border-light dark:border-border-dark p-4 mb-6">
                        <h2 class="text-lg font-bold mb-3">Notify Residents</h2>
                        <form id="notifyForm" class="flex flex-col md:flex-row gap-2">
                            <input id="notifyMessage" type="text" required placeholder="Notification message to residents" class="flex-1 h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                            <button type="submit" class="h-10 px-4 rounded-lg bg-accent-orange text-white text-sm font-semibold">Send notification</button>
                        </form>
                        <p id="notifyStatus" class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2"></p>
                    </div>

                    <div class="rounded-lg border border-border-light dark:border-border-dark p-4 mb-6">
                        <h2 class="text-lg font-bold mb-3">Approve BHW/BNS Announcements</h2>
                        <div id="pendingApprovals" class="space-y-3"></div>
                    </div>

                </div>

                @php
                    $initialScheduledEvents = collect($events ?? [])->map(function ($event) {
                        return [
                            'id' => $event->id,
                            'name' => $event->title,
                            'date' => optional($event->event_date)->format('M d, Y') ?? 'TBA',
                            'time' => $event->start_time,
                            'endTime' => $event->end_time,
                            'venue' => $event->location,
                            'imageUrl' => $event->image_url,
                        ];
                    })->values();
                @endphp
            </div>
        </div>
    </main>

    @include('partials.captain-mobile-nav')
</div>

<script>
const announcementForm = document.getElementById('announcementForm');
    const eventForm = document.getElementById('eventForm');
    const notifyForm = document.getElementById('notifyForm');
    const notifyStatus = document.getElementById('notifyStatus');

    const pendingApprovalsEl = document.getElementById('pendingApprovals');
    const publishedAnnouncementsEl = document.getElementById('publishedAnnouncements');
    const scheduledEventsEl = document.getElementById('scheduledEvents');

    let pendingApprovals = [
        {
            id: crypto.randomUUID(),
            source: 'BHW',
            title: 'Free check-up this Friday at Barangay Hall.',
            message: 'Open for all residents from 8:00 AM to 3:00 PM.',
            createdAt: new Date().toLocaleString(),
        },
        {
            id: crypto.randomUUID(),
            source: 'BNS',
            title: 'Nutrition briefing for parents',
            message: 'Sunday 9:00 AM at the barangay covered court.',
            createdAt: new Date().toLocaleString(),
        },
    ];

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderPendingApprovals() {
        if (!pendingApprovals.length) {
            pendingApprovalsEl.innerHTML = '<p class="text-sm text-text-light/70 dark:text-text-dark/70">No pending BHW/BNS announcements.</p>';
            return;
        }

        pendingApprovalsEl.innerHTML = pendingApprovals.map((item) => `
            <div class="rounded-lg border border-border-light dark:border-border-dark p-3">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                    <div>
                        <p class="font-semibold">${escapeHtml(item.title)}</p>
                        <p class="text-sm text-text-light/80 dark:text-text-dark/80">${escapeHtml(item.message)}</p>
                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">Source: ${escapeHtml(item.source)} • ${escapeHtml(item.createdAt)}</p>
                    </div>
                    <div class="flex gap-2">
                        <button data-approve="${escapeHtml(item.id)}" class="h-9 px-3 rounded-lg bg-primary text-white text-sm font-semibold">Approve</button>
                        <button data-reject="${escapeHtml(item.id)}" class="h-9 px-3 rounded-lg border border-border-light dark:border-border-dark text-sm font-semibold">Reject</button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    eventForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const name = document.getElementById('eventName').value.trim();
        const date = document.getElementById('eventDate').value;
        const time = document.getElementById('eventTime').value;
        const endTime = document.getElementById('eventEndTime').value;
        const venue = document.getElementById('eventVenue').value.trim();
        const imageInput = document.getElementById('eventImage');
        const eventStatus = document.getElementById('eventStatus');

        if (!name || !date || !time || !venue) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formData = new FormData();
        formData.append('title', name);
        formData.append('description', '');
        formData.append('event_date', date);
        formData.append('start_time', time);
        formData.append('end_time', endTime);
        formData.append('location', venue);
        formData.append('category', 'event');

        if (imageInput && imageInput.files && imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        fetch('/barangay-captain/events', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        })
            .then(async (response) => {
                const data = await response.json().catch(() => null);

                if (!response.ok) {
                    throw new Error(data?.message || data?.error || 'Unable to create event');
                }

                return data;
            })
            .then(data => {
                if (data.success) {
                    eventForm.reset();
                    if (eventStatus) {
                        eventStatus.textContent = 'Event posted successfully.';
                        eventStatus.className = 'mt-2 text-sm text-green-700 dark:text-green-300 font-medium';
                        setTimeout(() => {
                            eventStatus.textContent = '';
                        }, 3000);
                    }
                } else {
                    const message = 'Error creating event: ' + (data.error || 'Unknown error');
                    if (eventStatus) {
                        eventStatus.textContent = message;
                        eventStatus.className = 'mt-2 text-sm text-red-700 dark:text-red-300 font-medium';
                    }
                    alert(message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const message = 'Error creating event: ' + error.message;
                if (eventStatus) {
                    eventStatus.textContent = message;
                    eventStatus.className = 'mt-2 text-sm text-red-700 dark:text-red-300 font-medium';
                }
                alert(message);
            });
    });

    notifyForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const message = document.getElementById('notifyMessage').value.trim();
        if (!message) {
            return;
        }

        notifyStatus.textContent = `Notification sent to residents: "${message}"`;
        document.getElementById('notifyMessage').value = '';
    });

    pendingApprovalsEl.addEventListener('click', (event) => {
        const approveId = event.target.getAttribute('data-approve');
        const rejectId = event.target.getAttribute('data-reject');

        if (approveId) {
            const item = pendingApprovals.find((entry) => entry.id === approveId);
            if (item) {
                publishedAnnouncements.unshift(item);
            }
            pendingApprovals = pendingApprovals.filter((entry) => entry.id !== approveId);
            renderPendingApprovals();
            renderPublishedAnnouncements();
        }

        if (rejectId) {
            pendingApprovals = pendingApprovals.filter((entry) => entry.id !== rejectId);
            renderPendingApprovals();
        }
    });

    renderPendingApprovals();

    const eventImageInput = document.getElementById('eventImage');
    const eventImagePreview = document.createElement('div');
    eventImagePreview.className = 'hidden mt-2 overflow-hidden rounded-lg border border-border-light dark:border-border-dark';
    if (eventImageInput && eventImageInput.parentNode) {
        eventImageInput.parentNode.insertBefore(eventImagePreview, eventImageInput.nextSibling);

        eventImageInput.addEventListener('change', () => {
            const file = eventImageInput.files && eventImageInput.files[0];
            if (!file) {
                eventImagePreview.classList.add('hidden');
                eventImagePreview.innerHTML = '';
                return;
            }

            const previewUrl = URL.createObjectURL(file);
            const img = document.createElement('img');
            img.src = previewUrl;
            img.alt = 'Event image preview';
            img.className = 'h-36 w-full object-cover';
            eventImagePreview.classList.remove('hidden');
            eventImagePreview.replaceChildren(img);
        });
    }
</script>
</body>
</html>
