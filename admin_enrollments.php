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

// Fetch Filters
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
    <title>Student Registry | Premium Suite</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .bento-card { background: white; border-radius: 24px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .row-pill:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="text-slate-900">

    <div class="flex min-h-screen">
        <?php include 'aside.php';?>

        <main class="flex-1 p-4 md:p-8">
            <div class="max-w-7xl mx-auto">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Student Registry</h1>
                        <p class="text-slate-500 font-medium">Monitoring <?= $view ?> enrollments across all centers.</p>
                    </div>
                    <div class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-slate-200">
                        <a href="?view=all" class="px-5 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'all' ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50' ?>">All</a>
                        <a href="?view=pending" class="px-5 py-2 rounded-xl text-xs font-bold transition-all <?= $view == 'pending' ? 'bg-orange-500 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50' ?>">Pending</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-8">
                    <div class="md:col-span-5 bento-card p-2 flex items-center gap-3">
                        <div class="pl-4 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="studentSearch" placeholder="Quick search students..." class="w-full py-3 bg-transparent outline-none text-sm font-medium">
                    </div>
                    <div class="md:col-span-3 bento-card p-2">
                        <select onchange="location.href='?view=<?= $view ?>&batch=<?= $batch_filter ?>&location=' + this.value" class="w-full py-3 px-4 bg-transparent outline-none text-sm font-bold text-slate-600 appearance-none cursor-pointer">
                            <option value="">Select Center</option>
                            <?php while($l = mysqli_fetch_assoc($locations_res)): ?>
                                <option value="<?= $l['enrolled_at'] ?>" <?= $location_filter == $l['enrolled_at'] ? 'selected' : '' ?>><?= $l['enrolled_at'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="md:col-span-4 bento-card p-2">
                        <select onchange="location.href='?view=<?= $view ?>&location=<?= $location_filter ?>&batch=' + this.value" class="w-full py-3 px-4 bg-transparent outline-none text-sm font-bold text-slate-600 appearance-none cursor-pointer">
                            <option value="">Select Batch</option>
                            <?php mysqli_data_seek($batches_res, 0); while($b = mysqli_fetch_assoc($batches_res)): ?>
                                <option value="<?= $b['batch'] ?>" <?= $batch_filter == $b['batch'] ? 'selected' : '' ?>><?= $b['batch'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="bento-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-y-0">
                            <thead>
                                <tr class="bg-slate-50/50">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-left">Full Name</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-left">Enrollment Info</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-left">Tuition Progress</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentTable" class="divide-y divide-slate-100">
                                <?php
                                $where = ($view === 'pending') ? "WHERE enrollments.status = 'pending'" : "WHERE 1=1";
                                if ($batch_filter) $where .= " AND enrollments.batch = '$batch_filter'";
                                if ($location_filter) $where .= " AND enrollments.enrolled_at = '$location_filter'";

                                $sql = "SELECT enrollments.*, users.firstname, users.lastname, users.email, users.profile_pic 
                                        FROM enrollments 
                                        JOIN users ON enrollments.user_id = users.id 
                                        $where 
                                        ORDER BY enrollments.enrolled_at ASC, enrollments.batch DESC";
                                $result = mysqli_query($conn, $sql);

                                if(mysqli_num_rows($result) > 0):
                                    while($row = mysqli_fetch_assoc($result)):
                                        $fullName = $row['firstname'] . ' ' . $row['lastname'];
                                        $p_sql = "SELECT SUM(amount) as paid FROM payments WHERE user_id = '{$row['user_id']}' AND status = 'paid'";
                                        $paid = mysqli_fetch_assoc(mysqli_query($conn, $p_sql))['paid'] ?? 0;
                                        $prog = ($paid / $base_fee) * 100;
                                        $avatar = $row['profile_pic'] ? 'uploads/profiles/'.$row['profile_pic'] : null;
                                ?>
                                <tr class="transition-all hover:bg-slate-50 group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <?php if($avatar): ?>
                                                <img src="<?= $avatar ?>" class="w-11 h-11 rounded-2xl object-cover ring-2 ring-white shadow-sm">
                                            <?php else: ?>
                                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                                    <?= strtoupper(substr($row['firstname'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-bold text-slate-900 tracking-tight"><?= $fullName ?></p>
                                                <p class="text-[11px] text-slate-400"><?= $row['email'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                                <?= $row['enrolled_at'] ?: 'Not Assigned' ?>
                                            </span>
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter bg-slate-100 px-2 py-0.5 rounded w-fit">
                                                <?= $row['batch'] ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="w-48">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-[10px] font-bold text-slate-600">₱<?= number_format($paid) ?> Paid</span>
                                                <span class="text-[10px] font-black <?= $prog >= 100 ? 'text-emerald-500' : 'text-blue-600' ?>"><?= round($prog) ?>%</span>
                                            </div>
                                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-blue-600 rounded-full transition-all duration-1000" style="width: <?= $prog ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <?php if($row['status'] === 'pending'): ?>
                                            <a href="?approve=<?= $row['id'] ?>&view=<?= $view ?>" class="bg-orange-500 hover:bg-orange-600 text-white text-[10px] font-black uppercase px-4 py-2 rounded-xl transition-all shadow-md shadow-orange-100">Approve</a>
                                        <?php else: ?>
                                            <div class="flex items-center justify-end gap-2 text-emerald-500">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                                                <span class="text-[10px] font-black uppercase">Enrolled</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="py-24 text-center">
                                        <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <p class="text-slate-400 font-bold">No students found.</p>
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