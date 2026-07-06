<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- Action: Log Walk-in Payment ---
if (isset($_POST['log_walkin'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $ref = mysqli_real_escape_string($conn, $_POST['receipt_no']);

    $query = "INSERT INTO payments (user_id, amount, payment_method, status, reference_number, payment_date, payment_type) 
              VALUES ('$user_id', '$amount', 'Walk-in Cash', 'paid', '$ref', CURDATE(), '$payment_type')";
    
    if (mysqli_query($conn, $query)) {
        $payment_id = mysqli_insert_id($conn);
        log_activity($conn, 'payment.walkin', null, [
            'entity_type' => 'payment',
            'entity_id' => $payment_id,
            'target_user_id' => (int) $user_id,
            'amount' => $amount,
        ]);
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
        log_activity($conn, 'payment.verify', null, [
            'entity_type' => 'payment',
            'entity_id' => (int) $p_id,
        ]);
        header("Location: admin_payments.php?success=verified");
        exit();
    }
}

// --- Action: Process Refund ---
if (isset($_GET['refund'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['refund']);
    $find_p = mysqli_query($conn, "SELECT user_id, amount, status FROM payments WHERE id = '$p_id'");
    $p_data = mysqli_fetch_assoc($find_p);

    if ($p_data && in_array($p_data['status'], ['paid', 'refund_requested'], true)) {
        $u_id = $p_data['user_id'];
        mysqli_query($conn, "UPDATE payments SET status = 'refunded' WHERE id = '$p_id'");

        $remaining_paid = mysqli_fetch_assoc(mysqli_query(
            $conn,
            "SELECT COUNT(*) as count FROM payments WHERE user_id = '$u_id' AND status = 'paid'"
        ))['count'] ?? 0;
        if ((int) $remaining_paid === 0) {
            mysqli_query($conn, "UPDATE enrollments SET status = 'pending' WHERE user_id = '$u_id' AND status = 'enrolled'");
        }

        log_activity($conn, 'payment.refund', null, [
            'entity_type' => 'payment',
            'entity_id' => (int) $p_id,
            'target_user_id' => (int) $u_id,
            'amount' => $p_data['amount'],
        ]);
        header("Location: admin_payments.php?success=refunded");
        exit();
    }
}

// Analytics
$total_collected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'paid'"))['total'] ?? 0;
$pending_verification = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'"))['count'] ?? 0;
$walkin_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE payment_method = 'Walk-in Cash' AND status = 'paid'"))['total'] ?? 0;
$refund_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE status = 'refund_requested'"))['count'] ?? 0;

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
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Financial Ledger | Review Center Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em; background-color: #020617; } /* slate-950 */
        .bento-card { background: rgba(15, 23, 42, 0.6); border: 1px solid #1e293b; border-radius: 2rem; backdrop-filter: blur(24px); } /* slate-900/60, slate-800, backdrop-blur-xl */
        .receipt-pill { background: repeating-linear-gradient(45deg, #0f172a, #0f172a 10px, #1e293b 10px, #1e293b 20px); }
        
        /* Responsive Table */
        @media (max-width: 1024px) {
            .responsive-table thead { display: none; }
            .responsive-table tr { display: block; margin-bottom: 1.5rem; border: 1px solid #1e293b; border-radius: 1.5rem; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(24px); padding: 1rem; }
            .responsive-table td { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0.5rem; border: none; text-align: right; }
            .responsive-table td::before { content: attr(data-label); font-weight: 800; font-size: 10px; text-transform: uppercase; color: #64748b; text-align: left; } /* slate-500 */
        }
    </style>
</head>
<body class="text-white antialiased bg-slate-950">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php'; ?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-10">
            <div class="max-w-7xl mx-auto">
                
                <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10">
                    <div class="flex items-center justify-between w-full lg:w-auto">
                        <div>
                            <h2 class="text-3xl md:text-4xl font-[800] text-white tracking-tight">Financial Ledger</h2>
                            <p class="text-slate-400 font-medium mt-1">Review transfers or log physical ORs.</p>
                        </div>
                        <button id="openMenu" class="lg:hidden p-3 bg-slate-900/60 border border-slate-800 rounded-2xl shadow-sm backdrop-blur-xl">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                        </button>
                    </div>
                    <button onclick="openWalkinModal()" class="w-full lg:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-blue-950/40 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Log Walk-in Payment
                    </button>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 p-8 rounded-[2.5rem] text-white shadow-2xl border border-slate-800 relative overflow-hidden">
                        <!-- Background glow accents -->
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-600/20 rounded-full blur-2xl"></div>
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Total Collections</p>
                        <h3 class="text-3xl md:text-4xl font-bold">₱<?= number_format($total_collected, 2) ?></h3>
                    </div>
                    <div class="bento-card p-8 shadow-sm border-l-4 border-emerald-500">
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Walk-in Revenue</p>
                        <h3 class="text-3xl md:text-4xl font-bold text-emerald-400">₱<?= number_format($walkin_revenue, 2) ?></h3>
                    </div>
                    <div class="bento-card p-8 shadow-sm border-l-4 border-orange-500">
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Verification Needed</p>
                        <h3 class="text-3xl md:text-4xl font-bold text-orange-400"><?= $pending_verification ?></h3>
                    </div>
                    <div class="bento-card p-8 shadow-sm border-l-4 border-red-500 sm:col-span-2 lg:col-span-1">
                        <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Refund Requests</p>
                        <h3 class="text-3xl md:text-4xl font-bold text-red-400"><?= $refund_requests ?></h3>
                    </div>
                </div>

                <div class="lg:bento-card overflow-hidden lg:shadow-sm">
                    <div class="overflow-x-auto lg:overflow-visible">
                        <table class="w-full text-left responsive-table">
                            <thead>
                                <tr class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] bg-slate-900/40">
                                    <th class="px-8 py-5 border-b border-slate-800">Student</th>
                                    <th class="px-8 py-5 border-b border-slate-800 text-center">Type</th>
                                    <th class="px-8 py-5 border-b border-slate-800 text-center">Ref / OR #</th>
                                    <th class="px-8 py-5 border-b border-slate-800">Amount</th>
                                    <th class="px-8 py-5 border-b border-slate-800 text-right">Verification</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php
                                $sql = "SELECT payments.*, users.firstname, users.lastname FROM payments 
                                        JOIN users ON payments.user_id = users.id 
                                        ORDER BY payments.created_at DESC";
                                $res = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($res)):
                                    $isWalkin = ($row['payment_method'] == 'Walk-in Cash');
                                ?>
                                <tr class="hover:bg-slate-900/40 transition-all">
                                    <td class="px-8 py-6" data-label="Student">
                                        <div class="text-left">
                                            <p class="font-bold text-white"><?= $row['firstname'] ?> <?= $row['lastname'] ?></p>
                                            <p class="text-[10px] text-slate-500 font-bold"><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center" data-label="Method">
                                        <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase <?= $isWalkin ? 'bg-emerald-950/20 text-emerald-400 border border-emerald-900/30' : 'bg-blue-950/20 text-blue-400 border border-blue-900/30' ?>">
                                            <?= $row['payment_method'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center" data-label="Reference">
                                        <span class="font-mono text-[11px] font-bold text-slate-300 bg-slate-900/60 border border-slate-800 px-3 py-1.5 rounded-xl">
                                            <?= $row['reference_number'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6" data-label="Amount">
                                        <p class="font-black text-white">₱<?= number_format($row['amount'], 2) ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-right" data-label="Status">
                                        <div class="flex items-center justify-end gap-3">
                                            <?php if (!$isWalkin && !empty($row['receipt'])): ?>
                                                <a href="uploads/receipts/<?= $row['receipt'] ?>" target="_blank" class="p-2 bg-slate-800 text-slate-400 rounded-lg hover:bg-slate-700 hover:text-white transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                            <?php endif; ?>

                                            <?php if($row['status'] == 'pending'): ?>
                                                <button onclick="confirmVerify(<?= $row['id'] ?>)" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md">Verify</button>
                                            <?php elseif($row['status'] == 'paid'): ?>
                                                <div class="flex items-center gap-1.5 text-emerald-400 bg-emerald-950/20 border border-emerald-900/30 px-3 py-1.5 rounded-xl">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                    <span class="text-[9px] font-black uppercase">Paid</span>
                                                </div>
                                                <button onclick="confirmRefund(<?= $row['id'] ?>, '<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname'], ENT_QUOTES) ?>', <?= (float) $row['amount'] ?>)" class="bg-red-950/20 text-red-400 hover:bg-red-600 hover:text-white border border-red-900/30 px-3 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">Refund</button>
                                            <?php elseif($row['status'] == 'refund_requested'): ?>
                                                <div class="flex items-center gap-1.5 text-red-400 bg-red-950/20 px-3 py-1.5 rounded-xl border border-red-900/30">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                    <span class="text-[9px] font-black uppercase">Refund Requested</span>
                                                </div>
                                                <button onclick="confirmRefund(<?= $row['id'] ?>, '<?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname'], ENT_QUOTES) ?>', <?= (float) $row['amount'] ?>)" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md">Process Refund</button>
                                            <?php elseif($row['status'] == 'refunded'): ?>
                                                <div class="flex items-center gap-1.5 text-slate-400 bg-slate-900/60 border border-slate-800 px-3 py-1.5 rounded-xl">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                                                    <span class="text-[9px] font-black uppercase">Refunded</span>
                                                </div>
                                            <?php elseif($row['status'] == 'cancelled'): ?>
                                                <div class="flex items-center gap-1.5 text-slate-500 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800">
                                                    <span class="text-[9px] font-black uppercase">Cancelled</span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[9px] font-black uppercase text-slate-400 bg-slate-950 border border-slate-800 px-3 py-1.5 rounded-xl"><?= htmlspecialchars($row['status']) ?></span>
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

    <div id="walkinModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-xl font-black text-white">Record Walk-in</h3>
                <button onclick="closeWalkinModal()" class="text-slate-400 hover:text-white p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            
            <form action="" method="POST" class="p-8 space-y-5">
                <div class="receipt-pill p-5 rounded-2xl border border-dashed border-slate-800">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-blue-400 mb-2">OR / Receipt Number</label>
                    <input type="text" name="receipt_no" required placeholder="OR-XXXX" class="w-full bg-transparent text-lg font-black text-white outline-none placeholder:text-slate-600">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Student</label>
                    <select name="user_id" required class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 text-white rounded-2xl text-sm font-bold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        <option value="" class="bg-slate-900 text-white">Choose Reviewee...</option>
                        <?php mysqli_data_seek($students_res, 0); while($s = mysqli_fetch_assoc($students_res)): ?>
                            <option value="<?= $s['id'] ?>" class="bg-slate-900 text-white"><?= $s['lastname'] ?>, <?= $s['firstname'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Amount</label>
                        <input type="number" name="amount" required step="0.01" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 text-white rounded-2xl text-sm font-bold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-2">Type</label>
                        <select name="payment_type" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 text-white rounded-2xl text-sm font-bold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            <option value="full" class="bg-slate-900 text-white">Full</option>
                            <option value="installment" class="bg-slate-900 text-white">Installment</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="log_walkin" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-md">Save Transaction</button>
            </form>
        </div>
    </div>

    <script>
        // SweetAlert Custom Dynamic Dark Mixin
        const customSwalMixin = Swal.mixin({
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#2563eb'
        });

        // Menu Toggle
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar(state) {
            if(state) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        openBtn?.addEventListener('click', () => toggleSidebar(true));
        closeBtn?.addEventListener('click', () => toggleSidebar(false));
        overlay?.addEventListener('click', () => toggleSidebar(false));

        // Modal Logic
        function openWalkinModal() { document.getElementById('walkinModal').classList.remove('hidden'); }
        function closeWalkinModal() { document.getElementById('walkinModal').classList.add('hidden'); }

        function confirmVerify(id) {
            customSwalMixin.fire({
                title: 'Verify Payment?',
                text: "Finalize this transaction in the ledger?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Yes, Verify',
                customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = `?verify=${id}`; } });
        }

        function confirmRefund(id, studentName, amount) {
            customSwalMixin.fire({
                title: 'Process Refund?',
                html: `Refund <strong>₱${Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong> to <strong>${studentName}</strong>?<br><span class="text-sm text-slate-500">Enrollment may revert to pending if no paid payments remain.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Refund',
                customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = `?refund=${id}`; } });
        }

        <?php if(isset($_GET['success'])): ?>
        customSwalMixin.fire({
            icon: 'success',
            title: <?= json_encode(
                $_GET['success'] === 'refunded' ? 'Refund Processed' :
                ($_GET['success'] === 'verified' ? 'Payment Verified' : 'Ledger Updated')
            ) ?>,
            timer: 2000,
            showConfirmButton: false
        });
        <?php endif; ?>
    </script>
</body>
</html>