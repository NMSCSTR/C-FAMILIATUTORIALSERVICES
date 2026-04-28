<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: login.php"); 
    exit(); 
}
include 'db.php';

// --- AJAX HANDLER FOR INSURANCE UPDATE ---
if (isset($_POST['action']) && $_POST['action'] === 'update_insurance') {
    $enrollment_id = intval($_POST['enrollment_id']);
    $is_insured = intval($_POST['insured']);
    
    $update_sql = "UPDATE enrollments SET insured = $is_insured WHERE id = $enrollment_id";
    if (mysqli_query($conn, $update_sql)) {
        echo "success";
    } else {
        echo "error";
    }
    exit();
}

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE id = $id");
    header("Location: admin_enrollments.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$view = isset($_GET['view']) ? $_GET['view'] : 'all'; 
$batch_filter = isset($_GET['batch']) ? mysqli_real_escape_string($conn, $_GET['batch']) : '';
$location_filter = isset($_GET['location']) ? mysqli_real_escape_string($conn, $_GET['location']) : '';
$base_fee = 5000;

$batches_res = mysqli_query($conn, "SELECT DISTINCT batch FROM enrollments WHERE batch IS NOT NULL AND batch != '' ORDER BY batch DESC");
$locations_res = mysqli_query($conn, "SELECT DISTINCT enrolled_at FROM enrollments WHERE enrolled_at IS NOT NULL AND enrolled_at != '' ORDER BY enrolled_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Registry Management | Admin Suite</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fcfcfd; }
        .registry-card { background: white; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .modal-slide { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Custom Scrollbar for better UX */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* Responsive Table Magic */
        @media (max-width: 768px) {
            .responsive-table thead { display: none; }
            .responsive-table tr { display: block; margin-bottom: 1rem; border: 1px solid #f1f5f9; border-radius: 16px; padding: 1rem; background: white; }
            .responsive-table td { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border: none; text-align: right; }
            .responsive-table td::before { content: attr(data-label); font-weight: 800; font-size: 10px; text-transform: uppercase; color: #94a3b8; text-align: left; }
        }
    </style>
</head>
<body class="text-slate-800 antialiased">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php';?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-12 w-full">
            <div class="max-w-7xl mx-auto">
                
                <header class="mb-8 md:mb-12">
                    <div class="flex items-center justify-between lg:hidden mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-black text-white">C</div>
                            <h1 class="text-lg font-bold">C-Familia</h1>
                        </div>
                        <button id="openMenu" class="p-2 bg-white border border-slate-200 rounded-xl shadow-sm">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                        </button>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 mb-2 block text-center md:text-left">System Administration</span>
                            <h1 class="text-3xl md:text-4xl font-[800] text-slate-900 tracking-tight text-center md:text-left">Student Registry</h1>
                        </div>
                        <div class="inline-flex p-1 bg-slate-100 rounded-2xl border border-slate-200 self-center md:self-end">
                            <a href="?view=all" class="px-4 md:px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'all' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">All Students</a>
                            <a href="?view=pending" class="px-4 md:px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'pending' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">Review Pending</a>
                        </div>
                    </div>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="sm:col-span-2 relative group">
                        <div class="absolute inset-y-0 left-5 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="studentSearch" placeholder="Search students..." class="w-full pl-14 pr-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/5 outline-none transition-all text-sm font-semibold shadow-sm">
                    </div>
                    <select onchange="location.href='?view=<?= $view ?>&batch=<?= $batch_filter ?>&location=' + this.value" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 outline-none shadow-sm appearance-none">
                        <option value="">All Review Centers</option>
                        <?php while($l = mysqli_fetch_assoc($locations_res)): ?>
                            <option value="<?= $l['enrolled_at'] ?>" <?= $location_filter == $l['enrolled_at'] ? 'selected' : '' ?>><?= $l['enrolled_at'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select onchange="location.href='?view=<?= $view ?>&location=<?= $location_filter ?>&batch=' + this.value" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 outline-none shadow-sm appearance-none">
                        <option value="">All Batches</option>
                        <?php mysqli_data_seek($batches_res, 0); while($b = mysqli_fetch_assoc($batches_res)): ?>
                            <option value="<?= $b['batch'] ?>" <?= $batch_filter == $b['batch'] ? 'selected' : '' ?>><?= $b['batch'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="registry-card overflow-hidden">
                    <div class="overflow-x-auto lg:overflow-visible">
                        <table class="w-full text-left responsive-table">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Identity</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 text-center">Insured</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Center</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Payment</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentTable" class="divide-y divide-slate-50">
                                <?php
                                $where = ($view === 'pending') ? "WHERE enrollments.status = 'pending'" : "WHERE 1=1";
                                if ($batch_filter) $where .= " AND enrollments.batch = '$batch_filter'";
                                if ($location_filter) $where .= " AND enrollments.enrolled_at = '$location_filter'";

                                $sql = "SELECT enrollments.*, users.firstname, users.lastname, users.email, users.profile_pic 
                                        FROM enrollments 
                                        JOIN users ON enrollments.user_id = users.id 
                                        $where 
                                        ORDER BY enrollments.enrolled_at ASC, enrollments.created_at DESC";
                                $result = mysqli_query($conn, $sql);

                                while($row = mysqli_fetch_assoc($result)):
                                    $p_sql = "SELECT SUM(amount) as paid FROM payments WHERE user_id = '{$row['user_id']}' AND status = 'paid'";
                                    $paid = mysqli_fetch_assoc(mysqli_query($conn, $p_sql))['paid'] ?? 0;
                                    $prog = ($paid / $base_fee) * 100;
                                ?>
                                <tr class="group hover:bg-slate-50/80 transition-colors">
                                    <td class="px-8 py-6" data-label="Student">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 shrink-0">
                                                <?= strtoupper(substr($row['firstname'],0,1)) ?>
                                            </div>
                                            <div class="text-left">
                                                <p class="font-bold text-slate-900 text-[14px] leading-tight"><?= $row['firstname'] ?> <?= $row['lastname'] ?></p>
                                                <p class="text-[11px] text-slate-400 font-medium truncate max-w-[150px]"><?= $row['email'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center" data-label="Insurance">
                                        <input type="checkbox" class="w-5 h-5 accent-blue-600 cursor-pointer" onchange="toggleInsurance(<?= $row['id'] ?>, this.checked)" <?= $row['insured'] ? 'checked' : '' ?>>
                                    </td>
                                    <td class="px-8 py-6" data-label="Center">
                                        <div class="flex flex-col text-left lg:text-left">
                                            <span class="text-xs font-bold text-slate-700"><?= $row['enrolled_at'] ?: 'N/A' ?></span>
                                            <span class="text-[10px] text-slate-400 font-black uppercase"><?= $row['batch'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6" data-label="Paid Status">
                                        <div class="flex items-center gap-3 w-full lg:w-auto justify-end lg:justify-start">
                                            <span class="text-xs font-black text-slate-900 shrink-0">₱<?= number_format($paid) ?></span>
                                            <div class="hidden sm:block w-20 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-600" style="width: <?= $prog ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right" data-label="Actions">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="viewDetails(<?= $row['user_id'] ?>)" class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-900 hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                            <?php if($row['status'] === 'pending'): ?>
                                                <a href="?approve=<?= $row['id'] ?>" class="p-2 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-500 hover:text-white transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </a>
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

    <div id="detailsModal" class="fixed inset-0 z-50 hidden">
        <div id="modalOverlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeModal()"></div>
        <div id="modalContent" class="absolute right-0 top-0 h-full w-full sm:max-w-lg bg-white shadow-2xl translate-x-full modal-slide flex flex-col">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xl font-extrabold text-slate-900">Student Profile</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="modalBody" class="flex-1 overflow-y-auto p-6"></div>
        </div>
    </div>

    <script>
        // --- Sidebar Logic ---
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

        openBtn.addEventListener('click', () => toggleSidebar(true));
        closeBtn.addEventListener('click', () => toggleSidebar(false));
        overlay.addEventListener('click', () => toggleSidebar(false));

        // --- Existing AJAX Logic ---
        async function toggleInsurance(enrollmentId, isChecked) {
            const formData = new FormData();
            formData.append('action', 'update_insurance');
            formData.append('enrollment_id', enrollmentId);
            formData.append('insured', isChecked ? 1 : 0);

            try {
                const response = await fetch('admin_enrollments.php', { method: 'POST', body: formData });
                const result = await response.text();
                if (result.trim() !== 'success') alert('Error updating status.');
            } catch (error) { alert('Connection error.'); }
        }

        async function viewDetails(userId) {
            const modal = document.getElementById('detailsModal');
            const overlayM = document.getElementById('modalOverlay');
            const content = document.getElementById('modalContent');
            const body = document.getElementById('modalBody');

            modal.classList.remove('hidden');
            setTimeout(() => {
                overlayM.classList.add('opacity-100');
                content.classList.remove('translate-x-full');
            }, 10);

            body.innerHTML = '<div class="flex items-center justify-center h-48"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
            const response = await fetch(`get_student_details.php?user_id=${userId}`);
            body.innerHTML = await response.text();
        }

        function closeModal() {
            const overlayM = document.getElementById('modalOverlay');
            const content = document.getElementById('modalContent');
            overlayM.classList.remove('opacity-100');
            content.classList.add('translate-x-full');
            setTimeout(() => document.getElementById('detailsModal').classList.add('hidden'), 400);
        }

        document.getElementById('studentSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#enrollmentTable tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    </script>
</body>
</html>