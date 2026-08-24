<?php
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/uploads.php';
secure_session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Logic to Save Post
if (isset($_POST['save_post'])) {
    csrf_verify();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $file_name = null;

    if ($title === '' || mb_strlen($title) > 255) {
        header("Location: admin_posts.php?error=invalid");
        exit();
    }

    if (!empty($_FILES['resource']['name'])) {
        [$ok, $file_name, $upload_error] = store_uploaded_file(
            $_FILES['resource'],
            'uploads/resources/',
            ['pdf', 'doc', 'docx', 'zip', 'rar']
        );

        if (!$ok) {
            header("Location: admin_posts.php?error=upload");
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO posts (title, content, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $file_name);

    if ($stmt->execute()) {
        $post_id = $stmt->insert_id;
        log_activity($conn, 'post.create', "Created learning resource: $title", [
            'entity_type' => 'post',
            'entity_id' => $post_id,
        ]);
        header("Location: admin_posts.php?success=1");
        exit();
    }

    if ($file_name && is_file('uploads/resources/' . $file_name)) {
        @unlink('uploads/resources/' . $file_name);
    }
    error_log('Post insert failed: ' . $stmt->error);
    header("Location: admin_posts.php?error=failed");
    exit();
}

// Logic to Delete
if (isset($_POST['delete_id'])) {
    csrf_verify();

    $id = intval($_POST['delete_id']);
    $post_result = mysqli_query($conn, "SELECT title, file_path FROM posts WHERE id = '" . intval($id) . "' LIMIT 1");
    $post_row = mysqli_fetch_assoc($post_result);

    if ($post_row) {
        if (!empty($post_row['file_path']) && is_file('uploads/resources/' . $post_row['file_path'])) {
            @unlink('uploads/resources/' . $post_row['file_path']);
        }

        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        log_activity($conn, 'post.delete', null, [
            'entity_type' => 'post',
            'entity_id' => $id,
            'entity_label' => $post_row['title'] ?? 'Unknown resource',
        ]);
    }

    header("Location: admin_posts.php?deleted=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/app.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Manage Resources | C-Familia Admin</title>
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
                
                <header class="mb-10 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-[800] text-white tracking-tight">Resource Management</h2>
                        <p class="text-slate-400 mt-1">Upload guides, learning modules, and community posts.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-slate-900/60 border border-slate-800 rounded-2xl shadow-sm ml-4 backdrop-blur-xl">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-4">
                        <div class="bg-slate-900/60 p-6 md:p-8 rounded-[2.5rem] border border-slate-800 sticky top-10 backdrop-blur-xl">
                            <h3 class="font-bold text-white mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Create New Post
                            </h3>
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                                <?= csrf_field() ?>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-2 block px-1">Post Title</label>
                                    <input type="text" name="title" required class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold text-white placeholder:text-slate-600">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-2 block px-1">Content Description</label>
                                    <textarea name="content" rows="4" required class="w-full px-5 py-4 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-medium text-white placeholder:text-slate-600"></textarea>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-2 block px-1">Attachment (PDF/Image)</label>
                                    <div class="bg-slate-950 border border-slate-800 p-4 rounded-2xl">
                                        <input type="file" name="resource" class="text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gradient-to-r file:from-blue-600 file:to-indigo-600 file:text-white cursor-pointer w-full">
                                    </div>
                                </div>
                                <button type="submit" name="save_post" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-950/40 transition-all mt-4">Publish Post</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-8 space-y-6">
                        <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest px-2">Published Resources</h4>
                        
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");
                        if(mysqli_num_rows($res) > 0):
                            while($row = mysqli_fetch_assoc($res)):
                        ?>
                        <div class="bg-slate-900/60 p-6 rounded-[2rem] border border-slate-800 group flex flex-col md:flex-row items-start justify-between gap-6 transition-all backdrop-blur-xl">
                            <div class="flex gap-5 w-full">
                                <div class="w-14 h-14 bg-slate-950 rounded-2xl flex items-center justify-center text-slate-500 shrink-0 border border-slate-800">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-bold text-white leading-tight text-lg group-hover:text-blue-400 transition-colors"><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></h5>
                                    <p class="text-sm text-slate-400 mt-2 leading-relaxed"><?= nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8')) ?></p>

                                    <?php if($row['file_path']): ?>
                                    <div class="mt-4">
                                        <a href="uploads/resources/<?= rawurlencode($row['file_path']) ?>" target="_blank" class="inline-flex items-center gap-2 text-[10px] font-black text-blue-400 uppercase tracking-widest bg-blue-950/20 border border-blue-900/30 px-4 py-2 rounded-xl hover:bg-gradient-to-r hover:from-blue-600 hover:to-indigo-600 hover:text-white transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download Resource
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center gap-2 mt-6 text-[10px] text-slate-500 font-bold uppercase tracking-tighter">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="" class="self-end md:self-center" onsubmit="return confirmAction(event, 'Delete this resource permanently?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="delete_id" value="<?= (int) $row['id'] ?>">
                                <button type="submit" class="p-3 text-slate-600 hover:text-red-400 hover:bg-red-950/20 rounded-2xl transition-all md:opacity-0 md:group-hover:opacity-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        <?php endwhile; else: ?>
                        <div class="bg-slate-900/40 border-2 border-dashed border-slate-800 rounded-[2.5rem] p-16 text-center backdrop-blur-xl">
                            <div class="w-16 h-16 bg-slate-950 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-600 border border-slate-800">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <p class="text-slate-500 font-bold text-sm uppercase tracking-widest">Storage is empty</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const customSwalMixin = Swal.mixin({
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#2563eb'
        });

        function confirmAction(event, message) {
            event.preventDefault();
            const form = event.target;
            customSwalMixin.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#1e293b'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }
        // Menu Toggle Logic
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

        openBtn?.addEventListener('click', () => toggleSidebar(true));
        closeBtn?.addEventListener('click', () => toggleSidebar(false));
        overlay?.addEventListener('click', () => toggleSidebar(false));
    </script>
</body>
</html>