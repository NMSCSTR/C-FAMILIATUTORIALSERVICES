<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Gather table metadata
$tables_info = [];
$res = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($res)) {
    $tname = $row[0];
    $count_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM `$tname`");
    $count = (int)(mysqli_fetch_assoc($count_res)['c'] ?? 0);

    $size_res = mysqli_query($conn, "SELECT
        ROUND((data_length + index_length) / 1024, 2) AS size_kb
        FROM information_schema.TABLES
        WHERE table_schema = DATABASE() AND table_name = '" . mysqli_real_escape_string($conn, $tname) . "'");
    $size_kb = (float)(mysqli_fetch_assoc($size_res)['size_kb'] ?? 0);

    $tables_info[$tname] = ['rows' => $count, 'size_kb' => $size_kb];
}

$total_rows  = array_sum(array_column($tables_info, 'rows'));
$total_size  = array_sum(array_column($tables_info, 'size_kb'));
$table_count = count($tables_info);

// Last backup from activity log (optional, gracefully skipped)
$last_backup = null;
$al_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'")) > 0;
if ($al_exists) {
    $lb = mysqli_query($conn, "SELECT created_at FROM activity_logs WHERE action='backup' ORDER BY created_at DESC LIMIT 1");
    if ($lb && mysqli_num_rows($lb) > 0) {
        $last_backup = mysqli_fetch_assoc($lb)['created_at'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Database Backup | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; background-color: #020617; } /* slate-950 */
        .card { background: rgba(15, 23, 42, 0.6); border: 1px solid #1e293b; border-radius: 24px; backdrop-filter: blur(24px); } /* slate-900/60 & slate-800 */

        .table-row-check:checked + label .check-box { background: #2563eb; border-color: #2563eb; }
        .table-row-check:checked + label .check-icon { display: block; }
        .check-icon { display: none; }

        .progress-bar-inner { animation: shimmer 1.8s infinite linear; background-size: 200% 100%;
            background-image: linear-gradient(90deg, #3b82f6 0%, #6366f1 50%, #3b82f6 100%); }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        /* Responsive sidebar */
        @media (max-width: 1024px) {
            #mobileSidebar { transform: translateX(-100%); }
            #mobileSidebar.open { transform: translateX(0); }
        }

        /* Pulse animation for download button */
        @keyframes btn-glow { 0%,100%{box-shadow:0 0 0 0 rgba(37,99,235,.2)} 50%{box-shadow:0 0 0 8px rgba(37,99,235,0)} }
        .btn-glow { animation: btn-glow 2s ease-in-out infinite; }

        .stat-card { transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-2px); border-color: #334155; } /* slate-700 */
    </style>
</head>
<body class="text-white antialiased">

<div class="flex min-h-screen relative">
    <?php include 'aside.php'; ?>

    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

    <main class="flex-1 p-4 md:p-10">
        <div class="max-w-12xl mx-auto">

            <!-- Header -->
            <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10">
                <div class="flex items-center justify-between w-full lg:w-auto">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400 mb-2 block">System</span>
                        <h2 class="text-3xl md:text-4xl font-[800] text-white tracking-tight">Database Backup</h2>
                        <p class="text-slate-400 font-medium mt-1">Export your database as a ready-to-restore SQL file.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-slate-900/60 border border-slate-800 rounded-2xl shadow-sm backdrop-blur-xl">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="card p-5 stat-card">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Tables</p>
                    <p class="text-3xl font-[800] text-white"><?= $table_count ?></p>
                </div>
                <div class="card p-5 stat-card">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Total Rows</p>
                    <p class="text-3xl font-[800] text-white"><?= number_format($total_rows) ?></p>
                </div>
                <div class="card p-5 stat-card">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">DB Size</p>
                    <p class="text-3xl font-[800] text-white"><?= $total_size >= 1024 ? round($total_size/1024,1).' MB' : $total_size.' KB' ?></p>
                </div>
                <div class="card p-5 stat-card">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Last Backup</p>
                    <p class="text-sm font-bold text-white leading-tight mt-0.5">
                        <?= $last_backup ? date('M d, Y', strtotime($last_backup)) : '—' ?>
                    </p>
                    <?php if ($last_backup): ?>
                        <p class="text-[11px] text-slate-500 font-semibold mt-0.5"><?= date('g:i A', strtotime($last_backup)) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                <!-- LEFT: Table selector -->
                <div class="xl:col-span-2 card p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-[800] text-white text-lg">Select Tables</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Choose which tables to include in the backup.</p>
                        </div>
                        <div class="flex gap-2">
                            <button id="selectAllBtn" onclick="toggleAll(true)"
                                class="text-[10px] font-black uppercase tracking-widest px-3 py-2 rounded-xl bg-blue-950/40 text-blue-400 border border-blue-900/30 hover:bg-blue-900/40 transition">
                                All
                            </button>
                            <button onclick="toggleAll(false)"
                                class="text-[10px] font-black uppercase tracking-widest px-3 py-2 rounded-xl bg-slate-800 text-slate-400 border border-slate-700/60 hover:bg-slate-700 hover:text-white transition">
                                None
                            </button>
                        </div>
                    </div>

                    <div id="tableList" class="space-y-2 max-h-[460px] overflow-y-auto pr-1 custom-scrollbar">
                        <?php foreach ($tables_info as $tname => $info): ?>
                        <div class="flex items-center gap-3 p-3 rounded-2xl hover:bg-slate-900/40 transition group cursor-pointer border border-transparent hover:border-slate-800/60" onclick="toggleTable('tbl_<?= htmlspecialchars($tname) ?>')">
                            <input
                                type="checkbox"
                                id="tbl_<?= htmlspecialchars($tname) ?>"
                                name="tables[]"
                                value="<?= htmlspecialchars($tname) ?>"
                                checked
                                class="table-checkbox w-5 h-5 rounded-lg border-2 border-slate-700 bg-slate-950 text-blue-500 accent-blue-500 cursor-pointer focus:ring-0 focus:ring-offset-0"
                                onclick="event.stopPropagation(); updateSummary();"
                            >
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm text-white truncate"><?= htmlspecialchars($tname) ?></p>
                                <p class="text-[11px] text-slate-500 font-semibold">
                                    <?= number_format($info['rows']) ?> row<?= $info['rows'] !== 1 ? 's' : '' ?> &bull;
                                    <?= $info['size_kb'] ?> KB
                                </p>
                            </div>
                            <!-- Mini bar -->
                            <?php $bar_pct = $total_rows > 0 ? round(($info['rows'] / max($total_rows,1)) * 100) : 0; ?>
                            <div class="w-24 hidden sm:block">
                                <div class="h-1.5 bg-slate-950 border border-slate-800/80 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width:<?= max($bar_pct, $info['rows'] > 0 ? 5 : 0) ?>%"></div>
                                </div>
                                <p class="text-[10px] text-slate-500 font-semibold mt-1 text-right"><?= $bar_pct ?>%</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- RIGHT: Options & Download -->
                <div class="flex flex-col gap-4">

                    <!-- Options card -->
                    <div class="card p-6">
                        <h3 class="font-[800] text-white text-lg mb-4">Backup Options</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-2">Format</label>
                                <div class="flex gap-2">
                                    <button class="flex-1 py-2.5 rounded-xl text-xs font-black uppercase bg-gradient-to-r from-blue-600 to-indigo-600 text-white tracking-widest">SQL</button>
                                    <button disabled class="flex-1 py-2.5 rounded-xl text-xs font-black uppercase bg-slate-950 border border-slate-800 text-slate-600 tracking-widest cursor-not-allowed" title="Coming soon">CSV</button>
                                </div>
                            </div>

                            <div class="border-t border-slate-800/80 pt-4">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 block mb-3">Includes</label>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-slate-400 font-semibold">DROP TABLE statements</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-slate-400 font-semibold">CREATE TABLE structure</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-slate-400 font-semibold">INSERT data rows</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-slate-400 font-semibold">Foreign key constraints</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-slate-400 font-semibold">UTF-8 character encoding</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary + Download -->
                    <div class="card p-6">
                        <h3 class="font-[800] text-white text-lg mb-1">Summary</h3>
                        <p id="summaryText" class="text-sm text-slate-400 font-semibold mb-5">
                            <?= $table_count ?> tables selected &bull; <?= number_format($total_rows) ?> rows
                        </p>

                        <!-- Progress bar placeholder (shown during download) -->
                        <div id="downloadProgress" class="hidden mb-4">
                            <div class="h-2 bg-slate-950 border border-slate-800 rounded-full overflow-hidden">
                                <div class="progress-bar-inner h-full w-full rounded-full"></div>
                            </div>
                            <p class="text-[11px] text-blue-400 font-bold mt-2">Generating backup…</p>
                        </div>

                        <button id="downloadBtn" onclick="startDownload()"
                            class="btn-glow w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white rounded-2xl font-[800] text-sm uppercase tracking-widest shadow-xl shadow-blue-950/40 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Backup
                        </button>

                        <p class="text-[11px] text-slate-500 font-semibold text-center mt-3">
                            File: cfts_backup_<span class="font-mono"><?= date('Y-m-d') ?></span>.sql
                        </p>
                    </div>

                    <!-- Warning card -->
                    <div class="rounded-2xl border border-amber-900/40 bg-amber-950/20 p-4 backdrop-blur-xl">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <div>
                                <p class="text-xs font-black text-amber-400 uppercase tracking-wide mb-1">Security Note</p>
                                <p class="text-xs text-amber-500/80 font-semibold leading-relaxed">
                                    The backup contains sensitive data including hashed passwords. Store it securely and restrict access.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How to restore section -->
            <div class="card p-6 mt-6">
                <h3 class="font-[800] text-white text-lg mb-4">How to Restore</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-950/50 border border-slate-900">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-sm mb-3">1</div>
                        <p class="font-bold text-sm text-white mb-1">Open phpMyAdmin</p>
                        <p class="text-xs text-slate-400 font-semibold leading-relaxed">Navigate to your database in phpMyAdmin and click the <strong>Import</strong> tab.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/50 border border-slate-900">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-sm mb-3">2</div>
                        <p class="font-bold text-sm text-white mb-1">Upload .sql file</p>
                        <p class="text-xs text-slate-400 font-semibold leading-relaxed">Choose the downloaded <code class="bg-slate-900 border border-slate-800 text-slate-300 px-1 rounded">.sql</code> file and click <strong>Go</strong> to begin the import.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/50 border border-slate-900">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-sm mb-3">3</div>
                        <p class="font-bold text-sm text-white mb-1">Verify the data</p>
                        <p class="text-xs text-slate-400 font-semibold leading-relaxed">Browse each table to confirm all records were imported successfully.</p>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    // --- Sidebar toggle ---
    const openBtn = document.getElementById('openMenu');
    const closeBtn = document.getElementById('closeMenu');
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar(state) {
        if (state) {
            sidebar?.classList.remove('-translate-x-full');
            overlay?.classList.remove('hidden');
            setTimeout(() => overlay?.classList.add('opacity-100'), 10);
        } else {
            sidebar?.classList.add('-translate-x-full');
            overlay?.classList.remove('opacity-100');
            setTimeout(() => overlay?.classList.add('hidden'), 300);
        }
    }
    openBtn?.addEventListener('click', () => toggleSidebar(true));
    closeBtn?.addEventListener('click', () => toggleSidebar(false));
    overlay?.addEventListener('click', () => toggleSidebar(false));

    // --- Table selection ---
    function toggleAll(checked) {
        document.querySelectorAll('.table-checkbox').forEach(cb => cb.checked = checked);
        updateSummary();
    }

    function toggleTable(id) {
        const cb = document.getElementById(id);
        if (cb) { cb.checked = !cb.checked; updateSummary(); }
    }

    function updateSummary() {
        const checked = document.querySelectorAll('.table-checkbox:checked');
        const total = <?= $total_rows ?>;
        const allRowCounts = <?= json_encode(array_map(fn($i) => $i['rows'], $tables_info)) ?>;
        const tableNames = <?= json_encode(array_keys($tables_info)) ?>;

        let rowCount = 0;
        checked.forEach(cb => {
            const idx = tableNames.indexOf(cb.value);
            if (idx >= 0) rowCount += allRowCounts[Object.keys(allRowCounts)[idx]] || 0;
        });

        const summaryEl = document.getElementById('summaryText');
        const btn = document.getElementById('downloadBtn');
        if (checked.length === 0) {
            summaryEl.textContent = 'No tables selected';
            btn.disabled = true;
            btn.classList.add('opacity-40', 'cursor-not-allowed');
            btn.classList.remove('btn-glow');
        } else {
            summaryEl.textContent = `${checked.length} table${checked.length !== 1 ? 's' : ''} selected`;
            btn.disabled = false;
            btn.classList.remove('opacity-40', 'cursor-not-allowed');
            btn.classList.add('btn-glow');
        }
    }

    function startDownload() {
        const checked = document.querySelectorAll('.table-checkbox:checked');
        if (checked.length === 0) { return; }

        const tables = Array.from(checked).map(cb => cb.value).join(',');
        const csrfToken = <?= json_encode(csrf_token()) ?>;
        const url = `backup_handler.php?tables=${encodeURIComponent(tables)}&csrf_token=${encodeURIComponent(csrfToken)}`;

        // Show progress bar briefly
        const progress = document.getElementById('downloadProgress');
        const btn = document.getElementById('downloadBtn');
        progress.classList.remove('hidden');
        btn.disabled = true;
        btn.classList.add('opacity-50');

        // Use hidden iframe to trigger download without leaving the page
        let iframe = document.getElementById('dlFrame');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'dlFrame';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url;

        // Hide progress bar after a moment
        setTimeout(() => {
            progress.classList.add('hidden');
            btn.disabled = false;
            btn.classList.remove('opacity-50');

            // Show a brief success flash
            const originalText = btn.innerHTML;
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg> Backup Downloaded!`;
            btn.classList.replace('from-blue-600', 'from-emerald-600');
            btn.classList.replace('to-indigo-600', 'to-emerald-500');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.replace('from-emerald-600', 'from-blue-600');
                btn.classList.replace('to-emerald-500', 'to-indigo-600');
            }, 3000);
        }, 2200);
    }

    // Init
    updateSummary();
</script>
</body>
</html>