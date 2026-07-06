<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$per_page = 25;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$filter_action = trim($_GET['action'] ?? '');
$filter_role = trim($_GET['role'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = [];
if ($filter_action !== '') {
    $escaped_action = mysqli_real_escape_string($conn, $filter_action);
    $where[] = "al.action = '$escaped_action'";
}
if ($filter_role !== '') {
    $escaped_role = mysqli_real_escape_string($conn, $filter_role);
    $where[] = "al.user_role = '$escaped_role'";
}
if ($search !== '') {
    $escaped_search = mysqli_real_escape_string($conn, $search);
    $where[] = "(al.description LIKE '%$escaped_search%' OR u.firstname LIKE '%$escaped_search%' OR u.lastname LIKE '%$escaped_search%' OR u.email LIKE '%$escaped_search%' OR al.action LIKE '%$escaped_search%')";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total_logs = 0;
$logs = [];
$action_options = [];
$table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'activity_logs'")) > 0;

if ($table_exists) {
    $count_query = "SELECT COUNT(*) as total
                    FROM activity_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    $where_sql";
    $total_logs = (int) (mysqli_fetch_assoc(mysqli_query($conn, $count_query))['total'] ?? 0);

    $logs_query = "SELECT al.*, u.firstname, u.lastname, u.email
                   FROM activity_logs al
                   LEFT JOIN users u ON al.user_id = u.id
                   $where_sql
                   ORDER BY al.created_at DESC
                   LIMIT $per_page OFFSET $offset";
    $logs = mysqli_query($conn, $logs_query);

    $actions_res = mysqli_query($conn, "SELECT DISTINCT action FROM activity_logs ORDER BY action ASC");
    while ($row = mysqli_fetch_assoc($actions_res)) {
        $action_options[] = $row['action'];
    }
}

$total_pages = max(1, (int) ceil($total_logs / $per_page));

function activity_log_query_string($overrides = []) {
    $params = array_merge([
        'action' => $_GET['action'] ?? '',
        'role' => $_GET['role'] ?? '',
        'q' => $_GET['q'] ?? '',
        'page' => $_GET['page'] ?? 1,
    ], $overrides);

    $params = array_filter($params, function ($value) {
        return $value !== '' && $value !== null;
    });

    return http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Activity Log | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; background-color: #020617; } /* slate-950 */
        .log-card { background: rgba(15, 23, 42, 0.6); border: 1px solid #1e293b; border-radius: 24px; backdrop-filter: blur(24px); } /* slate-900/60 & slate-800 */

        @media (max-width: 1024px) {
            .responsive-table thead { display: none; }
            .responsive-table tr { display: block; margin-bottom: 1rem; border: 1px solid #1e293b; border-radius: 16px; padding: 1rem; background: rgba(15, 23, 42, 0.4); }
            .responsive-table td { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border: none; text-align: right; }
            .responsive-table td::before { content: attr(data-label); font-weight: 800; font-size: 10px; text-transform: uppercase; color: #64748b; text-align: left; margin-right: 1rem; } /* slate-500 */
        }
    </style>
</head>
<body class="text-white antialiased">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php'; ?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-10">
            <div class="max-w-7xl mx-auto">
                <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-10">
                    <div class="flex items-center justify-between w-full lg:w-auto">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400 mb-2 block">Audit Trail</span>
                            <h2 class="text-3xl md:text-4xl font-[800] text-white tracking-tight">Activity Log</h2>
                            <p class="text-slate-400 font-medium mt-1">Track logins, enrollments, payments, and admin actions.</p>
                        </div>
                        <button id="openMenu" class="lg:hidden p-3 bg-slate-900/60 border border-slate-800 rounded-2xl shadow-sm backdrop-blur-xl">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                        </button>
                    </div>
                </header>

                <?php if (!$table_exists): ?>
                    <div class="log-card p-8 text-center">
                        <p class="text-slate-400 font-medium">The activity log table has not been created yet.</p>
                        <p class="text-slate-500 text-sm mt-2">Run <code class="bg-slate-950 border border-slate-800 text-slate-400 px-2 py-1 rounded">migrations/add_activity_logs.sql</code> against your database.</p>
                    </div>
                <?php else: ?>
                    <form method="GET" class="log-card p-6 mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Search</label>
                            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="User, email, or details"
                                   class="mt-2 w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl outline-none focus:border-blue-500 font-medium text-white placeholder:text-slate-600">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Action</label>
                            <select name="action" class="mt-2 w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl outline-none focus:border-blue-500 font-medium text-white">
                                <option value="" class="bg-slate-900 text-white">All actions</option>
                                <?php foreach ($action_options as $action_option): ?>
                                    <option value="<?= htmlspecialchars($action_option) ?>" <?= $filter_action === $action_option ? 'selected' : '' ?> class="bg-slate-900 text-white">
                                        <?= htmlspecialchars(activity_action_label($action_option)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Role</label>
                            <select name="role" class="mt-2 w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-2xl outline-none focus:border-blue-500 font-medium text-white">
                                <option value="" class="bg-slate-900 text-white">All roles</option>
                                <option value="admin" <?= $filter_role === 'admin' ? 'selected' : '' ?> class="bg-slate-900 text-white">Admin</option>
                                <option value="student" <?= $filter_role === 'student' ? 'selected' : '' ?> class="bg-slate-900 text-white">Student</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-3">
                            <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-950/40 transition">Filter</button>
                            <a href="admin_activity_log.php" class="px-4 py-3 bg-slate-800 border border-slate-700/60 text-slate-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-700 hover:text-white transition">Reset</a>
                        </div>
                    </form>

                    <div class="log-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-400"><?= number_format($total_logs) ?> total entries</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left responsive-table">
                                <thead>
                                    <tr class="text-[10px] font-black uppercase text-slate-500 tracking-[0.2em] bg-slate-950/40 border-b border-slate-800">
                                        <th class="px-6 py-4">When</th>
                                        <th class="px-6 py-4">User</th>
                                        <th class="px-6 py-4">Action</th>
                                        <th class="px-6 py-4">Details</th>
                                        <th class="px-6 py-4">IP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60">
                                    <?php if ($logs && mysqli_num_rows($logs) > 0): ?>
                                        <?php while ($log = mysqli_fetch_assoc($logs)): ?>
                                            <?php
                                            $display_name = trim(($log['firstname'] ?? '') . ' ' . ($log['lastname'] ?? ''));
                                            if ($display_name === '' && !empty($log['email'])) {
                                                $display_name = $log['email'];
                                            } elseif ($display_name === '') {
                                                $display_name = 'System / Guest';
                                            }
                                            ?>
                                            <tr class="hover:bg-slate-900/40 transition">
                                                <td class="px-6 py-4 text-xs text-slate-400 font-semibold whitespace-nowrap" data-label="When">
                                                    <?= date('M d, Y g:i A', strtotime($log['created_at'])) ?>
                                                </td>
                                                <td class="px-6 py-4" data-label="User">
                                                    <p class="font-bold text-sm text-white"><?= htmlspecialchars($display_name) ?></p>
                                                    <?php if (!empty($log['user_role'])): ?>
                                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500"><?= htmlspecialchars($log['user_role']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4" data-label="Action">
                                                    <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-blue-950/40 text-blue-400 border border-blue-900/30">
                                                        <?= htmlspecialchars(activity_action_label($log['action'])) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-400 max-w-md leading-relaxed" data-label="Details">
                                                    <?= htmlspecialchars(activity_log_display_description($conn, $log)) ?>
                                                </td>
                                                <td class="px-6 py-4 text-xs text-slate-500 font-mono" data-label="IP">
                                                    <?= htmlspecialchars($log['ip_address'] ?? '—') ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-slate-500 font-medium">No activity found for the selected filters.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="px-6 py-4 border-t border-slate-800 flex items-center justify-between gap-4">
                                <p class="text-xs text-slate-500 font-semibold">Page <?= $page ?> of <?= $total_pages ?></p>
                                <div class="flex gap-2">
                                    <?php if ($page > 1): ?>
                                        <a href="admin_activity_log.php?<?= activity_log_query_string(['page' => $page - 1]) ?>"
                                           class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700/60 text-slate-300 text-xs font-bold uppercase tracking-widest hover:bg-slate-700 hover:text-white transition">Previous</a>
                                    <?php endif; ?>
                                    <?php if ($page < $total_pages): ?>
                                        <a href="admin_activity_log.php?<?= activity_log_query_string(['page' => $page + 1]) ?>"
                                           class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold uppercase tracking-widest shadow-xl shadow-blue-950/20 transition">Next</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
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
    </script>
</body>
</html>