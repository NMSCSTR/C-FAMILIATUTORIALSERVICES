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

// Check if student has a pending enrollment to pay for
$enroll_check = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = '$user_id' AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$enrollment = mysqli_fetch_assoc($enroll_check);

if (isset($_POST['submit_payment'])) {
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $ref_no = mysqli_real_escape_string($conn, $_POST['reference_number']);
    $type   = mysqli_real_escape_string($conn, $_POST['payment_type']);
    
    // File Upload Logic
    $target_dir = "uploads/";
    $file_name = time() . "_" . basename($_FILES["receipt"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Simple Validation
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
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Submit Payment | C-Familia</title>
</head>
<body class="bg-slate-50 min-h-screen py-12 px-4">

    <div class="max-w-xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-slate-800">Secure Payment</h1>
            <p class="text-slate-500 mt-2">Upload your receipt to activate your enrollment.</p>
        </div>

        <?php if(!$enrollment && !$message): ?>
            <div class="bg-amber-50 border border-amber-200 p-6 rounded-2xl text-center">
                <p class="text-amber-700 font-bold">No Pending Enrollment Found</p>
                <p class="text-amber-600 text-sm mt-1 mb-4">You need to apply for a program before making a payment.</p>
                <a href="enroll.php" class="text-blue-600 font-bold underline">Enroll Now</a>
            </div>
        <?php else: ?>

            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-8 md:p-12">
                    <?php if($message): ?>
                        <div class="text-center py-10">
                            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl font-bold">✓</div>
                            <h2 class="text-2xl font-bold text-slate-800 mb-2">Receipt Received</h2>
                            <p class="text-slate-500 mb-8"><?= $message ?></p>
                            <a href="student_dashboard.php" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold transition hover:bg-slate-800">Back to Dashboard</a>
                        </div>
                    <?php else: ?>

                        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div class="bg-blue-600 rounded-3xl p-6 text-white mb-8">
                                <p class="text-blue-200 text-xs font-bold uppercase">Amount to Pay</p>
                                <h3 class="text-3xl font-black">₱<?= number_format($enrollment['total_fee'], 2) ?></h3>
                                <p class="text-sm opacity-80 mt-1"><?= $enrollment['program_type'] ?></p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Method</label>
                                    <select name="payment_method" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-semibold">
                                        <option value="GCash">GCash</option>
                                        <option value="Maya">Maya</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Over-the-counter">Walk-in / OTC</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Payment Plan</label>
                                    <select name="payment_type" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none font-semibold">
                                        <option value="full">Full Payment</option>
                                        <option value="installment">Installment</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Reference / Transaction No.</label>
                                <input type="text" name="reference_number" required placeholder="Enter the Ref #"
                                       class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>

                            <input type="hidden" name="amount" value="<?= $enrollment['total_fee'] ?>">

                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Upload Receipt (Image/PDF)</label>
                                <div class="relative border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center hover:bg-slate-50 transition">
                                    <input type="file" name="receipt" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <p class="text-slate-400 text-sm font-medium">Click to upload or drag and drop</p>
                                    <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold">PNG, JPG up to 5MB</p>
                                </div>
                            </div>

                            <button type="submit" name="submit_payment" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-200">
                                SUBMIT PAYMENT
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>