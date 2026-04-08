<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'"))['total'];


// Logic to Post Announcement
if (isset($_POST['post_announcement'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $sql = "INSERT INTO announcements (title, message, category, created_at) VALUES ('$title', '$message', '$category', NOW())";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_announcements.php?posted=1");
        exit();
    }
}

// Logic to Delete
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM announcements WHERE id = '$id'");
    header("Location: admin_announcements.php?deleted=1");
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
    <title>Announcements | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .sidebar-link-active { background: #2563eb; color: white; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900">

    <div class="flex min-h-screen">
        <?php include 'aside.php';?>

        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-4xl mx-auto">
                <header class="mb-10 text-center">
                    <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Broadcast Center</h2>
                    <p class="text-slate-500 mt-1">Keep your students informed with real-time updates.</p>
                </header>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 mb-10">
                    <form action="" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase text-slate-400 tracking-widest px-1">Announcement Title</label>
                                <input type="text" name="title" required placeholder="e.g. Midterm Schedule Update" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-500/10 outline-none transition font-semibold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase text-slate-400 tracking-widest px-1">Category</label>
                                <select name="category" class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-500/10 outline-none transition font-semibold">
                                    <option value="General">General News</option>
                                    <option value="Urgent">Urgent Alert</option>
                                    <option value="Event">Event/Holiday</option>
                                    <option value="Academic">Academic</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase text-slate-400 tracking-widest px-1">Message Detail</label>
                            <textarea name="message" rows="4" required placeholder="Write your announcement here..." class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-500/10 outline-none transition font-medium"></textarea>
                        </div>
                        <button type="submit" name="post_announcement" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-blue-600 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            Post Announcement
                        </button>
                    </form>
                </div>

                <div class="space-y-4">
                    <h4 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Post History</h4>
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
                    while($row = mysqli_fetch_assoc($res)):
                        $color = ($row['category'] == 'Urgent') ? 'red' : (($row['category'] == 'Event') ? 'purple' : 'blue');
                    ?>
                    <div class="bg-white p-6 rounded-[1.5rem] border border-slate-200 flex justify-between items-start group">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-<?= $color ?>-100 text-<?= $color ?>-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase text-<?= $color ?>-500 tracking-widest bg-<?= $color ?>-50 px-2 py-0.5 rounded"><?= $row['category'] ?></span>
                                <h5 class="font-bold text-slate-900 mt-1"><?= $row['title'] ?></h5>
                                <p class="text-sm text-slate-500 mt-2 leading-relaxed"><?= $row['message'] ?></p>
                                <p class="text-[10px] text-slate-400 mt-4 font-bold uppercase tracking-tighter"><?= date('F d, Y • h:i A', strtotime($row['created_at'])) ?></p>
                            </div>
                        </div>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Remove this post?')" class="text-slate-300 hover:text-red-500 transition-colors p-2 opacity-0 group-hover:opacity-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

</body>
</html>