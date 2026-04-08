<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'"))['total'];

// Logic to Save Post
if (isset($_POST['save_post'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $file_name = null;

    // Handle File Upload
    if (!empty($_FILES['resource']['name'])) {
        $target_dir = "uploads/resources/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $file_name = time() . "_" . basename($_FILES["resource"]["name"]);
        move_uploaded_file($_FILES["resource"]["tmp_name"], $target_dir . $file_name);
    }

    $sql = "INSERT INTO posts (title, content, file_path) VALUES ('$title', '$content', '$file_name')";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_posts.php?success=1");
        exit();
    }
}

// Logic to Delete
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM posts WHERE id = '$id'");
    header("Location: admin_posts.php?deleted=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Manage Resources | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .sidebar-link-active { background: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-950 text-white hidden lg:flex flex-col sticky top-0 h-screen">
            <div class="p-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20 text-white">C</div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-white">C-Familia</h1>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest leading-none mt-1">Admin Suite</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-1.5 overflow-y-auto text-sm">
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mb-4 px-4">Main Menu</p>
                <a href="admin_dashboard.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_dashboard.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Dashboard</a>
                <a href="admin_enrollments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_enrollments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Enrollments</a>
                <a href="admin_payments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_payments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Payments</a>
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-8 mb-4 px-4">Communication</p>
                <a href="admin_announcements.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_announcements.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Announcements</a>
                <a href="admin_posts.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_posts.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Posts</a>
            </nav>

            <div class="p-6 border-t border-white/5">
                <a href="logout.php" class="flex items-center gap-3 py-3 px-4 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold">Logout</a>
            </div>
        </aside>

        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-5xl mx-auto">
                <header class="mb-10">
                    <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Resource Management</h2>
                    <p class="text-slate-500 mt-1">Upload guides, learning modules, and community posts.</p>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 sticky top-10">
                            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Create New Post
                            </h3>
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Post Title</label>
                                    <input type="text" name="title" required class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 outline-none transition text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Content Description</label>
                                    <textarea name="content" rows="4" required class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 outline-none transition text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Attachment (PDF/Image)</label>
                                    <input type="file" name="resource" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                </div>
                                <button type="submit" name="save_post" class="w-full bg-blue-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all mt-4">Publish Post</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2">Published Resources</h4>
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");
                        if(mysqli_num_rows($res) > 0):
                            while($row = mysqli_fetch_assoc($res)):
                        ?>
                        <div class="bg-white p-6 rounded-[1.5rem] border border-slate-200 group flex items-start justify-between gap-4">
                            <div class="flex gap-4">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors"><?= $row['title'] ?></h5>
                                    <p class="text-xs text-slate-500 mt-2 line-clamp-2"><?= $row['content'] ?></p>
                                    
                                    <?php if($row['file_path']): ?>
                                    <div class="mt-3">
                                        <a href="uploads/resources/<?= $row['file_path'] ?>" target="_blank" class="inline-flex items-center gap-1.5 text-[10px] font-black text-blue-600 uppercase tracking-tighter hover:underline">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download Resource
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <p class="text-[10px] text-slate-400 mt-4 font-bold uppercase"><?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                                </div>
                            </div>
                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this resource permanently?')" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </a>
                        </div>
                        <?php endwhile; else: ?>
                        <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2rem] p-12 text-center">
                            <p class="text-slate-400 font-bold text-sm">No resources published yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>