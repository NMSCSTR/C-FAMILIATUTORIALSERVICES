<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Configuration for Dynamic Programs & Fees
$programs = [
    "Criminology Review" => ["fee" => 5500.00, "desc" => "Comprehensive CLE board preparation."],
    "LET Review" => ["fee" => 4500.00, "desc" => "Professional Education and Gen Ed focus."],
    "Civil Service Review" => ["fee" => 3500.00, "desc" => "Intensive prep for Professional/Sub-prof level."]
];

$message = "";
$error = "";

if (isset($_POST['submit_enrollment'])) {
    $user_id = $_SESSION['user_id'];
    $program = mysqli_real_escape_string($conn, $_POST['program_type']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    
    // Dynamically assign fee based on the selected program
    $fee = isset($programs[$program]) ? $programs[$program]['fee'] : 5000.00;

    $check = mysqli_query($conn, "SELECT id FROM enrollments WHERE user_id = '$user_id' AND program_type = '$program' AND status != 'completed'");
    
    if (mysqli_num_rows($check) > 0) {
        $error = "You already have an active application for the $program.";
    } else {
        $sql = "INSERT INTO enrollments (user_id, program_type, batch, total_fee, status, enrollment_date) 
                VALUES ('$user_id', '$program', '$batch', '$fee', 'pending', CURDATE())";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Success! Your application for <b>$program</b> has been submitted.";
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Secure Enrollment | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); }
    </style>
</head>
<body class="gradient-bg min-h-screen pb-20">

    <nav class="bg-white/70 backdrop-blur-md border-b border-white sticky top-0 z-50 px-6 py-4">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">C</div>
                <a href="student_dashboard.php" class="font-extrabold text-slate-800 text-xl tracking-tight">C-Familia Tutorial Services</a>
            </div>
            <div class="flex items-center gap-4 bg-white/50 p-1 pr-4 rounded-full border border-slate-200">
                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold uppercase">
                    <?= substr($_SESSION['username'], 0, 1) ?>
                </div>
                <span class="text-slate-600 text-xs font-bold hidden md:block"><?= $_SESSION['username'] ?></span>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 mt-12">
        <div class="grid lg:grid-cols-5 gap-10">
            
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6">
                    <h2 class="text-4xl font-extrabold text-slate-900 leading-tight">Start Your <br><span class="text-blue-600">Journey.</span></h2>
                    <p class="text-slate-500 mt-4 leading-relaxed">Complete the form to register. Our administrators will review your data within 24 hours.</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-white/60 rounded-2xl border border-white">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Select Program</p>
                            <p class="text-xs text-slate-500">Choose your review path</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 opacity-50">
                        <div class="w-10 h-10 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center font-bold">2</div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Payment</p>
                            <p class="text-xs text-slate-500">Upload your receipt</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="bg-white rounded-[2rem] shadow-2xl shadow-blue-200/50 border border-white overflow-hidden">
                    <div class="p-10">
                        <?php if($error): ?>
                            <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-2xl border border-red-100 flex items-center gap-3 text-sm font-semibold">
                                <span>⚠️</span> <?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($message): ?>
                            <div class="mb-6 p-6 bg-green-50 text-green-700 rounded-2xl border border-green-100">
                                <p class="font-bold text-lg mb-1">Application Sent! 🚀</p>
                                <p class="text-sm opacity-90"><?= $message ?></p>
                                <a href="student_dashboard.php" class="inline-block mt-4 text-sm font-bold underline">Go to Dashboard</a>
                            </div>
                        <?php else: ?>

                        <form action="" method="POST" class="space-y-8">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Review Category</label>
                                <div class="grid grid-cols-1 gap-3">
                                    <?php foreach($programs as $name => $details): ?>
                                    <label class="relative flex items-center p-4 rounded-2xl border-2 border-slate-100 cursor-pointer hover:border-blue-200 hover:bg-blue-50/30 transition-all group">
                                        <input type="radio" name="program_type" value="<?= $name ?>" required class="peer hidden" onchange="updateFee('<?= $details['fee'] ?>')">
                                        <div class="w-5 h-5 border-2 border-slate-300 rounded-full mr-4 peer-checked:border-blue-600 peer-checked:bg-blue-600 flex items-center justify-center transition-all">
                                            <div class="w-2 h-2 bg-white rounded-full"></div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-800"><?= $name ?></p>
                                            <p class="text-[11px] text-slate-400 font-medium"><?= $details['desc'] ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-black text-blue-600">₱<?= number_format($details['fee'], 0) ?></p>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Schedule Batch</label>
                                <select name="batch" required class="w-full p-4 rounded-2xl border border-slate-200 bg-slate-50 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition font-semibold text-slate-700">
                                    <option value="Batch 2026-A (April - June)">Batch 2026-A (April - June)</option>
                                    <option value="Batch 2026-B (July - Sept)">Batch 2026-B (July - Sept)</option>
                                    <option value="Weekend Intensive">Weekend Intensive Only</option>
                                </select>
                            </div>

                            <div class="bg-slate-900 rounded-3xl p-6 text-white overflow-hidden relative">
                                <div class="relative z-10 flex justify-between items-center">
                                    <div>
                                        <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Total Investment</p>
                                        <h4 id="displayFee" class="text-3xl font-black">₱0.00</h4>
                                    </div>
                                    <svg class="w-12 h-12 text-blue-500/30" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>
                                </div>
                                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-600/10 rounded-full -mr-10 -mt-10 blur-3xl"></div>
                            </div>

                            <button type="submit" name="submit_enrollment" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 transform active:scale-[0.98]">
                                CONFIRM ENROLLMENT
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateFee(amount) {
            const display = document.getElementById('displayFee');
            display.innerText = '₱' + parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2});
            // Simple animation
            display.classList.add('scale-105', 'text-blue-400');
            setTimeout(() => {
                display.classList.remove('scale-105', 'text-blue-400');
            }, 200);
        }
    </script>
</body>
</html>