<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!$conn) {
    die("Database connection failed.");
}

// Stats Queries
$student_query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$total_students = mysqli_fetch_assoc(mysqli_query($conn, $student_query))['total'] ?? 0;

$revenue_query = "SELECT SUM(amount) as confirmed_total FROM payments WHERE status = 'paid'";
$revenue_data = mysqli_fetch_assoc(mysqli_query($conn, $revenue_query));
$total_revenue_raw = $revenue_data['confirmed_total'] ?? 0;

if ($total_revenue_raw >= 1000) {
    $total_revenue = '₱' . number_format($total_revenue_raw / 1000, 1) . 'K';
} else {
    $total_revenue = '₱' . number_format($total_revenue_raw, 0);
}

$pending_query = "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'";
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, $pending_query))['total'] ?? 0;

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
    <title>Command Center | C-Familia Admin</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        
        @media (max-width: 768px) {
            .mobile-table-card thead { display: none; }
            .mobile-table-card tr { display: block; border-bottom: 1px solid #334155; padding: 1rem 0; }
            .mobile-table-card td { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1rem; border: none; }
            .mobile-table-card td::before { content: attr(data-label); font-weight: 700; font-size: 10px; color: #64748b; text-transform: uppercase; }
        }
    </style>
</head>

<body class="bg-cf-dark text-slate-100 antialiased">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php';?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 w-full p-4 md:p-10">
            <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10">
                <div class="flex items-center justify-between lg:block">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-[800] text-white tracking-tight">Command Center</h2>
                        <div class="hidden md:flex items-center gap-2.5 mt-2.5 bg-cf-card/50 border border-cf-border py-1.5 px-4 rounded-full inline-flex">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Live System Active</p>
                        </div>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-cf-card border border-cf-border rounded-2xl text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-4 bg-cf-card p-2.5 pr-5 rounded-2xl border border-cf-border shadow-xl self-start lg:self-auto w-full lg:w-auto">
                    <div class="w-11 h-11 bg-cf-accent/20 border border-cf-accent/40 rounded-xl flex items-center justify-center font-bold text-cf-accent">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 lg:flex-none">
                        <p class="text-sm font-bold text-white"><?= $_SESSION['username'] ?></p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Administrator</p>
                    </div>
                    <a href="logout.php" class="p-2 text-slate-500 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                    </a>
                </div>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-10">
                <div class="bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-cf-accent/10 text-cf-accent border border-cf-accent/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Total Students</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= number_format($total_students) ?></h3>
                </div>

                <div class="bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-amber-500/10 text-amber-500 border border-amber-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Queue</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= $pending_count ?></h3>
                </div>

                <div class="bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-green-500/10 text-green-500 border border-green-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Revenue</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= $total_revenue ?></h3>
                </div>

                <div class="bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-purple-500/10 text-purple-500 border border-purple-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Resources</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= $total_posts ?></h3>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="xl:col-span-2 space-y-8">
                    <div class="bg-cf-card rounded-3xl border border-cf-border shadow-lg overflow-hidden">
                        <div class="p-6 md:p-8 border-b border-cf-border flex flex-col md:flex-row justify-between md:items-center gap-4">
                            <div>
                                <h4 class="font-bold text-lg text-white">Recent Activity</h4>
                                <p class="text-slate-500 text-xs mt-1">Latest enrollment applications.</p>
                            </div>
                            <a href="admin_enrollments.php" class="text-center md:text-left text-cf-accent text-[10px] font-black uppercase tracking-widest bg-cf-accent/10 px-4 py-2.5 rounded-xl">Manage All</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left mobile-table-card">
                                <thead class="bg-cf-dark/30 border-b border-cf-border">
                                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                        <th class="px-8 py-4">Student</th>
                                        <th class="px-8 py-4">Batch</th>
                                        <th class="px-8 py-4">Status</th>
                                        <th class="px-8 py-4 text-right">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-cf-border">
                                    <?php
                                    $recent_enroll = mysqli_query($conn, "SELECT e.*, u.firstname, u.lastname, u.profile_pic FROM enrollments e JOIN users u ON e.user_id = u.id ORDER BY e.created_at DESC LIMIT 5");
                                    while($row = mysqli_fetch_assoc($recent_enroll)):
                                    ?>
                                    <tr class="hover:bg-cf-dark/40 transition">
                                        <td class="px-8 py-4" data-label="Student">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-cf-border flex items-center justify-center font-bold text-xs text-slate-400">
                                                    <?= substr($row['firstname'], 0, 1) ?>
                                                </div>
                                                <p class="font-bold text-white text-xs"><?= $row['firstname'] ?> <?= $row['lastname'] ?></p>
                                            </div>
                                        </td>
                                        <td class="px-8 py-4 text-xs text-slate-400" data-label="Batch"><?= $row['batch'] ?></td>
                                        <td class="px-8 py-4" data-label="Status">
                                            <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase <?= $row['status'] == 'pending' ? 'bg-amber-900/40 text-amber-500' : 'bg-green-900/40 text-green-500' ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-4 text-[10px] text-slate-500 font-bold text-right" data-label="Applied"><?= date('M d', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-cf-card p-6 md:p-8 rounded-3xl border border-cf-border shadow-lg">
                        <h4 class="font-bold text-lg text-white mb-6">Revenue Growth</h4>
                        <div class="h-64 relative">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-cf-accent p-8 rounded-3xl shadow-xl shadow-blue-600/10 relative overflow-hidden group">
                        <div class="relative z-10 text-white">
                            <h4 class="font-black text-xl leading-tight">Post New<br>Announcement</h4>
                            <p class="text-blue-100 text-xs mt-3 opacity-80">Broadcast updates to all reviewees instantly.</p>
                            <a href="admin_announcements.php" class="inline-block mt-6 px-6 py-3 bg-white text-cf-accent rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg">Create Now</a>
                        </div>
                        <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform"></div>
                    </div>

                    <div class="bg-cf-card p-6 rounded-3xl border border-cf-border">
                        <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6">System Health</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-400">Database</span>
                                <span class="text-[9px] font-black bg-green-500/10 text-green-500 px-2 py-1 rounded uppercase tracking-tighter">Connected</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span class="text-slate-400 font-bold">Storage</span>
                                    <span class="text-slate-500">84%</span>
                                </div>
                                <div class="w-full h-1.5 bg-cf-dark rounded-full">
                                    <div class="h-full bg-cf-accent w-[84%] rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar Mobile Logic
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar(state) {
            if(state) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        openBtn.addEventListener('click', () => toggleSidebar(true));
        closeBtn.addEventListener('click', () => toggleSidebar(false));
        overlay.addEventListener('click', () => toggleSidebar(false));

        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['W1', 'W2', 'W3', 'W4', 'W5'],
                datasets: [{
                    data: [12000, 19000, 15000, 22000, 18000],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b' } },
                    y: { grid: { color: '#334155' }, ticks: { color: '#64748b' } }
                }
            }
        });
    </script>
</body>
</html>