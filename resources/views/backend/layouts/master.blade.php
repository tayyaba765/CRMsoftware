<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CORK — Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: '#5b3fd4'
                    },
                    screens: {
                        'xs': '475px'
                    }
                }
            }
        };
    </script>
    <script>
        (function() {
            var theme = localStorage.getItem('cork-theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }

            // Pre-apply desktop sidebar collapsed width before paint to eliminate layout jump
            if (window.innerWidth >= 1024 && localStorage.getItem('cork-sidebar-collapsed') === 'true') {
                document.documentElement.style.setProperty('--sidebar-w', '5.25rem');
            }
        })();
    </script>

    <!-- Modular Light & Dark Theme Setup (Tailwind, DataTables, Bootstrap Modals) -->
    @include('backend.layouts.theme')

    <style>
        * {
            box-sizing: border-box;
        }

        /* Responsive typography scaling:
     - Mobile (< 640px): 100% (16px base) so text, padding and elements fit mobile viewports cleanly.
     - Tablet (640px - 1023px): 106.25% (17px base).
     - Laptop & Desktop (1024px - 1919px): 125% (20px base) — maintains the exact comfortable scale for laptop.
     - Ultra-large / 4K (>= 1920px): 131.25% (21px base) for crystal clarity on ultrawides.
  */
        html {
            font-size: 100%;
        }

        @media (min-width: 640px) {
            html {
                font-size: 106.25%;
            }
        }

        @media (min-width: 1024px) {
            html {
                font-size: 125%;
            }
        }

        @media (min-width: 1920px) {
            html {
                font-size: 131.25%;
            }
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #ffffff;
            color: #111827;
        }

        html.dark body {
            background-color: #111827 !important;
            color: #f9fafb !important;
        }

        /* Base Mobile & Tablet Layout (< 1024px) */
        #shell {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            position: relative;
            overflow-x: hidden;
        }

        #slot-navbar {
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 30;
            background-color: #ffffff !important;
            transition: background-color .2s ease, border-color .2s ease;
        }

        html.dark #slot-navbar,
        html.dark #slot-navbar.bg-white {
            background-color: #111827 !important;
            border-color: #1f2937 !important;
        }

        #profileMenu {
            background-color: #ffffff !important;
        }

        html.dark #profileMenu,
        html.dark #profileMenu.bg-white {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }

        #slot-content {
            flex: 1 0 auto;
            width: 100%;
            min-width: 0;
            max-width: 100%;
            position: relative;
            z-index: 1;
        }

        /* Responsive padding refinement for mobile screens (< 640px) */
        @media (max-width: 639.98px) {
            #slot-content {
                padding: 0.875rem !important;
            }
        }

        /* Universal Responsive X-Axis Table Scrolling for Smaller Screens */
        .table-responsive,
        .overflow-x-auto,
        .dataTables_wrapper {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            max-width: 100%;
        }

        @media (max-width: 1023.98px) {

            #orgsTable,
            #profilesTable,
            #usersTable,
            #rolesTable,
            #permissionsTable,
            table.dataTable,
            .overflow-x-auto>table {
                min-width: 720px !important;
            }
        }

        /* Sleek custom scrollbars for horizontal table scrolling */
        .overflow-x-auto::-webkit-scrollbar,
        .dataTables_wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb,
        .dataTables_wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.45);
            border-radius: 9999px;
        }

        .overflow-x-auto::-webkit-scrollbar-track,
        .dataTables_wrapper::-webkit-scrollbar-track {
            background: transparent;
        }

        #slot-footer {
            width: 100%;
            margin-top: auto;
            z-index: 1;
            background-color: #ffffff !important;
        }

        html.dark #slot-footer {
            background-color: #111827 !important;
        }

        /* Sidebar Drawer on Mobile & Tablet (< 1024px) */
        #slot-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 17.5rem;
            max-width: 85vw;
            height: 100%;
            z-index: 50;
            background-color: #ffffff !important;
            transform: translateX(-100%);
            transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        html.dark #slot-sidebar {
            background-color: #111827 !important;
        }

        #slot-sidebar.is-mobile-open {
            transform: translateX(0);
        }

        /* Backdrop Overlay for Mobile Drawer */
        #sidebarBackdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(17, 24, 39, 0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 45;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }

        #sidebarBackdrop.is-visible {
            opacity: 1;
            pointer-events: auto;
        }

        /* Laptop, Desktop & Large Screens (>= 1024px) */
        @media (min-width: 1024px) {
            #shell {
                display: grid;
                grid-template-columns: var(--sidebar-w, 17.5rem) 1fr;
                grid-template-rows: 5rem 1fr auto;
                grid-template-areas:
                    "sidebar navbar"
                    "sidebar content"
                    "sidebar footer";
                min-height: 100vh;
                overflow-x: visible;
                transition: grid-template-columns .25s ease;
            }

            #slot-sidebar {
                grid-area: sidebar;
                position: sticky;
                top: 0;
                height: 100vh;
                width: 100%;
                max-width: none;
                transform: none !important;
                box-shadow: none;
                z-index: 20;
                background-color: #ffffff !important;
            }

            html.dark #slot-sidebar {
                background-color: #111827 !important;
            }

            #slot-navbar {
                grid-area: navbar;
                position: relative;
                top: auto;
                background-color: #ffffff !important;
            }

            html.dark #slot-navbar,
            html.dark #slot-navbar.bg-white {
                background-color: #111827 !important;
            }

            #slot-content {
                grid-area: content;
                position: relative;
                z-index: 0;
            }

            #slot-footer {
                grid-area: footer;
                z-index: 0;
            }

            #sidebarBackdrop {
                display: none !important;
            }
        }

        /* Desktop Collapsed Sidebar Modifiers (>= 1024px) */
        @media (min-width: 1024px) {
            #slot-sidebar.is-collapsed {
                width: 5.25rem !important;
            }

            #slot-sidebar.is-collapsed .nav-label,
            #slot-sidebar.is-collapsed #brandLabel {
                display: none !important;
            }

            #slot-sidebar.is-collapsed .sidebar-header {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                justify-content: space-between !important;
            }

            #slot-sidebar.is-collapsed .nav-item {
                justify-content: center !important;
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            #slot-sidebar.is-collapsed .nav-arrow {
                display: none !important;
            }

            #slot-sidebar.is-collapsed .sub-items {
                margin-left: 0 !important;
            }

            #slot-sidebar.is-collapsed .section-label {
                display: none !important;
            }

            #slot-sidebar.is-collapsed .section-divider-line {
                display: block !important;
            }
        }

        /* Thin Scrollbar for Sidebar */
        #slot-sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.4) transparent;
        }

        #slot-sidebar::-webkit-scrollbar {
            width: 5px;
        }

        #slot-sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.4);
            border-radius: 9999px;
        }
    </style>
</head>

<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop" aria-hidden="true"></div>

    <div id="shell">
        @include('backend.layouts.sidebar')
        @include('backend.layouts.navbar')

        @yield('content')

        @include('backend.layouts.footer')
    </div>

    <script>
        window.addEventListener('cork:sidebar-collapse', (e) => {
            const shell = document.getElementById('shell');
            if (shell && window.innerWidth >= 1024) {
                shell.style.setProperty('--sidebar-w', e.detail.collapsed ? '5.25rem' : '17.5rem');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
