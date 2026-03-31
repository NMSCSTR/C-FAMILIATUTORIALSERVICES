<?php
session_start();
include 'db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Personal Data
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

// 3. Fetch Enrollment Data (Checks if they have submitted the form)
$enroll_query = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = '$user_id' AND status != 'completed' LIMIT 1");
$is_enrolled = mysqli_num_rows($enroll_query) > 0;
$enroll = mysqli_fetch_assoc($enroll_query);

// 4. Calculate Financials (Only if enrolled)
$total_paid = 0;
$balance = 0;
$total_fee = $enroll['total_fee'] ?? 0;

if ($is_enrolled) {
    $payment_total_query = mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE user_id = '$user_id' AND status = 'paid'");
    $payment_data = mysqli_fetch_assoc($payment_total_query);
    $total_paid = $payment_data['total'] ?? 0;
    $balance = $total_fee - $total_paid;
}

// 5. Fetch Payment History & Announcements
$payments_query = mysqli_query($conn, "SELECT * FROM payments WHERE user_id = '$user_id' ORDER BY created_at DESC");
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
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-white border-r border-slate-200 hidden lg:flex flex-col sticky top-0 h-screen">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-200">C</div>
                    <span class="font-bold text-slate-800 text-lg tracking-tight">C-Familia</span>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-2">
                <a href="student_dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-blue-100 transition-all">
                    <span>🏠</span> Dashboard
                </a>
                <a href="student_resources.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-2xl font-semibold transition-all group">
                    <span class="group-hover:scale-110 transition">📚</span> My Resources
                </a>
                <a href="student_profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-2xl font-semibold transition-all group">
                    <span class="group-hover:scale-110 transition">👤</span> Profile
                </a>
            </nav>

            <div class="p-6 border-t border-slate-100">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-2xl font-bold transition-all">
                    <span>🚪</span> Logout
                </a>
            </div>
        </aside>

        <main class="flex-1">
            <header class="bg-white/80 backdrop-blur-md border-b border-slate-200 px-8 py-5 flex justify-between items-center sticky top-0 z-30">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Student Dashboard</h2>
                </div>
                
                <div class="flex items-center gap-3 bg-slate-100 p-1.5 pr-4 rounded-2xl">
                    <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=random' ?>" 
                         class="w-9 h-9 rounded-xl object-cover">
                    <span class="text-xs font-bold text-slate-700"><?= explode(' ', $user['name'])[0] ?></span>
                </div>
            </header>

            <div class="p-8 space-y-8">
                
                <?php if (!$is_enrolled): ?>
                    <div class="bg-white rounded-[3rem] p-12 text-center border-2 border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-6">📝</div>
                        <h2 class="text-3xl font-black text-slate-800 mb-4">You're not enrolled yet!</h2>
                        <p class="text-slate-500 max-w-md mx-auto mb-8">To access review materials, track your progress, and join a batch, please complete the enrollment form.</p>
                        <a href="enroll.php" class="inline-block px-10 py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-200">
                            Start Enrollment Now
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-gradient-to-br from-slate-900 to-blue-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden shadow-2xl shadow-blue-100">
                        <div class="relative z-10">
                            <h1 class="text-3xl font-black mb-2">Hello, <?= explode(' ', $user['name'])[0] ?>! 👋</h1>
                            <p class="text-blue-200 opacity-90 max-w-md">Your enrollment for <span class="text-white font-bold"><?= $enroll['program_type'] ?></span> is currently <span class="uppercase"><?= $enroll['status'] ?></span>.</p>
                        </div>
                        <div class="absolute right-[-5%] top-[-10%] w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Total Course Fee</p>
                            <h3 class="text-xl font-bold text-slate-800">₱<?= number_format($total_fee, 2) ?></h3>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Paid Amount</p>
                            <h3 class="text-xl font-bold text-green-600">₱<?= number_format($total_paid, 2) ?></h3>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Balance Due</p>
                            <h3 class="text-xl font-bold text-red-500">₱<?= number_format($balance, 2) ?></h3>
                        </div>
                        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3">Review Batch</p>
                            <h3 class="text-sm font-bold text-blue-600"><?= $enroll['batch'] ?></h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1 space-y-6">
                            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                                    <h3 class="font-bold text-slate-800">Latest News</h3>
                                    <span class="flex h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                                </div>
                                <div class="p-6 space-y-4">
                                    <?php while($ann = mysqli_fetch_assoc($ann_query)): ?>
                                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                                            <h4 class="font-bold text-slate-800 text-sm"><?= $ann['title'] ?></h4>
                                            <p class="text-[11px] text-slate-500 mt-1 line-clamp-2"><?= $ann['message'] ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                                    <h3 class="font-bold text-slate-800">My Payments</h3>
                                    <a href="upload_payment.php" class="text-xs font-black text-blue-600 uppercase tracking-widest">+ Submit Receipt</a>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="bg-slate-50/50">
                                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Date/Ref</th>
                                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Amount</th>
                                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php if(mysqli_num_rows($payments_query) > 0): ?>
                                                <?php while($pay = mysqli_fetch_assoc($payments_query)): ?>
                                                <tr>
                                                    <td class="px-6 py-4">
                                                        <p class="text-xs font-bold text-slate-700"><?= date('M d, Y', strtotime($pay['created_at'])) ?></p>
                                                        <p class="text-[10px] text-slate-400">#<?= $pay['reference_number'] ?? 'N/A' ?></p>
                                                    </td>
                                                    <td class="px-6 py-4 text-xs font-black text-slate-900 italic">
                                                        ₱<?= number_format($pay['amount'], 2) ?>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <?php 
                                                            $st = $pay['status'];
                                                            $cl = ($st == 'paid') ? 'bg-green-100 text-green-700' : (($st == 'pending') ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700');
                                                        ?>
                                                        <span class="px-2 py-1 <?= $cl ?> text-[9px] font-black rounded-lg uppercase"><?= $st ?></span>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr><td colspan="3" class="p-10 text-center text-xs text-slate-400 italic">No transactions found.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>