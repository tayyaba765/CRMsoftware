<div id="slot-sidebar" class="relative z-10 border-r border-gray-200 dark:border-gray-800 transition-all duration-300">
    <!-- Sidebar Header -->
    <div class="sidebar-header flex h-16 lg:h-20 items-center justify-between px-4 lg:px-6 border-b border-gray-200 dark:border-gray-800">
        <a href="{{ route('newdash') }}" id="brand" class="flex items-center gap-3 font-bold text-xl lg:text-2xl text-gray-900 dark:text-white no-underline focus:outline-none">
            <svg width="32" height="32" viewBox="0 0 32 32" class="flex-shrink-0">
                <circle cx="16" cy="16" r="14" fill="#5b3fd4" />
                <circle cx="16" cy="16" r="5.5" fill="#fff" />
            </svg>
            <span id="brandLabel" class="transition-opacity duration-200">CORK</span>
        </a>

        <!-- Desktop Collapse Button (>= 1024px) -->
        <button id="collapseBtn" type="button" class="hidden lg:inline-flex items-center justify-center p-1.5 rounded-lg text-gray-400 hover:text-brand hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition-colors" aria-label="Collapse sidebar" title="Toggle collapse">
            <svg id="collapseIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="transition-transform duration-300">
                <path d="M11 17l-5-5 5-5M18 17l-5-5 5-5" />
            </svg>
        </button>

        <!-- Mobile Close Button (< 1024px) -->
        <button id="sidebarCloseBtn" type="button" class="lg:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:text-gray-200 dark:hover:bg-gray-800 focus:outline-none transition-colors" aria-label="Close sidebar">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="p-3 lg:px-4 lg:py-5 text-sm lg:text-base font-medium space-y-1.5">
        <a href="{{ route('newdash') }}" data-tooltip="Dashboard" title="Dashboard" class="nav-item {{ request()->routeIs('newdash') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2.5 lg:px-4 lg:py-3 transition-colors">
            <svg class="flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <path d="M9 22V12h6v10" />
            </svg>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="{{ route('profiles.index') }}" data-tooltip="Profiles" title="Profiles" class="nav-item {{ request()->routeIs('profiles.*') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2.5 lg:px-4 lg:py-3 transition-colors">
            <svg class="flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <span class="nav-label">Profiles</span>
        </a>

        <a href="{{ route('organizations.index') }}" data-tooltip="Organizations" title="Organizations" class="nav-item {{ request()->routeIs('organizations.*') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2.5 lg:px-4 lg:py-3 transition-colors">
            <svg class="flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 21h18M3 7v14M21 7v14M6 7V3h12v4M9 7v14M15 7v14" />
            </svg>
            <span class="nav-label">Organizations</span>
        </a>

        <a href="{{ route('scraper.index') }}" data-tooltip="Web Scraper" title="Web Scraper" class="nav-item {{ request()->routeIs('scraper.index') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2.5 lg:px-4 lg:py-3 transition-colors">
            <svg class="flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10" />
                <line x1="2" y1="12" x2="22" y2="12" />
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
            </svg>
            <span class="nav-label">Web Scraper</span>
        </a>

        <a href="{{ route('scraper.pdf') }}" data-tooltip="PDF Scraper" title="PDF Scraper" class="nav-item {{ request()->routeIs('scraper.pdf') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2.5 lg:px-4 lg:py-3 transition-colors">
            <svg class="flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span class="nav-label">Static PDF Scraper</span>
        </a>

        <!-- Section Heading -->
        <div class="section-divider px-3.5 pt-4 pb-2 lg:px-4 lg:pt-5 lg:pb-2">
            <span class="section-label nav-label text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 block">
                Assigning Roles Panel
            </span>
            <div class="section-divider-line hidden border-t border-gray-200 dark:border-gray-800 my-2"></div>
        </div>

        <!-- Admin Panel Accordion -->
        <details id="adminPanel" class="group" {{ (request()->routeIs('users1.*') || request()->routeIs('roles.*') || request()->routeIs('assignments.*') || request()->routeIs('users.*') || request()->routeIs('permissions.*')) ? 'open' : '' }}>
            <summary data-tooltip="Admin Panel" class="nav-item flex items-center gap-3.5 rounded-lg px-3.5 py-2.5 lg:px-4 lg:py-3 cursor-pointer list-none transition-colors" title="Admin Panel">
                <svg class="flex-shrink-0" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
                <span class="nav-label flex-1">Admin Panel</span>
                <svg class="nav-arrow transition-transform duration-200 group-open:rotate-180 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6" />
                </svg>
            </summary>
            <div class="sub-items ml-4 lg:ml-5 mt-1 space-y-1">
                <a href="{{ route('users1.index') }}" data-tooltip="Users Management" title="Users Management" class="nav-item {{ request()->routeIs('users1.*') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2 lg:px-4 lg:py-2.5 transition-colors">
                    <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    <span class="nav-label">Users Management</span>
                </a>
                <a href="{{ route('roles.index') }}" data-tooltip="Roles Management" title="Roles Management" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2 lg:px-4 lg:py-2.5 transition-colors">
                    <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 2l-2 2m-7.6 7.6a5.5 5.5 0 1 1-7.8 7.8 5.5 5.5 0 0 1 7.8-7.8z" />
                    </svg>
                    <span class="nav-label">Roles Management</span>
                </a>
                <a href="{{ route('assignments.index') }}" data-tooltip="Assignment" title="Assignment" class="nav-item {{ request()->routeIs('assignments.*') || request()->routeIs('users.*') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2 lg:px-4 lg:py-2.5 transition-colors">
                    <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="8.5" cy="7" r="4" />
                        <path d="M20 8v6M23 11h-6" />
                    </svg>
                    <span class="nav-label">Assignment</span>
                </a>
                <a href="{{ route('permissions.index') }}" data-tooltip="Permissions" title="Permissions" class="nav-item {{ request()->routeIs('permissions.*') ? 'active' : '' }} flex items-center gap-3.5 rounded-lg px-3.5 py-2 lg:px-4 lg:py-2.5 transition-colors">
                    <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M15 7a2 2 0 0 1 2 2m4 0a6 6 0 0 1-7.743 5.743L11 17H9v2H7v2H4a1 1 0 0 1-1-1v-2.586a1 1 0 0 1 .293-.707l5.964-5.964A6 6 0 1 1 21 9z"/>
                    </svg>
                    <span class="nav-label">Permissions</span>
                </a>
            </div>
        </details>
    </nav>
</div>

<style>
    #slot-sidebar .nav-item {
        color: #6b7280;
        text-decoration: none;
    }

    html.dark #slot-sidebar .nav-item {
        color: #9ca3af;
    }

    #slot-sidebar .nav-item:hover {
        background: #f3f4f6;
        color: #111827;
    }

    html.dark #slot-sidebar .nav-item:hover {
        background: #1f2937;
        color: #f9fafb;
    }

    #slot-sidebar .nav-item.active {
        background: #f5f3ff;
        color: #5b3fd4;
        font-weight: 600;
    }

    html.dark #slot-sidebar .nav-item.active {
        background: #2e1065;
        color: #c4b5fd;
    }

    /* Sleek Desktop Collapsed Tooltip */
    @media (min-width: 1024px) {
        #slot-sidebar.is-collapsed .nav-item {
            position: relative;
        }
        #slot-sidebar.is-collapsed .nav-item:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 0.65rem;
            background: #1f2937;
            color: #ffffff;
            padding: 0.35rem 0.7rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            z-index: 60;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            pointer-events: none;
        }
        html.dark #slot-sidebar.is-collapsed .nav-item:hover::after {
            background: #374151;
            color: #f9fafb;
        }
    }
</style>

<script>
    (function sidebarComponent() {
        const sidebar = document.getElementById('slot-sidebar');
        const shell = document.getElementById('shell');
        const collapseBtn = document.getElementById('collapseBtn');
        const collapseIcon = document.getElementById('collapseIcon');
        const closeBtn = document.getElementById('sidebarCloseBtn');
        const backdrop = document.getElementById('sidebarBackdrop');
        const adminPanel = document.getElementById('adminPanel');

        // Admin panel accordion open state persistence
        if (adminPanel) {
            if (localStorage.getItem('adminPanelOpen') === 'true') {
                adminPanel.setAttribute('open', '');
            }
            adminPanel.addEventListener('toggle', function() {
                localStorage.setItem('adminPanelOpen', adminPanel.open ? 'true' : 'false');
            });
        }

        // Apply Desktop Collapsed State
        function applyDesktopCollapsed(collapsed) {
            if (!sidebar || !shell) return;
            if (window.innerWidth >= 1024) {
                sidebar.classList.toggle('is-collapsed', collapsed);
                shell.style.setProperty('--sidebar-w', collapsed ? '5.25rem' : '17.5rem');
                if (collapseIcon) {
                    collapseIcon.classList.toggle('rotate-180', collapsed);
                }
                window.dispatchEvent(new CustomEvent('cork:sidebar-collapse', { detail: { collapsed } }));
            } else {
                sidebar.classList.remove('is-collapsed');
                shell.style.removeProperty('--sidebar-w');
                if (collapseIcon) collapseIcon.classList.remove('rotate-180');
            }
        }

        // Initialize desktop state
        let isDesktopCollapsed = localStorage.getItem('cork-sidebar-collapsed') === 'true';
        if (window.innerWidth >= 1024 && isDesktopCollapsed) {
            applyDesktopCollapsed(true);
        }

        if (collapseBtn) {
            collapseBtn.onclick = () => {
                isDesktopCollapsed = !sidebar.classList.contains('is-collapsed');
                localStorage.setItem('cork-sidebar-collapsed', isDesktopCollapsed ? 'true' : 'false');
                applyDesktopCollapsed(isDesktopCollapsed);
            };
        }

        // Mobile Drawer Functions
        function openMobileSidebar() {
            if (!sidebar) return;
            sidebar.classList.add('is-mobile-open');
            if (backdrop) backdrop.classList.add('is-visible');
            document.body.classList.add('overflow-hidden');
        }

        function closeMobileSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('is-mobile-open');
            if (backdrop) backdrop.classList.remove('is-visible');
            document.body.classList.remove('overflow-hidden');
        }

        function toggleMobileSidebar() {
            if (!sidebar) return;
            if (sidebar.classList.contains('is-mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        }

        if (closeBtn) closeBtn.onclick = closeMobileSidebar;
        if (backdrop) backdrop.onclick = closeMobileSidebar;

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('is-mobile-open')) {
                closeMobileSidebar();
            }
        });

        // Close mobile drawer when clicking a navigation link
        sidebar.querySelectorAll('a.nav-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    closeMobileSidebar();
                }
            });
        });

        // Global sidebar toggle event (triggered from navbar hamburger button)
        window.addEventListener('cork:toggle-sidebar', () => {
            if (window.innerWidth < 1024) {
                toggleMobileSidebar();
            } else {
                isDesktopCollapsed = !sidebar.classList.contains('is-collapsed');
                localStorage.setItem('cork-sidebar-collapsed', isDesktopCollapsed ? 'true' : 'false');
                applyDesktopCollapsed(isDesktopCollapsed);
            }
        });

        // Responsive resize listener
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeMobileSidebar();
                applyDesktopCollapsed(localStorage.getItem('cork-sidebar-collapsed') === 'true');
            } else {
                sidebar.classList.remove('is-collapsed');
                shell.style.removeProperty('--sidebar-w');
            }
        });
    })();
</script>
