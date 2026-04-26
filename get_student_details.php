<?php
include 'db.php';
$user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

// Get User & Enrollment Info
$sql = "SELECT users.*, enrollments.* FROM users 
        JOIN enrollments ON users.id = enrollments.user_id 
        WHERE users.id = '$user_id' LIMIT 1";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));

// Get Payment History
$pay_sql = "SELECT * FROM payments WHERE user_id = '$user_id' ORDER BY payment_date DESC, created_at DESC";
$payments = mysqli_query($conn, $pay_sql);

if (!$user) exit("<p class='p-10 text-center font-bold'>No records found.</p>");
?>

<div class="flex items-center gap-6 mb-10">
    <div class="w-24 h-24 rounded-[2rem] bg-blue-600 flex items-center justify-center text-white text-3xl font-black shadow-xl shadow-blue-200">
        <?= strtoupper(substr($user['firstname'], 0, 1)) ?>
    </div>
    <div>
        <h3 class="text-2xl font-black text-slate-900"><?= $user['firstname'] . ' ' . $user['lastname'] ?></h3>
        <p class="text-blue-600 font-bold"><?= $user['email'] ?></p>
        <span class="inline-block mt-2 px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black uppercase rounded-lg">ID: #<?= $user['user_id'] ?></span>
    </div>
</div>

<div class="grid grid-cols-2 gap-6 mb-10">
    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Batch</p>
        <p class="text-sm font-bold text-slate-800"><?= $user['batch'] ?></p>
    </div>
    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Center Location</p>
        <p class="text-sm font-bold text-slate-800"><?= $user['enrolled_at'] ?: 'Main Center' ?></p>
    </div>
</div>

<h4 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
    Payment Ledger
</h4>

<div class="space-y-3">
    <?php if(mysqli_num_rows($payments) > 0): while($p = mysqli_fetch_assoc($payments)): ?>
        <div class="p-4 border border-slate-100 rounded-2xl flex items-center justify-between hover:border-blue-200 transition-all">
            <div>
                <p class="text-sm font-bold text-slate-900">₱<?= number_format($p['amount'], 2) ?></p>
                <p class="text-[10px] text-slate-400 font-medium"><?= $p['payment_method'] ?> • Ref: <?= $p['reference_number'] ?: 'N/A' ?></p>
            </div>
            <div class="text-right">
                <span class="block text-[10px] font-black uppercase <?= $p['status'] == 'paid' ? 'text-emerald-500' : 'text-orange-500' ?>">
                    <?= $p['status'] ?>
                </span>
                <p class="text-[10px] text-slate-400 mt-0.5"><?= date('M d, Y', strtotime($p['created_at'])) ?></p>
            </div>
        </div>
    <?php endwhile; else: ?>
        <p class="text-center py-10 text-slate-400 italic text-sm">No payment records found.</p>
    <?php endif; ?>
</div>