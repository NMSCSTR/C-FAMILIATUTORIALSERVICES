<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

// Helper for Sidebar Active State
$current_page = basename($_SERVER['PHP_SELF']);

// Logic to Approve Enrollment
if (isset($_GET['approve'])) {
    $id = mysqli_real_escape_string($conn, $_GET['approve']);
    mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE id = '$id'");
    header("Location: admin_enrollments.php?success=1");
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
    <title>Manage Enrollments | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .sidebar-link-active { background: #2563eb; color: white; shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-950 text-white hidden lg:flex flex-col sticky top-0 h-screen">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20 text-white">C</div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight">C-Familia</h1>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest leading-none mt-1">Admin Suite</p>
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

                <a href="admin_announcements.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_announcements.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    Announcements
                </a>

                <a href="admin_posts.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_posts.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
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

        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-6xl mx-auto">
                <header class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Enrollment Requests</h2>
                        <p class="text-slate-500 mt-1">Review and process student applications.</p>
                    </div>
                    <?php if(isset($_GET['success'])): ?>
                        <div class="bg-green-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg shadow-green-500/20 animate-bounce">
                            ✓ Request Approved Successfully
                        </div>
                    <?php endif; ?>
                </header>

                <div class="bg-white p-4 rounded-[1.5rem] shadow-sm border border-slate-200 mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="relative w-full md:w-96">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" placeholder="Search by name or email..." class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition text-sm">
                    </div>
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button class="px-4 py-1.5 bg-white shadow-sm text-slate-900 rounded-lg text-xs font-bold">Pending</button>
                        <button class="px-4 py-1.5 text-slate-500 rounded-lg text-xs font-bold hover:text-slate-900">History</button>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Student Info</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Program Details</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Payment Status</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php
                                $sql = "SELECT enrollments.*, users.name, users.email FROM enrollments 
                                        JOIN users ON enrollments.user_id = users.id 
                                        WHERE status = 'pending' ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                
                                if(mysqli_num_rows($result) > 0):
                                    while($row = mysqli_fetch_assoc($result)):
                                        // Fetch payment info for this enrollment
                                        $e_id = $row['id'];
                                        $p_res = mysqli_query($conn, "SELECT * FROM payments WHERE id = '$e_id' LIMIT 1");
                                        $payment = mysqli_fetch_assoc($p_res);
                                ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center font-bold text-sm">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-sm"><?= $row['name'] ?></p>
                                                <p class="text-xs text-slate-400 mt-0.5"><?= $row['email'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="bg-slate-100 inline-block px-2 py-1 rounded text-[10px] font-bold text-slate-600 mb-1"><?= $row['batch'] ?></div>
                                        <p class="text-sm font-semibold text-slate-700"><?= $row['program_type'] ?></p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php if($payment): ?>
                                            <div class="space-y-1">
                                                <a href="uploads/<?= $payment['receipt'] ?>" target="_blank" class="text-[10px] font-black text-blue-600 hover:text-blue-700 uppercase flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    View Receipt
                                                </a>
                                                <p class="text-xs font-bold text-slate-800">₱<?= number_format($payment['amount'], 2) ?></p>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-slate-400 italic px-2 py-1 border border-dashed border-slate-200 rounded">No proof yet</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <a href="?approve=<?= $row['id'] ?>" 
                                           onclick="return confirm('Confirm enrollment for <?= addslashes($row['name']) ?>?')"
                                           class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-blue-700 hover:-translate-y-0.5 transition-all shadow-md shadow-blue-500/10">
                                            Approve
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-32 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mb-6 text-slate-200">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            </div>
                                            <h5 class="text-lg font-bold text-slate-800">Inbox Zero!</h5>
                                            <p class="text-slate-400 text-sm max-w-xs mt-1">There are currently no students waiting for approval. You're all caught up!</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>