<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'"))['total'];

// --- Logic to Verify Payment ---
if (isset($_GET['verify'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['verify']);
    
    // First, get the user_id to update enrollment automatically
    $find_p = mysqli_query($conn, "SELECT user_id FROM payments WHERE id = '$p_id'");
    $p_data = mysqli_fetch_assoc($find_p);
    
    if ($p_data) {
        $u_id = $p_data['user_id'];
        mysqli_query($conn, "UPDATE payments SET status = 'paid' WHERE id = '$p_id'");
        mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE user_id = '$u_id' AND status = 'pending'");
        
        header("Location: admin_payments.php?success=1");
        exit();
    }
}

// --- Stats Fetching ---
$total_collected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'paid'"))['total'] ?? 0;
$pending_verification = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'"))['count'] ?? 0;
$total_tx = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM payments"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Financial Ledger | Admin Suite</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .stat-glow-blue { box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04); }
        .sidebar-link-active { background: #0f172a !important; color: white !important; }
    </style>
</head>
<body class="bg-[#fcfcfd] text-slate-900">

    <div id="overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

    <div class="flex min-h-screen">
        <?php include 'aside.php'; ?>

        <main class="flex-1 w-full min-w-0">
            <header class="lg:hidden bg-white/80 backdrop-blur-md border-b border-slate-200 p-4 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-slate-900 rounded-xl flex items-center justify-center font-black text-white text-sm">C</div>
                    <span class="font-bold tracking-tight">Financials</span>
                </div>
                <button id="openMenu" class="p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                </button>
            </header>

            <div class="p-6 md:p-10 max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div>
                        <span class="text-blue-600 font-bold text-[10px] uppercase tracking-[0.2em]">Accounting Department</span>
                        <h2 class="text-3xl md:text-4xl font-[800] text-slate-900 tracking-tight mt-1">Payment Ledger</h2>
                        <p class="text-slate-500 mt-2 text-sm">Reviewing <span class="text-slate-900 font-semibold"><?= $total_tx ?> total transactions</span> across the system.</p>
                    </div>

                    <?php if(isset($_GET['success'])): ?>
                    <div class="flex items-center gap-3 bg-emerald-50 text-emerald-700 px-5 py-3 rounded-2xl border border-emerald-100 animate-bounce">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <span class="text-xs font-bold uppercase tracking-wider">Verification Successful</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="bg-blue-600 p-8 rounded-[2.5rem] text-white shadow-2xl stat-glow-blue relative overflow-hidden">
                        <div class="relative z-10">
                            <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mb-4">Total Revenue</p>
                            <h3 class="text-4xl font-bold tracking-tighter">₱<?= number_format($total_collected, 2) ?></h3>
                        </div>
                        <div class="absolute -right-4 -bottom-4 opacity-10">
                            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm flex flex-col justify-between">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Awaiting Review</p>
                        <div>
                            <h3 class="text-4xl font-bold text-slate-900"><?= $pending_verification ?></h3>
                            <p class="text-amber-500 text-[10px] font-bold uppercase mt-2 italic">Requires immediate action</p>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm flex flex-col justify-between">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Volume</p>
                        <h3 class="text-4xl font-bold text-slate-900"><?= $total_tx ?></h3>
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-10 py-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                        <h4 class="font-bold text-slate-900 tracking-tight">Transaction History</h4>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Live Updates</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-black uppercase text-slate-400 tracking-[0.15em]">
                                    <th class="px-10 py-5">Student Information</th>
                                    <th class="px-10 py-5">Reference</th>
                                    <th class="px-10 py-5">Amount</th>
                                    <th class="px-10 py-5">Evidence</th>
                                    <th class="px-10 py-5 text-right">Verification</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                <?php
                                $sql = "SELECT payments.*, users.firstname, users.middlename, users.lastname, users.email FROM payments 
                                        JOIN users ON payments.user_id = users.id WHERE users.role != 'admin'                                        ORDER BY payments.created_at DESC";
                                $res = mysqli_query($conn, $sql);
                                
                                if(mysqli_num_rows($res) > 0):
                                    while($row = mysqli_fetch_assoc($res)):
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-all duration-200 group">
                                    <td class="px-10 py-7">
                                        <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors"><?= $row['firstname'] ?></div>
                                        <div class="text-[10px] text-slate-400 mt-1 font-medium italic"><?= date('F j, Y • g:i a', strtotime($row['created_at'])) ?></div>
                                    </td>
                                    <td class="px-10 py-7 font-mono text-xs text-slate-500">
                                        <span class="bg-slate-100 px-2 py-1 rounded"><?= $row['reference_number'] ?></span>
                                    </td>
                                    <td class="px-10 py-7 font-black text-slate-900">₱<?= number_format($row['amount'], 2) ?></td>
                                    <td class="px-10 py-7">
                                        <a href="uploads/receipts/<?= $row['receipt'] ?>" target="_blank" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-xl text-[10px] font-bold transition-all uppercase tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Inspect
                                        </a>
                                    </td>
                                    <td class="px-10 py-7 text-right">
                                        <?php if($row['status'] == 'pending'): ?>
                                            <button type="button" 
                                                onclick="confirmVerification('<?= $row['id'] ?>', '<?= addslashes($row['name']) ?>')"
                                                class="bg-slate-900 hover:bg-blue-600 text-white px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-[0.1em] transition-all shadow-lg shadow-slate-200 inline-block">
                                                Verify Payment
                                            </button>
                                        <?php else: ?>
                                            <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Settled
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="5" class="py-24 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                            </div>
                                            <p class="text-slate-400 font-medium italic">No transactions found in the ledger.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function confirmVerification(paymentId, studentName) {
        Swal.fire({
            title: 'Verify Payment?',
            text: `You are about to confirm the transaction for ${studentName}. Review first the receipt and make sure it is being receive.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0f172a', // Slate 900
            cancelButtonColor: '#f1f5f9',
            confirmButtonText: 'Yes, Verify It',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'rounded-2xl px-6 py-3 font-bold uppercase text-[10px] tracking-widest',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold uppercase text-[10px] tracking-widest text-slate-500'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?verify=${paymentId}`;
            }
        });
    }

    // Success Toast Notification
    <?php if(isset($_GET['success'])): ?>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: 'success',
        title: 'Payment Verified successfully'
    });
    <?php endif; ?>

    const openBtn = document.getElementById('openMenu');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('overlay');

    function toggleMenu() {
        mobileSidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }

    if(openBtn) openBtn.addEventListener('click', toggleMenu);
    if(overlay) overlay.addEventListener('click', toggleMenu);
    </script>
</body>
</html>