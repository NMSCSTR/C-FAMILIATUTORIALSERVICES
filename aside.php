<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set current page for sidebar highlighting
$current_page = 'admin_payments.php';

// 2. Handle Verification Logic
if (isset($_GET['verify'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['verify']);
    
    // Find associated user
    $find_user = mysqli_query($conn, "SELECT user_id FROM payments WHERE id = '$p_id'");
    $payment_row = mysqli_fetch_assoc($find_user);
    
    if ($payment_row) {
        $u_id = $payment_row['user_id'];
        
        // Update payment to paid
        mysqli_query($conn, "UPDATE payments SET status = 'paid' WHERE id = '$p_id'");
        
        // Update enrollment to enrolled
        mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE user_id = '$u_id' AND status = 'pending'");
        
        header("Location: admin_payments.php?success=1");
        exit();
    }
}

// 3. Fetch Statistics
$total_collected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'paid'"))['total'] ?? 0;
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM payments WHERE status = 'pending'"))['total'] ?? 0;
$total_transactions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM payments"))['total'] ?? 0;
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
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900">

    <div class="flex min-h-screen">
        <div id="overlay" class="fixed inset-0 bg-slate-950/50 z-40 hidden lg:hidden"></div>

        <?php include 'aside.php'; ?>

        <main class="flex-1 min-w-0 overflow-hidden">
            
            <div class="lg:hidden bg-white border-b border-slate-200 p-4 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-black text-white">C</div>
                    <span class="font-bold tracking-tight">C-Familia</span>
                </div>
                <button id="openMenu" class="p-2 text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                </button>
            </div>

            <div class="p-6 lg:p-10 max-w-[1600px] mx-auto">
                
                <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    <div>
                        <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Payments Ledger</h2>
                        <p class="text-slate-500 mt-1">Verify student transactions and track school revenue.</p>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                        <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 animate-fade-in">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Payment Verified Successfully
                        </div>
                    <?php endif; ?>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm relative overflow-hidden group">
                        <div class="relative z-10">
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.1em] mb-2">Total Collected</p>
                            <h3 class="text-3xl font-black text-slate-900 leading-none">₱<?= number_format($total_collected, 2) ?></h3>
                        </div>
                        <div class="absolute -right-4 -bottom-4 text-slate-50 opacity-10 group-hover:scale-110 transition-transform">
                             <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.24-1.87-1.5 0-2.22.61-2.22 1.5 0 .93.8 1.43 2.87 1.96 2.83.73 3.98 1.93 3.98 3.79 0 1.9-1.38 3.13-3.28 3.47z"/></svg>
                        </div>
                    </div>

                    <div class="bg-blue-600 p-6 rounded-[2rem] shadow-xl shadow-blue-500/20 text-white relative overflow-hidden">
                        <p class="text-blue-100 text-[10px] font-black uppercase tracking-[0.1em] mb-2">Pending Tasks</p>
                        <h3 class="text-3xl font-black leading-none"><?= $pending_count ?> <span class="text-sm font-normal text-blue-100">to verify</span></h3>
                    </div>

                    <div class="bg-slate-950 p-6 rounded-[2rem] text-white">
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.1em] mb-2">Platform Status</p>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <h3 class="text-lg font-bold">Systems Active</h3>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-50 flex items-center justify-between bg-white">
                        <h3 class="font-bold text-slate-800 text-lg">Recent Transactions</h3>
                        <div class="flex gap-2">
                            <button class="p-2 hover:bg-slate-50 rounded-lg text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Student Info</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Details</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Amount</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Receipt</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php
                                $sql = "SELECT payments.*, users.name, users.email 
                                        FROM payments 
                                        JOIN users ON payments.user_id = users.id 
                                        ORDER BY payments.created_at DESC";
                                $res = mysqli_query($conn, $sql);

                                if(mysqli_num_rows($res) > 0):
                                    while($row = mysqli_fetch_assoc($res)):
                                ?>
                                <tr class="hover:bg-slate-50/30 transition-all">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center font-bold">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 leading-tight"><?= $row['name'] ?></p>
                                                <p class="text-[10px] text-slate-400 mt-0.5"><?= $row['email'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="font-mono text-[11px] text-slate-600 bg-slate-100 px-2 py-1 rounded inline-block">
                                            <?= $row['reference_number'] ?>
                                        </p>
                                        <p class="text-[9px] text-slate-400 mt-1 uppercase font-bold"><?= $row['payment_method'] ?></p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="font-black text-slate-900">₱<?= number_format($row['amount'], 2) ?></p>
                                        <p class="text-[9px] text-slate-400 uppercase"><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                    </td>
                                    <td class="px-8 py-6">
                                        <?php if($row['receipt']): ?>
                                            <a href="uploads/receipts/<?= $row['receipt'] ?>" target="_blank" 
                                               class="text-blue-600 hover:text-blue-800 font-bold text-[10px] uppercase tracking-wider flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Preview
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-300 italic text-[10px]">No File</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <?php if($row['status'] == 'pending'): ?>
                                            <a href="admin_payments.php?verify=<?= $row['id'] ?>" 
                                               onclick="return confirm('Confirm payment verification? Student will be automatically enrolled.')"
                                               class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-lg shadow-blue-500/20">
                                                Verify
                                            </a>
                                        <?php else: ?>
                                            <div class="inline-flex items-center gap-2 text-emerald-500">
                                                <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></div>
                                                <span class="font-black text-[10px] uppercase tracking-widest">Completed</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center text-slate-400 italic">No payments found.</td>
                                </tr>
                                <?php endif; ?>
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

        if(openBtn) openBtn.addEventListener('click', toggleMenu);
        if(closeBtn) closeBtn.addEventListener('click', toggleMenu);
        if(overlay) overlay.addEventListener('click', toggleMenu);
    </script>
</body>
</html>