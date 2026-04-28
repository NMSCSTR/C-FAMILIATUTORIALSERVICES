<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- Action: Log Walk-in Payment (Official Receipt) ---
if (isset($_POST['log_walkin'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $ref = mysqli_real_escape_string($conn, $_POST['receipt_no']);

    $query = "INSERT INTO payments (user_id, amount, payment_method, status, reference_number, payment_date, payment_type) 
              VALUES ('$user_id', '$amount', 'Walk-in Cash', 'paid', '$ref', CURDATE(), '$payment_type')";
    
    if (mysqli_query($conn, $query)) {
        mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE user_id = '$user_id' AND status = 'pending'");
        header("Location: admin_payments.php?success=logged");
        exit();
    }
}

// --- Action: Verify Online Payment ---
if (isset($_GET['verify'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['verify']);
    $find_p = mysqli_query($conn, "SELECT user_id FROM payments WHERE id = '$p_id'");
    $p_data = mysqli_fetch_assoc($find_p);
    
    if ($p_data) {
        $u_id = $p_data['user_id'];
        mysqli_query($conn, "UPDATE payments SET status = 'paid' WHERE id = '$p_id'");
        mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE user_id = '$u_id' AND status = 'pending'");
        header("Location: admin_payments.php?success=verified");
        exit();
    }
}

// --- Analytics ---
$total_collected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'paid'"))['total'] ?? 0;
$pending_verification = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'"))['count'] ?? 0;
$walkin_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE payment_method = 'Walk-in Cash'"))['total'] ?? 0;

$students_res = mysqli_query($conn, "SELECT id, firstname, lastname FROM users WHERE role = 'student' ORDER BY lastname ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Financial Ledger | Review Center Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em; background-color: #fcfcfd; }
        .bento-card { background: white; border: 1px solid #f1f5f9; border-radius: 2rem; }
        .receipt-pill { background: repeating-linear-gradient(45deg, #f8fafc, #f8fafc 10px, #ffffff 10px, #ffffff 20px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-900 antialiased custom-scrollbar">

    <div class="flex min-h-screen">
        <?php include 'aside.php'; ?>

        <main class="flex-1 p-6 md:p-10">
            <div class="max-w-7xl mx-auto">
                
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10">
                    <div>
                        <h2 class="text-4xl font-[800] text-slate-900 tracking-tight">Payment Ledger</h2>
                        <p class="text-slate-500 font-medium mt-1">Review transfers or log physical OR numbers.</p>
                    </div>
                    <button onclick="openWalkinModal()" class="bg-slate-900 hover:bg-blue-600 text-white px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-slate-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Log Walk-in Payment
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-blue-600 p-8 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden">
                        <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mb-2">Total Collections</p>
                        <h3 class="text-4xl font-bold">₱<?= number_format($total_collected, 2) ?></h3>
                    </div>
                    <div class="bento-card p-8 shadow-sm">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Walk-in Revenue</p>
                        <h3 class="text-4xl font-bold text-emerald-600">₱<?= number_format($walkin_revenue, 2) ?></h3>
                    </div>
                    <div class="bento-card p-8 shadow-sm">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Verification Needed</p>
                        <h3 class="text-4xl font-bold text-orange-500"><?= $pending_verification ?></h3>
                    </div>
                </div>

                <div class="bento-card overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] bg-slate-50/50">
                                    <th class="px-8 py-5 border-b border-slate-100">Student</th>
                                    <th class="px-8 py-5 border-b border-slate-100 text-center">Type</th>
                                    <th class="px-8 py-5 border-b border-slate-100 text-center">Ref / OR #</th>
                                    <th class="px-8 py-5 border-b border-slate-100">Amount</th>
                                    <th class="px-8 py-5 border-b border-slate-100 text-right">Verification</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php
                                $sql = "SELECT payments.*, users.firstname, users.lastname FROM payments 
                                        JOIN users ON payments.user_id = users.id 
                                        ORDER BY payments.created_at DESC";
                                $res = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($res)):
                                    $isWalkin = ($row['payment_method'] == 'Walk-in Cash');
                                ?>
                                <tr class="hover:bg-slate-50 transition-all">
                                    <td class="px-8 py-6">
                                        <p class="font-bold text-slate-900"><?= $row['firstname'] ?> <?= $row['lastname'] ?></p>
                                        <p class="text-[10px] text-slate-400 font-bold"><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase <?= $isWalkin ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-blue-50 text-blue-600 border border-blue-100' ?>">
                                            <?= $row['payment_method'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="font-mono text-[11px] font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl">
                                            <?= $row['reference_number'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="font-black text-slate-900">₱<?= number_format($row['amount'], 2) ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <?php if (!$isWalkin && !empty($row['receipt'])): ?>
                                                <a href="uploads/receipts/<?= $row['receipt'] ?>" target="_blank" class="p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="View Digital Receipt">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                            <?php elseif ($isWalkin): ?>
                                                <div class="p-2.5 bg-emerald-50 text-emerald-500 rounded-xl cursor-help" title="Physical Cash Payment - Receipt Handed Over">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                </div>
                                            <?php endif; ?>

                                            <?php if($row['status'] == 'pending'): ?>
                                                <button onclick="confirmVerify(<?= $row['id'] ?>)" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-slate-200">Verify</button>
                                            <?php else: ?>
                                                <div class="flex items-center gap-1.5 text-emerald-500">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                                                    <span class="text-[10px] font-black uppercase">Settled</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="walkinModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-xl font-black text-slate-900">Log Physical Receipt</h3>
                <button onclick="closeWalkinModal()" class="text-slate-400 hover:text-slate-900 transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            
            <form action="" method="POST" class="p-10 space-y-6">
                <div class="receipt-pill p-6 rounded-3xl border border-dashed border-slate-200">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-blue-600 mb-2">Physical Receipt Number (OR)</label>
                    <input type="text" name="receipt_no" required placeholder="OR-0000" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-lg font-black text-slate-900 outline-none focus:border-blue-600 placeholder:text-slate-300">
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Select Student</label>
                    <select name="user_id" required class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold outline-none">
                        <option value="">Choose Reviewee...</option>
                        <?php mysqli_data_seek($students_res, 0); while($s = mysqli_fetch_assoc($students_res)): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['lastname'] ?>, <?= $s['firstname'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="number" name="amount" required step="0.01" placeholder="Amount (₱)" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold outline-none focus:border-blue-500">
                    <select name="payment_type" class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold outline-none">
                        <option value="full">Full Payment</option>
                        <option value="installment">Installment</option>
                    </select>
                </div>

                <button type="submit" name="log_walkin" class="w-full bg-slate-900 hover:bg-blue-600 text-white py-5 rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-2xl transition-all">Record Transaction</button>
            </form>
        </div>
    </div>

    <script>
        function openWalkinModal() { document.getElementById('walkinModal').classList.remove('hidden'); }
        function closeWalkinModal() { document.getElementById('walkinModal').classList.add('hidden'); }

        function confirmVerify(id) {
            Swal.fire({
                title: 'Verify Online Payment?',
                text: "Confirming this will finalize the enrollment process.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                confirmButtonText: 'Yes, Verify',
                customClass: { confirmButton: 'rounded-2xl px-6 py-3', cancelButton: 'rounded-2xl px-6 py-3' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = `?verify=${id}`; } });
        }

        <?php if(isset($_GET['success'])): ?>
        Swal.fire({ icon: 'success', title: 'Ledger Updated', timer: 2000, showConfirmButton: false });
        <?php endif; ?>
    </script>
</body>
</html>