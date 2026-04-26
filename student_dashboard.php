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

// 3. Fetch Enrollment Data
$enroll_query = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = '$user_id' AND status != 'completed' LIMIT 1");
$is_enrolled = mysqli_num_rows($enroll_query) > 0;
$enroll = mysqli_fetch_assoc($enroll_query);

// 4. Calculate Financials
$total_paid = 0;
$balance = 0;
$total_fee = $enroll['total_fee'] ?? 0;

if ($is_enrolled) {
    $payment_total_query = mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE user_id = '$user_id' AND status = 'paid'");
    $payment_data = mysqli_fetch_assoc($payment_total_query);
    $total_paid = $payment_data['total'] ?? 0;
    $balance = $total_fee - $total_paid;
}

// 5. Fetch Data for UI
$payments_query = mysqli_query($conn, "SELECT * FROM payments WHERE user_id = '$user_id' ORDER BY created_at DESC");
$ann_query = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Student Portal | C-Familia</title>
    <style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        letter-spacing: -0.01em;
    }

    .sidebar-link-active {
        background-color: #f1f5f9;
        color: #4f46e5;
        border-left: 4px solid #4f46e5;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        transition: all 0.3s ease;
    }
    </style>
</head>

<body class="bg-[#fcfcfd] text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-white border-r border-slate-100 hidden lg:flex flex-col sticky top-0 h-screen z-50">
            <div class="p-8 pb-12 border-b border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-100"><img src="cuevaslogo.jpg" alt=""></div>
                    <span class="font-extrabold text-slate-950 text-xl tracking-tight">C-Familia</span>
                </div>
            </div>

            <nav class="flex-1 pt-8 px-4 space-y-2">
                <a href="student_dashboard.php" class="flex items-center gap-3.5 px-6 py-4 rounded-xl font-bold transition-all sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span>Dashboard</span>
                </a>
                <a href="student_resources.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-600 hover:bg-slate-50 rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    <span>Resources</span>
                </a>
                <a href="student_profile.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-600 hover:bg-slate-50 rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    <span>Account</span>
                </a>
            </nav>

            <div class="p-6 border-t border-slate-100">
                <button onclick="confirmLogout()" class="w-full flex items-center gap-3 px-6 py-4 text-red-500 hover:bg-red-50 rounded-xl font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Logout
                </button>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <header class="bg-white/95 backdrop-blur-sm border-b border-slate-100 px-10 py-6 flex justify-between items-center sticky top-0 z-40">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Portal Home</h2>
                <div class="flex items-center gap-4">
                    <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['firstname'].' '.$user['lastname']).'&background=4f46e5&color=fff' ?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-50">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block"><?= $user['firstname'] ?> <?= $user['middlename'] ?> <?= $user['lastname'] ?></span>
                        <span class="text-[10px] font-semibold text-indigo-600">Active Student</span>
                    </div>
                </div>
            </header>

            <div class="p-10 max-w-[1500px] mx-auto space-y-10">

                <?php if (!$is_enrolled): ?>
                <div class="bg-white rounded-3xl p-16 text-center border border-slate-100 shadow-lg shadow-slate-100/50">
                    <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-8 font-black">?</div>
                    <h2 class="text-4xl font-extrabold text-slate-950 mb-4 tracking-tight">Enrollment Required</h2>
                    <p class="text-slate-500 max-w-lg mx-auto mb-12 text-lg leading-relaxed">You do not have an active enrollment record. To access learning materials and track your batch progress, please complete the enrollment form.</p>
                    <a href="enroll.php" class="inline-flex items-center gap-3 px-10 py-5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-slate-950 transition-all group">
                        <span>Submit Enrollment</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>
                <?php else: ?>
                
                <div class="bg-white rounded-3xl p-10 border border-slate-100 shadow-lg shadow-slate-100/50 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-black uppercase text-indigo-600 tracking-widest mb-1">C-Familia Portal</p>
                            <h1 class="text-4xl font-extrabold mb-1 tracking-tight text-slate-950">Welcome, <?= explode(' ', $user['firstname'])[0] ?>!</h1>
                            <div class="flex flex-wrap items-center gap-3 mt-3">
                                <span class="text-sm font-semibold text-slate-600 flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                                    <span class="text-indigo-500">📚</span> <?= $enroll['program_type'] ?>
                                </span>
                                <span class="text-sm font-semibold text-slate-600 flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                                    <span class="text-indigo-500">📍</span> <?= $enroll['enrolled_at'] ?: 'Not Specified' ?>
                                </span>
                                <?php if($enroll['insured'] == 1): ?>
                                    <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg border border-emerald-100 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zM9 10a1 1 0 011-1h3a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                                        Insured
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] font-black uppercase tracking-wider bg-slate-50 text-slate-400 px-3 py-1.5 rounded-lg border border-slate-100">
                                        No Insurance
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="px-8 py-4 bg-indigo-50 rounded-2xl border border-indigo-100 text-center min-w-[140px]">
                            <span class="text-[10px] font-extrabold text-indigo-800 uppercase tracking-widest block mb-1">Status</span>
                            <span class="text-sm font-black uppercase text-indigo-700 tracking-tighter"><?= $enroll['status'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-7 rounded-3xl border border-slate-100 shadow-lg shadow-slate-100/50 stat-card flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-black">₱</div>
                        <div>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Fee</p>
                            <h3 class="text-2xl font-extrabold text-slate-950"><?= number_format($total_fee, 2) ?></h3>
                        </div>
                    </div>
                    <div class="bg-white p-7 rounded-3xl border border-slate-100 shadow-lg shadow-slate-100/50 stat-card flex items-center gap-5 hover:border-emerald-100">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-black">✓</div>
                        <div>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Paid Amount</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600"><?= number_format($total_paid, 2) ?></h3>
                        </div>
                    </div>
                    <div class="bg-white p-7 rounded-3xl border border-slate-100 shadow-lg shadow-slate-100/50 stat-card flex items-center gap-5 hover:border-rose-100">
                        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl font-black">!</div>
                        <div>
                            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Balance Due</p>
                            <h3 class="text-2xl font-extrabold text-rose-500"><?= number_format($balance, 2) ?></h3>
                        </div>
                    </div>
                    <div class="bg-indigo-600 p-7 rounded-3xl border border-indigo-600 shadow-lg shadow-indigo-100 stat-card flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 text-indigo-100 flex items-center justify-center text-xl font-black">#</div>
                        <div>
                            <p class="text-indigo-200 text-[10px] font-black uppercase tracking-widest">Review Batch</p>
                            <h3 class="text-xl font-extrabold text-white leading-tight"><?= $enroll['batch'] ?></h3>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-3xl border border-slate-100 shadow-lg shadow-slate-100/50">
                            <div class="px-8 py-6 border-b border-slate-100">
                                <h3 class="font-extrabold text-slate-950 text-sm tracking-tight uppercase">Latest News</h3>
                            </div>
                            <div class="p-8 space-y-6">
                                <?php if(mysqli_num_rows($ann_query) > 0): ?>
                                <?php while($ann = mysqli_fetch_assoc($ann_query)): ?>
                                <div class="space-y-2">
                                    <span class="text-[10px] font-extrabold text-indigo-600 uppercase"><?= date('F d, Y', strtotime($ann['created_at'])) ?></span>
                                    <h4 class="font-bold text-slate-900 leading-snug"><?= $ann['title'] ?></h4>
                                    <p class="text-xs text-slate-500 line-clamp-2"><?= $ann['message'] ?></p>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <p class="text-center py-4 text-xs font-bold text-slate-400 italic">No announcements.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl border border-slate-100 shadow-lg shadow-slate-100/50">
                            <div class="px-10 py-6 border-b border-slate-100 flex justify-between items-center">
                                <h3 class="font-extrabold text-slate-950 text-sm tracking-tight uppercase">My Payments</h3>
                                <a href="upload_payment.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-lg text-xs transition-colors hover:bg-slate-950">+ Submit Receipt</a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr>
                                            <th class="px-10 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Date / Ref</th>
                                            <th class="px-10 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Amount</th>
                                            <th class="px-10 py-5 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if(mysqli_num_rows($payments_query) > 0): ?>
                                        <?php while($pay = mysqli_fetch_assoc($payments_query)): ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-10 py-6">
                                                <p class="text-xs font-bold text-slate-900"><?= date('M d, Y', strtotime($pay['created_at'])) ?></p>
                                                <p class="text-[10px] text-slate-400 mt-1 uppercase">Ref: <?= $pay['reference_number'] ?: 'N/A' ?></p>
                                            </td>
                                            <td class="px-10 py-6 font-extrabold text-slate-950 text-sm">₱<?= number_format($pay['amount'], 2) ?></td>
                                            <td class="px-10 py-6 text-right">
                                                <?php 
                                                    $st = $pay['status'];
                                                    $cl = ($st == 'paid') ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : (($st == 'pending') ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-rose-50 text-rose-700 border-rose-100');
                                                ?>
                                                <span class="inline-block px-3 py-1.5 <?= $cl ?> text-[9px] font-extrabold rounded-lg border uppercase tracking-wider"><?= $st ?></span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php else: ?>
                                        <tr><td colspan="3" class="p-20 text-center text-xs text-slate-300 font-extrabold italic">No payment history.</td></tr>
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

    <script>
    function confirmLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be signed out of your account.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel',
            customClass: {
                title: 'font-extrabold text-slate-900',
                confirmButton: 'rounded-xl font-bold px-6 py-3',
                cancelButton: 'rounded-xl font-bold px-6 py-3 text-slate-600'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        })
    }
    </script>
</body>
</html>