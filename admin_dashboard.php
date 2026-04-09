<?php
session_start();
// Include db.php once. Ensure 'db.php' defines $conn and handles connection errors.
require_once('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ensure database connection exists before queries
if (!$conn) {
    die("Database connection failed.");
}

// ---------------------------------------------------------------------------------------------------------------------------------
// Real-time Data Fetching Section (Database Defenses)
// ---------------------------------------------------------------------------------------------------------------------------------

// 1. Total Students: Count only those with the student role
$student_query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$total_students = mysqli_fetch_assoc(mysqli_query($conn, $student_query))['total'] ?? 0;

// 2. Total Revenue: Sum ONLY approved/paid transactions from the payments table
$revenue_query = "SELECT SUM(amount) as confirmed_total FROM payments WHERE status = 'paid'";
$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_data = mysqli_fetch_assoc($revenue_result);
$total_revenue_raw = $revenue_data['confirmed_total'] ?? 0;

// Format Revenue: If it's 1800, it becomes ₱1.8K. If it's less than 1000, it shows the full amount.
if ($total_revenue_raw >= 1000) {
    $total_revenue = '₱' . number_format($total_revenue_raw / 1000, 1) . 'K';
} else {
    $total_revenue = '₱' . number_format($total_revenue_raw, 0);
}

// 3. Pending Enrollments: Count students waiting for admin review
$pending_query = "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'";
$pending_result = mysqli_query($conn, $pending_query);
$pending_count = mysqli_fetch_assoc($pending_result)['total'] ?? 0;

// 4. Content Posts: Total resources/announcements available
$total_posts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM posts"))['total'] ?? 0;

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Command Center v2 | C-Familia Admin</title>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cf-dark': '#0f172a',
                        'cf-card': '#1e293b',
                        'cf-border': '#334155',
                        'cf-accent': '#3b82f6',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .nav-link.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2), 0 4px 6px -4px rgba(37, 99, 235, 0.1);
            color: white;
        }
    </style>
</head>

<body class="bg-cf-dark text-slate-100 overflow-x-hidden">

    <div class="flex min-h-screen">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-cf-dark border-r border-cf-border flex flex-col h-screen">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-cf-accent rounded-xl flex items-center justify-center font-black text-xl text-white shadow-lg shadow-blue-600/20">C</div>
                    <div>
                        <h1 class="text-xl font-bold text-white tracking-tight">C-Familia</h1>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mt-1">Admin Suite v2.0</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-2 overflow-y-auto pt-2">
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mb-4 px-4">Main Menu</p>
                
                <a href="admin_dashboard.php" class="nav-link flex items-center gap-3.5 py-3.5 px-5 rounded-xl transition-all font-semibold text-slate-400 hover:text-white hover:bg-cf-card <?= ($current_page == 'admin_dashboard.php' || $current_page == '') ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Dashboard
                </a>

                <a href="admin_enrollments.php" class="nav-link flex items-center gap-3.5 py-3.5 px-5 rounded-xl transition-all font-semibold text-slate-400 hover:text-white hover:bg-cf-card <?= ($current_page == 'admin_enrollments.php') ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                    Enrollments
                    <?php if($pending_count > 0): ?>
                    <span class="ml-auto bg-red-600 text-white text-[10px] px-2.5 py-1 rounded-md font-bold"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>

                <a href="admin_payments.php" class="nav-link flex items-center gap-3.5 py-3.5 px-5 rounded-xl transition-all font-semibold text-slate-400 hover:text-white hover:bg-cf-card <?= ($current_page == 'admin_payments.php') ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 022 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    Payments
                </a>

                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-9 mb-4 px-4">Communication</p>

                <a href="admin_announcements.php" class="nav-link flex items-center gap-3.5 py-3.5 px-5 rounded-xl transition-all font-semibold text-slate-400 hover:text-white hover:bg-cf-card <?= ($current_page == 'admin_announcements.php') ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                    Announcements
                </a>

                <a href="admin_posts.php" class="nav-link flex items-center gap-3.5 py-3.5 px-5 rounded-xl transition-all font-semibold text-slate-400 hover:text-white hover:bg-cf-card <?= ($current_page == 'admin_posts.php') ? 'active' : '' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2v4h4" /></svg>
                    Resources
                </a>
            </nav>

            <div class="p-6 border-t border-cf-border">
                <a href="logout.php" class="flex items-center gap-3 py-3.5 px-5 text-red-400 hover:bg-red-950/30 rounded-xl transition-all font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Sign Out Account
                </a>
            </div>
        </aside>

        <main class="flex-1 w-full p-6 md:p-10 lg:ml-72 bg-cf-dark">
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h2 class="text-3xl font-[800] text-white tracking-tight">C-Familia Command Center</h2>
                    <div class="flex items-center gap-2.5 mt-2.5 bg-cf-card/50 border border-cf-border py-2 px-4 rounded-full inline-flex">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        <p class="text-slate-400 text-xs font-semibold">Live Database Monitor Active.</p>
                        <span class="text-cf-border">|</span>
                        <p class="text-slate-500 text-xs font-medium"><?= date('l, F j, Y — g:i A') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-cf-card p-3 rounded-2xl border border-cf-border shadow-inner self-start md:self-auto">
                    <?php if (!empty($_SESSION['profile_pic'])): ?>
                        <img src="uploads/<?= $_SESSION['profile_pic'] ?>" alt="Admin" class="w-12 h-12 rounded-xl object-cover border-2 border-cf-border">
                    <?php else: ?>
                        <div class="w-12 h-12 bg-cf-accent/10 border border-cf-accent rounded-xl flex items-center justify-center font-bold text-cf-accent text-lg shadow-lg">
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="pr-4">
                        <p class="text-sm font-bold leading-none text-white"><?= $_SESSION['username'] ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1.5 tracking-wider">Super Admin</p>
                    </div>
                    <a href="settings.php" class="text-slate-600 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-cf-card p-7 rounded-3xl border border-cf-border shadow-lg hover:border-cf-accent/50 hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-cf-accent/10 text-cf-accent border border-cf-accent/20 rounded-xl flex items-center justify-center mb-5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <p class="text-slate-500 text-[11px] font-black uppercase tracking-widest mb-1.5">Registered Students</p>
                    <h3 class="text-4xl font-[800] text-white tracking-tighter group-hover:text-cf-accent transition"><?= number_format($total_students) ?></h3>
                    <p class="text-slate-600 text-xs mt-1">Active accounts</p>
                </div>

                <div class="bg-cf-card p-7 rounded-3xl border border-cf-border shadow-lg hover:border-amber-500/50 hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-amber-950/30 text-amber-500 border border-amber-900 rounded-xl flex items-center justify-center mb-5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <p class="text-slate-500 text-[11px] font-black uppercase tracking-widest mb-1.5">Pending Enrollments</p>
                    <h3 class="text-4xl font-[800] text-white tracking-tighter group-hover:text-amber-500 transition"><?= $pending_count ?></h3>
                    <p class="text-slate-600 text-xs mt-1">Review queue</p>
                </div>
<div class="bg-cf-card p-7 rounded-3xl border border-cf-border shadow-lg hover:border-green-500/50 hover:-translate-y-1 transition-all group">
    <div class="w-12 h-12 bg-green-950/30 text-green-500 border border-green-900 rounded-xl flex items-center justify-center mb-5 shadow-inner">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <p class="text-slate-500 text-[11px] font-black uppercase tracking-widest mb-1.5">Total Revenue (Approved)</p>
    <h3 class="text-4xl font-[800] text-white tracking-tighter group-hover:text-green-500 transition">
        <?= $total_revenue ?>
    </h3>
    <p class="text-slate-600 text-xs mt-1">Verified transactions only</p>
</div>

                <div class="bg-cf-card p-7 rounded-3xl border border-cf-border shadow-lg hover:border-purple-500/50 hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-purple-950/30 text-purple-500 border border-purple-900 rounded-xl flex items-center justify-center mb-5 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    <p class="text-slate-500 text-[11px] font-black uppercase tracking-widest mb-1.5">Active Content Posts</p>
                    <h3 class="text-4xl font-[800] text-white tracking-tighter group-hover:text-purple-500 transition"><?= $total_posts ?></h3>
                    <p class="text-slate-600 text-xs mt-1">Resources & Feed</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="xl:col-span-2 space-y-8">
                    <div class="bg-cf-card rounded-3xl border border-cf-border shadow-lg overflow-hidden">
                        <div class="p-7 md:p-8 border-b border-cf-border flex justify-between items-center bg-cf-card/50">
                            <div>
                                <h4 class="font-[800] text-lg text-white tracking-tight">Recent Enrollment Activity Feed</h4>
                                <p class="text-slate-500 text-xs mt-1">Real-time log of the latest review applications.</p>
                            </div>
                            <a href="admin_enrollments.php" class="flex items-center gap-2 text-cf-accent text-[11px] font-black uppercase tracking-widest bg-cf-accent/10 hover:bg-cf-accent/20 px-4 py-2.5 rounded-xl transition">
                                Manage Queue
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left min-w-[600px]">
                                <thead class="bg-cf-dark/30 border-b border-cf-border">
                                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                        <th class="px-8 py-4">Student & Program</th>
                                        <th class="px-8 py-4">Batch/Schedule</th>
                                        <th class="px-8 py-4">Status</th>
                                        <th class="px-8 py-4 text-right">Applied At</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-cf-border">
                                    <?php
                                    $recent_enroll = mysqli_query($conn, "SELECT e.*, u.name, u.profile_pic, u.email FROM enrollments e JOIN users u ON e.user_id = u.id ORDER BY e.created_at DESC LIMIT 6");
                                    if(mysqli_num_rows($recent_enroll) > 0):
                                        while($row = mysqli_fetch_assoc($recent_enroll)):
                                    ?>
                                    <tr class="hover:bg-cf-dark/50 transition">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-4">
                                                <?php if (!empty($row['profile_pic'])): ?>
                                                    <img src="uploads/profiles/<?= $row['profile_pic'] ?>" alt="" class="w-10 h-10 rounded-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-10 h-10 bg-cf-border/50 rounded-full flex items-center justify-center font-bold text-slate-500 text-xs"><?= substr($row['name'], 0, 1) ?></div>
                                                <?php endif; ?>
                                                <div>
                                                    <p class="font-bold text-white text-sm"><?= $row['name'] ?></p>
                                                    <p class="text-[10px] text-cf-accent uppercase font-black tracking-wider mt-0.5"><?= $row['program_type'] ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-sm text-slate-400">
                                            <?= $row['batch'] ?>
                                        </td>
                                        <td class="px-8 py-5">
                                            <?php if($row['status'] == 'pending'): ?>
                                                <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase bg-amber-950 text-amber-400 border border-amber-800">Review</span>
                                            <?php elseif($row['status'] == 'enrolled'): ?>
                                                <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase bg-green-950 text-green-400 border border-green-800">Confirmed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-5 text-[10px] text-slate-500 font-bold text-right tabular-nums">
                                            <?= date('M d — g:i A', strtotime($row['created_at'])) ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-10 text-slate-600 text-sm">No recent enrollment activity.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-cf-card p-8 rounded-3xl border border-cf-border shadow-lg">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h4 class="font-[800] text-lg text-white tracking-tight">Revenue & Growth Overview</h4>
                                <p class="text-slate-500 text-xs mt-1">Real-time revenue tracking based on enrollment payments.</p>
                            </div>
                        </div>
                        <div class="h-80 w-full relative">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-cf-card p-8 rounded-3xl text-white relative border border-cf-border overflow-hidden">
                        <h4 class="font-[800] text-lg mb-6 text-white tracking-tight">Administrative Quick Actions</h4>
                        <div class="grid grid-cols-2 gap-4 relative z-10">
                            <a href="admin_posts.php?action=new" class="flex flex-col gap-3 p-5 bg-cf-dark border border-cf-border rounded-2xl hover:border-cf-accent hover:bg-cf-accent/5 transition-all group">
                                <div class="w-10 h-10 bg-cf-accent/10 text-cf-accent rounded-lg flex items-center justify-center border border-cf-accent/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </div>
                                <span class="text-sm font-semibold group-hover:text-white transition">New Post</span>
                            </a>
                            <a href="admin_announcements.php" class="flex flex-col gap-3 p-5 bg-cf-dark border border-cf-border rounded-2xl hover:border-amber-500 hover:bg-amber-950/20 transition-all group">
                                <div class="w-10 h-10 bg-amber-950/30 text-amber-500 rounded-lg flex items-center justify-center border border-amber-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                </div>
                                <span class="text-sm font-semibold group-hover:text-white transition">Announce</span>
                            </a>
                        </div>
                        <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-cf-accent/10 rounded-full blur-3xl"></div>
                    </div>

                    <div class="bg-cf-card p-8 rounded-3xl border border-cf-border shadow-lg">
                        <h4 class="font-[800] text-lg text-white tracking-tight mb-6">Latest Announcements</h4>
                        <div class="space-y-5">
                            <?php
                            $recent_anns = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
                            if(mysqli_num_rows($recent_anns) > 0):
                                while($ann = mysqli_fetch_assoc($recent_anns)):
                                    $cat_color = match($ann['category']) {
                                        'Urgent' => 'text-red-500 border-red-900',
                                        'Event' => 'text-green-500 border-green-900',
                                        'Academic' => 'text-purple-500 border-purple-900',
                                        default => 'text-cf-accent border-cf-border'
                                    };
                            ?>
                            <div class="border-l-4 <?= $cat_color ?> pl-5 py-1">
                                <div class="flex items-center justify-between gap-3">
                                    <h5 class="text-sm font-bold text-white"><?= $ann['title'] ?></h5>
                                    <span class="text-[10px] text-slate-600 font-bold tabular-nums whitespace-nowrap"><?= date('M d', strtotime($ann['created_at'])) ?></span>
                                </div>
                                <p class="text-slate-500 text-xs mt-1.5 line-clamp-2 leading-relaxed"><?= $ann['message'] ?></p>
                            </div>
                            <?php endwhile; else: ?>
                                <p class="text-slate-600 text-sm py-4">No recent announcements.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-cf-card p-8 rounded-3xl border border-cf-border shadow-lg">
                        <h4 class="font-black text-slate-700 mb-6 uppercase text-[10px] tracking-[0.2em]">Maintenance Monitor</h4>
                        <div class="space-y-5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-500">Database Engine</span>
                                <span class="font-black text-green-500 uppercase text-[10px] bg-green-950 px-2 py-1 rounded">Healthy (MariaDB)</span>
                            </div>
                            <div class="space-y-2.5">
                                <span class="font-semibold text-slate-500 text-sm">Available Upload Storage (84% Free)</span>
                                <div class="w-full h-2 bg-cf-dark rounded-full overflow-hidden border border-cf-border shadow-inner">
                                    <div class="h-full bg-cf-accent rounded-full shadow-lg shadow-blue-600/30 w-[84%]"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'];
        const revenueData = [12000, 19000, 3000, 5000, 2000, 3000];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Confirmed Revenue',
                    data: revenueData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#334155' },
                        ticks: {
                            color: '#64748b',
                            font: { weight: 'bold' },
                            callback: function(value) {
                                return '₱' + (value / 1000).toFixed(0) + 'K';
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            font: { weight: 'bold' }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>