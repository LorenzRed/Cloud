<style>
    html, body { height: 100%; overflow: hidden; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

    .bhw-messenger-chat-page .main-container {
        height: 100dvh;
        height: 100svh;
        display: flex;
        overflow: hidden;
    }

    @media (min-width: 1024px) {
        .bhw-messenger-chat-page .main-container { height: 100vh; }
    }

    .bhw-messenger-shell {
        min-height: 0;
        background: #fff;
    }
    .dark .bhw-messenger-shell {
        background: #2C2C2C;
    }
    .bhw-messenger-header {
        box-shadow: 0 1px 0 rgb(0 0 0 / 0.06);
    }
    .dark .bhw-messenger-header {
        box-shadow: 0 1px 0 rgb(255 255 255 / 0.06);
    }
    .bhw-messenger-chat-body .chat-bg {
        background-color: #eef2f7;
        background-image: none;
    }
    .dark .bhw-messenger-chat-body .chat-bg {
        background-color: #1a1a1a;
    }
    .bhw-messenger-chat-body .rbhw-chat-scroll {
        padding-top: 2.5rem !important;
        padding-bottom: 0.75rem !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
    .bhw-messenger-chat-body > .shrink-0 {
        background: #fff;
        border-top-color: #e7eaef !important;
        padding: 0.5rem 0.625rem 0.625rem !important;
    }
    .dark .bhw-messenger-chat-body > .shrink-0 {
        background: #2C2C2C;
        border-top-color: #3D3D3D !important;
    }
    .bhw-messenger-chat-body form {
        align-items: center;
        gap: 0.375rem;
        background: #f0f4f8;
        border-radius: 1.5rem;
        padding: 0.3125rem 0.375rem 0.3125rem 0.5rem;
    }
    .dark .bhw-messenger-chat-body form {
        background: #1f2937;
    }
    .bhw-messenger-chat-body .rbhw-media-btn {
        height: 2.25rem;
        width: 2.25rem;
        border: none !important;
        background: transparent !important;
        border-radius: 9999px;
    }
    .bhw-messenger-chat-body textarea {
        min-height: 2.25rem !important;
        max-height: 6.5rem;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        padding-left: 0.25rem !important;
        padding-right: 0.25rem !important;
    }
    .bhw-messenger-chat-body textarea:focus {
        outline: none !important;
    }
    .bhw-messenger-chat-body button[type="submit"] {
        height: 2.375rem;
        width: 2.375rem;
        border-radius: 9999px;
        flex-shrink: 0;
    }

    @media (max-width: 1023px) {
        .bhw-messenger-chat-page #sidebar { display: none !important; }
        .bhw-messenger-chat-page #bhwChatMenuToggle { display: none !important; }
        .bhw-messenger-chat-page #overlay { display: none !important; }
        .bhw-messenger-chat-page .bhw-messenger-main {
            padding: 0 !important;
            width: 100% !important;
        }
        .bhw-messenger-shell {
            min-height: 100dvh;
            min-height: 100svh;
        }
        .bhw-messenger-header {
            padding-top: max(0.5rem, env(safe-area-inset-top, 0px));
        }
        .bhw-messenger-chat-body > .shrink-0 {
            padding-bottom: calc(0.625rem + env(safe-area-inset-bottom, 0px)) !important;
        }
    }

    @media (min-width: 1024px) {
        .bhw-messenger-chat-page {
            background: #fff;
        }
        .dark .bhw-messenger-chat-page {
            background: #2C2C2C;
        }
        .bhw-messenger-chat-page .bhw-messenger-main {
            padding: 0 !important;
            background: #fff;
        }
        .dark .bhw-messenger-chat-page .bhw-messenger-main {
            background: #2C2C2C;
        }
        .bhw-messenger-shell {
            flex: 1;
            width: 100%;
            max-width: none;
            margin: 0;
            border: none;
            border-radius: 0;
            box-shadow: none;
            min-height: 100%;
        }
        .bhw-messenger-header {
            padding: 0.875rem 1.25rem;
        }
        .bhw-messenger-chat-body .rbhw-chat-scroll {
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
            padding-top: 3rem !important;
        }
        .bhw-messenger-chat-body > .shrink-0 {
            padding: 0.75rem 1.25rem 1rem !important;
        }
        .bhw-messenger-chat-body form {
            padding: 0.375rem 0.5rem 0.375rem 0.625rem;
        }
        .bhw-messenger-chat-body .rbhw-media-btn,
        .bhw-messenger-chat-body button[type="submit"] {
            height: 2.5rem;
            width: 2.5rem;
        }
        .bhw-messenger-chat-body textarea {
            min-height: 2.5rem !important;
            font-size: 0.9375rem;
        }
    }

    .toast-enter { animation: bhwToastIn 0.2s ease-out; }
    .toast-exit { animation: bhwToastOut 0.18s ease-in forwards; }
    @keyframes bhwToastIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes bhwToastOut { from { opacity: 1; } to { opacity: 0; transform: translateY(-8px); } }
</style>
