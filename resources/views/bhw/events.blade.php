<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - BHW Events</title>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
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
    
    @include('sidebars.bhw')
    
    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">Events</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Manage barangay health events and announcements</p>
                    </div>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Post Barangay Health Announcements</h2>
                            <form id="announcementForm" method="POST" action="{{ route('bhw.posts.store') }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="type" value="announcement">
                                <input type="hidden" name="event_date" value="{{ now()->toDateString() }}">
                                <input type="hidden" name="start_time" value="">
                                <input type="hidden" name="end_time" value="">
                                <input type="hidden" name="location" value="">
                                <div>
                                    <label for="announcementTitle" class="block text-sm font-medium mb-1">Title</label>
                                    <input id="announcementTitle" name="title" type="text" required placeholder="Announcement title" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                </div>
                                <div>
                                    <label for="announcementMessage" class="block text-sm font-medium mb-1">Details</label>
                                    <textarea id="announcementMessage" name="description" required rows="3" placeholder="Announcement details" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 py-2"></textarea>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Post announcement</button>
                                </div>
                            </form>
                            <p id="announcementStatus" class="mt-2 text-sm"></p>
                        </div>

                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4">
                            <h2 class="text-lg font-bold mb-3">Schedule Health Events</h2>
                            <form id="eventForm" method="POST" action="{{ route('bhw.events.store') }}" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="eventName" class="block text-sm font-medium mb-1">Event name</label>
                                    <input id="eventName" name="title" type="text" required placeholder="Event name" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div>
                                        <label for="eventDate" class="block text-sm font-medium mb-1">Date</label>
                                        <input id="eventDate" name="event_date" type="date" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                    </div>
                                    <div>
                                        <label for="eventTime" class="block text-sm font-medium mb-1">Start time</label>
                                        <input id="eventTime" name="start_time" type="time" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                    </div>
                                </div>
                                <div>
                                    <label for="eventEndTime" class="block text-sm font-medium mb-1">End time</label>
                                    <input id="eventEndTime" name="end_time" type="time" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3" placeholder="End time">
                                </div>
                                <div>
                                    <label for="eventVenue" class="block text-sm font-medium mb-1">Venue</label>
                                    <input id="eventVenue" name="location" type="text" required placeholder="Venue" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                </div>
                                <div>
                                    <label for="eventRegistrationLimit" class="block text-sm font-medium mb-1">Registration limit (optional)</label>
                                    <input id="eventRegistrationLimit" name="registration_limit" type="number" min="1" placeholder="Leave blank for no limit" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                </div>
                                <div>
                                    <label for="eventImage" class="block text-sm font-medium mb-1">Image (optional)</label>
                                    <input id="eventImage" name="image" type="file" accept="image/*" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 py-2 text-sm">
                                </div>
                                <button type="submit" class="h-10 px-4 rounded-lg bg-secondary text-white text-sm font-semibold">Schedule event</button>
                            </form>
                        </div>
                    </div>

                    <!-- Removed Notify Residents and Approve BHW/BNS Announcements sections per request -->

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

    const announcementForm = document.getElementById('announcementForm');
    const eventForm = document.getElementById('eventForm');
    const eventStoreUrl = @json(route('bhw.events.store'));
    const announcementStoreUrl = @json(route('bhw.posts.store'));
    const announcementStatus = document.getElementById('announcementStatus');
    const toastContainer = document.createElement('div');
    toastContainer.className = 'fixed top-4 right-4 z-50 flex w-[min(92vw,24rem)] flex-col gap-3';
    document.body.appendChild(toastContainer);

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const isSuccess = type === 'success';
        toast.className = `rounded-xl border px-4 py-3 text-sm shadow-lg ${isSuccess ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-200' : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200'}`;
        toast.setAttribute('role', isSuccess ? 'status' : 'alert');
        toast.setAttribute('aria-live', isSuccess ? 'polite' : 'assertive');

        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-base">${isSuccess ? 'check_circle' : 'error'}</span>
                <div class="flex-1">${message}</div>
            </div>
        `;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function handleToastError(message) {
        showToast(message, 'error');
    }

    announcementForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const title = document.getElementById('announcementTitle').value.trim();
        const message = document.getElementById('announcementMessage').value.trim();

        if (!title || !message) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formData = new FormData();
        formData.append('type', 'announcement');
        formData.append('title', title);
        formData.append('description', message);
        formData.append('event_date', new Date().toISOString().slice(0, 10));
        formData.append('start_time', '');
        formData.append('end_time', '');
        formData.append('location', '');
        const announcementOther = document.getElementById('announcementOther') ? document.getElementById('announcementOther').value.trim() : '';
        if (announcementOther) {
            formData.append('other', announcementOther);
        }

        fetch(announcementStoreUrl, {
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
                    throw new Error(data?.message || data?.error || 'Unable to post announcement');
                }

                return data;
            })
            .then((data) => {
                if (data.success) {
                    announcementForm.reset();
                    showToast('Announcement posted successfully.');
                }
            })
            .catch((error) => {
                const message = 'Error posting announcement: ' + error.message;
                handleToastError(message);
            });
    });

    eventForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const name = document.getElementById('eventName').value.trim();
        const date = document.getElementById('eventDate').value;
        const time = document.getElementById('eventTime').value;
        const endTime = document.getElementById('eventEndTime').value;
        const venue = document.getElementById('eventVenue').value.trim();
        const registrationLimit = document.getElementById('eventRegistrationLimit').value.trim();
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
        formData.append('registration_limit', registrationLimit);
        formData.append('category', 'event');
        const eventOther = document.getElementById('eventOther') ? document.getElementById('eventOther').value.trim() : '';
        if (eventOther) {
            formData.append('other', eventOther);
        }

        if (imageInput && imageInput.files && imageInput.files[0]) {
            formData.append('image', imageInput.files[0]);
        }

        fetch(eventStoreUrl, {
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
                    showToast('Event posted successfully.');
                } else {
                    const message = 'Error creating event: ' + (data.error || 'Unknown error');
                    handleToastError(message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const message = 'Error creating event: ' + error.message;
                handleToastError(message);
            });
    });

    // Notification and pending-approval UI removed per request

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
            eventImagePreview.classList.remove('hidden');
            eventImagePreview.innerHTML = `<img src="${previewUrl}" alt="Event image preview" class="h-36 w-full object-cover">`;
        });
    }
</script>
</body>
</html>