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
$base_fee = 5000; // Standard program fee

// --- ACTION HANDLER: APPROVAL ---
if (isset($_GET['approve'])) {
    $id = mysqli_real_escape_string($conn, $_GET['approve']);
    mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE id = '$id'");
    header("Location: " . $current_page . "?success=1&view=" . $view . "&batch=" . $batch_filter);
    exit();
}

// Fetch distinct batches for the dropdown
$batches_res = mysqli_query($conn, "SELECT DISTINCT batch FROM enrollments ORDER BY batch DESC");
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .glass-header { background: rgba(252, 252, 253, 0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-[#fcfcfd] text-slate-900 custom-scrollbar">

    <div class="flex min-h-screen">
        <?php include 'aside.php';?>

        <main class="flex-1 min-w-0">
            <div class="p-6 md:p-10 max-w-7xl mx-auto">
                
                <header class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-lg">Admin Portal</span>
                            <?php if(isset($_GET['success'])): ?>
                                <span class="text-emerald-500 text-[10px] font-bold uppercase animate-pulse">✓ Registry Updated</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="text-4xl font-[800] text-slate-900 tracking-tight">Student Registry</h2>
                        <p class="text-slate-500 mt-2 text-sm font-medium">Manage program approvals and monitor individual payment progress.</p>
                    </div>
                    
                    <div class="flex bg-slate-100 p-1.5 rounded-2xl shadow-inner">
                        <a href="?view=all&batch=<?= $batch_filter ?>" class="px-6 py-2.5 rounded-xl text-xs font-bold transition-all <?= ($view === 'all') ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700' ?>">All Records</a>
                        <a href="?view=pending&batch=<?= $batch_filter ?>" class="px-6 py-2.5 rounded-xl text-xs font-bold transition-all <?= ($view === 'pending') ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700' ?>">Pending Only</a>
                    </div>
                </header>

                <div class="flex flex-col md:flex-row gap-4 mb-8">
                    <div class="flex-1 relative">
                        <span class="absolute inset-y-0 left-5 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input type="text" id="studentSearch" placeholder="Search by name, email, or program..." class="w-full pl-14 pr-6 py-4 bg-white border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-blue-500/5 outline-none transition-all text-sm font-medium shadow-sm">
                    </div>
                    
                    <select onchange="location.href='?view=<?= $view ?>&batch=' + this.value" class="md:w-64 px-6 py-4 bg-white border border-slate-200 rounded-[1.5rem] text-sm font-bold outline-none cursor-pointer focus:border-blue-500 transition-all shadow-sm appearance-none">
                        <option value="">All Academic Batches</option>
                        <?php mysqli_data_seek($batches_res, 0); while($b = mysqli_fetch_assoc($batches_res)): ?>
                            <option value="<?= $b['batch'] ?>" <?= ($batch_filter == $b['batch']) ? 'selected' : '' ?>>Batch <?= $b['batch'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden transition-all">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 bg-slate-50/50">
                                    <th class="px-8 py-6">Student Profile</th>
                                    <th class="px-8 py-6">Academic Info</th>
                                    <th class="px-8 py-6">Payment Progress</th>
                                    <th class="px-8 py-6 text-right">Operations</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentTable" class="divide-y divide-slate-50">
                                <?php
                                $where_clause = ($view === 'pending') ? "WHERE enrollments.status = 'pending'" : "WHERE 1=1";
                                if ($batch_filter) { $where_clause .= " AND enrollments.batch = '$batch_filter'"; }

                                $sql = "SELECT enrollments.*, users.name, users.email 
                                        FROM enrollments 
                                        JOIN users ON enrollments.user_id = users.id 
                                        $where_clause 
                                        ORDER BY enrollments.batch DESC, enrollments.created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                
                                if(mysqli_num_rows($result) > 0):
                                    while($row = mysqli_fetch_assoc($result)):
                                        // Dynamic Finance Calculation
                                        $uid = $row['user_id'];
                                        $p_sql = "SELECT SUM(amount) as paid FROM payments WHERE user_id = '$uid' AND status = 'paid'";
                                        $total_paid = mysqli_fetch_assoc(mysqli_query($conn, $p_sql))['paid'] ?? 0;
                                        $balance = $base_fee - $total_paid;
                                        $progress = ($total_paid / $base_fee) * 100;
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-all group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-gradient-to-tr from-slate-100 to-white text-slate-400 border border-slate-200 rounded-2xl flex items-center justify-center font-bold text-lg group-hover:border-blue-400 group-hover:text-blue-600 transition-all shadow-sm">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-sm tracking-tight"><?= $row['name'] ?></p>
                                                <p class="text-[11px] text-slate-400 font-medium"><?= $row['email'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="inline-flex px-2.5 py-1 bg-slate-900 text-white text-[9px] font-black rounded-md uppercase mb-1.5">Batch <?= $row['batch'] ?></div>
                                        <p class="text-xs font-bold text-slate-700"><?= $row['program_type'] ?></p>
                                    </td>
                                    <td class="px-8 py-6 min-w-[220px]">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-[11px] font-bold text-slate-900">₱<?= number_format($total_paid) ?> <span class="text-slate-300 font-normal">/ ₱<?= number_format($base_fee) ?></span></span>
                                            <span class="text-[10px] font-black <?= ($balance <= 0) ? 'text-emerald-500' : 'text-blue-600' ?>"><?= round($progress) ?>%</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                            <div class="h-full bg-blue-600 rounded-full transition-all duration-700" style="width: <?= $progress ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button onclick="openHistory(<?= $uid ?>, '<?= addslashes($row['name']) ?>')" class="p-2.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Payment Timeline">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </button>
                                            
                                            <?php if($row['status'] === 'pending'): ?>
                                                <a href="?approve=<?= $row['id'] ?>&view=<?= $view ?>&batch=<?= $batch_filter ?>" onclick="return confirm('Approve this enrollment?')" class="bg-blue-600 hover:bg-slate-900 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-blue-100">Approve</a>
                                            <?php else: ?>
                                                <div class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase border border-emerald-100">Verified</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" class="py-32 text-center">
                                        <p class="text-slate-300 font-bold text-sm italic">No students found matching your criteria.</p>
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

    <div id="hOverlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300">
        <div id="hSidebar" class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl translate-x-full transition-transform duration-300 flex flex-col">
            <div class="p-8 border-b border-slate-100 flex items-center justify-between glass-header sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900">Payment Timeline</h3>
                    <p id="hName" class="text-sm text-blue-600 font-bold mt-1"></p>
                </div>
                <button onclick="closeHistory()" class="p-2.5 hover:bg-slate-100 rounded-2xl transition-colors text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="hBody" class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                </div>
        </div>
    </div>

    <script>
        // Real-time local filtering
        document.getElementById('studentSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#enrollmentTable tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });

        // AJAX Sidebar Logic
        async function openHistory(userId, name) {
            const overlay = document.getElementById('hOverlay');
            const sidebar = document.getElementById('hSidebar');
            const body = document.getElementById('hBody');
            
            document.getElementById('hName').innerText = name;
            body.innerHTML = '<div class="flex flex-col items-center justify-center py-20"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mb-4"></div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fetching Ledgers...</p></div>';
            
            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.add('opacity-100');
                sidebar.classList.remove('translate-x-full');
            }, 10);

            try {
                const response = await fetch(`get_payments.php?user_id=${userId}`);
                body.innerHTML = await response.text();
            } catch (err) {
                body.innerHTML = '<p class="text-rose-500 font-bold p-10 text-center">Connection error. Could not load data.</p>';
            }
        }

        function closeHistory() {
            const overlay = document.getElementById('hOverlay');
            const sidebar = document.getElementById('hSidebar');
            overlay.classList.remove('opacity-100');
            sidebar.classList.add('translate-x-full');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    </script>

    <script>
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            if(!mobileSidebar || !overlay) return;
            mobileSidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        if(openBtn) openBtn.addEventListener('click', toggleMenu);
        if(closeBtn) closeBtn.addEventListener('click', toggleMenu);
        if(overlay) overlay.addEventListener('click', toggleMenu);
    </script>
</body>
</html>