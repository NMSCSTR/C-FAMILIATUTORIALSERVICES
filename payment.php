<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";


$enroll_check = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = '$user_id' AND status != 'completed' ORDER BY created_at DESC LIMIT 1");
$enrollment = mysqli_fetch_assoc($enroll_check);

if (isset($_POST['submit_payment'])) {
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $ref_no = mysqli_real_escape_string($conn, $_POST['reference_number']);
    $type   = mysqli_real_escape_string($conn, $_POST['payment_type']);
    
    if (!is_dir('uploads/receipts')) {
        mkdir('uploads/receipts', 0777, true);
    }

    $file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["receipt"]["name"]));
    $target_file = "uploads/receipts/" . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($_FILES["receipt"]["size"] > 5000000) {
        $error = "File is too large. Max 5MB.";
    } elseif (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'pdf'])) {
        $error = "Only JPG, PNG, JPEG & PDF files are allowed.";
    } else {
        if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO payments (user_id, amount, payment_method, reference_number, receipt, payment_type, status, payment_date) 
                    VALUES ('$user_id', '$amount', '$method', '$ref_no', '$file_name', '$type', 'pending', CURDATE())";
            
            if (mysqli_query($conn, $sql)) {
                $message = "Payment proof uploaded! Admin will verify your receipt shortly.";
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        } else {
            $error = "Error uploading file. Check folder permissions.";
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
    <title>Submit Payment | C-Familia</title>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen py-12 px-4">

    <div class="max-w-xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="student_dashboard.php" class="p-3 bg-white rounded-2xl shadow-sm hover:bg-slate-100 transition">⬅️</a>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Payment Submission</h1>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Proof of Transaction</p>
            </div>
        </div>

        <?php if(!$enrollment && !$message): ?>
            <div class="bg-white p-10 rounded-[2.5rem] border border-slate-200 text-center shadow-xl shadow-blue-900/5">
                <div class="text-4xl mb-4">⚠️</div>
                <p class="text-slate-800 font-bold text-lg">No Enrollment Found</p>
                <p class="text-slate-500 text-sm mt-2 mb-6">You need an active enrollment record to process a payment.</p>
                <a href="enroll.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-bold">Enroll Now</a>
            </div>
        <?php else: ?>

            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/5 border border-slate-100 overflow-hidden">
                <div class="p-8 md:p-10">
                    <?php if($message): ?>
                        <div class="text-center py-6">
                            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-3xl">🎉</div>
                            <h2 class="text-2xl font-black text-slate-800 mb-2">Submitted!</h2>
                            <p class="text-slate-500 text-sm leading-relaxed mb-8"><?= $message ?></p>
                            <a href="student_dashboard.php" class="block w-full bg-slate-900 text-white py-4 rounded-2xl font-bold transition hover:bg-slate-800">Return to Dashboard</a>
                        </div>
                    <?php else: ?>

                        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div class="bg-slate-900 rounded-3xl p-6 text-white relative overflow-hidden">
                                <div class="relative z-10 flex justify-between items-center">
                                    <div>
                                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Course Fee</p>
                                        <h3 class="text-3xl font-black italic">₱<?= number_format($enrollment['total_fee'], 2) ?></h3>
                                        <p class="text-[11px] text-blue-400 font-bold mt-2 px-3 py-1 bg-blue-400/10 rounded-lg inline-block"><?= $enrollment['program_type'] ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Status</p>
                                        <span class="text-[10px] font-black px-2 py-1 bg-amber-500 rounded-md uppercase"><?= $enrollment['status'] ?></span>
                                    </div>
                                </div>
                            </div>

                            <?php if($error): ?>
                                <div class="p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-xs font-bold flex items-center gap-2">
                                    <span>🚫</span> <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Payment Type</label>
                                    <select name="payment_type" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 outline-none font-bold text-slate-700">
                                        <option value="full">Full Payment</option>
                                        <option value="installment">Partial / Installment</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1 tracking-widest">Amount to Send</label>
                                    <input type="number" name="amount" value="<?= $enrollment['total_fee'] ?>" step="0.01" required 
                                           class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 outline-none font-bold text-slate-700">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Payment Method</label>
                                    <select name="payment_method" class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 outline-none font-bold text-slate-700">
                                        <option value="GCash">GCash</option>
                                        <option value="Maya">Maya</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash">Cash (Walk-in)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1 tracking-widest">Reference Number</label>
                                    <input type="text" name="reference_number" required placeholder="e.g. 9012 345 678"
                                           class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 outline-none font-bold text-slate-700 uppercase">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Upload Receipt Image</label>
                                <div id="drop-area" class="relative group border-2 border-dashed border-slate-200 rounded-[2rem] p-8 text-center hover:bg-slate-50 hover:border-blue-400 transition-all cursor-pointer">
                                    <input type="file" name="receipt" id="receipt-input" required accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFile()">
                                    
                                    <div id="upload-placeholder">
                                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl group-hover:scale-110 transition">📄</div>
                                        <p class="text-slate-600 font-bold text-sm">Click to select photo</p>
                                        <p class="text-[10px] text-slate-400 mt-1 uppercase font-black tracking-widest">JPG, PNG, JPEG (MAX 5MB)</p>
                                    </div>
                                    
                                    <div id="preview-container" class="hidden">
                                        <img id="file-preview" src="#" class="max-h-48 mx-auto rounded-xl shadow-md mb-2">
                                        <p id="file-name-display" class="text-xs font-bold text-blue-600"></p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="submit_payment" class="w-full py-5 bg-blue-600 text-white font-black rounded-[1.5rem] hover:bg-blue-700 transition shadow-xl shadow-blue-200 hover:-translate-y-1 active:translate-y-0 uppercase tracking-widest text-sm">
                                Confirm & Submit Payment
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function previewFile() {
            const preview = document.getElementById('file-preview');
            const file = document.getElementById('receipt-input').files[0];
            const placeholder = document.getElementById('upload-placeholder');
            const previewContainer = document.getElementById('preview-container');
            const fileNameDisplay = document.getElementById('file-name-display');
            const reader = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
                placeholder.classList.add('hidden');
                previewContainer.classList.remove('hidden');
                fileNameDisplay.textContent = file.name;
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
            }
        }
    </script>
</body>
</html>