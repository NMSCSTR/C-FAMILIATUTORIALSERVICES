<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- Financial Summary ---
$total_collected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'verified'"))['total'] ?? 0;
$pending_verification = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'"))['count'];

// Logic to Verify Payment
if (isset($_GET['verify'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['verify']);
    mysqli_query($conn, "UPDATE payments SET status = 'verified' WHERE id = '$p_id'");
    header("Location: admin_payments.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Financial Ledger | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .sidebar-link-active { background: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-950 text-white hidden lg:flex flex-col sticky top-0 h-screen">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20">C</div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight">C-Familia</h1>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mt-1">Admin Suite</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-1.5 overflow-y-auto text-sm">
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mb-4 px-4">Main Menu</p>
                <a href="admin_dashboard.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_dashboard.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Dashboard</a>
                <a href="admin_enrollments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_enrollments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Enrollments</a>
                <a href="admin_payments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_payments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Payments</a>
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-8 mb-4 px-4">Communication</p>
                <a href="admin_announcements.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group text-slate-400 hover:text-white hover:bg-white/5">Announcements</a>
                <a href="admin_posts.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group text-slate-400 hover:text-white hover:bg-white/5">Posts</a>
            </nav>

            <div class="p-6 border-t border-white/5">
                <a href="logout.php" class="flex items-center gap-3 py-3.5 px-4 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-sm">Sign Out</a>
            </div>
        </aside>

        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-6xl mx-auto">
                <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    <div>
                        <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Payment Ledger</h2>
                        <p class="text-slate-500 mt-1">Track collections and verify student transactions.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="bg-white px-6 py-3 rounded-2xl border border-slate-200 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Verified</p>
                            <p class="text-xl font-black text-green-600">₱<?= number_format($total_collected, 2) ?></p>
                        </div>
                    </div>
                </header>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h4 class="font-bold text-slate-800">Recent Transactions</h4>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-black uppercase"><?= $pending_verification ?> Pending</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Student</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Ref Number</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Amount</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Receipt</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <?php
                                $sql = "SELECT payments.*, users.name FROM payments 
                                        JOIN users ON payments.user_id = users.id 
                                        ORDER BY payments.created_at DESC";
                                $res = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($res)):
                                ?>
                                <tr class="hover:bg-slate-50/50 transition group">
                                    <td class="px-8 py-5">
                                        <p class="font-bold text-slate-900"><?= $row['name'] ?></p>
                                        <p class="text-[10px] text-slate-400"><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                    </td>
                                    <td class="px-8 py-5 font-mono text-xs text-slate-600"><?= $row['reference_number'] ?></td>
                                    <td class="px-8 py-5 font-bold text-slate-900">₱<?= number_format($row['amount'], 2) ?></td>
                                    <td class="px-8 py-5">
                                        <a href="uploads/<?= $row['receipt'] ?>" target="_blank" class="text-blue-600 font-bold text-xs hover:underline flex items-center gap-1">
                                            View Image
                                        </a>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <?php if($row['status'] == 'pending'): ?>
                                            <a href="?verify=<?= $row['id'] ?>" class="bg-slate-900 text-white px-4 py-1.5 rounded-lg text-[10px] font-bold hover:bg-blue-600 transition">Verify Now</a>
                                        <?php else: ?>
                                            <span class="text-green-500 font-black text-[10px] uppercase tracking-widest">✓ Verified</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>