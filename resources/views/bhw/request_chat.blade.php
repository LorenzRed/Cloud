<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Bantay Kalusugan - Verification Chat</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
@include('partials.bhw-messenger-chat-styles')
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2",
                    "secondary": "#50E3C2",
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
<body class="bhw-messenger-chat-page font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@php
    $residentInitials = collect(explode(' ', $residentRequest->full_name))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('') ?: 'R';
    $bhwInitials = collect(explode(' ', Auth::user()->name ?? 'BHW'))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('') ?: 'B';
    $residentUser = $residentRequest->user;
    $peerAvatarUrl = $residentUser?->profile_picture_path
        ? route('profile.picture', ['user' => $residentUser->id, 'v' => optional($residentUser->updated_at)->timestamp])
        : null;
    $myAvatarUrl = Auth::user()->profile_picture_path
        ? route('profile.picture', ['user' => Auth::id(), 'v' => optional(Auth::user()->updated_at)->timestamp])
        : null;
    $purokLabel = $residentRequest->purok?->name ?? 'No purok';
    $fromUnread = request()->query('from') === 'unread';
    $chatBackUrl = $fromUnread
        ? route('bhw.unread-chats')
        : route('bhw.requests');
    $chatBackAria = $fromUnread ? 'Back to unread chats' : 'Back to requests';
@endphp
@include('sidebars.bhw')

<div id="bhwContent" data-chat-keyboard-shell class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    <main class="bhw-messenger-main flex-1 min-h-0 flex flex-col overflow-hidden">
        <section class="bhw-messenger-shell flex-1 min-h-0 flex flex-col overflow-hidden">
            @include('partials.bhw-messenger-chat-header', [
                'backUrl' => $chatBackUrl,
                'backAriaLabel' => $chatBackAria,
                'peerAvatarUrl' => $peerAvatarUrl,
                'peerInitials' => $residentInitials,
                'peerName' => $residentRequest->full_name,
                'headerSubtitle' => $purokLabel,
                'headerStatusDot' => 'amber',
                'headerBadge' => ['label' => 'Pending', 'tone' => 'amber'],
                'headerMeta' => $purokLabel.' · '.$residentRequest->idTypeLabel(),
            ])

            <div class="bhw-messenger-chat-body flex flex-col flex-1 min-h-0">
                @include('partials.resident-bhw-chat', [
                    'messages' => $messages,
                    'messagesUrl' => route('bhw.requests.chat.messages', $residentRequest),
                    'messageStoreUrl' => route('bhw.requests.message', $residentRequest),
                    'messageActionUrl' => fn ($id) => route('bhw.requests.message.update', [$residentRequest, $id]),
                    'messageReactionUrl' => fn ($id) => route('bhw.requests.message.reaction', [$residentRequest, $id]),
                    'myInitials' => $bhwInitials,
                    'peerInitials' => $residentInitials,
                    'peerName' => $residentRequest->full_name,
                    'peerLabel' => $residentRequest->full_name,
                    'myLabel' => 'You · BHW',
                    'theme' => 'bhw',
                    'myAvatarUrl' => $myAvatarUrl,
                    'peerAvatarUrl' => $peerAvatarUrl,
                    'showMessengerIntro' => true,
                    'composerPlaceholder' => 'Aa',
                    'emptyTitle' => $purokLabel,
                    'emptySubtitle' => 'Send verification instructions to help this resident complete their request.',
                    'chatMessagesId' => 'chatMessages',
                    'chatFormId' => 'chatForm',
                    'chatInputId' => 'chatInput',
                    'chatSendBtnId' => 'chatSendBtn',
                    'unreadMessageIds' => $unreadBhwMessageIds ?? [],
                ])
            </div>
        </section>
    </main>
</div>

@if (session('status'))
<div data-auto-dismiss="3500" class="fixed top-4 right-4 z-[60] w-[min(92vw,24rem)] rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800 shadow-lg toast-enter dark:bg-green-900/30 dark:border-green-700 dark:text-green-200" role="status" aria-live="polite">
    {{ session('status') }}
</div>
<script>
    document.querySelectorAll('[data-auto-dismiss]').forEach((toast) => {
        const timeout = Number(toast.getAttribute('data-auto-dismiss')) || 3500;
        window.setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            window.setTimeout(() => toast.remove(), 180);
        }, timeout);
    });
</script>
@endif
@include('partials.mobile-chat-keyboard')
</body>
</html>
