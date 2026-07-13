<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - Schedule</title>
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

    .dark .page-content::-webkit-scrollbar-track {
        background: #2c2c2c;
    }

    .dark .page-content::-webkit-scrollbar-thumb {
        background: #555;
    }

    .dark .page-content::-webkit-scrollbar-thumb:hover {
        background: #777;
    }

    .nav-item.active {
        background-color: rgba(74, 144, 226, 0.2);
        color: #4A90E2;
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
                borderRadius: {
                    "DEFAULT": "0.5rem",
                    "lg": "0.75rem",
                    "xl": "1rem",
                    "full": "9999px"
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
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-text-light dark:text-text-dark">Schedule Overview</h1>
                        <p class="text-text-light/70 dark:text-text-dark/70 mt-1">View appointment dates and daily notes.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-2 bg-card-light dark:bg-card-dark p-6 rounded-lg shadow-sm border border-border-light dark:border-border-dark">
                        <div class="flex flex-wrap gap-4 items-center justify-between mb-6">
                            <div class="flex items-center gap-2 text-text-light dark:text-text-dark">
                                <button id="prevMonthBtn" class="p-1.5 rounded hover:bg-background-light dark:hover:bg-background-dark" type="button">
                                    <span class="material-symbols-outlined">chevron_left</span>
                                </button>
                                <h2 id="monthLabel" class="text-xl font-bold"></h2>
                                <button id="nextMonthBtn" class="p-1.5 rounded hover:bg-background-light dark:hover:bg-background-dark" type="button">
                                    <span class="material-symbols-outlined">chevron_right</span>
                                </button>
                                <button id="todayBtn" class="ml-2 px-3 py-1.5 rounded-md text-xs font-semibold bg-primary text-white" type="button">Today</button>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-text-light/70 dark:text-text-dark/70 text-lg">filter_list</span>
                                <select id="typeFilter" class="pl-3 pr-4 py-2 border border-border-light dark:border-border-dark rounded-md text-sm bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="all">All Types</option>
                                    <option value="prenatal">Prenatal Check-up</option>
                                    <option value="immunization">Immunization</option>
                                    <option value="checkup">General Check-up</option>
                                    <option value="follow-up">Follow-up</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-7 text-center text-sm font-semibold text-text-light/70 dark:text-text-dark/70 mb-2">
                            <div class="py-2">Sun</div>
                            <div class="py-2">Mon</div>
                            <div class="py-2">Tue</div>
                            <div class="py-2">Wed</div>
                            <div class="py-2">Thu</div>
                            <div class="py-2">Fri</div>
                            <div class="py-2">Sat</div>
                        </div>

                        <div id="calendarGrid" class="grid grid-cols-7 border-t border-l border-border-light dark:border-border-dark"></div>
                    </div>

                    <div class="bg-card-light dark:bg-card-dark p-6 rounded-lg shadow-sm border border-border-light dark:border-border-dark">
                        <h3 class="text-lg font-bold mb-1">Notes</h3>
                        <p id="selectedDateLabel" class="text-sm text-text-light/70 dark:text-text-dark/70 mb-4">Select a date</p>
                        <div id="selectedDateNotes" class="space-y-2 max-h-[520px] overflow-y-auto"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.bhw-mobile-nav')
</div>

<script>
    const scheduleNotes = @json($scheduleEvents ?? []);

    const monthLabel = document.getElementById('monthLabel');
    const calendarGrid = document.getElementById('calendarGrid');
    const selectedDateLabel = document.getElementById('selectedDateLabel');
    const selectedDateNotes = document.getElementById('selectedDateNotes');
    const typeFilter = document.getElementById('typeFilter');

    const today = new Date();
    let currentYear = today.getFullYear();
    let currentMonth = today.getMonth();
    let selectedDate = toKey(today);

    document.getElementById('prevMonthBtn').addEventListener('click', () => {
        currentMonth -= 1;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear -= 1;
        }
        renderCalendar();
    });

    document.getElementById('nextMonthBtn').addEventListener('click', () => {
        currentMonth += 1;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear += 1;
        }
        renderCalendar();
    });

    document.getElementById('todayBtn').addEventListener('click', () => {
        currentYear = today.getFullYear();
        currentMonth = today.getMonth();
        selectedDate = toKey(today);
        renderCalendar();
    });

    typeFilter.addEventListener('change', () => {
        renderCalendar();
        renderNotes();
    });

    function toKey(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function getFilteredNotes() {
        const filter = typeFilter.value;
        if (filter === 'all') {
            return scheduleNotes;
        }
        return scheduleNotes.filter(item => item.type === filter);
    }

    function renderCalendar() {
        const firstDay = new Date(currentYear, currentMonth, 1);
        const firstWeekday = firstDay.getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        monthLabel.textContent = firstDay.toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric'
        });

        calendarGrid.innerHTML = '';

        const filteredNotes = getFilteredNotes();
        const notesByDate = filteredNotes.reduce((acc, item) => {
            if (!acc[item.date]) {
                acc[item.date] = [];
            }
            acc[item.date].push(item);
            return acc;
        }, {});

        for (let i = 0; i < firstWeekday; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'h-32 p-2 border-r border-b border-border-light dark:border-border-dark bg-background-light/40 dark:bg-background-dark/40';
            calendarGrid.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayDate = new Date(currentYear, currentMonth, day);
            const key = toKey(dayDate);
            const isToday = key === toKey(today);
            const isSelected = key === selectedDate;
            const notes = notesByDate[key] || [];

            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'h-32 p-2 border-r border-b border-border-light dark:border-border-dark text-left align-top relative hover:bg-primary/5 dark:hover:bg-primary/10 transition';

            if (isSelected) {
                cell.classList.add('bg-primary/10', 'dark:bg-primary/20');
            }

            const dayHeader = document.createElement('div');
            dayHeader.className = 'flex items-center justify-between mb-1';

            const dayLabel = document.createElement('span');
            dayLabel.className = isToday ? 'font-semibold text-primary' : 'font-semibold text-text-light dark:text-text-dark';
            dayLabel.textContent = String(day);

            dayHeader.appendChild(dayLabel);

            if (isToday) {
                const todayBadge = document.createElement('span');
                todayBadge.className = 'text-[10px] font-bold text-primary';
                todayBadge.textContent = 'Today';
                dayHeader.appendChild(todayBadge);
            }

            cell.appendChild(dayHeader);

            if (notes.length > 0) {
                const noteDot = document.createElement('span');
                noteDot.className = 'absolute top-2 right-2 inline-block w-2 h-2 rounded-full bg-secondary';
                cell.appendChild(noteDot);

                const preview = document.createElement('div');
                preview.className = 'space-y-1';

                notes.slice(0, 2).forEach(item => {
                    const itemEl = document.createElement('div');
                    itemEl.className = 'p-1 rounded text-[11px] truncate bg-primary/10 text-primary';
                    itemEl.textContent = `${item.label}: ${item.resident}`;
                    preview.appendChild(itemEl);
                });

                if (notes.length > 2) {
                    const more = document.createElement('p');
                    more.className = 'text-[10px] text-text-light/70 dark:text-text-dark/70';
                    more.textContent = `+${notes.length - 2} more`;
                    preview.appendChild(more);
                }

                cell.appendChild(preview);
            }

            cell.addEventListener('click', () => {
                selectedDate = key;
                renderCalendar();
                renderNotes();
            });

            calendarGrid.appendChild(cell);
        }

        renderNotes();
    }

    function renderNotes() {
        const filter = typeFilter.value;
        const selected = scheduleNotes.filter(item => item.date === selectedDate && (filter === 'all' || item.type === filter));

        const displayDate = new Date(selectedDate + 'T00:00:00');
        selectedDateLabel.textContent = displayDate.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });

        selectedDateNotes.innerHTML = '';

        if (selected.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'rounded-lg border border-border-light dark:border-border-dark p-3 text-sm text-text-light/70 dark:text-text-dark/70';
            empty.textContent = 'No notes for this date.';
            selectedDateNotes.appendChild(empty);
            return;
        }

        selected.forEach(item => {
            const card = document.createElement('div');
            card.className = 'rounded-lg border border-border-light dark:border-border-dark p-3';

            const title = document.createElement('p');
            title.className = 'text-sm font-semibold text-text-light dark:text-text-dark';
            title.textContent = `${item.label}: ${item.resident}`;

            const time = document.createElement('p');
            time.className = 'text-xs text-text-light/70 dark:text-text-dark/70 mt-1';
            time.textContent = `Time: ${item.time || 'N/A'}`;

            const note = document.createElement('p');
            note.className = 'text-sm text-text-light/80 dark:text-text-dark/80 mt-1';
            note.textContent = item.note;

            card.appendChild(title);
            card.appendChild(time);
            card.appendChild(note);
            selectedDateNotes.appendChild(card);
        });
    }

    renderCalendar();
</script>
</body>
</html>
