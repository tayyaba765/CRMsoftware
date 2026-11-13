<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CORK · Analytics</title>

    <!-- Tailwind (CDN – use a compiled build for production) + Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#5b3fd4',
                            dark: '#4629b8',
                            soft: '#f1eefc'
                        },
                        page: '#f1f2f3',
                        ink: '#0e1726',
                        muted: '#6b7280'
                    }
                }
            }
        };
    </script>

    <!-- Shared layout styles -->
    <link rel="stylesheet" href="layouts_new/head.css" />
</head>

<body data-page="dashboard" class="bg-page font-sans text-ink antialiased">
    <div class="flex min-h-screen">

        <div id="backdrop" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden"></div>

        <!-- ===================== SIDEBAR ===================== -->
        <!-- Logo, collapse arrow, Dashboard group, and the "USERS" section (Add Users / Add Roles / Add Permissions).
     data-perm hides an item when the logged-in user lacks that permission. -->
        <aside id="sidebar"
            class="fixed left-0 top-0 z-40 flex h-screen w-60 shrink-0 flex-col overflow-y-auto border-r border-dashed border-gray-300 bg-white lg:sticky">
            <div id="sbHead" class="flex h-20 shrink-0 items-center justify-between px-5">
                <a id="brand" href="master.html" data-nav="dashboard" class="flex items-center gap-2.5">
                    <svg width="32" height="32" viewBox="0 0 32 32">
                        <circle cx="16" cy="16" r="14" fill="#5b3fd4" />
                        <circle cx="16" cy="16" r="5.5" fill="#fff" />
                    </svg>
                    <span class="text-xl font-bold tracking-wide">CORK</span>
                </a>
                <button id="collapseBtn" class="hidden text-muted hover:text-brand lg:block"
                    aria-label="Collapse sidebar" data-i="chevrons-left"></button>
            </div>

            <nav class="flex-1 px-3 pb-6 text-sm font-medium">
                <!-- Dashboard group -->
                <button id="dashToggle" class="item flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-brand">
                    <span class="inline-flex" data-i="home"></span>
                    <span class="nl flex-1 text-left">Dashboard</span>
                    <span id="dashChev" class="nl inline-flex transition-transform" data-i="chevron-down"></span>
                </button>
                <ul id="dashMenu" class="nl mt-1">
                    <li><a href="master.html" data-nav="dashboard"
                            class="flex items-center gap-3 rounded-lg px-5 py-2.5"><span
                                class="h-1.5 w-1.5 rounded-full bg-current"></span>Analytics</a></li>
                </ul>

                <!-- USERS section -->
                <p class="nl mb-2 mt-7 px-3 text-xs font-bold tracking-widest">USERS</p>
                <ul class="space-y-1">
                    <li data-perm="users.create"><a href="users.html" data-nav="users"
                            class="item flex items-center gap-3 rounded-lg px-3 py-2.5"><span class="inline-flex"
                                data-i="user-plus"></span><span class="nl">Add Users</span></a></li>
                    <li data-perm="roles.manage"><a href="roles.html" data-nav="roles"
                            class="item flex items-center gap-3 rounded-lg px-3 py-2.5"><span class="inline-flex"
                                data-i="shield"></span><span class="nl">Add Roles</span></a></li>
                    <li data-perm="permissions.manage"><a href="permissions.html" data-nav="permissions"
                            class="item flex items-center gap-3 rounded-lg px-3 py-2.5"><span class="inline-flex"
                                data-i="key"></span><span class="nl">Add Permissions</span></a></li>
                </ul>
            </nav>
        </aside>


        <div class="flex min-w-0 flex-1 flex-col">

            <!-- ===================== NAVBAR ===================== -->
            <!-- Menu (mobile), search, dark mode, notifications, profile dropdown -->
            <header class="sticky top-0 z-20 flex h-16 items-center gap-4 bg-page/90 px-5 backdrop-blur lg:px-8">
                <button id="menuBtn" class="lg:hidden" aria-label="Open menu" data-i="menu"></button>
                <label
                    class="flex w-full max-w-sm items-center gap-2.5 rounded-xl bg-[#e9ebee] px-3.5 py-2 text-sm text-muted">
                    <span class="inline-flex" data-i="search" data-s="18"></span>
                    <input id="globalSearch" placeholder="Search..."
                        class="w-full bg-transparent text-ink outline-none placeholder:text-muted" />
                    <kbd class="hidden whitespace-nowrap text-xs font-semibold sm:block">Ctrl + /</kbd>
                </label>

                <div class="ml-auto flex items-center gap-4 text-muted">
                    <button id="darkBtn" class="hover:text-brand" aria-label="Toggle dark mode"
                        data-i="moon"></button>
                    <button class="relative hover:text-brand" aria-label="Notifications">
                        <span class="inline-flex" data-i="bell"></span>
                        <span class="absolute right-0 top-0 h-2 w-2 rounded-full border border-page bg-red-500"></span>
                    </button>

                    <!-- Profile toggle + dropdown (Profile / Logout) -->
                    <div class="relative">
                        <button id="profileBtn" class="relative block" aria-haspopup="true" aria-expanded="false">
                            <span id="avatar"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#6d4bd8] to-[#3f26a8] text-sm font-bold text-white"></span>
                            <span
                                class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-green-500"></span>
                        </button>
                        <div id="profileMenu"
                            class="absolute right-0 mt-3 hidden w-60 rounded-xl border border-gray-100 bg-white p-2 text-ink shadow-xl">
                            <div class="border-b border-gray-100 px-3 pb-3 pt-2">
                                <p id="pName" class="font-semibold"></p>
                                <p id="pRole" class="text-xs text-muted"></p>
                            </div>
                            <a href="#"
                                class="mt-2 flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-brand-soft hover:text-brand"><span
                                    class="inline-flex" data-i="user" data-s="17"></span>Profile</a>
                            <button id="logoutBtn"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-red-500 hover:bg-red-50"><span
                                    class="inline-flex" data-i="log-out" data-s="17"></span>Logout</button>
                        </div>
                    </div>
                </div>
            </header>


            <!-- ===================== CONTENT AREA ===================== -->
            <main class="flex-1 px-5 pb-8 pt-1 lg:px-8">
                <h1 id="pageTitle" class="mb-5 text-xl font-semibold"></h1>

                <!-- VIEW 1: Analytics dashboard — click a card to select it and load its table; charts animate and respond to hover / click -->
                <div class="space-y-5">

                    <!-- Stat cards (clickable + selectable, built by JS). The selected card decides which table is shown below. -->
                    <section id="stats" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4"></section>

                    <!-- Charts row: bar chart + pie chart -->
                    <section class="grid gap-5 xl:grid-cols-3">
                        <div class="card p-5 xl:col-span-2">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-medium">Registrations</h2>
                                    <p class="text-sm text-muted">Hover a month to see values, click to select it.</p>
                                </div>
                                <div id="barLegend" class="flex gap-2 text-xs">
                                    <button type="button" data-s="Registered"
                                        class="lg flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-muted"><i
                                            class="h-2.5 w-2.5 rounded-full"
                                            style="background:#6ccf9f"></i>Registered</button>
                                    <button type="button" data-s="Roles assigned"
                                        class="lg flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-muted"><i
                                            class="h-2.5 w-2.5 rounded-full" style="background:#e88a90"></i>Roles
                                        assigned</button>
                                </div>
                            </div>
                            <div id="barChart" class="mt-3"></div>
                            <p id="barCaption" class="mt-2 text-sm text-muted"></p>
                        </div>
                        <div class="card p-5">
                            <h2 class="text-lg font-medium">Users by role</h2>
                            <p class="text-sm text-muted">Click a slice to filter the table below.</p>
                            <div id="pieChart" class="mt-3"></div>
                            <p id="pieCaption" class="mt-1 min-h-[1.25rem] text-center text-sm text-muted"></p>
                            <div id="pieLegend"
                                class="mt-3 flex flex-wrap justify-center gap-1.5 border-t border-dashed border-gray-200 pt-4 text-xs">
                            </div>
                        </div>
                    </section>

                    <!-- Details table (interactive: search, sort, pages, click a row to select it) -->
                    <section id="detail" class="card p-5">
                        <div class="mb-1 flex flex-wrap items-center gap-3">
                            <h2 id="detailTitle" class="text-lg font-medium"></h2><span id="roleChip"></span>
                        </div>
                        <p id="selInfo" class="mb-4 text-sm text-muted"></p>
                        <div id="tblDash"></div>
                    </section>
                </div>

            </main>

            <!-- ===================== FOOTER ===================== -->
            <footer class="flex flex-wrap items-center justify-between gap-3 px-5 py-5 text-xs text-muted lg:px-8">
                <p>© 2026 <span class="font-semibold text-ink">CORK</span>. All rights reserved.</p>
                <div class="flex gap-5"><a href="#" class="hover:text-brand">Privacy</a><a href="#"
                        class="hover:text-brand">Terms</a><a href="#" class="hover:text-brand">Support</a>
                </div>
            </footer>

            <!-- ===================== GLOBAL OVERLAYS ===================== -->
            <!-- Modal (assign roles / edit permissions), tooltip, toast -->
            <div id="modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/40 p-4">
                <div class="card w-full max-w-lg p-6">
                    <h3 id="mTitle" class="text-lg font-semibold"></h3>
                    <div id="mHint" class="mb-4 mt-1 text-sm text-muted"></div>
                    <div id="mBody" class="max-h-[50vh] space-y-2 overflow-y-auto pr-1"></div>
                    <div class="mt-5 flex justify-end gap-2"><button id="mCancel"
                            class="btn2">Cancel</button><button id="mSave" class="btn">Save roles</button>
                    </div>
                </div>
            </div>
            <div id="tip"
                class="pointer-events-none fixed z-[60] hidden rounded-md bg-ink px-2.5 py-1.5 text-xs text-white shadow">
            </div>
            <div id="toast"
                class="fixed bottom-5 right-5 z-50 hidden rounded-lg bg-ink px-4 py-2.5 text-sm text-white shadow-lg">
            </div>

        </div>
    </div>

    <!-- Shared core (icons, data model, table engine, nav wiring, boot()) -->
    <script src="layouts_new/body.js"></script>
    <!-- This page's own render logic -->
    <script src="pages/dashboard.js"></script>
    <script>
        boot();
    </script>
</body>

</html>
