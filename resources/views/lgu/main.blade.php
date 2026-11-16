<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
@include('partials.submit-guard')
<title>Bantay Kalusugan - LGU Dashboard</title>
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
    @include('partials.lgu-responsive-styles')
</head>
<body class="lgu-layout font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('partials.app-flash')
<div class="main-container">

    
    @include('sidebars.lgu')
    
    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-7xl mx-auto">
                <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-3xl font-bold leading-tight">LGU Dashboard</h1>
                        <p class="text-base font-normal leading-normal text-text-light/70 dark:text-text-dark/70">Welcome to your local government unit health management system</p>
                    </div>
                </header>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <div class="flex items-center justify-center h-96">
                        <p class="text-text-light/70 dark:text-text-dark/70 text-lg">Dashboard content will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>


@include('partials.lgu-mobile-nav')
</body>
</html>