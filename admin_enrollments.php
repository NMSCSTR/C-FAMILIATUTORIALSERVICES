<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: login.php"); 
    exit(); 
}
include 'db.php';

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
        .stat-badge { font-feature-settings: "tnum"; }
        .modal-slide { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="text-slate-800 antialiased">

    <div class="flex min-h-screen">
        <?php include 'aside.php';?>

        <main class="flex-1 p-6 md:p-12">
            <div class="max-w-7xl mx-auto">
                
                <header class="mb-12">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600 mb-2 block">System Administration</span>
                            <h1 class="text-4xl font-[800] text-slate-900 tracking-tight">Student Registry</h1>
                            <p class="text-slate-500 mt-1 font-medium italic">Displaying student profiles and center-specific enrollment data.</p>
                        </div>
                        <div class="inline-flex p-1 bg-slate-100 rounded-2xl border border-slate-200">
                            <a href="?view=all" class="px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'all' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">All Students</a>
                            <a href="?view=pending" class="px-6 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'pending' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-900' ?>">Review Pending</a>
                        </div>
                    </div>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="md:col-span-2 relative group">
                        <div class="absolute inset-y-0 left-5 flex items-center text-slate-400 group-focus-within:text-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="studentSearch" placeholder="Filter by name, email, or center..." class="w-full pl-14 pr-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/5 outline-none transition-all text-sm font-semibold shadow-sm">
                    </div>
                    <select onchange="location.href='?view=<?= $view ?>&batch=<?= $batch_filter ?>&location=' + this.value" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 outline-none cursor-pointer focus:border-blue-500 transition-all shadow-sm appearance-none">
                        <option value="">All Review Centers</option>
                        <?php while($l = mysqli_fetch_assoc($locations_res)): ?>
                            <option value="<?= $l['enrolled_at'] ?>" <?= $location_filter == $l['enrolled_at'] ? 'selected' : '' ?>><?= $l['enrolled_at'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select onchange="location.href='?view=<?= $view ?>&location=<?= $location_filter ?>&batch=' + this.value" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 outline-none cursor-pointer focus:border-blue-500 transition-all shadow-sm appearance-none">
                        <option value="">All Batches</option>
                        <?php mysqli_data_seek($batches_res, 0); while($b = mysqli_fetch_assoc($batches_res)): ?>
                            <option value="<?= $b['batch'] ?>" <?= $batch_filter == $b['batch'] ? 'selected' : '' ?>><?= $b['batch'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="registry-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Identity</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Enrollment & Status</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Financial Progress</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 text-right">Operations</th>
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
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 shadow-sm">
                                                <?= strtoupper(substr($row['firstname'],0,1)) ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-[15px]"><?= $row['firstname'] ?> <?= $row['lastname'] ?></p>
                                                <p class="text-[11px] text-slate-400 font-medium"><?= $row['email'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-bold text-slate-700"><?= $row['enrolled_at'] ?: 'Not Specified' ?></span>
                                            <span class="text-[10px] text-slate-400 font-black uppercase tracking-tighter"><?= $row['batch'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <span class="stat-badge text-xs font-black text-slate-900">₱<?= number_format($paid) ?></span>
                                            <div class="flex-1 max-w-[120px] h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                                <div class="h-full bg-blue-600 transition-all duration-700" style="width: <?= $prog ?>%"></div>
                                            </div>
                                            <span class="text-[10px] font-black text-blue-500 italic"><?= round($prog) ?>%</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button onclick="viewDetails(<?= $row['user_id'] ?>)" class="px-4 py-2 bg-slate-900 text-white text-[10px] font-black uppercase rounded-xl hover:bg-blue-600 transition-all shadow-sm">Details</button>
                                            <?php if($row['status'] === 'pending'): ?>
                                                <a href="?approve=<?= $row['id'] ?>" class="px-4 py-2 bg-orange-500 text-white text-[10px] font-black uppercase rounded-xl shadow-md shadow-orange-100">Approve</a>
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
        <div id="modalOverlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
        <div id="modalContent" class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl translate-x-full modal-slide flex flex-col">
            <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Student Profile</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-slate-100 rounded-xl transition-colors text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="modalBody" class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                </div>
        </div>
    </div>

    <script>
        async function viewDetails(userId) {
            const modal = document.getElementById('detailsModal');
            const overlay = document.getElementById('modalOverlay');
            const content = document.getElementById('modalContent');
            const body = document.getElementById('modalBody');

            modal.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.add('opacity-100');
                content.classList.remove('translate-x-full');
            }, 10);

            body.innerHTML = '<div class="flex items-center justify-center h-48"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';

            const response = await fetch(`get_student_details.php?user_id=${userId}`);
            body.innerHTML = await response.text();
        }

        function closeModal() {
            const overlay = document.getElementById('modalOverlay');
            const content = document.getElementById('modalContent');
            overlay.classList.remove('opacity-100');
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