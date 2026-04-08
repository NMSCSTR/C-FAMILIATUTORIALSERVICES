<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

if (isset($_POST['submit_payment'])) {
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $ref_no = mysqli_real_escape_string($conn, $_POST['reference_number']);
    $p_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $p_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Handle Receipt Upload
    $target_dir = "uploads/receipts/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_name = time() . "_" . basename($_FILES["receipt"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO payments (user_id, amount, reference_number, payment_type, payment_method, receipt, status, created_at) 
                VALUES ('$user_id', '$amount', '$ref_no', '$p_type', '$p_method', '$file_name', 'pending', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: student_dashboard.php?success=payment_submitted");
            exit();
        } else {
            $msg = "Database error: " . mysqli_error($conn);
        }
    } else {
        $msg = "Failed to upload receipt.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap"
        rel="stylesheet">
    <title>Submit Payment | C-Familia</title>
</head>

<body class="bg-slate-50 font-['Plus_Jakarta_Sans']">
    <div class="max-w-md mx-auto my-10 p-8 bg-white rounded-[2rem] shadow-xl border border-slate-100">
        <h2 class="text-2xl font-black text-slate-800 mb-6">Submit Payment</h2>

        <?php if($msg): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-4 text-sm font-bold"><?= $msg ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Amount Paid (₱)</label>
                <input type="number" step="0.01" name="amount" required
                    class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Reference Number</label>
                <input type="text" name="reference_number" required
                    class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Type</label>
                    <select name="payment_type"
                        class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none">
                        <option value="full">Full Payment</option>
                        <option value="installment">Installment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Method</label>
                    <input type="text" name="payment_method" placeholder="GCash/Maya"
                        class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Upload Receipt (Image)</label>
                <input type="file" name="receipt" accept="image/*" required
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <button type="submit" name="submit_payment"
                class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">Submit
                for Verification</button>
            <a href="student_dashboard.php" class="block text-center text-xs font-bold text-slate-400 mt-4">Cancel and
                Go Back</a>
        </form>
    </div>
</body>

</html>