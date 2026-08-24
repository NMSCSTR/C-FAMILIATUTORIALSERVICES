<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
    header("Location: logout.php");
    exit();
}

$enrollee_name = $user_data['firstname'] . ' ' . ($user_data['middlename'] ? $user_data['middlename'] . ' ' : '') . $user_data['lastname'];

// 2. Configuration for Dynamic Programs & Fees
$programs = [
    "Criminology Review" => ["fee" => 13500.00, "desc" => "Comprehensive CLE board preparation.", "icon" => "👮"]
];

$message = "";
$error = "";

// 3. Form Submission Logic
if (isset($_POST['submit_enrollment'])) {
    csrf_verify();

    $program = trim($_POST['program_type'] ?? '');
    $batch = trim($_POST['batch'] ?? '');
    $location = trim($_POST['enrolled_at'] ?? '');

    if (!isset($programs[$program])) {
        $error = "Please select a valid program.";
    } elseif ($batch === '' || mb_strlen($batch) > 50) {
        $error = "Please select a valid batch.";
    } elseif ($location === '' || mb_strlen($location) > 100) {
        $error = "Please select a valid review location.";
    } else {
        $fee = $programs[$program]['fee'];

        $check = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND program_type = ? AND status != 'completed'");
        $check->bind_param("is", $user_id, $program);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "You already have an active application for the $program.";
        } else {
            $insert = $conn->prepare("INSERT INTO enrollments (user_id, program_type, batch, total_fee, status, enrollment_date, enrolled_at)
                    VALUES (?, ?, ?, ?, 'pending', CURDATE(), ?)");
            $insert->bind_param("issds", $user_id, $program, $batch, $fee, $location);

            if ($insert->execute()) {
                $enrollment_id = $insert->insert_id;
                log_activity($conn, 'enrollment.submit', null, [
                    'entity_type' => 'enrollment',
                    'entity_id' => $enrollment_id,
                ]);
                $message = "Your application for <b>" . htmlspecialchars($program, ENT_QUOTES, 'UTF-8') . "</b> has been submitted successfully.";
            } else {
                error_log('Enrollment failed: ' . $insert->error);
                $error = "System error. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Secure Enrollment | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #020617; }
        .glass { background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(24px); border-bottom: 1px solid #1e293b; }
        .glass-card { background: rgba(15, 23, 42, 0.6); border: 1px solid #1e293b; backdrop-filter: blur(24px); }
    </style>
</head>
<body class="text-white min-h-screen antialiased">

    <nav class="glass sticky top-0 z-50 px-8 py-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-extrabold shadow-lg shadow-blue-500/20">C</div>
                <h1 class="font-extrabold text-white text-lg tracking-tight">C-Familia Portal</h1>
            </div>
            <a href="student_dashboard.php" class="text-sm font-bold text-slate-400 hover:text-white transition flex items-center gap-2">
                <span>🏠</span> Dashboard
            </a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="grid lg:grid-cols-12 gap-12 items-start">
            
            <div class="lg:col-span-5 space-y-8">
                <div>
                    <span class="px-4 py-2 bg-blue-950/40 border border-blue-900/30 text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest">Enrolling for <?= date('Y') ?></span>
                    <h2 class="text-5xl font-extrabold text-white leading-tight mt-4 tracking-tighter">Start Your <br><span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Review.</span></h2>
                    <p class="text-slate-400 mt-6 text-lg leading-relaxed">Secure your slot today. Select your field of expertise and preferred schedule below.</p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center font-bold shadow-xl shadow-blue-500/20">1</div>
                        <div>
                            <p class="font-bold text-white">Registration</p>
                            <p class="text-xs text-slate-500 font-medium">Choose program & batch</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 opacity-40">
                        <div class="w-12 h-12 bg-slate-900 text-slate-500 border border-slate-800 rounded-2xl flex items-center justify-center font-bold">2</div>
                        <div>
                            <p class="font-bold text-slate-400">Verification</p>
                            <p class="text-xs text-slate-600 font-medium">Administrator approval</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="glass-card rounded-[2.5rem] p-8 md:p-12 relative overflow-hidden">
                    
                    <?php if($error): ?>
                        <div role="alert" class="mb-8 p-4 bg-rose-950/20 text-rose-400 rounded-2xl border border-rose-900/30 flex items-center gap-3 text-sm font-bold animate-pulse">
                            <span>🚫</span> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if($message): ?>
                        <div class="text-center py-10">
                            <div class="w-24 h-24 bg-emerald-950/20 text-emerald-400 rounded-[2.5rem] border border-emerald-900/30 flex items-center justify-center mx-auto mb-6 text-4xl shadow-inner">✓</div>
                            <h3 class="text-3xl font-black text-white mb-4">Application Sent!</h3>
                            <p class="text-slate-400 leading-relaxed mb-10"><?= $message ?></p>
                            <a href="student_dashboard.php" class="block w-full py-5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black rounded-2xl transition-all text-xs tracking-widest uppercase text-center shadow-lg shadow-blue-950/40">
                                Back to Dashboard
                            </a>
                        </div>
                    <?php else: ?>

                        <form action="" method="POST" class="space-y-8">
                            <?= csrf_field() ?>
                            
                            <div class="flex items-center gap-4 p-5 bg-slate-950/40 border border-slate-800 rounded-[1.5rem] mb-6">
                                <div class="w-12 h-12 bg-blue-950/40 border border-blue-900/30 rounded-xl flex items-center justify-center text-blue-400 text-xl shadow-lg">
                                    👤
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-400/70">Enrolling As</p>
                                    <h4 class="text-lg font-bold text-white"><?= htmlspecialchars($enrollee_name) ?></h4>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-5 ml-1">Select Program</label>
                                <div class="grid gap-4">
                                    <?php foreach($programs as $name => $details): ?>
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="program_type" value="<?= $name ?>" required class="peer hidden" onchange="updateFee('<?= $details['fee'] ?>')">
                                        <div class="flex items-center p-5 rounded-2xl border-2 border-slate-800 bg-slate-950/20 transition-all duration-300 group-hover:border-slate-700 peer-checked:border-blue-600 peer-checked:bg-blue-950/20">
                                            <div class="w-12 h-12 bg-slate-900 border border-slate-800 rounded-xl shadow-sm flex items-center justify-center text-2xl mr-4 group-hover:rotate-12 transition">
                                                <?= $details['icon'] ?>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-extrabold text-white"><?= $name ?></p>
                                                <p class="text-[11px] text-slate-500 font-medium"><?= $details['desc'] ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-black text-blue-400">₱<?= number_format($details['fee'], 0) ?></p>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4 ml-1" for="enrolled_at">Review Location</label>
                                    <div class="relative">
                                        <select id="enrolled_at" name="enrolled_at" required class="w-full p-5 rounded-2xl border border-slate-800 bg-slate-950 text-white focus:border-blue-500 outline-none transition font-bold appearance-none">
                                            <option value="" disabled selected class="text-slate-600">Select Location</option>
                                            <option value="Tubod">Tubod, Lanao Del Norte</option>
                                            <option value="Oroqueta">Oroqueta City</option>
                                            <option value="Ozamis">Ozamis City</option>
                                            <option value="Iligan">Iligan City</option>
                                        </select>
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500 text-xs">▼</div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4 ml-1" for="batch">Schedule Batch</label>
                                    <div class="relative">
                                        <select id="batch" name="batch" required class="w-full p-5 rounded-2xl border border-slate-800 bg-slate-950 text-white focus:border-blue-500 outline-none transition font-bold appearance-none">
                                            <option value="" disabled selected class="text-slate-600">Select a Batch</option>
                                            <option value="Batch January <?= date('Y') ?>">Batch January <?= date('Y') ?></option>
                                            <option value="Batch August <?= date('Y') ?>">Batch August <?= date('Y') ?></option>
                                        </select>
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500 text-xs">▼</div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-950 border border-slate-800 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-inner">
                                <div class="relative z-10 flex justify-between items-center">
                                    <div>
                                        <p class="text-blue-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Fee</p>
                                        <h4 id="displayFee" class="text-4xl font-black tracking-tighter transition-all duration-300">₱0.00</h4>
                                    </div>
                                    <div class="text-4xl opacity-20">2️⃣</div>
                                </div>
                                <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-600 rounded-full blur-[80px] opacity-10"></div>
                            </div>

                            <button type="submit" name="submit_enrollment" class="w-full py-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black rounded-2xl transition-all shadow-xl shadow-blue-950/50 hover:-translate-y-1 active:translate-y-0 text-sm tracking-widest uppercase">
                                Confirm Enrollment
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateFee(amount) {
            const display = document.getElementById('displayFee');
            display.innerText = '₱' + parseFloat(amount).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            display.classList.add('scale-105', 'text-blue-400');
            setTimeout(() => {
                display.classList.remove('scale-105', 'text-blue-400');
            }, 300);
        }
    </script>
</body>
</html>