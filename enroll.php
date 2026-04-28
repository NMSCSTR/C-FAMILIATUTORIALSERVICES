<?php
session_start();
include 'db.php';

// 1. Security & Identity Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user's full name from database (Updated to match your 3-column name structure)
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$enrollee_name = $user_data['firstname'] . ' ' . ($user_data['middlename'] ? $user_data['middlename'] . ' ' : '') . $user_data['lastname'];

// 2. Configuration for Dynamic Programs & Fees
$programs = [
    "Criminology Review" => ["fee" => 5500.00, "desc" => "Comprehensive CLE board preparation.", "icon" => "👮"],
    "LET Review"         => ["fee" => 4500.00, "desc" => "Professional Education and Gen Ed focus.", "icon" => "👨‍🏫"],
    "Civil Service Review" => ["fee" => 3500.00, "desc" => "Intensive prep for Professional level.", "icon" => "🏛️"]
];

$message = "";
$error = "";

// 3. Form Submission Logic
if (isset($_POST['submit_enrollment'])) {
    $program = mysqli_real_escape_string($conn, $_POST['program_type']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $location = mysqli_real_escape_string($conn, $_POST['enrolled_at']);
    // $insured = isset($_POST['insured']) ? 1 : 0; // Map checkbox to tinyint(1)
    
    // Dynamically assign fee based on the selected program
    $fee = isset($programs[$program]) ? $programs[$program]['fee'] : 5000.00;

    // Prevent duplicate active applications
    $check = mysqli_query($conn, "SELECT id FROM enrollments WHERE user_id = '$user_id' AND program_type = '$program' AND status != 'completed'");
    
    if (mysqli_num_rows($check) > 0) {
        $error = "You already have an active application for the $program.";
    } else {
        // Updated SQL to include insured and enrolled_at (location) columns
        $sql = "INSERT INTO enrollments (user_id, program_type, batch, total_fee, status, enrollment_date, enrolled_at) 
                VALUES ('$user_id', '$program', '$batch', '$fee', 'pending', CURDATE(), '$location')";

        if (mysqli_query($conn, $sql)) {
            $message = "Your application for <b>$program</b> has been submitted successfully.";
        } else {
            $error = "System Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Secure Enrollment | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="glass border-b border-white sticky top-0 z-50 px-8 py-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-extrabold shadow-lg shadow-blue-200">C</div>
                <h1 class="font-extrabold text-slate-800 text-lg tracking-tight">C-Familia Portal</h1>
            </div>
            <a href="student_dashboard.php" class="text-sm font-bold text-slate-500 hover:text-blue-600 transition flex items-center gap-2">
                <span>🏠</span> Dashboard
            </a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-12">
        <div class="grid lg:grid-cols-12 gap-12 items-start">
            
            <div class="lg:col-span-5 space-y-8">
                <div>
                    <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-[10px] font-black uppercase tracking-widest">Enrolling for 2026</span>
                    <h2 class="text-5xl font-extrabold text-slate-900 leading-tight mt-4 tracking-tighter">Start Your <br><span class="text-blue-600">Review.</span></h2>
                    <p class="text-slate-500 mt-6 text-lg leading-relaxed">Secure your slot today. Select your field of expertise and preferred schedule below.</p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-bold shadow-xl shadow-blue-100">1</div>
                        <div>
                            <p class="font-bold text-slate-800">Registration</p>
                            <p class="text-xs text-slate-400 font-medium">Choose program & batch</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 opacity-40">
                        <div class="w-12 h-12 bg-white text-slate-400 border border-slate-200 rounded-2xl flex items-center justify-center font-bold">2</div>
                        <div>
                            <p class="font-bold text-slate-800">Verification</p>
                            <p class="text-xs text-slate-400 font-medium">Administrator approval</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/5 border border-white p-8 md:p-12 relative overflow-hidden">
                    
                    <?php if($error): ?>
                        <div class="mb-8 p-4 bg-red-50 text-red-600 rounded-2xl border border-red-100 flex items-center gap-3 text-sm font-bold animate-pulse">
                            <span>🚫</span> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if($message): ?>
                        <div class="text-center py-10">
                            <div class="w-24 h-24 bg-green-50 text-green-500 rounded-[2.5rem] flex items-center justify-center mx-auto mb-6 text-4xl shadow-inner italic">✓</div>
                            <h3 class="text-3xl font-black text-slate-800 mb-4">Application Sent!</h3>
                            <p class="text-slate-500 leading-relaxed mb-10"><?= $message ?></p>
                            <a href="student_dashboard.php" class="block w-full py-5 bg-slate-900 text-white font-black rounded-2xl hover:bg-slate-800 transition-all text-xs tracking-widest uppercase">
                                Back to Dashboard
                            </a>
                        </div>
                    <?php else: ?>

                        <form action="" method="POST" class="space-y-8">
                            
                            <div class="flex items-center gap-4 p-5 bg-blue-50/50 border border-blue-100 rounded-[1.5rem] mb-6">
                                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg shadow-blue-200">
                                    👤
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-600/70">Enrolling As</p>
                                    <h4 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($enrollee_name) ?></h4>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-5 ml-1">Select Program</label>
                                <div class="grid gap-4">
                                    <?php foreach($programs as $name => $details): ?>
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="program_type" value="<?= $name ?>" required class="peer hidden" onchange="updateFee('<?= $details['fee'] ?>')">
                                        <div class="flex items-center p-5 rounded-2xl border-2 border-slate-100 transition-all duration-300 group-hover:border-blue-200 peer-checked:border-blue-600 peer-checked:bg-blue-50/50">
                                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-2xl mr-4 group-hover:rotate-12 transition">
                                                <?= $details['icon'] ?>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-extrabold text-slate-800"><?= $name ?></p>
                                                <p class="text-[11px] text-slate-400 font-medium"><?= $details['desc'] ?></p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-black text-blue-600">₱<?= number_format($details['fee'], 0) ?></p>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-1">Review Location</label>
                                    <div class="relative">
                                        <select name="enrolled_at" required class="w-full p-5 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:border-blue-600 outline-none transition font-bold text-slate-700 appearance-none">
                                            <option value="" disabled selected>Select Location</option>
                                            <option value="Tubod">Tubod, Lanao Del Norte</option>
                                            <option value="Oroqueta">Oroqueta City</option>
                                            <option value="Ozamis">Ozamis City</option>
                                            <option value="Iligan">Iligan City</option>
                                        </select>
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">▼</div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-1">Schedule Batch</label>
                                    <div class="relative">
                                        <select name="batch" required class="w-full p-5 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:border-blue-600 outline-none transition font-bold text-slate-700 appearance-none">
                                            <option value="" disabled selected>Select a Batch</option>
                                            <option value="Batch 2026-A (Morning Session)">Batch 2026-A (Morning)</option>
                                            <option value="Batch 2026-B (Afternoon Session)">Batch 2026-B (Afternoon)</option>
                                            <option value="Intensive Weekend (Sat-Sun)">Intensive Weekend</option>
                                        </select>
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">▼</div>
                                    </div>
                                </div>
                            </div>

                            <!-- <label class="flex items-center gap-4 p-5 bg-slate-50 border-2 border-slate-100 rounded-2xl cursor-pointer hover:bg-slate-100 transition group">
                                <input type="checkbox" name="insured" class="w-6 h-6 rounded-lg border-2 border-slate-300 text-blue-600 focus:ring-blue-500">
                                <div class="flex-1">
                                    <p class="text-sm font-extrabold text-slate-800">Add Student Insurance</p>
                                    <p class="text-[11px] text-slate-400 font-medium italic">Recommended for off-site review sessions</p>
                                </div>
                                <span class="text-2xl group-hover:animate-bounce">🛡️</span>
                            </label> -->

                            <div class="bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-blue-900/20">
                                <div class="relative z-10 flex justify-between items-center">
                                    <div>
                                        <p class="text-blue-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Fee</p>
                                        <h4 id="displayFee" class="text-4xl font-black tracking-tighter transition-all duration-300">₱0.00</h4>
                                    </div>
                                    <div class="text-4xl opacity-40">📝</div>
                                </div>
                                <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-600 rounded-full blur-[80px] opacity-20"></div>
                            </div>

                            <button type="submit" name="submit_enrollment" class="w-full py-6 bg-blue-600 text-white font-black rounded-2xl hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 hover:-translate-y-1 active:translate-y-0 text-sm tracking-widest uppercase">
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
            
            display.classList.add('scale-110', 'text-blue-400');
            setTimeout(() => {
                display.classList.remove('scale-110', 'text-blue-400');
            }, 300);
        }
    </script>
</body>
</html>