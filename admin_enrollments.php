<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'db.php';

// --- AJAX HANDLER FOR GRADE INPUT VARIATIONS ---
if (isset($_POST['action']) && $_POST['action'] === 'update_grades') {
    csrf_verify();

    $user_id = intval($_POST['user_id'] ?? 0);

    $diagnostic = (trim($_POST['diagnostic'] ?? '') === '') ? null : intval($_POST['diagnostic']);
    $preboard   = (trim($_POST['preboard'] ?? '') === '') ? null : intval($_POST['preboard']);
    $compre     = (trim($_POST['compre'] ?? '') === '') ? null : intval($_POST['compre']);

    $check = $conn->prepare("SELECT exam_id FROM exam_result WHERE user_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $save = $conn->prepare("UPDATE exam_result
                     SET diagnostic_exam = ?, preboard_exam = ?, compre_exam = ?
                     WHERE user_id = ?");
        $save->bind_param("iiii", $diagnostic, $preboard, $compre, $user_id);
    } else {
        $save = $conn->prepare("INSERT INTO exam_result (user_id, diagnostic_exam, preboard_exam, compre_exam)
                     VALUES (?, ?, ?, ?)");
        $save->bind_param("iiii", $user_id, $diagnostic, $preboard, $compre);
    }

    if ($save->execute()) {
        log_activity($conn, 'enrollment.grades_update', null, [
            'entity_type' => 'user',
            'entity_id' => $user_id,
        ]);
        echo "success";
    } else {
        echo "error";
    }
    exit();
}

// --- AJAX HANDLER FOR INSURANCE UPDATE ---
if (isset($_POST['action']) && $_POST['action'] === 'update_insurance') {
    csrf_verify();

    $enrollment_id = intval($_POST['enrollment_id'] ?? 0);
    $is_insured = intval($_POST['insured'] ?? 0) === 1 ? 1 : 0;

    $update = $conn->prepare("UPDATE enrollments SET insured = ? WHERE id = ?");
    $update->bind_param("ii", $is_insured, $enrollment_id);

    if ($update->execute()) {
        log_activity($conn, 'enrollment.insurance_update', null, [
            'entity_type' => 'enrollment',
            'entity_id' => $enrollment_id,
            'insured' => $is_insured,
        ]);
        echo "success";
    } else {
        echo "error";
    }
    exit();
}

if (isset($_POST['approve_id'])) {
    csrf_verify();

    $id = intval($_POST['approve_id']);
    $stmt = $conn->prepare("UPDATE enrollments SET status = 'enrolled' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    log_activity($conn, 'enrollment.approve', null, [
        'entity_type' => 'enrollment',
        'entity_id' => $id,
    ]);
    header("Location: admin_enrollments.php?approved=1");
    exit();
}

$view = isset($_GET['view']) && $_GET['view'] === 'pending' ? 'pending' : 'all';
$batch_filter = trim($_GET['batch'] ?? '');
$location_filter = trim($_GET['location'] ?? '');
$base_fee = 5000;

$batches_res = mysqli_query($conn, "SELECT DISTINCT batch FROM enrollments WHERE batch IS NOT NULL AND batch != '' ORDER BY batch DESC");
$locations_res = mysqli_query($conn, "SELECT DISTINCT enrolled_at FROM enrollments WHERE enrolled_at IS NOT NULL AND enrolled_at != '' ORDER BY enrolled_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $page_title = 'Admin';
$load_sweetalert = true;
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="text-white antialiased bg-slate-950">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php';?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-12 w-full">
            <div class="max-w-12xl mx-auto">
                
                <header class="mb-8 md:mb-12">
                    <div class="flex items-center justify-between lg:hidden mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center font-black text-white">C</div>
                            <h1 class="text-lg font-bold text-white">C-Familia</h1>
                        </div>
                        <button id="openMenu" class="p-2 bg-slate-900/60 border border-slate-800 rounded-xl shadow-sm backdrop-blur-xl">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                        </button>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400 mb-2 block text-center md:text-left">System Administration</span>
                            <h1 class="text-3xl md:text-4xl font-[800] text-white tracking-tight text-center md:text-left">Student Registry</h1>
                        </div>
                        <div class="inline-flex p-1 bg-slate-900/60 rounded-2xl border border-slate-800 backdrop-blur-xl self-center md:self-end">
                            <a href="?view=all" class="px-4 md:px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'all' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' ?>">All Students</a>
                            <a href="?view=pending" class="px-4 md:px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'pending' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' ?>">Review Pending</a>
                        </div>
                    </div>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="sm:col-span-2 relative group">
                        <div class="absolute inset-y-0 left-5 flex items-center text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="studentSearch" placeholder="Search students..." class="w-full pl-14 pr-6 py-4 bg-slate-900/60 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-semibold shadow-sm text-white backdrop-blur-xl placeholder-slate-500">
                    </div>
                    <select onchange="location.href='?view=<?= $view ?>&batch=<?= $batch_filter ?>&location=' + this.value" class="px-6 py-4 bg-slate-900/60 border border-slate-800 rounded-2xl text-sm font-bold text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm appearance-none backdrop-blur-xl">
                        <option value="" class="bg-slate-900 text-white">All Review Centers</option>
                        <?php while($l = mysqli_fetch_assoc($locations_res)): ?>
                            <option value="<?= $l['enrolled_at'] ?>" <?= $location_filter == $l['enrolled_at'] ? 'selected' : '' ?> class="bg-slate-900 text-white"><?= $l['enrolled_at'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select onchange="location.href='?view=<?= $view ?>&location=<?= $location_filter ?>&batch=' + this.value" class="px-6 py-4 bg-slate-900/60 border border-slate-800 rounded-2xl text-sm font-bold text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm appearance-none backdrop-blur-xl">
                        <option value="" class="bg-slate-900 text-white">All Batches</option>
                        <?php mysqli_data_seek($batches_res, 0); while($b = mysqli_fetch_assoc($batches_res)): ?>
                            <option value="<?= $b['batch'] ?>" <?= $batch_filter == $b['batch'] ? 'selected' : '' ?> class="bg-slate-900 text-white"><?= $b['batch'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="card-dark overflow-hidden">
                    <div class="overflow-x-auto lg:overflow-visible">
                        <table class="w-full text-left responsive-table">
                            <thead>
                                <tr class="bg-slate-900/40 border-b border-slate-800">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Identity</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 text-center">Insured</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Center</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Payment</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentTable" class="divide-y divide-slate-800/50">
                                <?php
                                $where = ($view === 'pending') ? "WHERE enrollments.status = 'pending'" : "WHERE 1=1";
                                $types = '';
                                $params = [];
                                if ($batch_filter !== '') {
                                    $where .= " AND enrollments.batch = ?";
                                    $types .= 's';
                                    $params[] = $batch_filter;
                                }
                                if ($location_filter !== '') {
                                    $where .= " AND enrollments.enrolled_at = ?";
                                    $types .= 's';
                                    $params[] = $location_filter;
                                }

                                $sql = "SELECT enrollments.*, users.firstname, users.lastname, users.email, users.profile_pic,
                                            (SELECT COALESCE(SUM(p.amount), 0) FROM payments p WHERE p.user_id = enrollments.user_id AND p.status = 'paid') AS total_paid
                                        FROM enrollments
                                        JOIN users ON enrollments.user_id = users.id
                                        $where
                                        ORDER BY enrollments.enrolled_at ASC, enrollments.created_at DESC";

                                $listing = $conn->prepare($sql);
                                if ($types !== '') {
                                    $listing->bind_param($types, ...$params);
                                }
                                $listing->execute();
                                $result = $listing->get_result();

                                while($row = mysqli_fetch_assoc($result)):
                                    $paid = (float) $row['total_paid'];
                                    $prog = min(100, ($paid / $base_fee) * 100);
                                ?>
                                <tr class="group hover:bg-slate-900/40 transition-colors">
                                    <td class="px-8 py-6" data-label="Student">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-800 text-blue-400 flex items-center justify-center font-bold text-sm border border-slate-700 shrink-0">
                                                <?= strtoupper(mb_substr((string) $row['firstname'], 0, 1)) ?>
                                            </div>
                                            <div class="text-left">
                                                <p class="font-bold text-white text-[14px] leading-tight"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname'], ENT_QUOTES, 'UTF-8') ?></p>
                                                <p class="text-[11px] text-slate-400 font-medium truncate max-w-[150px]"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center" data-label="Insurance">
                                        <input type="checkbox" class="w-5 h-5 accent-blue-600 cursor-pointer" onchange="toggleInsurance(<?= (int) $row['id'] ?>, this.checked)" <?= $row['insured'] ? 'checked' : '' ?>>
                                    </td>
                                    <td class="px-8 py-6" data-label="Center">
                                        <div class="flex flex-col text-left lg:text-left">
                                            <span class="text-xs font-bold text-slate-300"><?= htmlspecialchars($row['enrolled_at'] ?: 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="text-[10px] text-slate-500 font-black uppercase"><?= htmlspecialchars((string) $row['batch'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6" data-label="Paid Status">
                                        <div class="flex items-center gap-3 w-full lg:w-auto justify-end lg:justify-start">
                                            <span class="text-xs font-black text-white shrink-0">₱<?= number_format($paid) ?></span>
                                            <div class="hidden sm:block w-20 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-blue-600 to-indigo-600" style="width: <?= $prog ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right" data-label="Actions">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="viewDetails(<?= $row['user_id'] ?>)" class="p-2 bg-slate-800 text-slate-400 rounded-lg hover:bg-slate-700 hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                            <?php if($row['status'] === 'pending'): ?>
                                                <form method="POST" action="" class="inline-flex" onsubmit="return AdminUI.confirmForm(event, 'Approve this enrollment?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="approve_id" value="<?= (int) $row['id'] ?>">
                                                    <button type="submit" class="p-2 bg-slate-800 text-blue-400 rounded-lg hover:bg-gradient-to-r hover:from-blue-600 hover:to-indigo-600 hover:text-white transition-all">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </form>
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

    <div id="detailsModal" data-modal-root role="dialog" aria-modal="true" aria-label="Student profile" class="fixed inset-0 z-50 hidden">
        <div id="modalOverlay" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeModal()"></div>
        <div id="modalContent" class="absolute right-0 top-0 h-full w-full sm:max-w-lg bg-slate-900 border-l border-slate-800 shadow-2xl translate-x-full modal-slide flex flex-col">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-xl font-extrabold text-white">Student Profile</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-slate-800 rounded-xl transition-colors text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="modalBody" class="flex-1 overflow-y-auto p-6 text-white"></div>
        </div>
    </div>

    <script>
    </script>
</body>
</html>