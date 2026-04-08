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

    <div id="overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

    <aside id="mobileSidebar" class="fixed inset-y-0 left-0 w-72 bg-slate-950 text-white z-50 transform -translate-x-full transition-transform duration-300 lg:hidden flex flex-col">
        <div class="p-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl text-white">C</div>
                <h1 class="text-xl font-bold">C-Familia</h1>
            </div>
            <button id="closeMenu" class="p-2 text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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

    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-950 text-white hidden lg:flex flex-col sticky top-0 h-screen">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20 text-white">C</div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight">C-Familia</h1>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mt-1">Admin Suite</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-1.5 overflow-y-auto text-sm">
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mb-4 px-4">Main Menu</p>
                
                <a href="admin_dashboard.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_dashboard.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>

                <a href="admin_enrollments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_enrollments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Enrollments
                </a>

                <a href="admin_payments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_payments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Payments
                </a>

                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-8 mb-4 px-4">Communication</p>
                <a href="admin_announcements.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold text-slate-400 hover:text-white hover:bg-white/5 group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    Announcements
                </a>
                <a href="admin_posts.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold text-slate-400 hover:text-white hover:bg-white/5 group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2v4h4"/></svg>
                    Posts
                </a>
            </nav>

            <div class="p-6 border-t border-white/5 bg-slate-900/50">
                <a href="logout.php" class="flex items-center gap-3 py-3 px-4 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </a>
            </div>
        </aside>

        <main class="flex-1 w-full">
            <header class="lg:hidden bg-white border-b border-slate-200 p-4 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white">C</div>
                    <span class="font-bold text-slate-900">Admin Suite</span>
                </div>
                <button id="openMenu" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </header>

            <div class="p-4 md:p-6 lg:p-10 max-w-6xl mx-auto">
                <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-[800] text-slate-900 tracking-tight">Payment Ledger</h2>
                        <p class="text-slate-500 mt-1 text-sm md:text-base">Track collections and verify student transactions.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="bg-white px-6 py-3 rounded-2xl border border-slate-200 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Verified</p>
                            <p class="text-xl font-black text-green-600">₱<?= number_format($total_collected, 2) ?></p>
                        </div>
                    </div>
                </header>

                <div class="bg-white rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h4 class="font-bold text-slate-800 text-sm md:text-base">Recent Transactions</h4>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-black uppercase"><?= $pending_verification ?> Pending</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left min-w-[800px]">
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
                                        <a href="uploads/receipts/<?= $row['receipt'] ?>" target="_blank" class="text-blue-600 font-bold text-xs hover:underline flex items-center gap-1">
                                            View Image
                                        </a>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <?php if($row['status'] == 'pending'): ?>
                                            <a href="?verify=<?= $row['id'] ?>" class="bg-slate-900 text-white px-4 py-1.5 rounded-lg text-[10px] font-bold hover:bg-blue-600 transition shadow-sm">Verify Now</a>
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

    <script>
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            mobileSidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        openBtn.addEventListener('click', toggleMenu);
        closeBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
    </script>
</body>
</html>