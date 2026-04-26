<?php
include 'db.php';
$user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
$sql = "SELECT * FROM users JOIN enrollments ON users.id = enrollments.user_id WHERE users.id = '$user_id' LIMIT 1";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));
$payments = mysqli_query($conn, "SELECT * FROM payments WHERE user_id = '$user_id' ORDER BY created_at DESC");

$total_paid_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as paid FROM payments WHERE user_id = '$user_id' AND status = 'paid'"));
$total_paid = $total_paid_res['paid'] ?? 0;
?>

<div class="flex items-center gap-6 mb-10 p-6 bg-blue-50/50 rounded-[2rem] border border-blue-100/50">
    <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-blue-200">
        <?= substr($user['firstname'], 0, 1) ?>
    </div>
    <div>
        <h3 class="text-2xl font-black text-slate-900 leading-tight"><?= $user['firstname'] ?> <?= $user['lastname'] ?></h3>
        <p class="text-blue-600 font-bold text-sm"><?= $user['email'] ?></p>
        <span class="inline-block mt-2 px-2 py-0.5 bg-white border border-blue-100 text-[10px] font-black uppercase text-blue-600 rounded-lg">Student Account</span>
    </div>
</div>

<div class="space-y-8">
    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Registration Data</h4>
        <div class="grid grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Middlename</p>
                <p class="text-sm font-bold text-slate-800"><?= $user['middlename'] ?: '--' ?></p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Insurance Status</p>
                <p class="text-sm font-bold <?= $user['insured'] ? 'text-emerald-500' : 'text-rose-500' ?>"><?= $user['insured'] ? 'Active (Insured)' : 'Not Covered' ?></p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Program Type</p>
                <p class="text-sm font-bold text-slate-800"><?= $user['program_type'] ?></p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Review Batch</p>
                <p class="text-sm font-bold text-slate-800"><?= $user['batch'] ?></p>
            </div>
        </div>
    </section>

    <hr class="border-slate-100">

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Tuition Summary</h4>
        <div class="grid grid-cols-3 gap-4">
            <div class="p-4 bg-slate-50 rounded-2xl text-center">
                <p class="text-[9px] text-slate-400 font-black uppercase mb-1">Total Fee</p>
                <p class="text-sm font-black text-slate-900">₱<?= number_format($user['total_fee']) ?></p>
            </div>
            <div class="p-4 bg-emerald-50 rounded-2xl text-center">
                <p class="text-[9px] text-emerald-600 font-black uppercase mb-1">Paid</p>
                <p class="text-sm font-black text-emerald-600">₱<?= number_format($total_paid) ?></p>
            </div>
            <div class="p-4 bg-rose-50 rounded-2xl text-center">
                <p class="text-[9px] text-rose-600 font-black uppercase mb-1">Balance</p>
                <p class="text-sm font-black text-rose-600">₱<?= number_format($user['total_fee'] - $total_paid) ?></p>
            </div>
        </div>
    </section>

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Transaction History</h4>
        <div class="space-y-2">
            <?php while($p = mysqli_fetch_assoc($payments)): ?>
                <div class="p-4 border border-slate-100 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black text-slate-900">₱<?= number_format($p['amount']) ?></p>
                        <p class="text-[10px] text-slate-400 font-bold"><?= $p['payment_method'] ?> • <?= $p['reference_number'] ?></p>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded <?= $p['status'] == 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' ?>"><?= $p['status'] ?></span>
                        <p class="text-[9px] text-slate-300 mt-1"><?= date('M d, Y', strtotime($p['created_at'])) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
</div>