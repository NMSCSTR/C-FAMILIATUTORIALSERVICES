<aside id="mobileSidebar"
    class="fixed inset-y-0 left-0 w-72 bg-slate-950 text-white z-50 transform -translate-x-full transition-transform duration-300 lg:hidden flex flex-col">
    <div class="p-8 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl text-white">
                C</div>
            <h1 class="text-xl font-bold">C-Familia</h1>
        </div>
        <button id="closeMenu" class="p-2 text-slate-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <nav class="flex-1 px-6 space-y-1.5 overflow-y-auto text-sm">
        <a href="admin_dashboard.php"
            class="flex items-center gap-3 py-3 px-4 rounded-xl font-semibold <?= ($current_page == 'admin_dashboard.php') ? 'sidebar-link-active' : 'text-slate-400' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>Dashboard</a>
        <a href="admin_enrollments.php"
            class="flex items-center gap-3 py-3 px-4 rounded-xl font-semibold <?= ($current_page == 'admin_enrollments.php') ? 'sidebar-link-active' : 'text-slate-400' ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>Enrollments</a>
        <a href="admin_payments.php"
            class="flex items-center gap-3 py-3 px-4 rounded-xl font-semibold <?= ($current_page == 'admin_payments.php') ? 'sidebar-link-active' : 'text-slate-400' ?>"><svg
                class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>Payments</a>
        <a href="admin_announcements.php"
            class="flex items-center gap-3 py-3 px-4 rounded-xl font-semibold <?= ($current_page == 'admin_announcements.php') ? 'sidebar-link-active' : 'text-slate-400' ?>"><svg
                class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>Announcements</a>
        <a href="admin_posts.php"
            class="flex items-center gap-3 py-3 px-4 rounded-xl font-semibold <?= ($current_page == 'admin_posts.php') ? 'sidebar-link-active' : 'text-slate-400' ?>"><svg
                class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2v4h4" />
            </svg>Posts</a>
    </nav>
</aside>