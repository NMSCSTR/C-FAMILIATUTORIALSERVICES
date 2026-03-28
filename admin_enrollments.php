<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

// Logic to Approve Enrollment
if (isset($_GET['approve'])) {
    $id = mysqli_real_escape_string($conn, $_GET['approve']);
    mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE id = '$id'");
    header("Location: admin_enrollments.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Manage Enrollments | C-Familia Admin</title>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-900 text-white hidden lg:block sticky top-0 h-screen">
            <div class="p-8">
                <h1 class="text-2xl font-black text-blue-400">C-Familia</h1>
            </div>
            <nav class="mt-4 px-4 space-y-2">
                <a href="admin_dashboard.php" class="flex items-center gap-3 py-3 px-4 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition">Dashboard</a>
                <a href="admin_enrollments.php" class="flex items-center gap-3 py-3 px-4 bg-blue-600 text-white rounded-xl font-bold transition shadow-lg shadow-blue-900/20">Enrollments</a>
                <a href="admin_posts.php" class="flex items-center gap-3 py-3 px-4 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition">Manage Posts</a>
                <div class="pt-10">
                    <a href="logout.php" class="flex items-center gap-3 py-3 px-4 text-red-400 hover:bg-red-900/20 rounded-xl transition font-bold">Logout</a>
                </div>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <div class="max-w-6xl mx-auto">
                <header class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Enrollment Requests</h2>
                        <p class="text-slate-500 mt-1">Review and approve incoming student applications.</p>
                    </div>
                    <?php if(isset($_GET['success'])): ?>
                        <div class="bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm font-bold border border-green-200 animate-bounce">
                            ✓ Successfully Approved
                        </div>
                    <?php endif; ?>
                </header>

                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-wrap gap-4 items-center justify-between">
                    <div class="relative w-full md:w-96">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" placeholder="Search student name..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm font-bold">Pending</button>
                        <button class="px-4 py-2 bg-white text-slate-600 border border-slate-200 rounded-xl text-sm font-bold hover:bg-slate-50">Approved</button>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Student Details</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Program & Batch</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500">Status</th>
                                    <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-slate-500 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php
                                $sql = "SELECT enrollments.*, users.name, users.email FROM enrollments 
                                        JOIN users ON enrollments.user_id = users.id 
                                        WHERE status = 'pending' ORDER BY created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                
                                if(mysqli_num_rows($result) > 0):
                                    while($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">
                                                <?= substr($row['name'], 0, 1) ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 leading-none"><?= $row['name'] ?></p>
                                                <p class="text-xs text-slate-400 mt-1"><?= $row['email'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm font-semibold text-slate-700"><?= $row['program_type'] ?></p>
                                        <p class="text-[11px] text-blue-500 font-bold uppercase mt-0.5"><?= $row['batch'] ?></p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-black uppercase border border-amber-100">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <a href="?approve=<?= $row['id'] ?>" 
                                           onclick="return confirm('Approve this enrollment?')"
                                           class="inline-block bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm">
                                            Approve Student
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            </div>
                                            <p class="text-slate-500 font-medium">No pending enrollments found.</p>
                                            <p class="text-slate-400 text-sm">All caught up! Check back later.</p>
                                        </div>
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

</body>
</html>