<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'"))['total'];


// --- Logic to Verify Payment ---
if (isset($_GET['verify'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['verify']);
    
    // CHANGED: Use 'paid' to match your ENUM definition
    $update_query = "UPDATE payments SET status = 'paid' WHERE id = '$p_id'";
    
    if (mysqli_query($conn, $update_query)) {
        header("Location: admin_payments.php?success=1");
        exit();
    }
}

// --- Financial Summary ---
// CHANGED: Query SUM where status is 'paid'
$total_query = "SELECT SUM(amount) as total FROM payments WHERE status = 'paid'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_collected = $total_row['total'] ?? 0;

// Pending count stays the same as 'pending' exists in your ENUM
$pending_query = "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'";
$pending_result = mysqli_query($conn, $pending_query);
$pending_row = mysqli_fetch_assoc($pending_result);
$pending_verification = $pending_row['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <title>Financial Ledger | C-Familia Admin</title>
    <style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        letter-spacing: -0.01em;
    }

    .sidebar-link-active {
        background: #2563eb;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
    }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-900">

    <div id="overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity">
    </div>

    <?php include 'aside.php';?>

        <main class="flex-1 w-full">
            <header
                class="lg:hidden bg-white border-b border-slate-200 p-4 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white">C
                    </div>
                    <span class="font-bold text-slate-900">Admin Suite</span>
                </div>
                <button id="openMenu" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </header>

            <div class="p-4 md:p-6 lg:p-10 max-w-6xl mx-auto">
                <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-[800] text-slate-900 tracking-tight">Payment Ledger</h2>
                        <p class="text-slate-500 mt-1 text-sm md:text-base">Track collections and verify student
                            transactions.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="bg-white px-6 py-3 rounded-2xl border border-slate-200 shadow-sm">
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">
                                Total Verified</p>
                            <p class="text-xl font-black text-green-600">₱<?= number_format($total_collected, 2) ?></p>
                        </div>
                    </div>
                </header>

                <div
                    class="bg-white rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h4 class="font-bold text-slate-800 text-sm md:text-base">Recent Transactions</h4>
                        <div class="flex gap-2">
                            <span
                                class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-black uppercase"><?= $pending_verification ?>
                                Pending</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Student</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Ref Number
                                    </th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Amount</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400">Receipt</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase text-slate-400 text-right">
                                        Status</th>
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
                                        <p class="text-[10px] text-slate-400">
                                            <?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                    </td>
                                    <td class="px-8 py-5 font-mono text-xs text-slate-600">
                                        <?= $row['reference_number'] ?></td>
                                    <td class="px-8 py-5 font-bold text-slate-900">
                                        ₱<?= number_format($row['amount'], 2) ?></td>
                                    <td class="px-8 py-5">
                                        <a href="uploads/receipts/<?= $row['receipt'] ?>" target="_blank"
                                            class="text-blue-600 font-bold text-xs hover:underline flex items-center gap-1">
                                            View Image
                                        </a>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <?php if($row['status'] == 'pending'): ?>
                                        <a href="?verify=<?= $row['id'] ?>"
                                            class="bg-slate-900 text-white px-4 py-1.5 rounded-lg text-[10px] font-bold">Verify
                                            Now</a>
                                        <?php elseif($row['status'] == 'paid'): ?>
                                        <span class="text-green-500 font-black text-[10px] uppercase tracking-widest">✓
                                            Paid & Verified</span>
                                        <?php else: ?>
                                        <span
                                            class="text-red-500 font-black text-[10px] uppercase tracking-widest">Failed</span>
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