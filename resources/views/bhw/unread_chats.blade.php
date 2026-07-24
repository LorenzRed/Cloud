<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - Unread Chats</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
<style>
    html, body { height: 100%; }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    .bhw-inbox-page #bhwContent {
        height: 100dvh;
        height: 100svh;
        display: flex;
        overflow: hidden;
    }
    @media (min-width: 1024px) {
        .bhw-inbox-page #bhwContent { height: 100vh; }
    }

    .bhw-inbox-shell {
        min-height: 0;
        background: #fff;
        display: flex;
        flex-direction: column;
    }
    .dark .bhw-inbox-shell { background: #2C2C2C; }

    .bhw-inbox-header {
        box-shadow: 0 1px 0 rgb(0 0 0 / 0.06);
    }
    .dark .bhw-inbox-header {
        box-shadow: 0 1px 0 rgb(255 255 255 / 0.06);
    }

    .bhw-inbox-search {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin: 0 0.75rem 0.75rem;
        padding: 0.625rem 0.875rem;
        border-radius: 9999px;
        background: #f0f2f5;
        color: #65676b;
    }
    .dark .bhw-inbox-search {
        background: #3a3b3c;
        color: #b0b3b8;
    }
    .bhw-inbox-search input {
        flex: 1;
        min-width: 0;
        border: 0;
        outline: none;
        background: transparent;
        font-size: 0.9375rem;
        line-height: 1.25rem;
        color: inherit;
    }
    .bhw-inbox-search input::placeholder {
        color: #8a8d91;
    }
    .dark .bhw-inbox-search input::placeholder {
        color: #8a8d91;
    }

    .bhw-inbox-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        padding-bottom: calc(5.5rem + env(safe-area-inset-bottom, 0px));
    }
    @media (min-width: 1024px) {
        .bhw-inbox-scroll { padding-bottom: 1.25rem; }
    }

    .bhw-inbox-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.625rem 0.875rem;
        text-align: left;
        transition: background-color 0.12s ease;
        -webkit-tap-highlight-color: transparent;
    }
    .bhw-inbox-row:hover,
    .bhw-inbox-row:focus-visible {
        background: #f0f2f5;
        outline: none;
    }
    .bhw-inbox-row:active {
        background: #e4e6eb;
    }
    .dark .bhw-inbox-row:hover,
    .dark .bhw-inbox-row:focus-visible {
        background: #3a3b3c;
    }
    .dark .bhw-inbox-row:active {
        background: #4e4f50;
    }

    .bhw-inbox-avatar {
        position: relative;
        flex-shrink: 0;
        width: 3.5rem;
        height: 3.5rem;
    }
    @media (min-width: 640px) {
        .bhw-inbox-avatar {
            width: 3.75rem;
            height: 3.75rem;
        }
    }
    .bhw-inbox-avatar img,
    .bhw-inbox-avatar-fallback {
        width: 100%;
        height: 100%;
        border-radius: 9999px;
        object-fit: cover;
    }
    .bhw-inbox-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(74, 144, 226, 0.14);
        color: #4A90E2;
        font-weight: 700;
        font-size: 1.05rem;
    }

    .bhw-inbox-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }
    .bhw-inbox-top {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .bhw-inbox-name {
        margin: 0;
        font-size: 0.975rem;
        font-weight: 700;
        line-height: 1.25;
        color: #050505;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dark .bhw-inbox-name { color: #e4e6eb; }
    .bhw-inbox-time {
        flex-shrink: 0;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
        color: #4A90E2;
        white-space: nowrap;
    }
    .bhw-inbox-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .bhw-inbox-preview {
        margin: 0;
        flex: 1;
        min-width: 0;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.3;
        color: #050505;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dark .bhw-inbox-preview { color: #e4e6eb; }
    .bhw-inbox-meta {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1.25;
        color: #65676b;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .dark .bhw-inbox-meta { color: #b0b3b8; }
    .bhw-inbox-badge {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.25rem;
        height: 1.25rem;
        padding: 0 0.375rem;
        border-radius: 9999px;
        background: #D0021B;
        color: #fff;
        font-size: 0.6875rem;
        font-weight: 700;
        line-height: 1;
    }

    .bhw-inbox-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 4rem 1.5rem;
        gap: 0.5rem;
        min-height: 16rem;
    }

    @media (min-width: 1024px) {
        .bhw-inbox-shell {
            margin: 0.75rem 1rem 1rem;
            border-radius: 1rem;
            border: 1px solid #EAEAEA;
            overflow: hidden;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
        }
        .dark .bhw-inbox-shell {
            border-color: #3D3D3D;
        }
        .bhw-inbox-search {
            margin-left: 1rem;
            margin-right: 1rem;
        }
        .bhw-inbox-row {
            padding-left: 1rem;
            padding-right: 1rem;
        }
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
                fontFamily: { "display": ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="bhw-inbox-page font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('sidebars.bhw')

@php
    $unreadChats = $unreadChats ?? collect();
    $unreadChatTotal = (int) $unreadChats->sum('unread_count');
@endphp

<div id="bhwContent" class="flex flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)]">
    <main class="flex-1 min-h-0 flex flex-col overflow-hidden">
        <section class="bhw-inbox-shell flex-1 min-h-0">
            <header class="bhw-inbox-header shrink-0 bg-white dark:bg-card-dark pt-[max(0.5rem,env(safe-area-inset-top))]">
                <div class="flex items-center gap-1 px-2 sm:px-3 pt-1 pb-2">
                    <a href="{{ route('bhw.my-profile') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full text-text-light dark:text-text-dark hover:bg-[#f0f2f5] dark:hover:bg-[#3a3b3c] transition shrink-0" aria-label="Back to profile">
                        <span class="material-symbols-outlined text-[26px]">arrow_back</span>
                    </a>
                    <div class="min-w-0 flex-1 pr-2">
                        <h1 class="text-[1.65rem] sm:text-[1.85rem] font-extrabold tracking-tight leading-none text-[#050505] dark:text-[#e4e6eb]">Unread</h1>
                        <p class="mt-1 text-[13px] text-[#65676b] dark:text-[#b0b3b8] truncate">
                            @if ($unreadChatTotal > 0)
                                {{ $unreadChatTotal }} unread · {{ $unreadChats->count() }} chat{{ $unreadChats->count() === 1 ? '' : 's' }}
                            @else
                                No unread messages
                            @endif
                        </p>
                    </div>
                </div>

                @if ($unreadChats->isNotEmpty())
                    <label class="bhw-inbox-search" for="unreadChatSearch">
                        <span class="material-symbols-outlined text-[20px] shrink-0" aria-hidden="true">search</span>
                        <input id="unreadChatSearch" type="search" autocomplete="off" placeholder="Search unread chats" enterkeyhint="search" />
                    </label>
                @endif
            </header>

            <div class="bhw-inbox-scroll" id="unreadChatList">
                @if ($unreadChats->isEmpty())
                    <div class="bhw-inbox-empty">
                        <span class="material-symbols-outlined text-[52px] text-[#bcc0c4] dark:text-[#8a8d91]" style="font-variation-settings: 'FILL' 1;">mark_chat_read</span>
                        <p class="text-[17px] font-bold text-[#050505] dark:text-[#e4e6eb]">You're all caught up</p>
                        <p class="text-[14px] text-[#65676b] dark:text-[#b0b3b8] max-w-[16rem] leading-relaxed">When residents message you, their chats will show up here like Messenger.</p>
                    </div>
                @else
                    <ul role="list" id="unreadChatItems">
                        @foreach ($unreadChats as $chat)
                            <li
                                class="unread-chat-item"
                                data-search="{{ strtolower(trim(($chat['name'] ?? '').' '.($chat['preview'] ?? '').' '.($chat['context_label'] ?? ''))) }}"
                            >
                                <a href="{{ $chat['chat_url'] }}" class="bhw-inbox-row">
                                    <div class="bhw-inbox-avatar" aria-hidden="true">
                                        @if (! empty($chat['avatar_url']))
                                            <img src="{{ $chat['avatar_url'] }}" alt="" loading="lazy" />
                                        @else
                                            <div class="bhw-inbox-avatar-fallback">{{ $chat['initials'] }}</div>
                                        @endif
                                    </div>
                                    <div class="bhw-inbox-body">
                                        <div class="bhw-inbox-top">
                                            <p class="bhw-inbox-name">{{ $chat['name'] }}</p>
                                            <span class="bhw-inbox-time">{{ $chat['time_label'] }}</span>
                                        </div>
                                        <div class="bhw-inbox-bottom">
                                            <p class="bhw-inbox-preview">{{ $chat['preview'] }}</p>
                                            <span class="bhw-inbox-badge">{{ $chat['unread_count'] > 99 ? '99+' : $chat['unread_count'] }}</span>
                                        </div>
                                        <p class="bhw-inbox-meta">{{ $chat['context_label'] }}</p>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div id="unreadChatNoResults" class="bhw-inbox-empty hidden">
                        <span class="material-symbols-outlined text-[44px] text-[#bcc0c4] dark:text-[#8a8d91]">search_off</span>
                        <p class="text-[16px] font-bold text-[#050505] dark:text-[#e4e6eb]">No matches</p>
                        <p class="text-[14px] text-[#65676b] dark:text-[#b0b3b8]">Try a different name or message.</p>
                    </div>
                @endif
            </div>
        </section>
    </main>

    @include('partials.bhw-mobile-nav')
</div>

@if ($unreadChats->isNotEmpty())
<script>
    (function () {
        const input = document.getElementById('unreadChatSearch');
        const items = Array.from(document.querySelectorAll('.unread-chat-item'));
        const empty = document.getElementById('unreadChatNoResults');
        if (!input || !items.length) return;

        input.addEventListener('input', () => {
            const q = (input.value || '').trim().toLowerCase();
            let visible = 0;
            items.forEach((item) => {
                const hay = item.getAttribute('data-search') || '';
                const show = !q || hay.includes(q);
                item.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });
            if (empty) empty.classList.toggle('hidden', visible > 0);
        });
    })();
</script>
@endif
</body>
</html>
