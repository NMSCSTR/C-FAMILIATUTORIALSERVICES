<?php
include 'db.php';
$user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
$sql = "SELECT * FROM users JOIN enrollments ON users.id = enrollments.user_id WHERE users.id = '$user_id' LIMIT 1";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));
$payments = mysqli_query($conn, "SELECT * FROM payments WHERE user_id = '$user_id' ORDER BY created_at DESC");

$total_paid_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as paid FROM payments WHERE user_id = '$user_id' AND status = 'paid'"));
$total_paid = $total_paid_res['paid'] ?? 0;

// Fetch current grades if they exist
$grades_query = mysqli_query($conn, "SELECT * FROM exam_result WHERE user_id = '$user_id' LIMIT 1");
$grades = mysqli_fetch_assoc($grades_query);

$diag = $grades['diagnostic_exam'] ?? '';
$pree = $grades['preboard_exam'] ?? '';
$comp = $grades['compre_exam'] ?? '';
?>

<div class="flex items-center gap-6 mb-10 p-6 bg-blue-50/50 rounded-[2rem] border border-blue-100/50">
    <?php if (!empty($user['profile_pic'])): ?>
        <img src="uploads/profiles/<?= $user['profile_pic'] ?>" alt="Profile Picture" class="w-20 h-20 rounded-3xl object-cover shadow-lg shadow-blue-200 ring-4 ring-white">
    <?php else: ?>
        <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-blue-200">
            <?= substr($user['firstname'], 0, 1) ?>
        </div>
    <?php endif; ?>
    
    <div>
        <h3 class="text-2xl font-black text-slate-900 leading-tight"><?= htmlspecialchars($user['firstname']) ?> <?= htmlspecialchars($user['lastname']) ?></h3>
        <p class="text-blue-600 font-bold text-sm"><?= htmlspecialchars($user['email']) ?></p>
        <span class="inline-block mt-2 px-2 py-0.5 bg-white border border-blue-100 text-[10px] font-black uppercase text-blue-600 rounded-lg">Student Account</span>
    </div>
</div>

<div class="space-y-8">
    <section class="bg-slate-900 text-white p-6 rounded-[2rem] shadow-xl shadow-slate-200/80">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-lg">📊</span>
            <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Academic Grading Metric Control</h4>
        </div>
        <p class="text-[11px] text-slate-300 mb-6 leading-relaxed">Input grade values below. Leaving an exam field empty completely clears the target dashboard card visibility layer.</p>
        
        <form onsubmit="submitGradesForm(event, <?= intval($user_id) ?>)" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-300 block mb-1.5 px-1">Diagnostic Exam</label>
                    <input type="number" min="0" max="100" name="diagnostic" value="<?= htmlspecialchars($diag) ?>" placeholder="Null / Blank" class="w-full px-4 py-3 bg-white/10 border border-white/10 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm font-semibold text-white transition-all placeholder:text-white/30">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-300 block mb-1.5 px-1">Preboard Exam</label>
                    <input type="number" min="0" max="100" name="preboard" value="<?= htmlspecialchars($pree) ?>" placeholder="Null / Blank" class="w-full px-4 py-3 bg-white/10 border border-white/10 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm font-semibold text-white transition-all placeholder:text-white/30">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-300 block mb-1.5 px-1">Compre Exam</label>
                    <input type="number" min="0" max="100" name="compre" value="<?= htmlspecialchars($comp) ?>" placeholder="Null / Blank" class="w-full px-4 py-3 bg-white/10 border border-white/10 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm font-semibold text-white transition-all placeholder:text-white/30">
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black uppercase text-[10px] tracking-widest py-3.5 rounded-xl shadow-md hover:bg-blue-500 transition-all mt-2">
                Commit & Save Student Metrics
            </button>
        </form>
    </section>

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Registration Data</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Middle Name</p>
                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user['middlename']) ?: '--' ?></p>
            </div>
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Insurance Status</p>
                <p class="text-sm font-bold <?= $user['insured'] ? 'text-emerald-500' : 'text-rose-500' ?>"><?= $user['insured'] ? 'Active (Insured)' : 'Not Covered' ?></p>
            </div>
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Program Type</p>
                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user['program_type']) ?></p>
            </div>
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Review Batch</p>
                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user['batch']) ?></p>
            </div>
        </div>
    </section>

    <hr class="border-slate-100">

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Personal Profile Details</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Birthday</p>
                <p class="text-sm font-bold text-slate-800">
                    <?= !empty($user['birthday']) ? date('F d, Y', strtotime($user['birthday'])) : '--' ?>
                </p>
            </div>
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Cellphone Number</p>
                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user['cellphone_no']) ?: '--' ?></p>
            </div>
            
            <div class="md:col-span-2 bg-slate-50/50 p-4 rounded-2xl border border-slate-100 break-all">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">FB / Messenger Account</p>
                <p class="text-sm font-bold text-indigo-600">
                    <?php if(!empty($user['fb_messenger_account'])): ?>
                        <a href="<?= (filter_var($user['fb_messenger_account'], FILTER_VALIDATE_URL)) ? htmlspecialchars($user['fb_messenger_account']) : 'https://facebook.com/'.htmlspecialchars($user['fb_messenger_account']) ?>" target="_blank" class="hover:underline inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0 text-indigo-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span class="underline decoration-indigo-200"><?= htmlspecialchars($user['fb_messenger_account']) ?></span>
                        </a>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </p>
            </div>

            <div class="md:col-span-2 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Home Address</p>
                <p class="text-sm font-bold text-slate-800 leading-relaxed"><?= nl2br(htmlspecialchars($user['address'])) ?: '--' ?></p>
            </div>
        </div>
    </section>

    <hr class="border-slate-100">

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Emergency & Guardian Contacts</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Parent Name / Guardian</p>
                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user['parents_name_guardian']) ?: '--' ?></p>
            </div>
            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Parent Contact Number</p>
                <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($user['parents_phone_no']) ?: '--' ?></p>
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
            <?php if(mysqli_num_rows($payments) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($payments)): ?>
                    <div class="p-4 border border-slate-100 rounded-2xl flex items-center justify-between">
                        <div>
                            <p class="text-xs font-black text-slate-900">₱<?= number_format($p['amount']) ?></p>
                            <p class="text-[10px] text-slate-400 font-bold"><?= htmlspecialchars($p['payment_method']) ?> • <?= htmlspecialchars($p['reference_number']) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded <?= $p['status'] == 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' ?>"><?= htmlspecialchars($p['status']) ?></span>
                            <p class="text-[9px] text-slate-300 mt-1"><?= date('M d, Y', strtotime($p['created_at'])) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-xs text-slate-400 italic text-center py-4 font-semibold">No transactions recorded yet.</p>
            <?php endif; ?>
        </div>
    </section>
</div>