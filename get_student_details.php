<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include 'db.php';

$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

if ($user_id === false || $user_id === null || $user_id <= 0) {
    http_response_code(400);
    exit('Invalid user id.');
}

$stmt = $conn->prepare("SELECT * FROM users JOIN enrollments ON users.id = enrollments.user_id WHERE users.id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    exit('Student not found.');
}

$pay_stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC");
$pay_stmt->bind_param("i", $user_id);
$pay_stmt->execute();
$payments = $pay_stmt->get_result();

$total_stmt = $conn->prepare("SELECT SUM(amount) as paid FROM payments WHERE user_id = ? AND status = 'paid'");
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_paid = $total_stmt->get_result()->fetch_assoc()['paid'] ?? 0;

// Fetch current grades if they exist
$grades_stmt = $conn->prepare("SELECT * FROM exam_result WHERE user_id = ? LIMIT 1");
$grades_stmt->bind_param("i", $user_id);
$grades_stmt->execute();
$grades = $grades_stmt->get_result()->fetch_assoc();

$diag = $grades['diagnostic_exam'] ?? '';
$pree = $grades['preboard_exam'] ?? '';
$comp = $grades['compre_exam'] ?? '';
?>

<div class="flex items-center gap-6 mb-10 p-6 bg-slate-900/60 rounded-[2rem] border border-slate-800 backdrop-blur-xl">
    <?php if (!empty($user['profile_pic'])): ?>
        <img src="uploads/profiles/<?= htmlspecialchars($user['profile_pic'], ENT_QUOTES, 'UTF-8') ?>" alt="Profile Picture" class="w-20 h-20 rounded-3xl object-cover shadow-lg shadow-blue-950/50 ring-4 ring-slate-800">
    <?php else: ?>
        <div class="w-20 h-20 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-blue-950/50">
            <?= htmlspecialchars(substr((string) $user['firstname'], 0, 1), ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
    
    <div>
        <h3 class="text-2xl font-black text-white leading-tight"><?= htmlspecialchars($user['firstname']) ?> <?= htmlspecialchars($user['lastname']) ?></h3>
        <p class="text-blue-400 font-bold text-sm"><?= htmlspecialchars($user['email']) ?></p>
        <span class="inline-block mt-2 px-2 py-0.5 bg-slate-950 border border-slate-800 text-[10px] font-black uppercase text-blue-400 rounded-lg">Student Account</span>
    </div>
</div>

<div class="space-y-8">
    <section class="bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white p-6 rounded-[2rem] shadow-xl border border-slate-800 relative overflow-hidden">
        <!-- Glow decoration layer -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-2 mb-2 relative z-10">
            <span class="text-lg">📊</span>
            <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Academic Grading Metric Control</h4>
        </div>
        <p class="text-[11px] text-slate-300 mb-6 leading-relaxed relative z-10">Input grade values below. Leaving an exam field empty completely clears the target dashboard card visibility layer.</p>
        
        <form onsubmit="submitGradesForm(event, <?= (int) $user_id ?>)" class="space-y-4 relative z-10">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-bold text-slate-300 block mb-1.5 px-1">Diagnostic Exam</label>
                    <input type="number" min="0" max="100" name="diagnostic" value="<?= htmlspecialchars($diag) ?>" placeholder="Null / Blank" class="w-full px-4 py-3 bg-slate-900/60 border border-slate-800 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-sm font-semibold text-white transition-all placeholder:text-slate-500">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-300 block mb-1.5 px-1">Preboard Exam</label>
                    <input type="number" min="0" max="100" name="preboard" value="<?= htmlspecialchars($pree) ?>" placeholder="Null / Blank" class="w-full px-4 py-3 bg-slate-900/60 border border-slate-800 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-sm font-semibold text-white transition-all placeholder:text-slate-500">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-300 block mb-1.5 px-1">Compre Exam</label>
                    <input type="number" min="0" max="100" name="compre" value="<?= htmlspecialchars($comp) ?>" placeholder="Null / Blank" class="w-full px-4 py-3 bg-slate-900/60 border border-slate-800 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-sm font-semibold text-white transition-all placeholder:text-slate-500">
                </div>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black uppercase text-[10px] tracking-widest py-3.5 rounded-xl shadow-md transition-all mt-2">
                Commit & Save Student Metrics
            </button>
        </form>
    </section>

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Registration Data</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Middle Name</p>
                <p class="text-sm font-bold text-white"><?= htmlspecialchars($user['middlename']) ?: '--' ?></p>
            </div>
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Insurance Status</p>
                <p class="text-sm font-bold <?= $user['insured'] ? 'text-emerald-400' : 'text-rose-400' ?>"><?= $user['insured'] ? 'Active (Insured)' : 'Not Covered' ?></p>
            </div>
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Program Type</p>
                <p class="text-sm font-bold text-white"><?= htmlspecialchars($user['program_type']) ?></p>
            </div>
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Review Batch</p>
                <p class="text-sm font-bold text-white"><?= htmlspecialchars($user['batch']) ?></p>
            </div>
        </div>
    </section>

    <hr class="border-slate-800">

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Personal Profile Details</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Birthday</p>
                <p class="text-sm font-bold text-white">
                    <?= !empty($user['birthday']) ? date('F d, Y', strtotime($user['birthday'])) : '--' ?>
                </p>
            </div>
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Cellphone Number</p>
                <p class="text-sm font-bold text-white"><?= htmlspecialchars($user['cellphone_no']) ?: '--' ?></p>
            </div>
            
            <div class="md:col-span-2 bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl break-all">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">FB / Messenger Account</p>
                <p class="text-sm font-bold text-blue-400">
                    <?php if(!empty($user['fb_messenger_account'])): ?>
                        <a href="<?= (filter_var($user['fb_messenger_account'], FILTER_VALIDATE_URL)) ? htmlspecialchars($user['fb_messenger_account']) : 'https://facebook.com/'.htmlspecialchars($user['fb_messenger_account']) ?>" target="_blank" class="hover:underline inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span class="underline decoration-blue-950"><?= htmlspecialchars($user['fb_messenger_account']) ?></span>
                        </a>
                    <?php else: ?>
                        --
                    <?php endif; ?>
                </p>
            </div>

            <div class="md:col-span-2 bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Home Address</p>
                <p class="text-sm font-bold text-white leading-relaxed"><?= nl2br(htmlspecialchars($user['address'])) ?: '--' ?></p>
            </div>
        </div>
    </section>

    <hr class="border-slate-800">

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Emergency & Guardian Contacts</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Parent Name / Guardian</p>
                <p class="text-sm font-bold text-white"><?= htmlspecialchars($user['parents_name_guardian']) ?: '--' ?></p>
            </div>
            <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800 backdrop-blur-xl">
                <p class="text-[10px] text-slate-500 font-bold uppercase mb-1">Parent Contact Number</p>
                <p class="text-sm font-bold text-white"><?= htmlspecialchars($user['parents_phone_no']) ?: '--' ?></p>
            </div>
        </div>
    </section>

    <hr class="border-slate-800">

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Tuition Summary</h4>
        <div class="grid grid-cols-3 gap-4">
            <div class="p-4 bg-slate-900/60 border border-slate-800 rounded-2xl text-center backdrop-blur-xl">
                <p class="text-[9px] text-slate-500 font-black uppercase mb-1">Total Fee</p>
                <p class="text-sm font-black text-white">₱<?= number_format($user['total_fee']) ?></p>
            </div>
            <div class="p-4 bg-emerald-950/20 border border-emerald-900/30 rounded-2xl text-center backdrop-blur-xl">
                <p class="text-[9px] text-emerald-400 font-black uppercase mb-1">Paid</p>
                <p class="text-sm font-black text-emerald-400">₱<?= number_format($total_paid) ?></p>
            </div>
            <div class="p-4 bg-red-950/20 border border-red-900/30 rounded-2xl text-center backdrop-blur-xl">
                <p class="text-[9px] text-red-400 font-black uppercase mb-1">Balance</p>
                <p class="text-sm font-black text-red-400">₱<?= number_format($user['total_fee'] - $total_paid) ?></p>
            </div>
        </div>
    </section>

    <section>
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Transaction History</h4>
        <div class="space-y-2">
            <?php if(mysqli_num_rows($payments) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($payments)): ?>
                    <div class="p-4 bg-slate-900/40 border border-slate-800 rounded-2xl flex items-center justify-between backdrop-blur-xl">
                        <div>
                            <p class="text-xs font-black text-white">₱<?= number_format($p['amount']) ?></p>
                            <p class="text-[10px] text-slate-500 font-bold"><?= htmlspecialchars($p['payment_method']) ?> • <?= htmlspecialchars($p['reference_number']) ?></p>
                        </div>
                        <div class="text-right">
                            <?php
                                $statusClass = match ($p['status']) {
                                    'paid' => 'bg-emerald-950/30 text-emerald-400 border border-emerald-900/50',
                                    'pending' => 'bg-orange-950/30 text-orange-400 border border-orange-900/50',
                                    'refund_requested' => 'bg-red-950/20 text-red-400 border border-red-900/50',
                                    'refunded', 'cancelled' => 'bg-slate-950 text-slate-500 border border-slate-800',
                                    default => 'bg-orange-950/30 text-orange-400 border border-orange-900/50',
                                };
                                $statusLabel = str_replace('_', ' ', $p['status']);
                            ?>
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                            <p class="text-[9px] text-slate-500 mt-1"><?= date('M d, Y', strtotime($p['created_at'])) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-xs text-slate-500 italic text-center py-4 font-semibold">No transactions recorded yet.</p>
            <?php endif; ?>
        </div>
    </section>
</div>