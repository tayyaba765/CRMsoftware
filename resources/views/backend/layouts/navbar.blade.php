<div id="slot-navbar" class="relative z-20 flex h-16 lg:h-20 items-center gap-2 sm:gap-4 lg:gap-5 px-3 sm:px-4 lg:px-6 border-b border-gray-200 dark:border-gray-800 transition-colors">

  <!-- Sidebar Toggle Button (Mobile: Drawer Open / Desktop: Collapse) -->
  <button id="navSidebarToggle" type="button" class="flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand/30 transition-colors flex-shrink-0" aria-label="Toggle sidebar" title="Toggle sidebar">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="3" y1="12" x2="21" y2="12"></line>
      <line x1="3" y1="6" x2="21" y2="6"></line>
      <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
  </button>

  <!-- Mobile Brand Badge (Visible on small screens when sidebar is hidden off-canvas) -->
  <a href="{{ route('newdash') }}" class="flex items-center gap-2 lg:hidden font-bold text-lg text-gray-900 dark:text-white no-underline flex-shrink-0">
    <svg width="24" height="24" viewBox="0 0 32 32">
      <circle cx="16" cy="16" r="14" fill="#5b3fd4" />
      <circle cx="16" cy="16" r="5.5" fill="#fff" />
    </svg>
    <span class="hidden xs:inline">CORK</span>
  </a>

  <!-- Search Input (Adaptive & Responsive across phone, tablet, laptop, large screens) -->
  <label class="flex items-center gap-2 sm:gap-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 px-3 py-2 sm:px-4 sm:py-2.5 text-sm sm:text-base text-gray-500 dark:text-gray-400 flex-1 max-w-[210px] xs:max-w-[240px] sm:max-w-xs md:max-w-md min-w-0 transition-all focus-within:ring-2 focus-within:ring-brand/30">
    <svg class="flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="7"/>
      <path d="M21 21l-3.5-3.5"/>
    </svg>
    <input placeholder="Search..." class="w-full min-w-0 bg-transparent outline-none placeholder:text-gray-400 text-sm sm:text-base text-gray-800 dark:text-gray-200" />
  </label>

  <!-- Right Actions: Theme Toggle & Profile -->
  <div class="ml-auto flex items-center gap-3.5 sm:gap-5 lg:gap-7 flex-shrink-0">
    <!-- Theme Toggle Button -->
    <button id="themeBtn" type="button" class="rounded-lg p-2 sm:p-2.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 focus:outline-none transition-colors" aria-label="Toggle theme" title="Toggle theme">
      <svg id="iconMoon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="sm:w-[22px] sm:h-[22px]">
        <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
      </svg>
      <svg id="iconSun" class="hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="sm:w-[22px] sm:h-[22px]">
        <circle cx="12" cy="12" r="4"/>
        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
      </svg>
    </button>

    <!-- Subtle vertical separator for clean visual spacing -->
    <div class="h-6 w-px bg-gray-200 dark:bg-gray-700/80" aria-hidden="true"></div>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button id="profileBtn" type="button" class="flex h-9 w-9 sm:h-10 sm:w-10 lg:h-11 lg:w-11 items-center justify-center rounded-full bg-gradient-to-br from-brand to-purple-800 text-sm sm:text-base font-bold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-brand/40 transition-transform active:scale-95" aria-label="User menu" aria-expanded="false">
        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
      </button>

      <div id="profileMenu" class="absolute z-50 right-0 mt-2 hidden w-48 sm:w-56 rounded-xl border border-gray-200 dark:border-gray-700 p-2 sm:p-2.5 shadow-2xl">
        <div class="block rounded-lg px-3.5 py-2 sm:px-4 sm:py-2.5 text-sm sm:text-base font-medium text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700/50 mb-1 truncate">
          {{ ucfirst(strtolower(auth()->user()->name ?? 'User')) }}
        </div>
        <a href="{{ route('profiles.index') }}" class="block rounded-lg px-3.5 py-2 sm:px-4 sm:py-2.5 text-sm sm:text-base text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors no-underline">
          Profile
        </a>
       
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit" class="w-full text-left block rounded-lg px-3.5 py-2 sm:px-4 sm:py-2.5 text-sm sm:text-base text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors font-medium">
            Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function navbarComponent() {
  const root = document.documentElement;
  const moon = document.getElementById('iconMoon'), sun = document.getElementById('iconSun');

  function applyTheme(dark) {
    root.classList.toggle('dark', dark);
    root.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
    if (moon) moon.classList.toggle('hidden', dark);
    if (sun) sun.classList.toggle('hidden', !dark);
  }
  applyTheme(localStorage.getItem('cork-theme') === 'dark');

  const themeBtn = document.getElementById('themeBtn');
  if (themeBtn) {
    themeBtn.onclick = () => {
      const dark = !root.classList.contains('dark');
      applyTheme(dark);
      localStorage.setItem('cork-theme', dark ? 'dark' : 'light');
      window.dispatchEvent(new CustomEvent('cork:theme', { detail: { dark } }));
    };
  }
  window.addEventListener('cork:theme', (e) => applyTheme(e.detail.dark));

  // Profile Menu Dropdown Toggle
  const btn = document.getElementById('profileBtn'), menu = document.getElementById('profileMenu');
  if (btn && menu) {
    btn.onclick = (e) => {
      e.stopPropagation();
      menu.classList.toggle('hidden');
      btn.setAttribute('aria-expanded', !menu.classList.contains('hidden'));
    };
    document.addEventListener('click', (e) => {
      if (!menu.contains(e.target) && !btn.contains(e.target)) {
        menu.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Sidebar Toggle Dispatcher
  const navSidebarToggle = document.getElementById('navSidebarToggle');
  if (navSidebarToggle) {
    navSidebarToggle.onclick = () => {
      window.dispatchEvent(new CustomEvent('cork:toggle-sidebar'));
    };
  }
})();
</script>