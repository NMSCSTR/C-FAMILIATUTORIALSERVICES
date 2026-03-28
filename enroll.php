<?php
session_start();
include 'db.php';

// Security: Only logged-in students (or admins) can enroll
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$error = "";

if (isset($_POST['submit_enrollment'])) {
    $user_id = $_SESSION['user_id'];
    $program = mysqli_real_escape_string($conn, $_POST['program_type']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $fee = 5000.00; // You can make this dynamic based on the program

    // Check if already enrolled in this program to prevent duplicates
    $check = mysqli_query($conn, "SELECT id FROM enrollments WHERE user_id = '$user_id' AND program_type = '$program' AND status != 'completed'");
    
    if (mysqli_num_rows($check) > 0) {
        $error = "You already have an active enrollment for this program.";
    } else {
        $sql = "INSERT INTO enrollments (user_id, program_type, batch, total_fee, status, enrollment_date) 
                VALUES ('$user_id', '$program', '$batch', '$fee', 'pending', CURDATE())";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Enrollment submitted! Please wait for admin approval.";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Enrollment | C-Familia</title>
</head>
<body class="bg-slate-50 min-h-screen pb-20">

    <nav class="bg-white shadow-sm p-4 mb-10">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <a href="index.php" class="font-bold text-blue-600 text-xl">C-Familia</a>
            <span class="text-slate-500 text-sm">Logged in as: <b><?= $_SESSION['username'] ?></b></span>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-blue-600 p-8 text-white">
                <h2 class="text-3xl font-bold">Enrollment Form</h2>
                <p class="opacity-80">Secure your slot for the upcoming review batch.</p>
            </div>

            <div class="p-8">
                <?php if($error): ?>
                    <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl border border-red-100"><?= $error ?></div>
                <?php endif; ?>
                <?php if($message): ?>
                    <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl border border-green-100"><?= $message ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Select Review Program</label>
                        <select name="program_type" class="w-full p-4 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none appearance-none bg-slate-50">
                            <option value="Criminology Review">Criminology Review (CLE)</option>
                            <option value="LET Review">LET Review (Teachers)</option>
                            <option value="Civil Service Review">Civil Service Review</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Preferred Batch</label>
                        <select name="batch" class="w-full p-4 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none appearance-none bg-slate-50">
                            <option value="Batch 2026-A (April - June)">Batch 2026-A (April - June)</option>
                            <option value="Batch 2026-B (July - Sept)">Batch 2026-B (July - Sept)</option>
                        </select>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100">
                        <div class="flex justify-between items-center">
                            <span class="text-blue-800 font-medium">Estimated Tuition Fee:</span>
                            <span class="text-2xl font-bold text-blue-900">₱5,000.00</span>
                        </div>
                        <p class="text-xs text-blue-600 mt-1">*Final fees may vary based on chosen materials.</p>
                    </div>

                    <button type="submit" name="submit_enrollment" class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                        Submit Enrollment Application
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>