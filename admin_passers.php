<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE status = 'pending'"))['total'];


if (isset($_POST['add_passer'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $photo_name = "default_user.jpg";

    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/passers/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $photo_name);
    }

    mysqli_query($conn, "INSERT INTO passers (name, program, batch, photo) VALUES ('$name', '$program', '$batch', '$photo_name')");
    header("Location: admin_passers.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Manage Passers | C-Familia Admin</title>
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
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20">C</div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight">C-Familia</h1>
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mt-1">Admin Suite</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-1.5 overflow-y-auto text-sm">
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mb-4 px-4 text-slate-500">Main Menu</p>
                <a href="admin_dashboard.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_dashboard.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Dashboard</a>
                <a href="admin_enrollments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_enrollments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Enrollments</a>
                <a href="admin_payments.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_payments.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Payments</a>
                
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-8 mb-4 px-4 text-slate-500">Communication</p>
                <a href="admin_announcements.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_announcements.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Announcements</a>
                <a href="admin_posts.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_posts.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">Posts</a>
                
                <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em] mt-8 mb-4 px-4 text-slate-500">Landing Page</p>
                <a href="admin_passers.php" class="flex items-center gap-3 py-3 px-4 rounded-xl transition-all font-semibold group <?= ($current_page == 'admin_passers.php') ? 'sidebar-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">List of Passers</a>
            </nav>

            <div class="p-6 border-t border-white/5">
                <a href="logout.php" class="flex items-center gap-3 py-3.5 px-4 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-sm">Sign Out</a>
            </div>
        </aside>

        <main class="flex-1 p-6 lg:p-10">
            <div class="max-w-6xl mx-auto">
                <header class="mb-10">
                    <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Passers Hall of Fame</h2>
                    <p class="text-slate-500 mt-1">Add successful candidates to be featured on the landing page.</p>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200 h-fit sticky top-10">
                            <h3 class="font-bold text-slate-800 mb-6">Register New Passer</h3>
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Full Name</label>
                                    <input type="text" name="name" required class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 outline-none transition text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Program</label>
                                    <input type="text" name="program" placeholder="e.g. BSIT" required class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 outline-none transition text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Batch Year</label>
                                    <input type="text" name="batch" placeholder="e.g. 2025" required class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500/10 outline-none transition text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block">Student Photo</label>
                                    <input type="file" name="photo" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                </div>
                                <button type="submit" name="add_passer" class="w-full bg-blue-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all mt-4">Add to Hall of Fame</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
                            if(mysqli_num_rows($res) > 0):
                                while($p = mysqli_fetch_assoc($res)):
                            ?>
                            <div class="bg-white p-5 rounded-[1.5rem] border border-slate-200 text-center group hover:border-blue-200 transition-all">
                                <div class="relative inline-block mb-3">
                                    <img src="uploads/passers/<?= $p['photo'] ?>" class="w-20 h-20 rounded-2xl mx-auto object-cover border-4 border-slate-50 shadow-sm group-hover:scale-105 transition-transform">
                                </div>
                                <h5 class="font-bold text-slate-900 text-sm leading-tight"><?= $p['name'] ?></h5>
                                <p class="text-[10px] text-blue-600 font-black uppercase mt-1 tracking-widest"><?= $p['program'] ?></p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">Batch <?= $p['batch'] ?></p>
                            </div>
                            <?php endwhile; else: ?>
                            <div class="col-span-full py-20 text-center bg-white border-2 border-dashed border-slate-200 rounded-[2rem]">
                                <p class="text-slate-400 font-medium">No passers added yet.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>