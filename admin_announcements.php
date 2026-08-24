<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Logic to Post Announcement
if (isset($_POST['post_announcement'])) {
    csrf_verify();

    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $category = $_POST['category'] ?? '';
    $audience = $_POST['audience'] ?? '';

    if ($title === '' || $message === '' || mb_strlen($title) > 255) {
        header("Location: admin_announcements.php?error=invalid");
        exit();
    }
    if (!in_array($category, ['General', 'Urgent', 'Event', 'Academic'], true)) {
        $category = 'General';
    }
    if (!in_array($audience, ['General', 'Students'], true)) {
        $audience = 'General';
    }

    $stmt = $conn->prepare("INSERT INTO announcements (title, message, category, audience, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $title, $message, $category, $audience);

    if ($stmt->execute()) {
        log_activity($conn, 'announcement.create', "Posted announcement: $title", [
            'entity_type' => 'announcement',
            'entity_id' => $stmt->insert_id,
        ]);
        header("Location: admin_announcements.php?posted=1");
        exit();
    }

    error_log('Announcement insert failed: ' . $stmt->error);
    header("Location: admin_announcements.php?error=failed");
    exit();
}

// Logic to Delete
if (isset($_POST['delete_id'])) {
    csrf_verify();

    $id = intval($_POST['delete_id']);
    $announcement_result = mysqli_query($conn, "SELECT title FROM announcements WHERE id = '" . intval($id) . "' LIMIT 1");
    $announcement_row = mysqli_fetch_assoc($announcement_result);

    if ($announcement_row) {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        log_activity($conn, 'announcement.delete', null, [
            'entity_type' => 'announcement',
            'entity_id' => $id,
            'entity_label' => $announcement_row['title'],
        ]);
    }

    header("Location: admin_announcements.php?deleted=1");
    exit();
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Announcements | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; background-color: #020617; } /* slate-950 */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; } /* slate-800 */
    </style>
</head>
<body class="bg-slate-950 text-white antialiased">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php';?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-8 lg:p-12">
            <div class="max-w-12xl mx-auto">
                
                <header class="mb-8 md:mb-12 flex items-center justify-between">
                    <div class="lg:text-center w-full">
                        <h2 class="text-3xl font-[800] text-white tracking-tight">Broadcast Center</h2>
                        <p class="text-slate-400 mt-1">Keep students informed with real-time updates.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-slate-900/60 border border-slate-800 rounded-2xl shadow-sm ml-4 backdrop-blur-xl">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </header>

                <div class="bg-slate-900/60 rounded-[2rem] shadow-sm border border-slate-800 p-6 md:p-10 mb-10 backdrop-blur-xl relative overflow-hidden">
                    <!-- Glow effects decoratively positioned inside card context layer -->
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <form action="" method="POST" class="space-y-6 relative z-10">
                        <?= csrf_field() ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Announcement Title</label>
                                <input type="text" name="title" required placeholder="e.g. Midterm Schedule Update" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition font-semibold text-white placeholder:text-slate-600">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Category</label>
                                <select name="category" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition font-semibold appearance-none text-white">
                                    <option value="General" class="bg-slate-900 text-white">General News</option>
                                    <option value="Urgent" class="bg-slate-900 text-white">Urgent Alert</option>
                                    <option value="Event" class="bg-slate-900 text-white">Event/Holiday</option>
                                    <option value="Academic" class="bg-slate-900 text-white">Academic</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Target Audience</label>
                                <select name="audience" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition font-semibold appearance-none text-white">
                                    <option value="General" class="bg-slate-900 text-white">Landing Page (Public)</option>
                                    <option value="Students" class="bg-slate-900 text-white">Student Portal Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-500 tracking-widest px-1">Message Detail</label>
                            <textarea name="message" rows="4" required placeholder="Write your announcement here..." class="w-full px-5 py-4 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition font-medium text-white placeholder:text-slate-600"></textarea>
                        </div>
                        <button type="submit" name="post_announcement" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-[0.2em] shadow-xl shadow-blue-950/40 transition-all flex items-center justify-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            Post Announcement
                        </button>
                    </form>
                </div>

                <div class="space-y-6">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-2">Post History</h4>
                    <div class="grid grid-cols-1 gap-4">
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
                        while($row = mysqli_fetch_assoc($res)):
                            $badgeClasses = match($row['category']) {
                                'Urgent' => 'bg-red-950/30 text-red-400 border border-red-900/40',
                                'Event' => 'bg-purple-950/30 text-purple-400 border border-purple-900/40',
                                'Academic' => 'bg-orange-950/30 text-orange-400 border border-orange-900/40',
                                default => 'bg-blue-950/30 text-blue-400 border border-blue-900/40'
                            };
                            $iconBgClasses = match($row['category']) {
                                'Urgent' => 'bg-red-950/50 text-red-400 border border-red-900/50',
                                'Event' => 'bg-purple-950/50 text-purple-400 border border-purple-900/50',
                                'Academic' => 'bg-orange-950/50 text-orange-400 border border-orange-900/50',
                                default => 'bg-blue-950/50 text-blue-400 border border-blue-900/50'
                            };
                        ?>
                        <div class="bg-slate-900/60 p-5 md:p-7 rounded-[2rem] border border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 group transition-all hover:bg-slate-900 backdrop-blur-xl">
                            <div class="flex gap-5">
                                <div class="w-12 h-12 <?= $iconBgClasses ?> rounded-2xl flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <h5 class="font-bold text-white"><?= htmlspecialchars($row['title']) ?></h5>
                                        <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-lg <?= $badgeClasses ?>"><?= $row['category'] ?></span>
                                        <span class="text-[9px] font-black uppercase tracking-widest bg-slate-950 text-slate-400 px-2 py-0.5 rounded-lg border border-slate-800">Visible To: <?= $row['audience'] ?></span>
                                    </div>
                                    <p class="text-sm text-slate-300 mt-2 leading-relaxed w-full"><?= htmlspecialchars($row['message']) ?></p>
                                    <div class="flex items-center gap-2 mt-4 text-slate-500 font-bold text-[10px] uppercase tracking-tighter">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?= date('M d, Y • h:i A', strtotime($row['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="" class="self-end md:self-center" onsubmit="return confirm('Remove this announcement permanently?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="delete_id" value="<?= (int) $row['id'] ?>">
                                <button type="submit" class="p-3 text-slate-600 hover:text-red-400 hover:bg-red-950/20 rounded-xl transition-all md:opacity-0 md:group-hover:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Setup SweetAlert2 Toast Layout Base Configurations for Theme Consistency
        const customSwalMixin = Swal.mixin({
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#2563eb'
        });

        const Toast = customSwalMixin.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2800,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Parse URL Parameters to trigger toasts dynamically
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('posted') === '1') {
            Toast.fire({
                icon: 'success',
                title: 'Announcement posted successfully'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (urlParams.get('deleted') === '1') {
            Toast.fire({
                icon: 'info',
                title: 'Announcement has been deleted'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Menu Toggle Logic
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar(state) {
            if(state) {
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