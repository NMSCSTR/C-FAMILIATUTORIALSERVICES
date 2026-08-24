<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
include 'db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Personal Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: logout.php");
    exit();
}

// 3. Fetch Enrollment Data
$enroll_stmt = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND status != 'completed' LIMIT 1");
$enroll_stmt->bind_param("i", $user_id);
$enroll_stmt->execute();
$enroll_result = $enroll_stmt->get_result();
$is_enrolled = $enroll_result->num_rows > 0;
$enroll = $enroll_result->fetch_assoc();

// 4. Calculate Financials
$total_paid = 0;
$balance = 0;
$total_fee = $enroll['total_fee'] ?? 0;

if ($is_enrolled) {
    $pay_stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status = 'paid'");
    $pay_stmt->bind_param("i", $user_id);
    $pay_stmt->execute();
    $total_paid = (float) ($pay_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $balance = $total_fee - $total_paid;
}

if(isset($_POST['submit_testimonial'])) {
    csrf_verify();

    $content = trim($_POST['testimonial_content'] ?? '');

    if ($content !== '' && mb_strlen($content) <= 2000) {
        $stmt = $conn->prepare("INSERT INTO testimonials (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        if ($stmt->execute()) {
            log_activity($conn, 'testimonial.submit', null, [
                'entity_type' => 'testimonial',
                'entity_id' => $stmt->insert_id,
            ]);
            $msg = "Thank you for your testimonial!";
        }
    }
}

if (isset($_POST['payment_action']) && $_POST['payment_action'] === 'request_refund') {
    csrf_verify();

    $payment_id = (int) ($_POST['payment_id'] ?? 0);
    $check = $conn->prepare("SELECT id, amount FROM payments WHERE id = ? AND user_id = ? AND status = 'paid' LIMIT 1");
    $check->bind_param("ii", $payment_id, $user_id);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();

    if ($row) {
        $update = $conn->prepare("UPDATE payments SET status = 'refund_requested' WHERE id = ? AND user_id = ?");
        $update->bind_param("ii", $payment_id, $user_id);
        if ($update->execute()) {
            log_activity($conn, 'payment.refund_request', null, [
                'entity_type' => 'payment',
                'entity_id' => $payment_id,
                'amount' => $row['amount'],
            ]);
            header("Location: student_dashboard.php?success=refund_requested");
            exit();
        }
    }
    header("Location: student_dashboard.php?error=refund_failed");
    exit();
}

if (isset($_POST['payment_action']) && $_POST['payment_action'] === 'cancel_payment') {
    csrf_verify();

    $payment_id = (int) ($_POST['payment_id'] ?? 0);
    $check = $conn->prepare("SELECT id FROM payments WHERE id = ? AND user_id = ? AND status = 'pending' LIMIT 1");
    $check->bind_param("ii", $payment_id, $user_id);
    $check->execute();

    if ($check->get_result()->fetch_assoc()) {
        $update = $conn->prepare("UPDATE payments SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $update->bind_param("ii", $payment_id, $user_id);
        if ($update->execute()) {
            log_activity($conn, 'payment.cancel', null, [
                'entity_type' => 'payment',
                'entity_id' => $payment_id,
            ]);
            header("Location: student_dashboard.php?success=payment_cancelled");
            exit();
        }
    }
    header("Location: student_dashboard.php?error=cancel_failed");
    exit();
}

// 5. Fetch Data for UI
$payments_stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC");
$payments_stmt->bind_param("i", $user_id);
$payments_stmt->execute();
$payments_query = $payments_stmt->get_result();

$ann_query = mysqli_query($conn, "SELECT * FROM announcements WHERE audience = 'Students' ORDER BY created_at DESC LIMIT 3");

// 6. Fetch Grade Performance Matrix
$grades_stmt = $conn->prepare("SELECT * FROM exam_result WHERE user_id = ? LIMIT 1");
$grades_stmt->bind_param("i", $user_id);
$grades_stmt->execute();
$grades = $grades_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Student Portal | C-Familia</title>
    <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; background-color: #020617; }
    .sidebar-link-active { background: linear-gradient(90deg, #2563eb, #4f46e5); color: #ffffff; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; }
    .stat-card { transition: transform 0.3s ease, border-color 0.3s ease; }
    .stat-card:hover { transform: translateY(-3px); border-color: #334155; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>

<body class="bg-slate-950 text-white antialiased">

    <div class="flex min-h-screen relative overflow-x-hidden">
        
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity duration-300 dynamic-overlay"></div>

        <aside id="sidebarMenu" class="w-72 bg-slate-900/40 border-r border-slate-800/80 flex flex-col fixed inset-y-0 left-0 -translate-x-full lg:translate-x-0 lg:static h-screen z-50 transition-transform duration-300 ease-in-out backdrop-blur-2xl">
            <div class="p-8 pb-12 border-b border-slate-800/60 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-500/20">
                        C
                    </div>
                    <span class="font-extrabold text-white text-xl tracking-tight">C-Familia</span>
                </div>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-xl bg-slate-800/60 border border-slate-700/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 pt-8 px-4 space-y-2 overflow-y-auto custom-scrollbar text-sm">
                <a href="student_dashboard.php" class="flex items-center gap-3.5 px-6 py-4 rounded-xl font-bold transition-all sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="student_resources.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-400 hover:text-white hover:bg-slate-800/40 border border-transparent rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Resources</span>
                </a>
                <a href="student_profile.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-400 hover:text-white hover:bg-slate-800/40 border border-transparent rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Account</span>
                </a>
            </nav>

            <div class="p-6 border-t border-slate-800/80">
                <button onclick="confirmLogout()" class="w-full flex items-center gap-3 px-6 py-4 text-red-400 hover:bg-red-500/10 rounded-xl font-bold transition-all border border-transparent hover:border-red-500/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </aside>

        <main class="flex-1 min-w-0 w-full">
            <header class="bg-slate-900/40 backdrop-blur-md border-b border-slate-800/80 px-6 sm:px-10 py-6 flex justify-between items-center sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800/60 transition-colors" aria-label="Toggle Navigation Side Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Portal Home</h2>
                </div>

                <div class="flex items-center gap-4">
                    <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.htmlspecialchars($user['profile_pic'], ENT_QUOTES, 'UTF-8') : 'https://ui-avatars.com/api/?name='.urlencode($user['firstname'].' '.$user['lastname']).'&background=2563eb&color=fff' ?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-blue-900/40">
                    <div class="hidden sm:block">
                        <span class="text-xs font-bold text-white block"><?= htmlspecialchars(trim($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-[10px] font-semibold text-blue-400">Active Student</span>
                    </div>
                </div>
            </header>

            <div class="p-4 sm:p-10 max-w-[1500px] mx-auto space-y-10">

                <?php if (!$is_enrolled): ?>
                <div class="bg-slate-900/60 rounded-3xl p-8 sm:p-16 text-center border border-slate-800 backdrop-blur-xl">
                    <div class="w-20 h-20 bg-slate-950 text-blue-400 border border-slate-800 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-8 font-black">?</div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4 tracking-tight">Enrollment Required</h2>
                    <p class="text-slate-400 max-w-lg mx-auto mb-12 text-base sm:text-lg leading-relaxed">You do not have an active enrollment record. To access learning materials and track your batch progress, please complete the enrollment form.</p>
                    <a href="enroll.php" class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-xl shadow-blue-950/30 transition-all group">
                        <span>Submit Enrollment</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
                <?php ?>
                <?php else: ?>

                <div class="bg-slate-900/60 rounded-3xl p-6 sm:p-10 border border-slate-800 backdrop-blur-xl flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                    <div class="space-y-3 w-full lg:w-auto">
                        <div>
                            <p class="text-[10px] font-black uppercase text-blue-400 tracking-widest mb-1">C-Familia Portal</p>
                            <h1 class="text-3xl sm:text-4xl font-extrabold mb-1 tracking-tight text-white">Welcome, <?= htmlspecialchars(explode(' ', (string) $user['firstname'])[0], ENT_QUOTES, 'UTF-8') ?>!</h1>
                            <div class="flex flex-wrap items-center gap-3 mt-3">
                                <span class="text-sm font-semibold text-slate-300 flex items-center gap-2 bg-slate-950 border border-slate-800 px-3 py-1.5 rounded-lg">
                                    <span class="text-blue-400">📚</span> <?= htmlspecialchars((string) $enroll['program_type'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="text-sm font-semibold text-slate-300 flex items-center gap-2 bg-slate-950 border border-slate-800 px-3 py-1.5 rounded-lg">
                                    <span class="text-blue-400">📍</span> <?= htmlspecialchars($enroll['enrolled_at'] ?: 'Not Specified', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <?php if($enroll['insured'] == 1): ?>
                                <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-950/20 text-emerald-400 px-3 py-1.5 rounded-lg border border-emerald-900/30 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zM9 10a1 1 0 011-1h3a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Insured
                                </span>
                                <?php else: ?>
                                <span class="text-[10px] font-black uppercase tracking-wider bg-slate-950 text-slate-500 px-3 py-1.5 rounded-lg border border-slate-800/80">No Insurance</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 w-full lg:w-auto">
                        <div class="px-8 py-4 bg-blue-950/20 rounded-2xl border border-blue-900/30 text-center w-full lg:min-w-[140px]">
                            <span class="text-[10px] font-extrabold text-blue-400 uppercase tracking-widest block mb-1">Status</span>
                            <span class="text-sm font-black uppercase text-blue-300 tracking-tighter"><?= htmlspecialchars((string) $enroll['status'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($grades && (!is_null($grades['diagnostic_exam']) || !is_null($grades['preboard_exam']) || !is_null($grades['compre_exam']))): ?>
                <div class="space-y-4">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-2">Academic Performance Reports</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <?php if(!is_null($grades['diagnostic_exam'])): ?>
                        <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800 stat-card flex items-center justify-between backdrop-blur-xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-950/30 text-blue-400 flex items-center justify-center font-bold text-lg border border-blue-900/20">📊</div>
                                <div>
                                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Diagnostic Exam</p>
                                    <h3 class="text-2xl font-extrabold text-white mt-0.5"><?= $grades['diagnostic_exam'] ?>%</h3>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!is_null($grades['preboard_exam'])): ?>
                        <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800 stat-card flex items-center justify-between backdrop-blur-xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-amber-950/30 text-amber-400 flex items-center justify-center font-bold text-lg border border-amber-900/20">4️⃣</div>
                                <div>
                                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Preboard Exam</p>
                                    <h3 class="text-2xl font-extrabold text-white mt-0.5"><?= $grades['preboard_exam'] ?>%</h3>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!is_null($grades['compre_exam'])): ?>
                        <div class="bg-slate-900/60 p-6 rounded-3xl border border-slate-800 stat-card flex items-center justify-between backdrop-blur-xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-purple-950/30 text-purple-400 flex items-center justify-center font-bold text-lg border border-purple-900/20">🎓</div>
                                <div>
                                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Comprehensive Exam</p>
                                    <h3 class="text-2xl font-extrabold text-white mt-0.5"><?= $grades['compre_exam'] ?>%</h3>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-slate-900/60 p-7 rounded-3xl border border-slate-800 stat-card flex items-center gap-5 backdrop-blur-xl">
                        <div class="w-14 h-14 rounded-2xl bg-slate-950 text-indigo-400 border border-slate-800 flex items-center justify-center text-2xl font-black">₱</div>
                        <div>
                            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Total Fee</p>
                            <h3 class="text-2xl font-extrabold text-white"><?= number_format($total_fee, 2) ?></h3>
                        </div>
                    </div>
                    <div class="bg-slate-900/60 p-7 rounded-3xl border border-slate-800 stat-card flex items-center gap-5 backdrop-blur-xl">
                        <div class="w-14 h-14 rounded-2xl bg-slate-950 text-emerald-400 border border-slate-800 flex items-center justify-center text-2xl font-black">✓</div>
                        <div>
                            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Paid Amount</p>
                            <h3 class="text-2xl font-extrabold text-emerald-400"><?= number_format($total_paid, 2) ?></h3>
                        </div>
                    </div>
                    <div class="bg-slate-900/60 p-7 rounded-3xl border border-slate-800 stat-card flex items-center gap-5 backdrop-blur-xl">
                        <div class="w-14 h-14 rounded-2xl bg-slate-950 text-rose-400 border border-slate-800 flex items-center justify-center text-2xl font-black">!</div>
                        <div>
                            <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Balance Due</p>
                            <h3 class="text-2xl font-extrabold text-rose-400"><?= number_format($balance, 2) ?></h3>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-7 rounded-3xl border border-transparent stat-card flex items-center gap-5 shadow-xl shadow-blue-950/20">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 text-indigo-100 flex items-center justify-center text-xl font-black">#</div>
                        <div>
                            <p class="text-indigo-200 text-[10px] font-black uppercase tracking-widest">Review Batch</p>
                            <h3 class="text-xl font-extrabold text-white leading-tight"><?= htmlspecialchars((string) $enroll['batch'], ENT_QUOTES, 'UTF-8') ?></h3>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900/60 p-6 sm:p-8 rounded-[2rem] border border-slate-800 backdrop-blur-xl">
                    <h3 class="text-xl font-bold mb-4 text-white">Share Your Experience</h3>
                    <p class="text-slate-400 text-sm mb-6">Your testimonial will be featured on our landing page.</p>
                    <form action="" method="POST">
                        <?= csrf_field() ?>
                        <textarea name="testimonial_content" required class="w-full p-4 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-sm min-h-[120px] text-white placeholder:text-slate-600" placeholder="How was your experience with C-Familia?"></textarea>
                        <button type="submit" name="submit_testimonial" class="mt-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-950/40 transition">Submit Review</button>
                    </form>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-slate-900/60 rounded-3xl border border-slate-800 backdrop-blur-xl">
                            <div class="px-8 py-6 border-b border-slate-800/60">
                                <h3 class="font-extrabold text-white text-sm tracking-tight uppercase">Latest News</h3>
                            </div>
                            <div class="p-8 space-y-6">
                                <?php if(mysqli_num_rows($ann_query) > 0): ?>
                                <?php while($ann = mysqli_fetch_assoc($ann_query)): ?>
                                <div class="space-y-2 border-b border-slate-800/40 pb-4 last:border-0 last:pb-0">
                                    <span class="text-[10px] font-extrabold text-blue-400 uppercase"><?= date('F d, Y', strtotime($ann['created_at'])) ?></span>
                                    <h4 class="font-bold text-white leading-snug"><?= htmlspecialchars($ann['title'], ENT_QUOTES, 'UTF-8') ?></h4>
                                    <p class="text-xs text-slate-400 line-clamp-2 mt-1 leading-relaxed"><?= htmlspecialchars($ann['message'], ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <p class="text-center py-4 text-xs font-bold text-slate-500 italic">No announcements.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="bg-slate-900/60 rounded-3xl border border-slate-800 backdrop-blur-xl">
                            <div class="px-6 sm:px-10 py-6 border-b border-slate-800/60 flex flex-col sm:flex-row gap-4 justify-between sm:items-center">
                                <h3 class="font-extrabold text-white text-sm tracking-tight uppercase">My Payments</h3>
                                <a href="upload_payment.php" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg text-xs hover:from-blue-700 hover:to-indigo-700 transition-colors shadow-lg shadow-blue-950/20">+ Submit Receipt</a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left min-w-[500px]">
                                    <thead>
                                        <tr class="text-[10px] font-black uppercase text-slate-500 tracking-widest border-b border-slate-800/60 bg-slate-950/20">
                                            <th class="px-6 sm:px-10 py-5">Date / Ref</th>
                                            <th class="px-6 sm:px-10 py-5">Amount</th>
                                            <th class="px-6 sm:px-10 py-5 text-right">Status</th>
                                            <th class="px-6 sm:px-10 py-5 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/40">
                                        <?php if(mysqli_num_rows($payments_query) > 0): ?>
                                        <?php while($pay = mysqli_fetch_assoc($payments_query)): ?>
                                        <tr class="hover:bg-slate-900/20 transition-colors">
                                            <td class="px-6 sm:px-10 py-6">
                                                <p class="text-xs font-bold text-white"><?= date('M d, Y', strtotime($pay['created_at'])) ?></p>
                                                <p class="text-[10px] text-slate-500 mt-1 uppercase">Ref: <?= htmlspecialchars($pay['reference_number'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
                                            </td>
                                            <td class="px-6 sm:px-10 py-6 font-extrabold text-white text-sm">₱<?= number_format($pay['amount'], 2) ?></td>
                                            <td class="px-6 sm:px-10 py-6 text-right">
                                                <?php 
                                                    $st = $pay['status'];
                                                    $cl = match ($st) {
                                                        'paid' => 'bg-emerald-950/20 text-emerald-400 border-emerald-900/30',
                                                        'pending' => 'bg-amber-950/20 text-amber-400 border-amber-900/30',
                                                        'refund_requested' => 'bg-rose-950/20 text-rose-400 border-rose-900/30',
                                                        'refunded' => 'bg-slate-950 text-slate-500 border-slate-800',
                                                        'cancelled' => 'bg-slate-950 text-slate-600 border-slate-800/60',
                                                        default => 'bg-rose-950/20 text-rose-400 border-rose-900/30',
                                                    };
                                                ?>
                                                <span class="inline-block px-3 py-1.5 <?= $cl ?> text-[9px] font-extrabold rounded-lg border uppercase tracking-wider"><?= str_replace('_', ' ', $st) ?></span>
                                            </td>
                                            <td class="px-6 sm:px-10 py-6 text-right">
                                                <?php if ($st === 'paid'): ?>
                                                <form method="POST" class="inline" id="refund-form-<?= (int) $pay['id'] ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="payment_id" value="<?= (int) $pay['id'] ?>">
                                                    <input type="hidden" name="payment_action" value="request_refund">
                                                    <button type="button" onclick="confirmRefundRequest(<?= (int) $pay['id'] ?>)" class="text-[10px] font-bold uppercase tracking-wider text-rose-400 hover:text-rose-300">Request Refund</button>
                                                </form>
                                                <?php elseif ($st === 'pending'): ?>
                                                <form method="POST" class="inline" id="cancel-form-<?= (int) $pay['id'] ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="payment_id" value="<?= (int) $pay['id'] ?>">
                                                    <input type="hidden" name="payment_action" value="cancel_payment">
                                                    <button type="button" onclick="confirmCancelPayment(<?= (int) $pay['id'] ?>)" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-slate-400">Cancel</button>
                                                </form>
                                                <?php else: ?>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="p-20 text-center text-xs text-slate-500 font-extrabold italic">No payment history.</td>
                                        </tr>
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
    // Mixin custom configuration for dark theme alert notifications
    const customSwalMixin = Swal.mixin({
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#2563eb'
    });

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }

    function confirmLogout() {
        customSwalMixin.fire({
            title: 'Are you sure?',
            text: "You will be signed out of your account.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#1e293b',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel',
            customClass: {
                title: 'font-extrabold text-white',
                confirmButton: 'rounded-xl font-bold px-6 py-3 text-sm',
                cancelButton: 'rounded-xl font-bold px-6 py-3 text-sm text-slate-400'
            }
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'logout.php';
        });
    }

    function confirmRefundRequest(paymentId) {
        customSwalMixin.fire({
            title: 'Request Refund?',
            text: 'An admin will review your refund request.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#1e293b',
            confirmButtonText: 'Submit Request',
            cancelButtonText: 'Keep Payment',
            customClass: {
                title: 'font-extrabold text-white',
                confirmButton: 'rounded-xl font-bold px-6 py-3 text-sm',
                cancelButton: 'rounded-xl font-bold px-6 py-3 text-sm text-slate-400'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('refund-form-' + paymentId).submit();
            }
        });
    }

    function confirmCancelPayment(paymentId) {
        customSwalMixin.fire({
            title: 'Cancel Payment?',
            text: 'This pending payment submission will be cancelled.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4b5563',
            cancelButtonColor: '#1e293b',
            confirmButtonText: 'Yes, Cancel',
            cancelButtonText: 'Keep',
            customClass: {
                title: 'font-extrabold text-white',
                confirmButton: 'rounded-xl font-bold px-6 py-3 text-sm',
                cancelButton: 'rounded-xl font-bold px-6 py-3 text-sm text-slate-400'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancel-form-' + paymentId).submit();
            }
        });
    }

    <?php if (isset($_GET['success'])): ?>
    customSwalMixin.fire({
        icon: 'success',
        title: <?= json_encode(
            $_GET['success'] === 'refund_requested' ? 'Refund Request Submitted' :
            ($_GET['success'] === 'payment_cancelled' ? 'Payment Cancelled' : 'Success')
        ) ?>,
        timer: 2000,
        showConfirmButton: false
    });
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    customSwalMixin.fire({
        icon: 'error',
        title: <?= json_encode(
            $_GET['error'] === 'refund_failed' ? 'Refund Request Failed' :
            ($_GET['error'] === 'cancel_failed' ? 'Cancellation Failed' : 'Something went wrong')
        ) ?>,
        text: 'Please try again or contact the admin.',
        confirmButtonColor: '#2563eb'
    });
    <?php endif; ?>
    </script>
</body>
</html>