<?php
require_once __DIR__ . '/lib/session.php';
secure_session_start();
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

$refund_request_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE status = 'refund_requested'"))['total'] ?? 0;

$total_posts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM posts"))['total'] ?? 0;

$current_page = basename($_SERVER['PHP_SELF']);

// Real 6-month revenue series for the dashboard chart
$series = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("first day of -$i months");
    $series[date('Y-m', $ts)] = ['label' => date('M Y', $ts), 'total' => 0.0];
}
$rev_res = mysqli_query(
    $conn,
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, SUM(amount) AS total
     FROM payments
     WHERE status = 'paid' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY ym"
);
if ($rev_res) {
    while ($r = mysqli_fetch_assoc($rev_res)) {
        if (isset($series[$r['ym']])) {
            $series[$r['ym']]['total'] = (float) $r['total'];
        }
    }
}
$chart_labels      = array_column($series, 'label');
$chart_data        = array_map('floatval', array_column($series, 'total'));
$has_revenue_data  = array_sum($chart_data) > 0;

// Real disk usage (falls back to null when the host blocks the call)
$disk_total = @disk_total_space('.');
$disk_free  = @disk_free_space('.');
$disk_pct   = ($disk_total && $disk_free) ? round((($disk_total - $disk_free) / $disk_total) * 100, 1) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = 'Command Center';
$load_charts = true;
    include __DIR__ . '/partials/head.php';
    ?>
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

            <?php if ($refund_request_count > 0): ?>
            <div class="mb-8 bg-rose-500/10 border border-rose-500/30 rounded-3xl p-5 md:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-bold"><?= $refund_request_count ?> Refund Request<?= $refund_request_count > 1 ? 's' : '' ?> Pending</p>
                        <p class="text-rose-200/80 text-sm mt-1">A student has requested a refund on a paid transaction. Review it in the Financial Ledger.</p>
                    </div>
                </div>
                <a href="admin_payments.php" class="inline-flex items-center justify-center bg-rose-500 hover:bg-rose-400 text-white text-[10px] font-black uppercase tracking-widest px-5 py-3 rounded-xl transition-colors shrink-0">
                    Review Refunds
                </a>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-10">
                <a href="admin_enrollments.php" class="block bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group hover:border-cf-accent/60 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-cf-accent/10 text-cf-accent border border-cf-accent/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Total Students</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= number_format($total_students) ?></h3>
                </a>

                <a href="admin_enrollments.php?view=pending" class="block bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group hover:border-cf-accent/60 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-amber-500/10 text-amber-500 border border-amber-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Pending Enrollments</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= $pending_count ?></h3>
                </a>

                <a href="admin_payments.php" class="block bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group hover:border-cf-accent/60 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-green-500/10 text-green-500 border border-green-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Revenue</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= $total_revenue ?></h3>
                </a>

                <a href="admin_posts.php" class="block bg-cf-card p-6 rounded-3xl border border-cf-border shadow-lg group hover:border-cf-accent/60 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 bg-purple-500/10 text-purple-500 border border-purple-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                    </div>
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Resources</p>
                    <h3 class="text-3xl font-[800] text-white mt-1"><?= $total_posts ?></h3>
                </a>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="xl:col-span-2 space-y-8">
                    <div class="bg-cf-card rounded-3xl border border-cf-border shadow-lg overflow-hidden">
                        <div class="p-6 md:p-8 border-b border-cf-border flex flex-col md:flex-row justify-between md:items-center gap-4">
                            <div>
                                <h4 class="font-bold text-lg text-white">Recent Activity</h4>
                                <p class="text-slate-500 text-xs mt-1">Latest user actions across the system.</p>
                            </div>
                            <a href="admin_activity_log.php" class="text-center md:text-left text-cf-accent text-[10px] font-black uppercase tracking-widest bg-cf-accent/10 px-4 py-2.5 rounded-xl">View All</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left table-responsive">
                                <thead class="bg-cf-dark/30 border-b border-cf-border">
                                    <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                        <th class="px-8 py-4">User</th>
                                        <th class="px-8 py-4">Action</th>
                                        <th class="px-8 py-4">Details</th>
                                        <th class="px-8 py-4 text-right">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-cf-border">
                                    <?php
                                    $activity_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'")) > 0;
                                    if ($activity_table_exists):
                                        $recent_activity = mysqli_query($conn, "SELECT al.*, u.firstname, u.lastname, u.email
                                                                                FROM activity_logs al
                                                                                LEFT JOIN users u ON al.user_id = u.id
                                                                                ORDER BY al.created_at DESC
                                                                                LIMIT 5");
                                        while($row = mysqli_fetch_assoc($recent_activity)):
                                            $activity_name = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
                                            if ($activity_name === '' && !empty($row['email'])) {
                                                $activity_name = $row['email'];
                                            } elseif ($activity_name === '') {
                                                $activity_name = 'Guest';
                                            }
                                    ?>
                                    <tr class="hover:bg-cf-dark/40 transition">
                                        <td class="px-8 py-4" data-label="User">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-cf-border flex items-center justify-center font-bold text-xs text-slate-400">
                                                    <?= strtoupper(substr($activity_name, 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-white text-xs"><?= htmlspecialchars($activity_name) ?></p>
                                                    <?php if (!empty($row['user_role'])): ?>
                                                        <p class="text-[9px] uppercase tracking-widest text-slate-500"><?= htmlspecialchars($row['user_role']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-4" data-label="Action">
                                            <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase bg-blue-900/40 text-blue-400">
                                                <?= htmlspecialchars(activity_action_label($row['action'])) ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-4 text-xs text-slate-400 max-w-xs truncate" data-label="Details"><?= htmlspecialchars(activity_log_display_description($conn, $row)) ?></td>
                                        <td class="px-8 py-4 text-[10px] text-slate-500 font-bold text-right" data-label="Date"><?= date('M d, g:i A', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="4" class="px-8 py-8 text-center text-slate-500 text-sm">Run the activity log migration to start tracking user actions.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-cf-card p-6 md:p-8 rounded-3xl border border-cf-border shadow-lg">
                        <h4 class="font-bold text-lg text-white mb-6">Revenue Growth</h4>
                        <?php if (!$has_revenue_data): ?>
                        <p class="text-slate-500 text-sm font-medium">No verified payments in the last 6 months yet. Verified transactions will chart here.</p>
                        <?php else: ?>
                        <div class="h-64 relative">
                            <canvas id="revenueChart"></canvas>
                        </div>
                        <?php endif; ?>
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
                                    <span class="text-slate-400 font-bold">Storage used</span>
                                    <span class="text-slate-500"><?= $disk_pct !== null ? $disk_pct . '%' : 'n/a' ?></span>
                                </div>
                                <?php if ($disk_pct !== null): ?>
                                <div class="w-full h-1.5 bg-cf-dark rounded-full">
                                    <div class="h-full bg-cf-accent rounded-full" style="width: <?= min(100, max(0, $disk_pct)) ?>%"></div>
                                </div>
                                <?php else: ?>
                                <p class="text-[10px] text-slate-600 font-medium">Disk usage is not available on this host.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>

        // Revenue Chart
        document.addEventListener('DOMContentLoaded', function () {
        const ctxEl = document.getElementById('revenueChart');
        if (!ctxEl) return;
        const ctx = ctxEl.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    data: <?= json_encode($chart_data) ?>,
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
        });
    </script>
</body>
</html>