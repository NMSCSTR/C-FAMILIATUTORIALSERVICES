<?php
session_start();
include 'db.php';

// 1. Security Check: Redirect if not logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Data
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

// 3. Calculate Total Paid (Only counting 'paid' status)
$payment_total_query = mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE user_id = '$user_id' AND status = 'paid'");
$payment_total = mysqli_fetch_assoc($payment_total_query);
$total_paid = $payment_total['total'] ?? 0;

// 4. Fetch Payment History
$payments_query = mysqli_query($conn, "SELECT * FROM payments WHERE user_id = '$user_id' ORDER BY created_at DESC");

// 5. Fetch Announcements
$ann_query = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Student Dashboard | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-white border-r border-slate-200 hidden lg:flex flex-col sticky top-0 h-screen">
            <div class="p-8">
                <a href="index.php" class="flex items-center gap-3">
                    <img src="cuevaslogo.jpg" class="w-10 h-10 rounded-xl shadow-sm" alt="Logo">
                    <span class="font-bold text-blue-700 text-xl tracking-tight">C-Familia</span>
                </a>
            </div>
            
            <nav class="flex-1 px-4 space-y-2">
                <a href="student_dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-blue-200 transition-all">
                    <span class="text-lg">🏠</span> Dashboard
                </a>
                <a href="student_resources.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-blue-600 rounded-2xl font-semibold transition-all group">
                    <span class="text-lg group-hover:scale-110 transition">📚</span> My Resources
                </a>
                <a href="student_profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-blue-600 rounded-2xl font-semibold transition-all group">
                    <span class="text-lg group-hover:scale-110 transition">👤</span> Profile Settings
                </a>
            </nav>

            <div class="p-6 border-t border-slate-100">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-2xl font-bold transition-all">
                    <span>🚪</span> Logout
                </a>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto custom-scrollbar">
            <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 px-8 py-5 flex justify-between items-center sticky top-0 z-30">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Student Portal</h2>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Overview & Progress</p>
                </div>
                
                <div class="flex items-center gap-4 bg-slate-50 p-1.5 pr-4 rounded-2xl border border-slate-100">
                    <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=random' ?>" 
                         class="w-10 h-10 rounded-xl object-cover shadow-sm">
                    <div class="hidden sm:block">
                        <p class="text-sm font-bold text-slate-800 leading-none"><?= $user['name'] ?></p>
                        <p class="text-[10px] text-blue-600 font-black uppercase mt-1">Active Student</p>
                    </div>
                </div>
            </header>

            <div class="p-8 space-y-8">
                <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-blue-200">
                    <div class="relative z-10">
                        <h1 class="text-3xl font-black mb-2">Welcome back, <?= explode(' ', $user['name'])[0] ?>!</h1>
                        <p class="text-blue-100 opacity-80 max-w-md">Your future starts here. Check your resources and stay updated with your payment records.</p>
                    </div>
                    <div class="absolute right-[-10%] top-[-20%] w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm group hover:border-blue-300 transition-all">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Enrolled Since</p>
                        <h3 class="text-lg font-bold text-slate-800"><?= date('M d, Y', strtotime($user['created_at'])) ?></h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm group hover:border-green-300 transition-all">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Total Investment</p>
                        <h3 class="text-2xl font-black text-green-600">₱<?= number_format($total_paid, 2) ?></h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm group hover:border-indigo-300 transition-all">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Files Accessed</p>
                        <h3 class="text-2xl font-black text-indigo-600">12 Available</h3>
                    </div>
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm group hover:border-orange-300 transition-all">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Account Status</p>
                        <span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-600 text-[10px] font-black rounded-lg uppercase">Verified</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800">Board News</h3>
                                <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            </div>
                            <div class="p-6 space-y-4">
                                <?php if(mysqli_num_rows($ann_query) > 0): ?>
                                    <?php while($ann = mysqli_fetch_assoc($ann_query)): ?>
                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-md transition-all cursor-default group">
                                        <h4 class="font-bold text-slate-800 text-sm group-hover:text-blue-600 transition"><?= $ann['title'] ?></h4>
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?= $ann['message'] ?></p>
                                        <p class="text-[9px] font-black text-slate-300 uppercase mt-3"><?= date('F d', strtotime($ann['created_at'])) ?></p>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-center text-slate-400 text-sm italic">No news today.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                            <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                                <div>
                                    <h3 class="font-bold text-lg text-slate-800 tracking-tight">Payment Ledger</h3>
                                    <p class="text-xs text-slate-400 font-medium">History of your financial transactions</p>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-slate-50/50">
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date & Reference</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Method</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if(mysqli_num_rows($payments_query) > 0): ?>
                                            <?php while($pay = mysqli_fetch_assoc($payments_query)): ?>
                                            <tr class="hover:bg-slate-50/30 transition-colors group">
                                                <td class="px-8 py-5">
                                                    <p class="text-sm font-bold text-slate-700">
                                                        <?= $pay['payment_date'] ? date('M d, Y', strtotime($pay['payment_date'])) : date('M d, Y', strtotime($pay['created_at'])) ?>
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 font-medium">REF: <?= $pay['reference_number'] ?? 'WALK-IN' ?></p>
                                                </td>
                                                <td class="px-8 py-5">
                                                    <p class="text-xs font-bold text-slate-600 uppercase tracking-tighter"><?= $pay['payment_method'] ?? 'Cash' ?></p>
                                                    <p class="text-[9px] text-blue-500 font-black uppercase"><?= $pay['payment_type'] ?></p>
                                                </td>
                                                <td class="px-8 py-5">
                                                    <p class="text-sm font-black text-slate-900 italic">₱<?= number_format($pay['amount'], 2) ?></p>
                                                </td>
                                                <td class="px-8 py-5">
                                                    <?php 
                                                        $colors = [
                                                            'paid' => 'bg-green-100 text-green-700',
                                                            'pending' => 'bg-amber-100 text-amber-700',
                                                            'failed' => 'bg-red-100 text-red-700'
                                                        ];
                                                        $st = $pay['status'];
                                                    ?>
                                                    <span class="px-3 py-1.5 <?= $colors[$st] ?> text-[9px] font-black rounded-lg uppercase tracking-tighter">
                                                        <?= $st ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="px-8 py-16 text-center">
                                                    <div class="bg-slate-50 w-16 h-16 rounded-3xl flex items-center justify-center mx-auto mb-4 text-2xl">💳</div>
                                                    <p class="text-slate-400 text-sm font-medium italic">You haven't made any payments yet.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>