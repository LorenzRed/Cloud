<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Notifications</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#19e66b",
                        "background-light": "#f6f8f7",
                        "background-dark": "#112117",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
    <main class="min-h-screen px-6 md:px-8 py-8">
        <div class="mx-auto w-full max-w-4xl">
            <header class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Notifications</h1>
                    <p class="text-sm text-[#4e976b] dark:text-gray-400 mt-1">Unread messages: {{ $unreadInboxCount }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('users.main') }}" class="inline-flex items-center gap-1.5 h-10 px-3 rounded-lg border border-[#d0e7d9] dark:border-gray-700 text-sm font-semibold hover:bg-white dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('users.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="h-10 px-3 rounded-lg bg-primary text-[#0e1b13] text-sm font-bold hover:opacity-90">Mark all as read</button>
                    </form>
                </div>
            </header>

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-[#d0e7d9] dark:border-gray-700 shadow-sm divide-y divide-[#e7f3ec] dark:divide-gray-700">
                @forelse ($notifications as $notification)
                    <div class="p-4 {{ is_null($notification->read_at) ? 'bg-[#f2fbf6] dark:bg-gray-700/30' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-[#0e1b13] dark:text-white">{{ $notification->title }}</p>
                                <p class="text-sm text-[#4e976b] dark:text-gray-300 mt-1">{{ $notification->message }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-[#4e976b] dark:text-gray-400">{{ $notification->created_at?->format('M d, Y h:i A') }}</p>
                                @if (is_null($notification->read_at))
                                    <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">New</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <span class="material-symbols-outlined text-5xl text-[#4e976b] dark:text-gray-400">notifications</span>
                        <p class="mt-3 text-sm text-[#4e976b] dark:text-gray-400">No notifications in your inbox yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</body>
</html>
