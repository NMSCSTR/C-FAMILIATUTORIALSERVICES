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

// Analytics
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
        
        /* Responsive Table */
        @media (max-width: 1024px) {
            .responsive-table thead { display: none; }
            .responsive-table tr { display: block; margin-bottom: 1.5rem; border: 1px solid #f1f5f9; border-radius: 1.5rem; background: white; padding: 1rem; }
            .responsive-table td { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0.5rem; border: none; text-align: right; }
            .responsive-table td::before { content: attr(data-label); font-weight: 800; font-size: 10px; text-transform: uppercase; color: #94a3b8; text-align: left; }
        }
    </style>
</head>
<body class="text-slate-900 antialiased">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php'; ?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-10">
            <div class="max-w-7xl mx-auto">
                
                <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10">
                    <div class="flex items-center justify-between w-full lg:w-auto">
                        <div>
                            <h2 class="text-3xl md:text-4xl font-[800] text-slate-900 tracking-tight">Financial Ledger</h2>
                            <p class="text-slate-500 font-medium mt-1">Review transfers or log physical ORs.</p>
                        </div>
                        <button id="openMenu" class="lg:hidden p-3 bg-white border border-slate-200 rounded-2xl shadow-sm">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                        </button>
                    </div>
                    <button onclick="openWalkinModal()" class="w-full lg:w-auto bg-slate-900 hover:bg-blue-600 text-white px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-slate-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Log Walk-in Payment
                    </button>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                    <div class="bg-blue-600 p-8 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden">
                        <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mb-2">Total Collections</p>
                        <h3 class="text-3xl md:text-4xl font-bold">₱<?= number_format($total_collected, 2) ?></h3>
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    </div>
                    <div class="bento-card p-8 shadow-sm border-l-4 border-emerald-500">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Walk-in Revenue</p>
                        <h3 class="text-3xl md:text-4xl font-bold text-emerald-600">₱<?= number_format($walkin_revenue, 2) ?></h3>
                    </div>
                    <div class="bento-card p-8 shadow-sm border-l-4 border-orange-500 sm:col-span-2 lg:col-span-1">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Verification Needed</p>
                        <h3 class="text-3xl md:text-4xl font-bold text-orange-500"><?= $pending_verification ?></h3>
                    </div>
                </div>

                <div class="lg:bento-card overflow-hidden lg:shadow-sm">
                    <div class="overflow-x-auto lg:overflow-visible">
                        <table class="w-full text-left responsive-table">
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
                                    <td class="px-8 py-6" data-label="Student">
                                        <div class="text-left">
                                            <p class="font-bold text-slate-900"><?= $row['firstname'] ?> <?= $row['lastname'] ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold"><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center" data-label="Method">
                                        <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase <?= $isWalkin ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-blue-50 text-blue-600 border border-blue-100' ?>">
                                            <?= $row['payment_method'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center" data-label="Reference">
                                        <span class="font-mono text-[11px] font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl">
                                            <?= $row['reference_number'] ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6" data-label="Amount">
                                        <p class="font-black text-slate-900">₱<?= number_format($row['amount'], 2) ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-right" data-label="Status">
                                        <div class="flex items-center justify-end gap-3">
                                            <?php if (!$isWalkin && !empty($row['receipt'])): ?>
                                                <a href="uploads/receipts/<?= $row['receipt'] ?>" target="_blank" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-all">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </a>
                                            <?php endif; ?>

                                            <?php if($row['status'] == 'pending'): ?>
                                                <button onclick="confirmVerify(<?= $row['id'] ?>)" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest">Verify</button>
                                            <?php else: ?>
                                                <div class="flex items-center gap-1.5 text-emerald-500 bg-emerald-50 px-3 py-1.5 rounded-xl">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                                                    <span class="text-[9px] font-black uppercase">Paid</span>
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

    <div id="walkinModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-900">Record Walk-in</h3>
                <button onclick="closeWalkinModal()" class="text-slate-400 hover:text-slate-900 p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            
            <form action="" method="POST" class="p-8 space-y-5">
                <div class="receipt-pill p-5 rounded-2xl border border-dashed border-slate-200">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-blue-600 mb-2">OR / Receipt Number</label>
                    <input type="text" name="receipt_no" required placeholder="OR-XXXX" class="w-full bg-transparent text-lg font-black text-slate-900 outline-none placeholder:text-slate-300">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Student</label>
                    <select name="user_id" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold outline-none">
                        <option value="">Choose Reviewee...</option>
                        <?php mysqli_data_seek($students_res, 0); while($s = mysqli_fetch_assoc($students_res)): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['lastname'] ?>, <?= $s['firstname'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Amount</label>
                        <input type="number" name="amount" required step="0.01" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Type</label>
                        <select name="payment_type" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold outline-none">
                            <option value="full">Full</option>
                            <option value="installment">Installment</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="log_walkin" class="w-full bg-slate-900 hover:bg-blue-600 text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all">Save Transaction</button>
            </form>
        </div>
    </div>

    <script>
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
            Swal.fire({
                title: 'Verify Payment?',
                text: "Finalize this transaction in the ledger?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                confirmButtonText: 'Yes, Verify',
                customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = `?verify=${id}`; } });
        }

        <?php if(isset($_GET['success'])): ?>
        Swal.fire({ icon: 'success', title: 'Ledger Updated', timer: 2000, showConfirmButton: false });
        <?php endif; ?>
    </script>
</body>
</html>