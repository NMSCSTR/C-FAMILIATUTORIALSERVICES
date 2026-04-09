<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

if (isset($_POST['submit_payment'])) {
    $amount = $_POST['amount'];
    $ref_no = $_POST['reference_number'];
    $p_type = $_POST['payment_type'];
    $p_method = $_POST['payment_method'];
    
    $target_dir = "uploads/receipts/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_ext = pathinfo($_FILES["receipt"]["name"], PATHINFO_EXTENSION);
    $file_name = "PAY_" . time() . "_" . $user_id . "." . $file_ext;
    $target_file = $target_dir . $file_name;

    // Advanced Validation
    if ($_FILES["receipt"]["size"] > 5000000) {
        $error = "File is too large. Max 5MB allowed.";
    } elseif (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file)) {
        // Use Prepared Statements for Advanced Security
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, reference_number, payment_type, payment_method, receipt, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("idssss", $user_id, $amount, $ref_no, $p_type, $p_method, $file_name);
        
        if ($stmt->execute()) {
            header("Location: student_dashboard.php?success=1");
            exit();
        } else {
            $error = "System error. Please try again later.";
        }
    } else {
        $error = "Failed to upload receipt. Check folder permissions.";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Secure Payment | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .input-focus { transition: all 0.3s ease; }
        .input-focus:focus { transform: translateY(-2px); box-shadow: 0 10px 20px -10px rgba(79, 70, 229, 0.3); }
    </style>
</head>

<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-6">

    <div class="max-w-5xl w-full grid lg:grid-cols-12 gap-8 items-start">
        
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-200">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight mb-4">Payment Verification</h1>
                <p class="text-indigo-100 leading-relaxed mb-6 text-sm">Our finance team typically reviews submissions within 24 hours. Ensure your receipt is clear and the reference number is accurate to avoid delays.</p>
                
                <ul class="space-y-4 text-xs font-semibold">
                    <li class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">1</span> GCash, Maya, or Bank Transfer</li>
                    <li class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">2</span> Screenshot must show "Success"</li>
                    <li class="flex items-center gap-3"><span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">3</span> Max file size 5MB (JPG/PNG)</li>
                </ul>
            </div>

            <a href="student_dashboard.php" class="flex items-center justify-center gap-2 text-slate-400 font-bold text-sm hover:text-indigo-600 transition-colors py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Dashboard
            </a>
        </div>

        <div class="lg:col-span-8 glass-card rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/60 border border-white">
            <h2 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3">
                Transaction Details
                <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] uppercase tracking-widest rounded-full border border-amber-100">Secure Entry</span>
            </h2>

            <?php if($error): ?>
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-sm font-bold rounded-r-xl">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" id="paymentForm" class="grid md:grid-cols-2 gap-x-8 gap-y-6">
                
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-wider ml-1">Amount Paid (PHP)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
                        <input type="number" step="0.01" name="amount" placeholder="0.00" required
                            class="w-full pl-9 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 input-focus font-bold text-slate-700">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-wider ml-1">Reference Number</label>
                    <input type="text" name="reference_number" placeholder="Enter transaction ID" required
                        class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 input-focus font-bold text-slate-700">
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-wider ml-1">Payment Category</label>
                    <select name="payment_type" class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 input-focus font-bold text-slate-700 appearance-none">
                        <option value="installment">Installment Payment</option>
                        <option value="full">Full Program Fee</option>
                        <option value="other">Other Fees</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-wider ml-1">Method / Platform</label>
                    <input type="text" name="payment_method" placeholder="e.g. GCash, BPI, PayMaya" required
                        class="w-full px-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 input-focus font-bold text-slate-700">
                </div>

                <div class="md:col-span-2 space-y-2">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-wider ml-1">Proof of Transaction (Receipt)</label>
                    <div class="group relative w-full h-44 border-2 border-dashed border-slate-200 rounded-[2rem] flex flex-col items-center justify-center transition-all hover:border-indigo-400 hover:bg-indigo-50/30 overflow-hidden">
                        <div id="preview-container" class="hidden absolute inset-0 bg-white">
                            <img id="receipt-preview" class="w-full h-full object-contain p-2" src="" alt="Preview">
                            <button type="button" onclick="resetFile()" class="absolute top-2 right-2 bg-rose-500 text-white p-2 rounded-full shadow-lg hover:bg-rose-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div id="upload-placeholder" class="text-center">
                            <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-xs font-bold text-slate-400">Click to upload receipt or drag and drop</p>
                        </div>
                        <input type="file" name="receipt" id="receipt-input" accept="image/*" required
                            class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit" name="submit_payment" 
                        class="w-full py-5 bg-indigo-600 text-white font-extrabold rounded-2xl shadow-xl shadow-indigo-100 hover:bg-slate-900 hover:shadow-none transition-all transform hover:-translate-y-1">
                        Submit Transaction for Audit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const input = document.getElementById('receipt-input');
        const preview = document.getElementById('receipt-preview');
        const previewContainer = document.getElementById('preview-container');
        const placeholder = document.getElementById('upload-placeholder');

        input.onchange = evt => {
            const [file] = input.files;
            if (file) {
                preview.src = URL.createObjectURL(file);
                previewContainer.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
        }

        function resetFile() {
            input.value = "";
            previewContainer.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        // Advanced Alert on Submit
        document.getElementById('paymentForm').onsubmit = function() {
            Swal.fire({
                title: 'Submitting...',
                text: 'Please wait while we secure your transaction.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        };
    </script>
</body>
</html>