<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Stats Queries
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'"))['total'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_fee) as total FROM enrollments WHERE status = 'enrolled'"))['total'] ?? 0;
$total_posts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM posts"))['total'];

// Page helper for active sidebar state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <title>Command Center | C-Familia Admin</title>
    <style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        letter-spacing: -0.01em;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    .sidebar-link-active {
        background: #2563eb;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
    }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-950 text-white hidden lg:flex flex-col sticky top-0 h-screen">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20 text-white">
                        C</div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight">C-Familia</h1>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest leading-none mt-1">
                            Admin Suite</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-1.5 overflow-y-auto">
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mb-4 px-4">Main Menu</p>

                <a href="admin_dashboard.php"
                    class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_dashboard.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>

                <a href="admin_enrollments.php"
                    class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_enrollments.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Enrollments
                    <?php if($pending_count > 0): ?>
                    <span
                        class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-md font-bold"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>

                <a href="admin_payments.php"
                    class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_payments.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Payments
                </a>

                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-8 mb-4 px-4">Communication
                </p>

                <a href="admin_announcements.php"
                    class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_announcements.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    Announcements
                </a>

                <a href="admin_posts.php"
                    class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_posts.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2v4h4" />
                    </svg>
                    Resources & Posts
                </a>
            </nav>

            <div class="p-6 border-t border-white/5 bg-slate-900/50">
                <a href="logout.php"
                    class="flex items-center gap-3 py-3.5 px-4 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign Out
                </a>
            </div>
        </aside>

        <main class="flex-1 p-4 md:p-10">
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">System Analytics</h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
                        <p class="text-slate-500 text-sm font-medium">Real-time system monitoring active.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-200">
                    <div
                        class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-bold text-slate-600">
                        A</div>
                    <div class="pr-4">
                        <p class="text-xs font-bold leading-none text-slate-900"><?= $_SESSION['username'] ?></p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Super Admin</p>
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div
                    class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Students</p>
                    <h3 class="text-3xl font-[800] text-slate-900"><?= number_format($total_students) ?></h3>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Queue</p>
                    <h3 class="text-3xl font-[800] text-slate-900"><?= $pending_count ?></h3>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Gross Revenue</p>
                    <h3 class="text-3xl font-[800] text-slate-900">₱<?= number_format($total_revenue / 1000, 1) ?>K</h3>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div
                        class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Content</p>
                    <h3 class="text-3xl font-[800] text-slate-900"><?= $total_posts ?></h3>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                        <h4 class="font-[800] text-slate-800 tracking-tight">Recent Enrollments</h4>
                        <a href="admin_enrollments.php"
                            class="text-blue-600 text-[10px] font-black hover:underline uppercase tracking-wider bg-blue-50 px-3 py-1.5 rounded-lg transition">Full
                            Table</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-slate-50">
                                <?php
                                $recent_enroll = mysqli_query($conn, "SELECT enrollments.*, users.name FROM enrollments JOIN users ON enrollments.user_id = users.id ORDER BY enrollments.created_at DESC LIMIT 6");
                                while($row = mysqli_fetch_assoc($recent_enroll)):
                                ?>
                                <tr class="hover:bg-slate-50/50 transition group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center font-bold text-slate-500 text-xs">
                                                <?= substr($row['name'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 text-sm"><?= $row['name'] ?></p>
                                                <p class="text-[10px] text-slate-400 uppercase font-black">
                                                    <?= $row['program_type'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <?php if($row['status'] == 'pending'): ?>
                                        <span
                                            class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[9px] font-black uppercase tracking-tighter">Waiting</span>
                                        <?php else: ?>
                                        <span
                                            class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[9px] font-black uppercase tracking-tighter">Approved</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-8 py-5 text-[10px] text-slate-400 font-bold text-right italic">
                                        <?= date('M d, H:i', strtotime($row['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white relative overflow-hidden group">
                        <div class="relative z-10">
                            <h4 class="font-black text-xl mb-4 tracking-tight">Quick Actions</h4>
                            <div class="space-y-3">
                                <a href="admin_posts.php"
                                    class="flex items-center justify-between w-full p-4 bg-white/5 rounded-2xl hover:bg-white/10 transition border border-white/5 group">
                                    <span class="text-sm font-bold">New Post</span>
                                    <span class="text-blue-500 group-hover:translate-x-1 transition-transform">→</span>
                                </a>
                                <a href="admin_enrollments.php"
                                    class="flex items-center justify-between w-full p-4 bg-white/5 rounded-2xl hover:bg-white/10 transition border border-white/5 group">
                                    <span class="text-sm font-bold">Review Queue</span>
                                    <span class="text-blue-500 group-hover:translate-x-1 transition-transform">→</span>
                                </a>
                            </div>
                        </div>
                        <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-blue-600/20 rounded-full blur-3xl"></div>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                        <h4 class="font-black text-slate-800 mb-4 tracking-tight uppercase text-xs tracking-[0.2em]">
                            Maintenance</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-600">Database</span>
                                <span class="text-[10px] font-black text-green-500 uppercase">Healthy</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-600">Storage</span>
                                <span class="text-[10px] font-black text-blue-500 uppercase">84% Free</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>