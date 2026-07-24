@php
    $chatMessagesId = $chatMessagesId ?? 'rbhwChatMessages';
    $chatFormId = $chatFormId ?? 'rbhwChatForm';
    $chatInputId = $chatInputId ?? 'rbhwChatInput';
    $chatSendBtnId = $chatSendBtnId ?? 'rbhwChatSendBtn';
    $showQuickReplies = $showQuickReplies ?? false;
    $hidePeerAvatar = $hidePeerAvatar ?? false;
    $hideChatAvatars = $hideChatAvatars ?? false;
    $myAvatarUrl = $myAvatarUrl ?? null;
    $peerAvatarUrl = $peerAvatarUrl ?? null;
    $showMessengerIntro = $showMessengerIntro ?? $isUsersTheme;
    $unreadMessageIds = $unreadMessageIds ?? [];
    $theme = $theme ?? 'bhw';
    $isUsersTheme = $theme === 'users';
    $messageActionPlaceholder = 999999999;
    $messageActionUrlTemplate = str_replace(
        (string) $messageActionPlaceholder,
        '__MSGID__',
        $messageActionUrl($messageActionPlaceholder)
    );
    $messageReactionPlaceholder = 888888888;
    $messageReactionUrlTemplate = isset($messageReactionUrl)
        ? str_replace(
            (string) $messageReactionPlaceholder,
            '__MSGID__',
            $messageReactionUrl($messageReactionPlaceholder)
        )
        : '';
@endphp

<style>
    .rbhw-chat-scroll { scrollbar-width: thin; overflow-x: hidden; }
    .rbhw-chat-message-row { overflow: visible; }
    .rbhw-message-lane { width: 100%; max-width: 100%; min-width: 0; overflow: visible; }
    .rbhw-message-bubble-wrap {
        position: relative;
        display: inline-block;
        width: fit-content;
        max-width: 100%;
        min-width: 0;
        vertical-align: bottom;
        -webkit-touch-callout: none;
        user-select: none;
    }
    .rbhw-message-bubble-wrap > .message-body {
        display: inline-block;
        width: fit-content;
        max-width: 100%;
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
        white-space: pre-wrap;
    }
    .rbhw-message-bubble-wrap .rbhw-chat-text {
        margin: 0;
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
        white-space: pre-wrap;
    }
    /* Composer: soft-wrap long lines so height grows instead of horizontal scroll */
    #{{ $chatInputId }},
    .rbhw-composer-input {
        white-space: pre-wrap !important;
        overflow-wrap: anywhere;
        word-break: break-word;
        overflow-x: hidden;
    }
    .rbhw-message-bubble-wrap .rbhw-chat-image-btn,
    .rbhw-message-bubble-wrap .rbhw-reaction-pill { user-select: none; }
    @if ($isUsersTheme)
    .rbhw-message-inline-edit { width: 100%; background: transparent; border: 0; color: #0e1b13; resize: none; outline: none; padding: 0; font: inherit; line-height: inherit; min-height: 1.25rem; field-sizing: content; }
    .rbhw-bubble-editing { box-shadow: 0 0 0 2px rgb(25 230 107 / 0.35); }
    @else
    .rbhw-message-inline-edit { width: 100%; background: transparent; border: 0; color: #fff; resize: none; outline: none; padding: 0; font: inherit; line-height: inherit; min-height: 1.25rem; field-sizing: content; }
    .rbhw-bubble-editing { box-shadow: 0 0 0 2px rgb(255 255 255 / 0.35); }
    @endif
    .rbhw-message-inline-edit:focus { outline: none; box-shadow: none; }
    .rbhw-chat-image {
        display: block;
        max-width: min(240px, 72vw);
        max-height: 280px;
        width: auto;
        height: auto;
        border-radius: 0.75rem;
        object-fit: cover;
        transition: opacity 0.15s ease;
    }
    .rbhw-chat-image-btn { cursor: zoom-in; }
    .rbhw-chat-image-btn:hover .rbhw-chat-image { opacity: 0.92; }
    .rbhw-photo-viewer:not(.hidden) { display: flex; }
    .rbhw-chat-emoji-only {
        font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
        line-height: 1.25;
        letter-spacing: 0.04em;
        word-break: normal;
        overflow: visible;
        display: block;
        padding: 0.125rem 0;
        margin: 0;
    }
    .rbhw-chat-emoji-only.size-1 { font-size: 3rem; line-height: 1.2; }
    .rbhw-chat-emoji-only.size-2 { font-size: 2.5rem; line-height: 1.2; }
    .rbhw-chat-emoji-only.size-3 { font-size: 2rem; line-height: 1.2; }
    .rbhw-chat-emoji-only.size-many { font-size: 1.5rem; line-height: 1.25; }
    .rbhw-chat-emoji-inline {
        font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
        font-size: 1.4em;
        line-height: 1;
        vertical-align: -0.1em;
    }
    .rbhw-chat-emoji-only-bubble { background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; overflow: visible !important; }
    .rbhw-message-bubble-wrap.is-emoji-only {
        overflow: visible;
        vertical-align: middle;
    }
    .rbhw-message-bubble-wrap.is-emoji-only > .message-body {
        overflow: visible;
        line-height: 1;
    }
    .rbhw-chat-message-row.is-emoji-only-row { align-items: center; }
    .rbhw-instant-react-bar {
        position: absolute;
        bottom: calc(100% + 2px);
        z-index: 50;
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 0.125rem;
        width: max-content;
        max-width: min(22rem, calc(100vw - 2.5rem));
        padding: 0.35rem 0.45rem;
        border-radius: 9999px;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: scale(0.88) translateY(2px);
        transition: opacity 0.16s ease, transform 0.16s cubic-bezier(0.34, 1.4, 0.64, 1), visibility 0.16s;
        @if ($isUsersTheme)
        background: #fff;
        border: 1px solid rgb(0 0 0 / 0.06);
        box-shadow: 0 4px 16px rgb(0 0 0 / 0.12), 0 1px 3px rgb(0 0 0 / 0.06);
        @else
        background: #fff;
        border: 1px solid rgb(0 0 0 / 0.06);
        box-shadow: 0 4px 16px rgb(0 0 0 / 0.12), 0 1px 3px rgb(0 0 0 / 0.06);
        @endif
    }
    .rbhw-instant-react-bar.is-outgoing { right: 0; left: auto; }
    .rbhw-instant-react-bar.is-incoming { left: 0; right: auto; }
    .dark .rbhw-instant-react-bar {
        background: rgb(38 38 38);
        border-color: rgb(255 255 255 / 0.08);
        box-shadow: 0 4px 20px rgb(0 0 0 / 0.45), 0 0 0 1px rgb(255 255 255 / 0.06);
    }
    @media (hover: hover) {
        .rbhw-message-bubble-wrap.is-actions-open .rbhw-instant-react-bar,
        .rbhw-message-bubble-wrap:hover .rbhw-instant-react-bar {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: scale(1) translateY(0);
        }
    }
    @media (hover: none) {
        .rbhw-instant-react-bar { display: none !important; }
    }
    .rbhw-instant-react-divider {
        width: 1px;
        height: 1.625rem;
        margin: 0 0.1rem;
        background: rgb(0 0 0 / 0.08);
        flex-shrink: 0;
    }
    .dark .rbhw-instant-react-divider { background: rgb(255 255 255 / 0.12); }
    .rbhw-instant-more-btn {
        width: 2.125rem;
        height: 2.125rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 0;
        background: transparent;
        cursor: pointer;
        transition: background 0.12s ease, transform 0.12s ease;
        @if ($isUsersTheme)
        color: rgb(78 151 107 / 0.85);
        @else
        color: rgb(74 74 74 / 0.75);
        @endif
    }
    .rbhw-instant-more-btn:hover { background: rgb(0 0 0 / 0.05); transform: scale(1.05); }
    .dark .rbhw-instant-more-btn:hover { background: rgb(255 255 255 / 0.08); }
    .rbhw-message-more-menu {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        z-index: 60;
        min-width: 10.5rem;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.06);
        @if ($isUsersTheme)
        background: #fff;
        border: 1px solid #d0e7d9;
        @else
        background: #fff;
        border: 1px solid #EAEAEA;
        @endif
    }
    .dark .rbhw-message-more-menu {
        background: rgb(38 38 38);
        border-color: rgb(255 255 255 / 0.08);
        box-shadow: 0 4px 20px rgb(0 0 0 / 0.45);
    }
    .rbhw-message-more-menu button {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.625rem 0.875rem;
        font-size: 0.8125rem;
        font-weight: 500;
        border: 0;
        background: transparent;
        cursor: pointer;
    }
    .rbhw-message-more-menu button + button {
        border-top: 1px solid rgb(0 0 0 / 0.06);
    }
    .dark .rbhw-message-more-menu button + button { border-top-color: rgb(255 255 255 / 0.08); }
    .rbhw-message-more-menu button:hover { background: rgb(0 0 0 / 0.04); }
    .dark .rbhw-message-more-menu button:hover { background: rgb(255 255 255 / 0.06); }
    .rbhw-message-more-menu .message-edit-btn { font-weight: 600; color: inherit; }
    .rbhw-message-more-menu .unsend-everyone-btn { font-weight: 600; color: inherit; }
    @if ($isUsersTheme)
    .rbhw-message-more-menu .unsend-everyone-btn { color: #1f7f4e; }
    .rbhw-message-more-menu .remove-you-btn { color: rgb(78 151 107 / 0.85); }
    @else
    .rbhw-message-more-menu .unsend-everyone-btn { color: #4A90E2; }
    .rbhw-message-more-menu .remove-you-btn { color: rgb(74 74 74 / 0.75); }
    @endif
    .rbhw-message-sheet:not(.hidden) { display: flex; }
    .rbhw-message-sheet-panel {
        animation: rbhwSheetUp 0.22s cubic-bezier(0.32, 0.72, 0, 1);
        @if ($isUsersTheme)
        background: #fff;
        border-top: 1px solid #d0e7d9;
        @else
        background: #fff;
        border-top: 1px solid #EAEAEA;
        @endif
    }
    .dark .rbhw-message-sheet-panel {
        background: rgb(28 28 28);
        border-top-color: rgb(255 255 255 / 0.08);
    }
    .rbhw-message-sheet-emojis {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.25rem;
        padding: 0.25rem 0 0.75rem;
    }
    .rbhw-message-sheet-emoji {
        width: 2.75rem;
        height: 2.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 9999px;
        background: transparent;
        font-size: 1.625rem;
        line-height: 1;
        cursor: pointer;
        transition: transform 0.12s ease, background 0.12s ease;
    }
    .rbhw-message-sheet-emoji:active { transform: scale(1.15); background: rgb(0 0 0 / 0.05); }
    .rbhw-message-sheet-action {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        padding: 0.875rem 1rem;
        border: 0;
        border-radius: 0.75rem;
        background: transparent;
        font-size: 0.9375rem;
        font-weight: 500;
        text-align: left;
        cursor: pointer;
    }
    .rbhw-message-sheet-action:hover { background: rgb(0 0 0 / 0.04); }
    .dark .rbhw-message-sheet-action:hover { background: rgb(255 255 255 / 0.06); }
    .rbhw-message-sheet-action .material-symbols-outlined { font-size: 1.25rem; opacity: 0.75; }
    .rbhw-message-sheet-action.is-danger { color: #dc2626; }
    @keyframes rbhwSheetUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    .rbhw-instant-react-btn {
        width: 2.125rem;
        height: 2.125rem;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        font-size: 1.375rem;
        line-height: 1;
        border: 0;
        background: transparent;
        cursor: pointer;
        transition: transform 0.14s cubic-bezier(0.34, 1.4, 0.64, 1);
    }
    .rbhw-instant-react-btn:hover { transform: scale(1.28); }
    .rbhw-instant-react-btn:active { transform: scale(1.1); }
    .rbhw-reaction-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.15rem;
        min-width: 1.5rem;
        min-height: 1.5rem;
        border-radius: 9999px;
        padding: 0.125rem 0.375rem;
        font-size: 0.8125rem;
        line-height: 1;
        border: none;
        cursor: pointer;
        transition: transform 0.12s ease;
        @if ($isUsersTheme)
        background: #fff;
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.12), 0 0 0 1px rgb(0 0 0 / 0.04);
        @else
        background: #fff;
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.12), 0 0 0 1px rgb(0 0 0 / 0.04);
        @endif
    }
    .dark .rbhw-reaction-pill {
        background: rgb(44 44 44);
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.35), 0 0 0 1px rgb(255 255 255 / 0.08);
    }
    .rbhw-reaction-pill.is-mine {
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.12), 0 0 0 1.5px rgb(24 119 242 / 0.55);
    }
    .rbhw-reaction-pill:hover { transform: scale(1.08); }
    .rbhw-reaction-count { font-size: 0.625rem; font-weight: 700; opacity: 0.7; margin-left: 0.05rem; }
    .rbhw-message-reactions {
        position: absolute;
        bottom: -0.5rem;
        z-index: 12;
        display: flex;
        flex-wrap: wrap;
        gap: 0.2rem;
        pointer-events: auto;
    }
    .rbhw-message-reactions.is-outgoing { left: 0; right: auto; justify-content: flex-start; }
    .rbhw-message-reactions.is-incoming { right: 0; left: auto; justify-content: flex-end; }
    .rbhw-message-bubble-wrap:has(.rbhw-message-reactions) { margin-bottom: 0.625rem; }
    .rbhw-bubble-unsent { border: 1px dashed; font-style: italic; }
    @if ($isUsersTheme)
    .rbhw-bubble-unsent { background: rgb(246 248 247); border-color: #d0e7d9; color: rgb(78 151 107 / 0.65); }
    .dark .rbhw-bubble-unsent { background: rgb(31 41 55); border-color: rgb(55 65 81); color: rgb(156 163 175 / 0.65); }
    @else
    .chat-bubble-out {
        background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
        box-shadow: 0 2px 8px rgb(74 144 226 / 0.25);
    }
    .rbhw-bubble-unsent { background: rgb(244 244 244); border-color: rgb(234 234 234); color: rgb(74 74 74 / 0.55); }
    .dark .rbhw-bubble-unsent { background: rgb(44 44 44); border-color: rgb(61 61 61); color: rgb(224 224 224 / 0.55); }
    @endif
    .rbhw-toast-enter { animation: rbhwToastIn 0.2s ease-out; }
    .rbhw-toast-exit { animation: rbhwToastOut 0.18s ease-in forwards; }
    @keyframes rbhwToastIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes rbhwToastOut { from { opacity: 1; } to { opacity: 0; transform: translateY(-8px); } }
    .rbhw-unread-divider-line { flex: 1; height: 1px; }
    @if ($isUsersTheme)
    .rbhw-unread-divider-line { background: linear-gradient(to right, transparent, #d0e7d9, transparent); }
    .dark .rbhw-unread-divider-line { background: linear-gradient(to right, transparent, rgb(55 65 81), transparent); }
    .rbhw-unread-divider-text { color: rgb(78 151 107 / 0.85); }
    .dark .rbhw-unread-divider-text { color: rgb(156 163 175 / 0.85); }
    @else
    .rbhw-unread-divider-line { background: linear-gradient(to right, transparent, #EAEAEA, transparent); }
    .dark .rbhw-unread-divider-line { background: linear-gradient(to right, transparent, #3D3D3D, transparent); }
    .rbhw-unread-divider-text { color: rgb(74 74 74 / 0.55); }
    .dark .rbhw-unread-divider-text { color: rgb(224 224 224 / 0.55); }
    @endif
    .rbhw-chat-message-row.is-being-edited { opacity: 0.45; pointer-events: none; }
    .rbhw-composer-edit-bar { border-left: 3px solid; }
    @if ($isUsersTheme)
    .rbhw-composer-edit-bar { border-left-color: #19e66b; background: rgb(242 251 246); }
    .dark .rbhw-composer-edit-bar { background: rgb(31 41 55 / 0.6); border-left-color: #19e66b; }
    @else
    .rbhw-composer-edit-bar { border-left-color: #4A90E2; background: rgb(74 144 226 / 0.08); }
    .dark .rbhw-composer-edit-bar { background: rgb(74 144 226 / 0.12); border-left-color: #4A90E2; }
    @endif
    .rbhw-camera-modal:not(.hidden) { display: flex; }
    .rbhw-camera-viewport {
        position: relative;
        flex: 1;
        min-height: 0;
        overflow: hidden;
        background: #000;
    }
    .rbhw-camera-video,
    .rbhw-camera-preview {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .rbhw-camera-preview.hidden { display: none; }
    .rbhw-camera-shutter {
        width: 4.25rem;
        height: 4.25rem;
        border-radius: 9999px;
        border: 4px solid #fff;
        background: rgb(255 255 255 / 0.25);
        box-shadow: 0 0 0 2px rgb(0 0 0 / 0.15);
        transition: transform 0.12s ease, background 0.12s ease;
    }
    .rbhw-camera-shutter:not(:disabled):active { transform: scale(0.92); background: rgb(255 255 255 / 0.45); }
    .rbhw-camera-shutter:disabled { opacity: 0.45; cursor: not-allowed; }
    .rbhw-messenger-intro {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: min(100%, 28rem);
        padding: 2.5rem 1.5rem 2rem;
        text-align: center;
    }
    .rbhw-messenger-intro--thread {
        min-height: auto;
        padding: 1.5rem 1.5rem 1.25rem;
    }
    .rbhw-messenger-intro-avatar {
        width: 7rem;
        height: 7rem;
        border-radius: 9999px;
        object-fit: cover;
        box-shadow: 0 8px 24px rgb(0 0 0 / 0.12), 0 0 0 4px #fff;
        margin-bottom: 1rem;
    }
    .dark .rbhw-messenger-intro-avatar {
        box-shadow: 0 8px 24px rgb(0 0 0 / 0.35), 0 0 0 4px @if ($isUsersTheme) rgb(17 33 23) @else rgb(44 44 44) @endif;
    }
    .rbhw-messenger-intro-avatar-fallback {
        width: 7rem;
        height: 7rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
        @if ($isUsersTheme)
        background: linear-gradient(to bottom right, rgb(25 230 107 / 0.35), rgb(25 230 107 / 0.1));
        color: #1f7f4e;
        box-shadow: 0 8px 24px rgb(0 0 0 / 0.12), 0 0 0 4px #fff;
        @else
        background: linear-gradient(to bottom right, rgb(74 144 226 / 0.25), rgb(74 144 226 / 0.08));
        color: #4A90E2;
        box-shadow: 0 8px 24px rgb(0 0 0 / 0.12), 0 0 0 4px #fff;
        @endif
    }
    .dark .rbhw-messenger-intro-avatar-fallback {
        box-shadow: 0 8px 24px rgb(0 0 0 / 0.35), 0 0 0 4px @if ($isUsersTheme) rgb(17 33 23) @else rgb(44 44 44) @endif;
    }
</style>

<div id="{{ $chatMessagesId }}" class="rbhw-chat-scroll chat-bg flex-1 min-h-0 overflow-y-auto px-4 md:px-6 pt-10 pb-5 md:pt-12 md:pb-6 space-y-3 md:space-y-4"></div>

<div class="shrink-0 border-t {{ $isUsersTheme ? 'border-[#e7f3ec] dark:border-gray-700' : 'border-border-light dark:border-border-dark' }} p-4">
    @if ($showQuickReplies)
        <div id="{{ $chatFormId }}QuickReplies" class="flex flex-wrap gap-2 mb-3">
            <button type="button" class="rbhw-quick-reply text-xs font-medium px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark hover:border-primary hover:text-primary hover:bg-primary/5 transition">Please upload a clearer photo of your valid ID.</button>
            <button type="button" class="rbhw-quick-reply text-xs font-medium px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark hover:border-primary hover:text-primary hover:bg-primary/5 transition">Your ID name does not match your request. Please resubmit.</button>
            <button type="button" class="rbhw-quick-reply text-xs font-medium px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark hover:border-primary hover:text-primary hover:bg-primary/5 transition">Please confirm your purok on the verification form.</button>
        </div>
    @endif
    <div id="{{ $chatFormId }}FilePreview" class="hidden mb-3 flex items-center gap-2 rounded-xl border {{ $isUsersTheme ? 'border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900/60' : 'border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark' }} px-3 py-2">
        <img id="{{ $chatFormId }}FilePreviewThumb" class="hidden h-12 w-12 rounded-lg object-cover shrink-0 border {{ $isUsersTheme ? 'border-[#d0e7d9] dark:border-gray-600' : 'border-border-light dark:border-border-dark' }}" alt="Selected image preview"/>
        <span class="material-symbols-outlined {{ $isUsersTheme ? 'text-[#1f7f4e]' : 'text-primary' }} text-lg shrink-0" id="{{ $chatFormId }}FilePreviewIcon">image</span>
        <span id="{{ $chatFormId }}FilePreviewName" class="text-xs font-medium truncate flex-1"></span>
        <button type="button" id="{{ $chatFormId }}FileRemoveBtn" class="p-1 rounded-lg hover:opacity-80 shrink-0" aria-label="Remove file">
            <span class="material-symbols-outlined text-base">close</span>
        </button>
    </div>
    <div id="{{ $chatFormId }}EditBar" class="rbhw-composer-edit-bar hidden mb-3 flex items-center justify-between gap-3 rounded-xl px-3 py-2.5">
        <div class="flex items-center gap-2 min-w-0">
            <span class="material-symbols-outlined text-[18px] {{ $isUsersTheme ? 'text-[#1f7f4e]' : 'text-primary' }} shrink-0">edit</span>
        <div class="flex flex-col min-w-0">
            <p class="text-xs font-semibold {{ $isUsersTheme ? 'text-[#0e1b13] dark:text-gray-200' : 'text-text-light dark:text-text-dark' }} truncate">Editing message</p>
            <p id="{{ $chatFormId }}EditHint" class="text-[10px] {{ $isUsersTheme ? 'text-[#4e976b] dark:text-gray-400' : 'text-text-light/50 dark:text-text-dark/50' }} truncate">You can edit within {{ \App\Support\ResidentBhwChat::MESSAGE_EDIT_MINUTES }} minutes of sending</p>
        </div>
        </div>
        <button type="button" id="{{ $chatFormId }}EditCancel" class="text-xs font-semibold {{ $isUsersTheme ? 'text-[#4e976b] hover:text-[#1f7f4e]' : 'text-text-light/60 hover:text-primary' }} shrink-0">Cancel</button>
    </div>
    <form id="{{ $chatFormId }}" class="flex items-end gap-2 md:gap-3">
        <input type="file" id="{{ $chatFormId }}CameraFallbackInput" class="hidden" accept="image/*" capture="environment"/>
        <input type="file" id="{{ $chatFormId }}GalleryInput" class="hidden" accept="image/jpeg,image/png,image/gif,image/webp"/>
        <button type="button" id="{{ $chatFormId }}CameraBtn" class="rbhw-media-btn inline-flex h-12 w-12 items-center justify-center rounded-2xl border {{ $isUsersTheme ? 'border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900/60 hover:border-primary hover:text-[#1f7f4e]' : 'border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark hover:border-primary hover:text-primary' }} shrink-0 transition" aria-label="Take photo">
            <span class="material-symbols-outlined">photo_camera</span>
        </button>
        <button type="button" id="{{ $chatFormId }}GalleryBtn" class="rbhw-media-btn inline-flex h-12 w-12 items-center justify-center rounded-2xl border {{ $isUsersTheme ? 'border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900/60 hover:border-primary hover:text-[#1f7f4e]' : 'border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark hover:border-primary hover:text-primary' }} shrink-0 transition" aria-label="Choose from gallery">
            <span class="material-symbols-outlined">photo_library</span>
        </button>
        <textarea id="{{ $chatInputId }}" rows="1" maxlength="500" wrap="soft" class="rbhw-composer-input flex-1 min-h-[48px] max-h-32 rounded-2xl border {{ $isUsersTheme ? 'border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900/60 focus:border-primary focus:ring-2 focus:ring-primary/30' : 'border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark focus:ring-2 focus:ring-primary/30 focus:border-primary' }} px-4 py-3 text-sm resize-none" placeholder="{{ $composerPlaceholder ?? 'Write a message...' }}"></textarea>
        <button type="submit" id="{{ $chatSendBtnId }}" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl {{ $isUsersTheme ? 'bg-primary text-[#0e1b13] hover:opacity-90' : 'bg-primary text-white hover:opacity-90 shadow-md shadow-primary/25' }} disabled:opacity-50 shrink-0 transition">
            <span class="material-symbols-outlined">send</span>
        </button>
    </form>
</div>

<div id="{{ $chatFormId }}CameraModal" class="rbhw-camera-modal fixed inset-0 z-[350] hidden flex-col bg-black" role="dialog" aria-modal="true" aria-label="Camera">
    <div class="flex items-center justify-between px-4 py-3 shrink-0 bg-black/80 text-white z-10">
        <button type="button" id="{{ $chatFormId }}CameraCloseBtn" class="inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-white/10" aria-label="Close camera">
            <span class="material-symbols-outlined">close</span>
        </button>
        <p id="{{ $chatFormId }}CameraStatus" class="text-sm font-medium text-white/90 truncate px-2">Camera</p>
        <button type="button" id="{{ $chatFormId }}CameraFlipBtn" class="inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-white/10" aria-label="Switch camera">
            <span class="material-symbols-outlined">cameraswitch</span>
        </button>
    </div>
    <div class="rbhw-camera-viewport">
        <video id="{{ $chatFormId }}CameraVideo" class="rbhw-camera-video" autoplay playsinline muted></video>
        <img id="{{ $chatFormId }}CameraPreview" class="rbhw-camera-preview hidden" alt="Captured photo preview"/>
    </div>
    <div id="{{ $chatFormId }}CameraLiveControls" class="shrink-0 flex items-center justify-center gap-6 px-4 py-6 pb-[max(1.5rem,env(safe-area-inset-bottom))] bg-black/80">
        <button type="button" id="{{ $chatFormId }}CameraCaptureBtn" class="rbhw-camera-shutter" disabled aria-label="Capture photo"></button>
    </div>
    <div id="{{ $chatFormId }}CameraPreviewControls" class="hidden shrink-0 flex items-center justify-center gap-4 px-4 py-6 pb-[max(1.5rem,env(safe-area-inset-bottom))] bg-black/80">
        <button type="button" id="{{ $chatFormId }}CameraRetakeBtn" class="inline-flex items-center gap-2 rounded-full border border-white/30 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10">Retake</button>
        <button type="button" id="{{ $chatFormId }}CameraUseBtn" class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-[#0e1b13] hover:opacity-90">Use photo</button>
    </div>
</div>

<div id="{{ $chatFormId }}PhotoViewer" class="rbhw-photo-viewer fixed inset-0 z-[300] hidden flex-col" role="dialog" aria-modal="true" aria-label="Photo viewer">
    <button type="button" id="{{ $chatFormId }}PhotoViewerBackdrop" class="absolute inset-0 bg-black/95 border-0 p-0 cursor-zoom-out" aria-label="Close photo"></button>
    <div class="relative z-10 flex flex-1 flex-col min-h-0 pointer-events-none">
        <div class="flex items-center justify-between px-4 py-3 shrink-0 pointer-events-auto">
            <p id="{{ $chatFormId }}PhotoViewerTitle" class="text-white/90 text-sm font-medium truncate max-w-[70%]"></p>
            <button type="button" id="{{ $chatFormId }}PhotoViewerClose" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-white hover:bg-white/10" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex flex-1 items-center justify-center p-4 min-h-0 overflow-hidden pointer-events-auto">
            <img id="{{ $chatFormId }}PhotoViewerImage" src="" alt="" class="max-w-full max-h-full object-contain select-none"/>
        </div>
    </div>
</div>

<div id="{{ $chatFormId }}MessageSheet" class="rbhw-message-sheet fixed inset-0 z-[260] hidden flex-col" role="dialog" aria-modal="true" aria-label="Message options">
    <button type="button" id="{{ $chatFormId }}MessageSheetBackdrop" class="rbhw-message-sheet-backdrop absolute inset-0 bg-black/45 border-0 p-0 cursor-default" aria-label="Close"></button>
    <div class="rbhw-message-sheet-panel relative z-10 mt-auto w-full rounded-t-2xl px-4 pt-3 pb-[max(1rem,env(safe-area-inset-bottom))] shadow-[0_-8px_32px_rgb(0_0_0/0.12)]">
        <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-black/15 dark:bg-white/20"></div>
        <div id="{{ $chatFormId }}MessageSheetEmojis" class="rbhw-message-sheet-emojis"></div>
        <div id="{{ $chatFormId }}MessageSheetActions" class="space-y-0.5"></div>
    </div>
</div>

<script>
(function () {
    const chatHistory = @json($messages);
    const messageStoreUrl = @json($messageStoreUrl);
    const messageActionUrlTemplate = @json($messageActionUrlTemplate);
    const messageReactionUrlTemplate = @json($messageReactionUrlTemplate);
    const REACTION_EMOJIS = @json(\App\Support\ResidentBhwChat::REACTION_EMOJIS);
    const myInitials = @json($myInitials);
    const peerInitials = @json($peerInitials);
    const peerName = @json($peerName);
    const myLabel = @json($myLabel ?? 'You');
    const peerLabel = @json($peerLabel ?? $peerName);
    const theme = @json($theme);
    const hidePeerAvatar = @json($hidePeerAvatar);
    const hideChatAvatars = @json($hideChatAvatars);
    const myAvatarUrl = @json($myAvatarUrl);
    const peerAvatarUrl = @json($peerAvatarUrl);
    const showMessengerIntro = @json($showMessengerIntro);
    const unreadMessageIds = new Set(@json($unreadMessageIds));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const emptyTitle = @json($emptyTitle ?? 'No messages yet');
    const emptySubtitle = @json($emptySubtitle ?? 'Send a message to start the conversation.');
    const editWindowMinutes = @json(\App\Support\ResidentBhwChat::MESSAGE_EDIT_MINUTES);
    const editWindowMs = editWindowMinutes * 60 * 1000;

    const chatMessages = document.getElementById(@json($chatMessagesId));
    const chatForm = document.getElementById(@json($chatFormId));
    const chatInput = document.getElementById(@json($chatInputId));
    const chatSendBtn = document.getElementById(@json($chatSendBtnId));
    const chatCameraBtn = document.getElementById(@json($chatFormId) + 'CameraBtn');
    const chatCameraFallbackInput = document.getElementById(@json($chatFormId) + 'CameraFallbackInput');
    const chatGalleryBtn = document.getElementById(@json($chatFormId) + 'GalleryBtn');
    const chatGalleryInput = document.getElementById(@json($chatFormId) + 'GalleryInput');
    const cameraModal = document.getElementById(@json($chatFormId) + 'CameraModal');
    const cameraCloseBtn = document.getElementById(@json($chatFormId) + 'CameraCloseBtn');
    const cameraFlipBtn = document.getElementById(@json($chatFormId) + 'CameraFlipBtn');
    const cameraVideo = document.getElementById(@json($chatFormId) + 'CameraVideo');
    const cameraPreview = document.getElementById(@json($chatFormId) + 'CameraPreview');
    const cameraStatus = document.getElementById(@json($chatFormId) + 'CameraStatus');
    const cameraCaptureBtn = document.getElementById(@json($chatFormId) + 'CameraCaptureBtn');
    const cameraLiveControls = document.getElementById(@json($chatFormId) + 'CameraLiveControls');
    const cameraPreviewControls = document.getElementById(@json($chatFormId) + 'CameraPreviewControls');
    const cameraRetakeBtn = document.getElementById(@json($chatFormId) + 'CameraRetakeBtn');
    const cameraUseBtn = document.getElementById(@json($chatFormId) + 'CameraUseBtn');
    const chatFilePreview = document.getElementById(@json($chatFormId) + 'FilePreview');
    const chatFilePreviewName = document.getElementById(@json($chatFormId) + 'FilePreviewName');
    const chatFilePreviewIcon = document.getElementById(@json($chatFormId) + 'FilePreviewIcon');
    const chatFilePreviewThumb = document.getElementById(@json($chatFormId) + 'FilePreviewThumb');
    const chatFileRemoveBtn = document.getElementById(@json($chatFormId) + 'FileRemoveBtn');
    const chatMediaBtns = [chatCameraBtn, chatGalleryBtn].filter(Boolean);
    const chatEditBar = document.getElementById(@json($chatFormId) + 'EditBar');
    const chatEditCancelBtn = document.getElementById(@json($chatFormId) + 'EditCancel');
    const chatQuickReplies = document.getElementById(@json($chatFormId) + 'QuickReplies');
    const photoViewer = document.getElementById(@json($chatFormId) + 'PhotoViewer');
    const photoViewerBackdrop = document.getElementById(@json($chatFormId) + 'PhotoViewerBackdrop');
    const photoViewerClose = document.getElementById(@json($chatFormId) + 'PhotoViewerClose');
    const photoViewerImage = document.getElementById(@json($chatFormId) + 'PhotoViewerImage');
    const photoViewerTitle = document.getElementById(@json($chatFormId) + 'PhotoViewerTitle');
    const messageSheet = document.getElementById(@json($chatFormId) + 'MessageSheet');
    const messageSheetBackdrop = document.getElementById(@json($chatFormId) + 'MessageSheetBackdrop');
    const messageSheetEmojis = document.getElementById(@json($chatFormId) + 'MessageSheetEmojis');
    const messageSheetActions = document.getElementById(@json($chatFormId) + 'MessageSheetActions');
    const defaultComposerPlaceholder = chatInput?.getAttribute('placeholder') || 'Write a message...';

    let selectedFile = null;
    let editingMessageId = null;
    let openMessageMenuId = null;
    let openMessageSheetId = null;
    let shouldScrollToUnread = false;
    let editExpiryTimer = null;
    let previewObjectUrl = null;
    let longPressTimer = null;
    let longPressStartX = 0;
    let longPressStartY = 0;
    let cameraStream = null;
    let cameraFacingMode = 'environment';
    let capturedCameraBlob = null;
    let cameraPreviewObjectUrl = null;

    const isTouchDevice = window.matchMedia('(hover: none)').matches;
    const LONG_PRESS_MS = 480;
    const LONG_PRESS_MOVE_TOLERANCE = 12;

    const isUsers = theme === 'users';
    const outgoingBubble = isUsers
        ? 'rounded-2xl rounded-br-md bg-primary px-4 py-2.5 text-sm text-[#0e1b13] leading-relaxed'
        : 'chat-bubble-out text-white rounded-2xl rounded-br-md px-4 py-2.5 text-sm leading-relaxed';
    const incomingBubble = isUsers
        ? 'rounded-2xl rounded-bl-md border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-[#0e1b13] dark:text-gray-200 leading-relaxed'
        : 'rounded-2xl rounded-bl-md border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-2.5 text-sm text-text-light dark:text-text-dark leading-relaxed';
    const metaClass = isUsers ? 'text-[10px] text-[#4e976b] dark:text-gray-500' : 'text-[10px] text-text-light/45 dark:text-text-dark/45';
    const labelClass = isUsers ? 'text-[11px] font-semibold text-[#4e976b] dark:text-gray-400 mb-1' : 'text-[11px] font-semibold text-text-light/50 dark:text-text-dark/50 mb-1';

    function buildAvatarHtml(initials, imageUrl, outgoing, muted = false) {
        if (isUsers || hideChatAvatars) return '';
        const sizeClass = 'w-9 h-9';
        const mutedClass = muted ? ' opacity-40' : '';
        if (imageUrl) {
            return `<img src="${escapeHtml(imageUrl)}" alt="" class="${sizeClass} rounded-full object-cover shrink-0 shadow-sm${mutedClass}" loading="lazy" />`;
        }
        if (outgoing) {
            return `<div class="${sizeClass} rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold shrink-0 shadow-sm${mutedClass}">${escapeHtml(initials)}</div>`;
        }
        if (isUsers) {
            return `<div class="w-8 h-8 rounded-full bg-primary/20 flex items-center justify-center shrink-0 text-xs font-bold text-[#1f7f4e]${mutedClass}">${escapeHtml(initials)}</div>`;
        }
        return `<div class="${sizeClass} rounded-full bg-primary/15 text-primary flex items-center justify-center text-xs font-bold shrink-0${mutedClass}">${escapeHtml(initials)}</div>`;
    }

    const myAvatar = buildAvatarHtml(myInitials, myAvatarUrl, true);
    const peerAvatar = hideChatAvatars || (isUsers && hidePeerAvatar)
        ? ''
        : buildAvatarHtml(peerInitials, peerAvatarUrl, false);
    const showMessageLabels = !hideChatAvatars;

    function messageActionUrl(id) {
        return messageActionUrlTemplate.replace('__MSGID__', String(id));
    }

    function messageReactionUrl(id) {
        return messageReactionUrlTemplate.replace('__MSGID__', String(id));
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `rbhw-toast-enter fixed top-4 right-4 z-[200] max-w-sm rounded-xl px-4 py-3 shadow-lg border text-sm ${type === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-green-200 bg-green-50 text-green-700'}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.remove('rbhw-toast-enter'); toast.classList.add('rbhw-toast-exit'); setTimeout(() => toast.remove(), 180); }, 2500);
    }

    function normalizeMessage(entry) {
        return {
            id: entry.id,
            is_mine: Boolean(entry.is_mine),
            message: entry.message ?? entry.body ?? '',
            sent_at: entry.sent_at || '',
            edited_at: entry.edited_at || null,
            unsent: Boolean(entry.unsent),
            unsent_at: entry.unsent_at || null,
            attachment_url: entry.attachment_url || null,
            attachment_name: entry.attachment_name || null,
            is_image: Boolean(entry.is_image),
            can_edit: Boolean(entry.can_edit),
            created_at_ms: entry.created_at_ms ?? null,
            reactions: entry.reactions ?? { summary: [], my_emoji: null },
        };
    }

    function canEditMessage(entry) {
        if (!entry?.is_mine || entry.unsent) return false;
        if (entry.is_image && entry.attachment_url) return false;
        if (entry.created_at_ms) {
            return Date.now() - entry.created_at_ms < editWindowMs;
        }
        return entry.can_edit === true;
    }

    function clearEditExpiryTimer() {
        if (editExpiryTimer) {
            clearTimeout(editExpiryTimer);
            editExpiryTimer = null;
        }
    }

    function scheduleEditExpiry(entry) {
        clearEditExpiryTimer();
        if (!entry?.created_at_ms) return;
        const remaining = entry.created_at_ms + editWindowMs - Date.now();
        if (remaining <= 0) {
            showToast(`You can only edit messages within ${editWindowMinutes} minutes of sending.`, 'error');
            cancelEditing();
            return;
        }
        editExpiryTimer = setTimeout(() => {
            showToast('Edit time expired.', 'error');
            cancelEditing();
        }, remaining);
    }

    function isImageOnlyMessage(entry) {
        if (!entry.is_image || !entry.attachment_url) return false;
        const text = String(entry.message || '').trim();
        return text === '' || text === 'Sent a file attachment.';
    }

    function segmentGraphemes(text) {
        const str = String(text ?? '');
        if (typeof Intl !== 'undefined' && Intl.Segmenter) {
            return [...new Intl.Segmenter(undefined, { granularity: 'grapheme' }).segment(str)].map((part) => part.segment);
        }
        return [...str];
    }

    function isEmojiGrapheme(grapheme) {
        const value = String(grapheme ?? '').trim();
        if (!value) return false;
        return /\p{Extended_Pictographic}/u.test(value);
    }

    function isEmojiOnlyText(text) {
        const trimmed = String(text ?? '').trim();
        if (!trimmed) return false;
        return segmentGraphemes(trimmed).every((part) => isEmojiGrapheme(part) || !part.trim());
    }

    function countEmojiGraphemes(text) {
        return segmentGraphemes(String(text ?? '').trim()).filter((part) => isEmojiGrapheme(part)).length;
    }

    function emojiOnlySizeClass(text) {
        const count = countEmojiGraphemes(text);
        if (count <= 1) return 'size-1';
        if (count === 2) return 'size-2';
        if (count === 3) return 'size-3';
        return 'size-many';
    }

    function isEmojiOnlyMessage(entry) {
        if (entry.unsent || entry.attachment_url) return false;
        return isEmojiOnlyText(entry.message);
    }

    function renderMessageText(text) {
        const str = String(text ?? '');
        if (!str) return '';
        if (isEmojiOnlyText(str)) {
            return `<p class="rbhw-chat-emoji-only ${emojiOnlySizeClass(str)} whitespace-pre-wrap">${escapeHtml(str.trim())}</p>`;
        }
        const parts = [];
        let buffer = '';
        let bufferIsEmoji = null;
        const flush = () => {
            if (!buffer) return;
            parts.push({ isEmoji: bufferIsEmoji, value: buffer });
            buffer = '';
            bufferIsEmoji = null;
        };
        segmentGraphemes(str).forEach((part) => {
            const emoji = isEmojiGrapheme(part);
            if (buffer && bufferIsEmoji !== emoji) flush();
            buffer += part;
            bufferIsEmoji = emoji;
        });
        flush();
        const html = parts.map((part) => {
            if (part.isEmoji) {
                return `<span class="rbhw-chat-emoji-inline">${escapeHtml(part.value)}</span>`;
            }
            return escapeHtml(part.value);
        }).join('');
        return `<p class="rbhw-chat-text whitespace-pre-wrap break-words">${html}</p>`;
    }

    function bubbleClassForEntry(baseClass, entry, outgoing) {
        if (isImageOnlyMessage(entry)) {
            if (isUsers) {
                return outgoing
                    ? 'message-body rounded-2xl rounded-br-md bg-primary p-1'
                    : 'message-body rounded-2xl rounded-bl-md border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-900 p-1';
            }
            return outgoing
                ? 'message-body chat-bubble-out text-white rounded-2xl rounded-br-md p-1'
                : 'message-body rounded-2xl rounded-bl-md border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-1';
        }
        if (isEmojiOnlyMessage(entry)) {
            return 'message-body rbhw-chat-emoji-only-bubble';
        }
        return `message-body ${baseClass}`;
    }

    function renderAttachment(entry, outgoing, imageOnly = false) {
        if (!entry.attachment_url) return '';
        if (entry.is_image) {
            const border = imageOnly
                ? ''
                : (outgoing && !isUsers ? ' border border-white/20' : (isUsers ? ' border border-[#d0e7d9] dark:border-gray-600' : ''));
            const spacing = imageOnly ? '' : ' mt-2';
            const imageUrl = escapeHtml(entry.attachment_url);
            const imageName = escapeHtml(entry.attachment_name || 'Photo');
            return `<button type="button" class="rbhw-chat-image-btn block${spacing} p-0 border-0 bg-transparent" data-image-url="${imageUrl}" data-image-name="${imageName}" aria-label="View photo">
                <img src="${imageUrl}" alt="${imageName}" class="rbhw-chat-image${border}"/>
            </button>`;
        }
        const linkClass = outgoing && !isUsers
            ? 'mt-2 inline-flex items-center gap-2 rounded-lg bg-white/15 px-3 py-2 text-xs font-semibold hover:bg-white/25 transition'
            : 'mt-2 inline-flex items-center gap-2 rounded-lg border border-current/20 px-3 py-2 text-xs font-semibold hover:opacity-80 transition';
        return `<a href="${escapeHtml(entry.attachment_url)}" target="_blank" rel="noopener" class="${linkClass}"><span class="material-symbols-outlined text-sm">description</span>${escapeHtml(entry.attachment_name || 'View file')}</a>`;
    }

    function renderMessageBody(entry, outgoing) {
        const imageOnly = isImageOnlyMessage(entry);
        const text = imageOnly ? '' : renderMessageText(entry.message);
        const attachment = renderAttachment(entry, outgoing, imageOnly);
        if (!text && !attachment) return '<span class="opacity-80 italic">Empty message</span>';
        return `${text}${attachment}`;
    }

    function openPhotoViewer(url, name) {
        if (!photoViewer || !photoViewerImage) return;
        photoViewerImage.src = url;
        photoViewerImage.alt = name || 'Photo';
        if (photoViewerTitle) photoViewerTitle.textContent = name || 'Photo';
        photoViewer.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePhotoViewer() {
        if (!photoViewer || !photoViewerImage) return;
        photoViewer.classList.add('hidden');
        photoViewerImage.removeAttribute('src');
        photoViewerImage.alt = '';
        if (photoViewerTitle) photoViewerTitle.textContent = '';
        document.body.style.overflow = '';
    }

    function renderMessageMeta(entry) {
        const edited = entry.edited_at ? ' · Edited' : '';
        return `${escapeHtml(entry.sent_at || '')}${edited}`;
    }

    function closeInstantReactBars() {
        chatMessages.querySelectorAll('.rbhw-message-bubble-wrap.is-actions-open').forEach((wrap) => {
            wrap.classList.remove('is-actions-open');
        });
    }

    function closeAllMessageMenus() {
        openMessageMenuId = null;
        chatMessages.querySelectorAll('.rbhw-message-more-menu').forEach((menu) => menu.classList.add('hidden'));
        closeInstantReactBars();
    }

    function closeMessageActionSheet() {
        openMessageSheetId = null;
        messageSheet?.classList.add('hidden');
        if (messageSheetEmojis) messageSheetEmojis.innerHTML = '';
        if (messageSheetActions) messageSheetActions.innerHTML = '';
    }

    function getMessageEntry(messageId) {
        return chatHistory.find((item) => Number(item.id) === Number(messageId)) || null;
    }

    function renderMoreMenu(entry) {
        if (!entry?.is_mine) return '';
        const editBtn = canEditMessage(entry)
            ? `<button type="button" class="message-edit-btn">Edit</button>`
            : '';
        return `
            <div class="relative shrink-0">
                <button type="button" class="rbhw-instant-more-btn message-more-toggle" title="More options" aria-label="More options">
                    <span class="material-symbols-outlined text-[18px]">more_horiz</span>
                </button>
                <div class="rbhw-message-more-menu hidden">
                    ${editBtn}
                    <button type="button" class="unsend-everyone-btn">Unsend for everyone</button>
                    <button type="button" class="remove-you-btn">Remove for you</button>
                </div>
            </div>`;
    }

    function renderInstantReactBar(entry, outgoing) {
        if (!messageReactionUrlTemplate || entry.unsent) return '';
        const sideClass = outgoing ? 'is-outgoing' : 'is-incoming';
        const choices = REACTION_EMOJIS.map((emoji) =>
            `<button type="button" class="rbhw-instant-react-btn" data-emoji="${escapeHtml(emoji)}" aria-label="React ${escapeHtml(emoji)}">${emoji}</button>`
        ).join('');
        const moreMenu = renderMoreMenu(entry);
        const divider = moreMenu ? '<div class="rbhw-instant-react-divider" aria-hidden="true"></div>' : '';
        return `<div class="rbhw-instant-react-bar ${sideClass}" role="toolbar" aria-label="React to message">${choices}${divider}${moreMenu}</div>`;
    }

    function openMessageActionSheet(messageId) {
        const entry = getMessageEntry(messageId);
        if (!entry || entry.unsent || !messageSheet) return;
        closeAllMessageMenus();
        openMessageSheetId = Number(messageId);

        if (messageSheetEmojis && messageReactionUrlTemplate) {
            messageSheetEmojis.innerHTML = REACTION_EMOJIS.map((emoji) =>
                `<button type="button" class="rbhw-message-sheet-emoji rbhw-sheet-react-btn" data-emoji="${escapeHtml(emoji)}" aria-label="React ${escapeHtml(emoji)}">${emoji}</button>`
            ).join('');
        }

        if (messageSheetActions) {
            const actions = [];
            if (entry.is_mine && canEditMessage(entry)) {
                actions.push(`<button type="button" class="rbhw-message-sheet-action message-edit-btn" data-message-id="${entry.id}"><span class="material-symbols-outlined">edit</span>Edit</button>`);
            }
            if (entry.is_mine) {
                actions.push(`<button type="button" class="rbhw-message-sheet-action unsend-everyone-btn" data-message-id="${entry.id}"><span class="material-symbols-outlined">undo</span>Unsend for everyone</button>`);
            }
            actions.push(`<button type="button" class="rbhw-message-sheet-action remove-you-btn${entry.is_mine ? ' is-danger' : ''}" data-message-id="${entry.id}"><span class="material-symbols-outlined">${entry.is_mine ? 'delete' : 'visibility_off'}</span>${entry.is_mine ? 'Remove for you' : 'Hide for you'}</button>`);
            messageSheetActions.innerHTML = actions.join('');
        }

        messageSheet.classList.remove('hidden');
    }

    function clearLongPressTimer() {
        if (longPressTimer) {
            clearTimeout(longPressTimer);
            longPressTimer = null;
        }
    }

    function renderMessageReactions(entry, outgoing) {
        if (entry.unsent || !entry.reactions?.summary?.length) return '';
        const sideClass = outgoing ? 'is-outgoing' : 'is-incoming';
        const pills = entry.reactions.summary.map((reaction) => {
            const mine = reaction.reacted_by_me ? ' is-mine' : '';
            const count = reaction.count > 1 ? `<span class="rbhw-reaction-count">${reaction.count}</span>` : '';
            return `<button type="button" class="rbhw-reaction-pill${mine}" data-emoji="${escapeHtml(reaction.emoji)}" title="Change reaction">${escapeHtml(reaction.emoji)}${count}</button>`;
        }).join('');
        return `<div class="rbhw-message-reactions ${sideClass}">${pills}</div>`;
    }

    function renderMessageBubbleBlock(entry, outgoing) {
        if (entry.unsent) {
            const bubbleClass = bubbleClassForEntry(outgoing ? outgoingBubble : incomingBubble, entry, outgoing);
            return `<div class="rbhw-message-bubble-wrap"><div class="${bubbleClass}">${renderMessageBody(entry, outgoing)}</div></div>`;
        }
        const bubbleClass = bubbleClassForEntry(outgoing ? outgoingBubble : incomingBubble, entry, outgoing);
        const emojiOnly = isEmojiOnlyMessage(entry);
        return `
            <div class="rbhw-message-bubble-wrap${emojiOnly ? ' is-emoji-only' : ''}">
                ${renderInstantReactBar(entry, outgoing)}
                <div class="${bubbleClass}">${renderMessageBody(entry, outgoing)}</div>
                ${renderMessageReactions(entry, outgoing)}
            </div>`;
    }

    async function toggleReaction(messageId, emoji) {
        if (!messageReactionUrlTemplate || !messageId || !emoji) return;
        try {
            const response = await fetch(messageReactionUrl(messageId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ emoji }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                showToast(data?.message || 'Unable to react to message.', 'error');
                return;
            }
            const index = chatHistory.findIndex((item) => item.id === messageId);
            if (index !== -1) {
                chatHistory[index] = normalizeMessage(data.message);
            }
            closeInstantReactBars();
            closeMessageActionSheet();
            renderMessages();
        } catch {
            showToast('Unable to react right now.', 'error');
        }
    }

    function getComposerMinHeight(el) {
        if (!el) return 36;
        const minHeight = parseFloat(window.getComputedStyle(el).minHeight);
        return Number.isFinite(minHeight) && minHeight > 0 ? minHeight : 36;
    }

    function autoResizeTextarea(el, maxHeight = 128) {
        if (!el) return;
        const minHeight = getComposerMinHeight(el);
        el.style.removeProperty('height');
        el.style.height = 'auto';
        const next = Math.min(Math.max(el.scrollHeight, minHeight), maxHeight);
        el.style.setProperty('height', `${next}px`, 'important');
        el.style.overflowY = next >= maxHeight ? 'auto' : 'hidden';
    }

    function resetComposerHeight() {
        if (!chatInput) return;
        const minHeight = getComposerMinHeight(chatInput);
        chatInput.style.setProperty('height', `${minHeight}px`, 'important');
        chatInput.style.overflowY = 'hidden';
        chatInput.scrollTop = 0;
    }

    /**
     * Keyboard closed: one-line composer, scrolled so the last draft line shows (222).
     */
    function collapseComposerPreview() {
        if (!chatInput) return;
        const minHeight = getComposerMinHeight(chatInput);
        chatInput.style.setProperty('height', `${minHeight}px`, 'important');
        // Must allow scroll so the last line can sit in the one-line window.
        chatInput.style.overflowY = 'auto';
        chatInput.scrollTop = chatInput.scrollHeight;
        // Re-apply after layout — mobile browsers often reset scrollTop on resize.
        window.requestAnimationFrame(() => {
            chatInput.scrollTop = chatInput.scrollHeight;
        });
    }

    /**
     * Keyboard open: expand to full draft and show from the start (111).
     */
    function expandComposerEditing() {
        if (!chatInput) return;
        if (!(chatInput.value || '').trim()) {
            resetComposerHeight();
            return;
        }
        chatInput.style.removeProperty('height');
        autoResizeTextarea(chatInput, 128);
        chatInput.scrollTop = 0;
    }

    function clearComposerInput({ keepFocus = false } = {}) {
        if (!chatInput) return;
        chatInput.value = '';
        resetComposerHeight();
        if (keepFocus) {
            chatInput.focus({ preventScroll: true });
        }
    }

    function keepLatestMessageVisible() {
        window.requestAnimationFrame(() => {
            if (window.ChatKeyboard?.sync) {
                window.ChatKeyboard.sync();
            }
            scrollChatToBottom();
            window.ChatKeyboard?.scrollToBottom?.();
            window.setTimeout(() => {
                scrollChatToBottom();
                window.ChatKeyboard?.scrollToBottom?.();
            }, 80);
            window.setTimeout(() => {
                scrollChatToBottom();
                window.ChatKeyboard?.scrollToBottom?.();
            }, 280);
        });
    }

    function clearSelectedFile() {
        selectedFile = null;
        if (chatGalleryInput) chatGalleryInput.value = '';
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
        chatFilePreview?.classList.add('hidden');
        chatFilePreviewThumb?.classList.add('hidden');
        if (chatFilePreviewThumb) chatFilePreviewThumb.removeAttribute('src');
        chatFilePreviewIcon?.classList.remove('hidden');
    }

    function setSelectedFile(file) {
        selectedFile = file;
        if (!file) { clearSelectedFile(); return; }
        chatFilePreviewName.textContent = file.name;
        chatFilePreview.classList.remove('hidden');
        if (file.type.startsWith('image/') && chatFilePreviewThumb) {
            if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = URL.createObjectURL(file);
            chatFilePreviewThumb.src = previewObjectUrl;
            chatFilePreviewThumb.classList.remove('hidden');
            chatFilePreviewIcon?.classList.add('hidden');
        } else {
            chatFilePreviewThumb?.classList.add('hidden');
            chatFilePreviewIcon?.classList.remove('hidden');
            chatFilePreviewIcon.textContent = 'image';
        }
    }

    function setMediaButtonsDisabled(disabled) {
        chatMediaBtns.forEach((btn) => {
            btn.toggleAttribute('disabled', disabled);
            btn.style.opacity = disabled ? '0.45' : '';
        });
    }

    function handleImageSelection(file) {
        if (!file) { clearSelectedFile(); return; }
        if (!file.type.startsWith('image/')) {
            showToast('Please choose an image file.', 'error');
            clearSelectedFile();
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            showToast('Image must be 5MB or smaller.', 'error');
            clearSelectedFile();
            return;
        }
        setSelectedFile(file);
    }

    function stopCameraStream() {
        if (cameraStream) {
            cameraStream.getTracks().forEach((track) => track.stop());
            cameraStream = null;
        }
        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }
    }

    function clearCameraPreviewState() {
        capturedCameraBlob = null;
        if (cameraPreviewObjectUrl) {
            URL.revokeObjectURL(cameraPreviewObjectUrl);
            cameraPreviewObjectUrl = null;
        }
        if (cameraPreview) {
            cameraPreview.removeAttribute('src');
            cameraPreview.classList.add('hidden');
        }
        if (cameraVideo) cameraVideo.classList.remove('hidden');
        cameraLiveControls?.classList.remove('hidden');
        cameraPreviewControls?.classList.add('hidden');
        if (cameraStatus) cameraStatus.textContent = 'Camera';
    }

    function resetCameraModalUi() {
        clearCameraPreviewState();
        stopCameraStream();
        if (cameraCaptureBtn) cameraCaptureBtn.disabled = true;
    }

    function closeCameraModal() {
        resetCameraModalUi();
        cameraModal?.classList.add('hidden');
    }

    async function startCameraStream() {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('unsupported');
        }
        stopCameraStream();
        if (cameraCaptureBtn) cameraCaptureBtn.disabled = true;
        if (cameraStatus) cameraStatus.textContent = 'Starting camera...';
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: cameraFacingMode,
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                },
                audio: false,
            });
            if (cameraVideo) {
                cameraVideo.srcObject = cameraStream;
                await cameraVideo.play().catch(() => {});
            }
            if (cameraCaptureBtn) cameraCaptureBtn.disabled = false;
            if (cameraStatus) cameraStatus.textContent = 'Tap the button to capture';
        } catch (error) {
            stopCameraStream();
            throw error;
        }
    }

    async function openCameraModal() {
        if (editingMessageId !== null || !cameraModal) return;
        clearCameraPreviewState();
        cameraModal.classList.remove('hidden');
        try {
            await startCameraStream();
        } catch {
            closeCameraModal();
            if (chatCameraFallbackInput) {
                chatCameraFallbackInput.click();
                return;
            }
            showToast('Unable to access camera.', 'error');
        }
    }

    function captureCameraPhoto() {
        if (!cameraVideo || !cameraStream || cameraVideo.readyState < 2) return;
        const canvas = document.createElement('canvas');
        canvas.width = cameraVideo.videoWidth;
        canvas.height = cameraVideo.videoHeight;
        const context = canvas.getContext('2d');
        if (!context) {
            showToast('Could not capture photo.', 'error');
            return;
        }
        context.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => {
            if (!blob) {
                showToast('Could not capture photo.', 'error');
                return;
            }
            capturedCameraBlob = blob;
            if (cameraPreviewObjectUrl) URL.revokeObjectURL(cameraPreviewObjectUrl);
            cameraPreviewObjectUrl = URL.createObjectURL(blob);
            if (cameraPreview) {
                cameraPreview.src = cameraPreviewObjectUrl;
                cameraPreview.classList.remove('hidden');
            }
            cameraVideo.classList.add('hidden');
            stopCameraStream();
            cameraLiveControls?.classList.add('hidden');
            cameraPreviewControls?.classList.remove('hidden');
            if (cameraStatus) cameraStatus.textContent = 'Use this photo?';
        }, 'image/jpeg', 0.92);
    }

    function useCameraPhoto() {
        if (!capturedCameraBlob) return;
        const file = new File([capturedCameraBlob], `photo-${Date.now()}.jpg`, { type: 'image/jpeg' });
        handleImageSelection(file);
        closeCameraModal();
    }

    async function retakeCameraPhoto() {
        clearCameraPreviewState();
        try {
            await startCameraStream();
        } catch {
            closeCameraModal();
            showToast('Unable to access camera.', 'error');
        }
    }

    async function flipCamera() {
        cameraFacingMode = cameraFacingMode === 'environment' ? 'user' : 'environment';
        if (capturedCameraBlob) {
            await retakeCameraPhoto();
            return;
        }
        try {
            await startCameraStream();
        } catch {
            showToast('Unable to switch camera.', 'error');
        }
    }

    function renderOutgoingRow(entry) {
        if (entry.unsent) {
            return `
                <div class="max-w-[min(85%,560px)] w-full">
                    ${showMessageLabels && !isUsers ? `<p class="${labelClass} text-right">${escapeHtml(myLabel)}</p>` : ''}
                    <div class="rbhw-bubble-unsent rounded-2xl rounded-br-md px-4 py-2.5 text-sm flex items-center gap-2 ml-auto w-fit">
                        <span class="material-symbols-outlined text-base">block</span>
                        You unsent a message
                    </div>
                    <p class="message-meta ${metaClass} mt-1.5 text-right">${escapeHtml(entry.unsent_at || entry.sent_at || '')}</p>
                </div>
                ${buildAvatarHtml(myInitials, myAvatarUrl, true, true)}`;
        }
        return `
            <div class="max-w-[min(85%,560px)] w-full">
                ${showMessageLabels && !isUsers ? `<p class="${labelClass} text-right">${escapeHtml(myLabel)}</p>` : ''}
                <div class="rbhw-message-lane is-outgoing flex w-full justify-end">
                    ${renderMessageBubbleBlock(entry, true)}
                </div>
                <p class="message-meta ${metaClass} mt-1.5 text-right">${renderMessageMeta(entry)}</p>
            </div>
            ${myAvatar}`;
    }

    function renderIncomingRow(entry) {
        const body = entry.unsent
            ? `<div class="rbhw-bubble-unsent rounded-2xl rounded-bl-md px-4 py-2.5 text-sm flex items-center gap-2"><span class="material-symbols-outlined text-base">block</span>Message was unsent</div>`
            : renderMessageBubbleBlock(entry, false);
        const meta = entry.unsent
            ? escapeHtml(entry.unsent_at || entry.sent_at || '')
            : renderMessageMeta(entry);
        return `
            ${peerAvatar}
            <div class="max-w-[min(85%,560px)] w-full">
                ${showMessageLabels && !isUsers ? `<p class="${labelClass}">${escapeHtml(peerLabel)}</p>` : ''}
                <div class="rbhw-message-lane is-incoming flex w-full justify-start">
                    ${body}
                </div>
                <p class="message-meta ${metaClass} mt-1">${meta}</p>
            </div>`;
    }

    function renderUnreadDivider() {
        return `
            <div class="rbhw-unread-divider flex items-center gap-3 py-3 my-1 w-full max-w-lg mx-auto px-2">
                <div class="rbhw-unread-divider-line"></div>
                <span class="rbhw-unread-divider-text shrink-0 text-[11px] font-semibold uppercase tracking-wide">Unread messages</span>
                <div class="rbhw-unread-divider-line"></div>
            </div>`;
    }

    function scrollChatToBottom() {
        if (!chatMessages) return;
        const apply = () => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };
        apply();
        requestAnimationFrame(() => {
            apply();
            requestAnimationFrame(apply);
        });
        chatMessages.querySelectorAll('.rbhw-chat-image').forEach((img) => {
            if (!img.complete) {
                img.addEventListener('load', apply, { once: true });
                img.addEventListener('error', apply, { once: true });
            }
        });
    }

    function scrollToUnreadDivider() {
        const unreadDivider = chatMessages.querySelector('.rbhw-unread-divider-row');
        if (!unreadDivider) {
            scrollChatToBottom();
            return;
        }
        const containerRect = chatMessages.getBoundingClientRect();
        const dividerRect = unreadDivider.getBoundingClientRect();
        const targetTop = dividerRect.top - containerRect.top + chatMessages.scrollTop - 16;
        chatMessages.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
    }

    function updateComposerEditState() {
        const isEditing = editingMessageId !== null;
        chatEditBar?.classList.toggle('hidden', !isEditing);
        chatQuickReplies?.classList.toggle('hidden', isEditing);
        setMediaButtonsDisabled(isEditing);
        if (chatInput) {
            chatInput.placeholder = isEditing ? 'Edit your message...' : defaultComposerPlaceholder;
        }
        if (chatSendBtn) {
            chatSendBtn.innerHTML = isEditing
                ? '<span class="material-symbols-outlined">check</span>'
                : '<span class="material-symbols-outlined">send</span>';
            chatSendBtn.setAttribute('aria-label', isEditing ? 'Save edit' : 'Send message');
        }
    }

    function startEditing(messageId) {
        const entry = chatHistory.find(item => item.id === messageId);
        if (!entry || entry.unsent) return;
        if (entry.is_image && entry.attachment_url) return;
        if (!canEditMessage(entry)) {
            showToast(`You can only edit messages within ${editWindowMinutes} minutes of sending.`, 'error');
            return;
        }
        editingMessageId = messageId;
        if (chatInput) {
            chatInput.value = entry.message || '';
            autoResizeTextarea(chatInput, 128);
            chatInput.focus();
            chatInput.setSelectionRange(chatInput.value.length, chatInput.value.length);
        }
        clearSelectedFile();
        closeAllMessageMenus();
        scheduleEditExpiry(entry);
        updateComposerEditState();
        renderMessages();
    }

    function cancelEditing() {
        if (editingMessageId === null) return;
        clearEditExpiryTimer();
        editingMessageId = null;
        if (chatInput) {
            chatInput.value = '';
            resetComposerHeight();
        }
        updateComposerEditState();
        renderMessages();
    }

    async function saveEditing() {
        if (editingMessageId === null) return;
        const messageId = editingMessageId;
        const newText = chatInput?.value.trim() || '';
        if (!newText) {
            showToast('Message cannot be empty.', 'error');
            return;
        }
        chatSendBtn.disabled = true;
        chatInput.disabled = true;
        try {
            const response = await fetch(messageActionUrl(messageId), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ message: newText }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                showToast(data?.message || 'Unable to edit message.', 'error');
                return;
            }
            const index = chatHistory.findIndex(item => item.id === messageId);
            if (index !== -1) {
                chatHistory[index] = normalizeMessage({
                    ...chatHistory[index],
                    message: data.message.body ?? data.message.message,
                    edited_at: data.message.edited_at,
                    can_edit: data.message.can_edit,
                    created_at_ms: data.message.created_at_ms ?? chatHistory[index].created_at_ms,
                });
            }
            clearEditExpiryTimer();
            editingMessageId = null;
            clearComposerInput({ keepFocus: true });
            updateComposerEditState();
            renderMessages();
            keepLatestMessageVisible();
            showToast('Message updated.');
        } catch {
            showToast('Unable to edit message right now.', 'error');
        } finally {
            chatSendBtn.disabled = false;
            chatInput.disabled = false;
            setMediaButtonsDisabled(false);
            if (document.activeElement !== chatInput) {
                chatInput?.focus({ preventScroll: true });
            }
        }
    }

    function renderMessengerIntro(inThread = false) {
        if (!showMessengerIntro) return '';
        const avatarBlock = peerAvatarUrl
            ? `<img src="${escapeHtml(peerAvatarUrl)}" alt="" class="rbhw-messenger-intro-avatar" loading="lazy" />`
            : `<div class="rbhw-messenger-intro-avatar-fallback">${escapeHtml(peerInitials)}</div>`;
        const introClass = inThread ? 'rbhw-messenger-intro rbhw-messenger-intro--thread' : 'rbhw-messenger-intro';
        const nameClass = isUsers
            ? 'text-lg font-bold text-[#0e1b13] dark:text-white'
            : 'text-lg font-bold text-text-light dark:text-text-dark';
        const metaClass = isUsers
            ? 'text-sm text-[#4e976b] dark:text-gray-400 mt-3'
            : 'text-sm text-text-light/60 dark:text-text-dark/60 mt-3';
        const subtitleClass = isUsers
            ? 'text-sm text-[#4e976b] dark:text-gray-400 mt-1.5 max-w-sm leading-relaxed'
            : 'text-sm text-text-light/60 dark:text-text-dark/60 mt-1.5 max-w-sm leading-relaxed';
        return `<div class="${introClass}">
            ${avatarBlock}
            <p class="${nameClass}">${escapeHtml(peerName)}</p>
            <p class="${metaClass}">${escapeHtml(emptyTitle)}</p>
            <p class="${subtitleClass}">${escapeHtml(emptySubtitle)}</p>
        </div>`;
    }

    function renderEmptyState() {
        if (showMessengerIntro) {
            return renderMessengerIntro(false);
        }
        return `<div class="flex flex-col items-center justify-center h-full min-h-[200px] text-center py-8">
            <span class="material-symbols-outlined text-4xl text-primary mb-2">forum</span>
            <p class="font-semibold">${escapeHtml(emptyTitle)}</p>
            <p class="text-sm text-text-light/60 dark:text-text-dark/60 mt-1 max-w-md">${escapeHtml(emptySubtitle)}</p>
        </div>`;
    }

    function appendMessengerIntro(inThread) {
        const introHtml = renderMessengerIntro(inThread);
        if (!introHtml) return;
        const introRow = document.createElement('div');
        introRow.className = 'rbhw-messenger-intro-row w-full shrink-0';
        introRow.innerHTML = introHtml;
        chatMessages.appendChild(introRow);
    }

    function renderMessages() {
        closeAllMessageMenus();
        closeMessageActionSheet();
        chatMessages.innerHTML = '';

        if (!chatHistory.length) {
            if (showMessengerIntro) {
                appendMessengerIntro(false);
            } else {
                chatMessages.innerHTML = renderEmptyState();
            }
            return;
        }

        if (showMessengerIntro) {
            appendMessengerIntro(true);
        }

        let unreadDividerInserted = false;

        chatHistory.forEach((rawEntry) => {
            const entry = normalizeMessage(rawEntry);
            if (!unreadDividerInserted && unreadMessageIds.has(Number(entry.id))) {
                const dividerRow = document.createElement('div');
                dividerRow.className = 'rbhw-unread-divider-row w-full flex justify-center';
                dividerRow.innerHTML = renderUnreadDivider();
                chatMessages.appendChild(dividerRow);
                unreadDividerInserted = true;
            }

            const row = document.createElement('div');
            row.className = 'rbhw-chat-message-row group flex items-end gap-2 md:gap-2.5 ' + (entry.is_mine ? 'justify-end' : 'justify-start');
            if (entry.unsent) row.classList.add('is-unsent');
            if (isEmojiOnlyMessage(entry)) row.classList.add('is-emoji-only-row');
            if (editingMessageId === entry.id) row.classList.add('is-being-edited');
            row.dataset.messageId = String(entry.id);
            row.innerHTML = entry.is_mine ? renderOutgoingRow(entry) : renderIncomingRow(entry);
            chatMessages.appendChild(row);
        });

        if (shouldScrollToUnread) {
            shouldScrollToUnread = false;
            requestAnimationFrame(() => requestAnimationFrame(() => scrollToUnreadDivider()));
        } else if (!editingMessageId) {
            scrollChatToBottom();
        }
    }

    async function removeMessage(messageId, scope) {
        if (!messageId) return;
        try {
            const response = await fetch(messageActionUrl(messageId), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ scope }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) { showToast(data?.message || 'Unable to remove message.', 'error'); return; }
            const index = chatHistory.findIndex(item => item.id === messageId);
            if (index !== -1) {
                if (scope === 'everyone') {
                    chatHistory[index] = normalizeMessage({ ...chatHistory[index], message: '', unsent: true, unsent_at: data.unsent_at || '', attachment_url: null, attachment_name: null, is_image: false, edited_at: null });
                } else {
                    chatHistory.splice(index, 1);
                }
            }
            if (editingMessageId === messageId) cancelEditing();
            closeAllMessageMenus();
            renderMessages();
            showToast(scope === 'everyone' ? 'Message unsent for everyone.' : 'Message removed for you.');
        } catch {
            showToast('Unable to remove message right now.', 'error');
        }
    }

    renderMessages();
    updateComposerEditState();
    resetComposerHeight();
    window.addEventListener('load', () => {
        resetComposerHeight();
        scrollChatToBottom();
    }, { once: true });

    chatInput?.addEventListener('input', () => {
        // Only grow while the keyboard is open / input is focused.
        autoResizeTextarea(chatInput);
        chatInput.scrollTop = chatInput.scrollHeight;
    });

    chatInput?.addEventListener('focus', () => {
        // Expand multi-line draft and show from the top (111...).
        window.requestAnimationFrame(() => {
            expandComposerEditing();
            keepLatestMessageVisible();
        });
    });

    chatInput?.addEventListener('blur', () => {
        // Collapse to 1 line; if draft remains, show the last line (…222).
        if ((chatInput.value || '').trim()) {
            collapseComposerPreview();
        } else {
            resetComposerHeight();
        }
        keepLatestMessageVisible();
        window.setTimeout(() => {
            if ((chatInput.value || '').trim()) {
                collapseComposerPreview();
            } else {
                resetComposerHeight();
            }
            keepLatestMessageVisible();
        }, 150);
    });

    let lastViewportHeight = window.visualViewport?.height ?? window.innerHeight;
    const onViewportChange = () => {
        const currentHeight = window.visualViewport?.height ?? window.innerHeight;
        const keyboardClosed = currentHeight > lastViewportHeight + 60;
        const keyboardOpened = currentHeight < lastViewportHeight - 60;
        lastViewportHeight = currentHeight;

        const hasDraft = !!(chatInput?.value || '').trim();
        const isFocused = document.activeElement === chatInput;

        window.ChatKeyboard?.sync?.();

        if (!isFocused) {
            if (hasDraft) {
                collapseComposerPreview();
            } else {
                resetComposerHeight();
            }
        } else if (keyboardOpened) {
            expandComposerEditing();
        }

        keepLatestMessageVisible();
        if (keyboardClosed && !isFocused) {
            window.setTimeout(() => {
                if ((chatInput?.value || '').trim()) {
                    collapseComposerPreview();
                } else {
                    resetComposerHeight();
                }
                keepLatestMessageVisible();
            }, 100);
        }
    };

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', onViewportChange);
        window.visualViewport.addEventListener('scroll', onViewportChange);
    } else {
        window.addEventListener('resize', onViewportChange);
    }

    chatEditCancelBtn?.addEventListener('click', cancelEditing);
    chatCameraBtn?.addEventListener('click', () => {
        if (editingMessageId !== null) return;
        openCameraModal();
    });
    chatCameraFallbackInput?.addEventListener('change', () => {
        const file = chatCameraFallbackInput.files?.[0];
        chatCameraFallbackInput.value = '';
        handleImageSelection(file);
    });
    cameraCloseBtn?.addEventListener('click', closeCameraModal);
    cameraCaptureBtn?.addEventListener('click', captureCameraPhoto);
    cameraRetakeBtn?.addEventListener('click', retakeCameraPhoto);
    cameraUseBtn?.addEventListener('click', useCameraPhoto);
    cameraFlipBtn?.addEventListener('click', flipCamera);
    chatGalleryBtn?.addEventListener('click', () => {
        if (editingMessageId !== null) return;
        chatGalleryInput?.click();
    });
    chatGalleryInput?.addEventListener('change', () => {
        const file = chatGalleryInput.files?.[0];
        chatGalleryInput.value = '';
        handleImageSelection(file);
    });
    chatFileRemoveBtn?.addEventListener('click', clearSelectedFile);

    document.querySelectorAll('.rbhw-quick-reply').forEach(btn => {
        btn.addEventListener('click', () => {
            chatInput.value = btn.textContent.trim();
            autoResizeTextarea(chatInput);
            chatInput.focus();
        });
    });

    photoViewerBackdrop?.addEventListener('click', closePhotoViewer);
    photoViewerClose?.addEventListener('click', closePhotoViewer);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && cameraModal && !cameraModal.classList.contains('hidden')) {
            event.preventDefault();
            closeCameraModal();
            return;
        }
        if (event.key === 'Escape' && photoViewer && !photoViewer.classList.contains('hidden')) {
            event.preventDefault();
            closePhotoViewer();
            return;
        }
        if (event.key === 'Escape' && openMessageSheetId) {
            closeMessageActionSheet();
            return;
        }
        closeAllMessageMenus();
    });

    messageSheetBackdrop?.addEventListener('click', closeMessageActionSheet);

    messageSheet?.addEventListener('click', async (event) => {
        const sheetReact = event.target.closest('.rbhw-sheet-react-btn');
        const editBtn = event.target.closest('.message-edit-btn');
        const unsendEveryoneBtn = event.target.closest('.unsend-everyone-btn');
        const removeYouBtn = event.target.closest('.remove-you-btn');
        const messageId = openMessageSheetId;

        if (sheetReact && messageId) {
            event.preventDefault();
            await toggleReaction(messageId, sheetReact.dataset.emoji || '');
            return;
        }
        if (editBtn && messageId) {
            closeMessageActionSheet();
            startEditing(messageId);
            return;
        }
        if (unsendEveryoneBtn && messageId) {
            closeMessageActionSheet();
            await removeMessage(messageId, 'everyone');
            return;
        }
        if (removeYouBtn && messageId) {
            closeMessageActionSheet();
            await removeMessage(messageId, 'you');
        }
    });

    chatMessages.addEventListener('contextmenu', (event) => {
        if (!isTouchDevice) return;
        const bubbleWrap = event.target.closest('.rbhw-message-bubble-wrap');
        const row = event.target.closest('.rbhw-chat-message-row');
        if (bubbleWrap && row && !row.classList.contains('is-unsent')) {
            event.preventDefault();
        }
    });

    chatMessages.addEventListener('touchstart', (event) => {
        if (!isTouchDevice) return;
        const bubbleWrap = event.target.closest('.rbhw-message-bubble-wrap');
        const row = event.target.closest('.rbhw-chat-message-row');
        if (!bubbleWrap || !row || row.classList.contains('is-unsent')) return;
        if (event.target.closest('.rbhw-reaction-pill') || event.target.closest('.rbhw-chat-image-btn')) return;

        const touch = event.changedTouches[0];
        longPressStartX = touch.clientX;
        longPressStartY = touch.clientY;
        const messageId = Number(row.dataset.messageId);
        clearLongPressTimer();
        longPressTimer = setTimeout(() => {
            longPressTimer = null;
            if (navigator.vibrate) navigator.vibrate(10);
            openMessageActionSheet(messageId);
        }, LONG_PRESS_MS);
    }, { passive: true });

    chatMessages.addEventListener('touchmove', (event) => {
        if (!longPressTimer) return;
        const touch = event.changedTouches[0];
        if (Math.abs(touch.clientX - longPressStartX) > LONG_PRESS_MOVE_TOLERANCE || Math.abs(touch.clientY - longPressStartY) > LONG_PRESS_MOVE_TOLERANCE) {
            clearLongPressTimer();
        }
    }, { passive: true });

    chatMessages.addEventListener('touchend', clearLongPressTimer);
    chatMessages.addEventListener('touchcancel', clearLongPressTimer);

    chatMessages.addEventListener('click', async (event) => {
        const imageBtn = event.target.closest('.rbhw-chat-image-btn');
        if (imageBtn) {
            event.preventDefault();
            openPhotoViewer(imageBtn.dataset.imageUrl, imageBtn.dataset.imageName);
            return;
        }

        const row = event.target.closest('.rbhw-chat-message-row');
        const bubbleWrap = event.target.closest('.rbhw-message-bubble-wrap');
        const instantReact = event.target.closest('.rbhw-instant-react-btn');
        const reactionPill = event.target.closest('.rbhw-reaction-pill');
        const moreToggle = event.target.closest('.message-more-toggle');
        const editBtn = event.target.closest('.message-edit-btn');
        const unsendEveryoneBtn = event.target.closest('.unsend-everyone-btn');
        const removeYouBtn = event.target.closest('.remove-you-btn');

        if (instantReact && row) {
            event.stopPropagation();
            await toggleReaction(Number(row.dataset.messageId), instantReact.dataset.emoji || '');
            return;
        }
        if (reactionPill && row) {
            event.stopPropagation();
            await toggleReaction(Number(row.dataset.messageId), reactionPill.dataset.emoji || '');
            return;
        }
        if (moreToggle && row && bubbleWrap) {
            event.stopPropagation();
            const messageId = Number(row.dataset.messageId);
            const menu = bubbleWrap.querySelector('.rbhw-message-more-menu');
            if (!menu) return;
            if (openMessageMenuId === messageId) {
                closeAllMessageMenus();
                return;
            }
            closeAllMessageMenus();
            openMessageMenuId = messageId;
            bubbleWrap.classList.add('is-actions-open');
            menu.classList.remove('hidden');
            return;
        }
        if (unsendEveryoneBtn && row) {
            event.stopPropagation();
            closeAllMessageMenus();
            await removeMessage(Number(row.dataset.messageId), 'everyone');
            return;
        }
        if (removeYouBtn && row) {
            event.stopPropagation();
            closeAllMessageMenus();
            await removeMessage(Number(row.dataset.messageId), 'you');
            return;
        }
        if (editBtn && row) {
            event.stopPropagation();
            closeAllMessageMenus();
            startEditing(Number(row.dataset.messageId));
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.message-more-toggle') && !event.target.closest('.rbhw-message-more-menu')) {
            closeAllMessageMenus();
        }
        if (openMessageSheetId && messageSheet && !messageSheet.contains(event.target)) {
            closeMessageActionSheet();
        }
    });

    chatInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && editingMessageId !== null) {
            event.preventDefault();
            cancelEditing();
        }
    });

    chatForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (editingMessageId !== null) {
            await saveEditing();
            return;
        }
        const message = chatInput.value.trim();
        if (!message && !selectedFile) { showToast('Enter a message or attach an image.', 'error'); return; }
        chatSendBtn.disabled = true;
        chatInput.disabled = true;
        setMediaButtonsDisabled(true);
        try {
            const formData = new FormData();
            if (message) formData.append('message', message);
            if (selectedFile) formData.append('attachment', selectedFile);
            const response = await fetch(messageStoreUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: formData,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) { showToast(data?.message || (data?.errors?.message?.[0]) || 'Unable to send message.', 'error'); return; }
            chatHistory.push(normalizeMessage(data.message));
            renderMessages();
            clearComposerInput({ keepFocus: false });
            clearSelectedFile();
            keepLatestMessageVisible();
            showToast('Message sent.');
        } catch {
            showToast('Unable to send message right now.', 'error');
        } finally {
            chatSendBtn.disabled = false;
            chatInput.disabled = false;
            setMediaButtonsDisabled(false);
            // Reset after re-enable so mobile browsers apply the collapsed height.
            resetComposerHeight();
            // On mobile, blur so the keyboard closes and the latest message is visible again.
            if (window.matchMedia('(max-width: 1023px)').matches) {
                chatInput?.blur();
                keepLatestMessageVisible();
                window.setTimeout(() => {
                    resetComposerHeight();
                    keepLatestMessageVisible();
                }, 120);
                window.setTimeout(keepLatestMessageVisible, 320);
            } else {
                chatInput?.focus({ preventScroll: true });
            }
        }
    });
})();
</script>
