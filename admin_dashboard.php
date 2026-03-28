<?php
session_start();
include 'db.php';

// Security: Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- FETCH CORE STATISTICS ---
// 1. Total Students
$res_students = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'");
$total_students = mysqli_fetch_assoc($res_students)['total'];

// 2. Pending Enrollments
$res_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'");
$pending_count = mysqli_fetch_assoc($res_pending)['total'];

// 3. Total Revenue (Approved Enrollments)
$res_revenue = mysqli_query($conn, "SELECT SUM(total_fee) as total FROM enrollments WHERE status = 'enrolled'");
$total_revenue = mysqli_fetch_assoc($res_revenue)['total'] ?? 0;

// 4. Total Posts
$res_posts = mysqli_query($conn, "SELECT COUNT(*) as total FROM posts");
$total_posts = mysqli_fetch_assoc($res_posts)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Dashboard | C-Familia</title>
</head>
<body class="bg-slate-50 font-sans">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-900 text-white hidden lg:block sticky top-0 h-screen">
            <div class="p-8">
                <h1 class="text-2xl font-black text-blue-400">C-Familia</h1>
                <p class="text-xs text-slate-500 uppercase tracking-widest mt-1">Management Suite</p>
            </div>
            <nav class="mt-4 px-4 space-y-2">
                <a href="admin_dashboard.php" class="flex items-center gap-3 py-3 px-4 bg-blue-600 rounded-xl font-bold transition">
                    Dashboard
                </a>
                <a href="admin_enrollments.php" class="flex items-center gap-3 py-3 px-4 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white">
                    Enrollments
                    <?php if($pending_count > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin_posts.php" class="flex items-center gap-3 py-3 px-4 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white">
                    Manage Posts
                </a>
                <div class="pt-10">
                    <a href="logout.php" class="flex items-center gap-3 py-3 px-4 text-red-400 hover:bg-red-900/20 rounded-xl transition font-bold">
                        Logout
                    </a>
                </div>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-800">System Overview</h2>
                    <p class="text-slate-500">Welcome back, <?= $_SESSION['username'] ?>. Here's what's happening today.</p>
                </div>
                <div class="flex gap-3">
                    <a href="admin_posts.php" class="bg-white border border-slate-200 px-4 py-2 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50 transition">New Post</a>
                    <a href="index.php" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 transition">View Website</a>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Total Students</p>
                    <h3 class="text-3xl font-black text-slate-900"><?= number_format($total_students) ?></h3>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <p class="text-amber-600 text-xs font-bold uppercase tracking-wider mb-2">Pending Enrollment</p>
                    <h3 class="text-3xl font-black text-slate-900"><?= $pending_count ?></h3>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <p class="text-green-600 text-xs font-bold uppercase tracking-wider mb-2">Total Revenue</p>
                    <h3 class="text-3xl font-black text-slate-900">₱<?= number_format($total_revenue, 2) ?></h3>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <p class="text-blue-600 text-xs font-bold uppercase tracking-wider mb-2">Published Posts</p>
                    <h3 class="text-3xl font-black text-slate-900"><?= $total_posts ?></h3>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="font-bold text-slate-800">Recent Enrollment Requests</h4>
                        <a href="admin_enrollments.php" class="text-blue-600 text-xs font-bold hover:underline uppercase">Manage All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody class="divide-y divide-slate-50">
                                <?php
                                $recent_enroll = mysqli_query($conn, "SELECT enrollments.*, users.name FROM enrollments JOIN users ON enrollments.user_id = users.id ORDER BY enrollments.created_at DESC LIMIT 5");
                                while($row = mysqli_fetch_assoc($recent_enroll)):
                                ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800 text-sm"><?= $row['name'] ?></p>
                                        <p class="text-[10px] text-slate-400 uppercase font-bold"><?= $row['program_type'] ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if($row['status'] == 'pending'): ?>
                                            <span class="px-2 py-1 bg-amber-100 text-amber-600 rounded text-[10px] font-black uppercase">Pending</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-green-100 text-green-600 rounded text-[10px] font-black uppercase">Enrolled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-400 text-right">
                                        <?= date('M d', strtotime($row['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-indigo-600 p-8 rounded-3xl text-white shadow-xl shadow-indigo-100">
                        <h4 class="font-bold text-xl mb-2">Growth Tip 📈</h4>
                        <p class="text-indigo-100 text-sm leading-relaxed mb-4">
                            You have <b><?= $pending_count ?></b> students waiting for approval. Quick response times lead to higher student satisfaction!
                        </p>
                        <a href="admin_enrollments.php" class="inline-block bg-white text-indigo-600 px-4 py-2 rounded-lg font-bold text-xs">Review Requests Now</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>