<?php
session_start();
include 'db.php';

// Security: Redirect to login if not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Student Data
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

// Fetch Enrollment Status
$enroll_query = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 1");
$enrollment = mysqli_fetch_assoc($enroll_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Student Dashboard | C-Familia</title>
</head>
<body class="bg-slate-50 min-h-screen font-sans">

    <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold text-blue-600">C-Familia</h1>
                <span class="hidden md:block h-6 w-px bg-slate-200"></span>
                <span class="hidden md:block text-slate-500 text-sm font-medium">Student Portal</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="index.php" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition">Home</a>
                <a href="logout.php" class="text-sm font-bold text-red-500 hover:text-red-700 transition">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        <header class="mb-10">
            <h2 class="text-3xl font-extrabold text-slate-800">Hello, <?= $user_data['name'] ?>! 👋</h2>
            <p class="text-slate-500">Track your review progress and stay updated with your classes.</p>
        </header>

        <div class="grid lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8">
                <section class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-lg text-slate-800">Current Enrollment</h3>
                        <a href="enroll.php" class="text-sm font-bold text-blue-600 hover:underline">+ New Enrollment</a>
                    </div>
                    <div class="p-8">
                        <?php if ($enrollment): ?>
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Program</p>
                                    <h4 class="text-xl font-bold text-slate-900"><?= $enrollment['program_type'] ?></h4>
                                    <p class="text-slate-500 text-sm mt-1"><?= $enrollment['batch'] ?></p>
                                </div>
                                <div class="text-left md:text-right">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Status</p>
                                    <?php 
                                        $status = $enrollment['status'];
                                        $status_class = $status == 'enrolled' ? 'bg-green-100 text-green-700' : ($status == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-700');
                                    ?>
                                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase <?= $status_class ?>">
                                        <?= $status ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-8">
                                <div class="flex justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Review Progress</span>
                                    <span class="text-xs font-bold text-blue-600"><?= ($status == 'enrolled') ? '15%' : '0%' ?></span>
                                </div>
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 transition-all duration-1000" style="width: <?= ($status == 'enrolled') ? '15%' : '2%' ?>;"></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-10">
                                <p class="text-slate-400 mb-4 text-sm">You haven't enrolled in any programs yet.</p>
                                <a href="enroll.php" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition inline-block">Start Enrollment</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section>
                    <h3 class="font-bold text-xl mb-4">Latest Updates</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <?php
                        $posts = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 2");
                        while($post = mysqli_fetch_assoc($posts)):
                        ?>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 hover:shadow-md transition">
                            <span class="text-[10px] font-bold text-blue-600 uppercase mb-2 block"><?= $post['category'] ?></span>
                            <h4 class="font-bold text-slate-800 mb-2"><?= $post['title'] ?></h4>
                            <a href="#" class="text-sm font-semibold text-slate-400 hover:text-blue-600">Read more →</a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </section>
            </div>

            <div class="space-y-8">
                <section class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm text-center">
                    <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                        <?= substr($user_data['name'], 0, 1) ?>
                    </div>
                    <h3 class="font-bold text-xl text-slate-800"><?= $user_data['name'] ?></h3>
                    <p class="text-slate-500 text-sm mb-6"><?= $user_data['email'] ?></p>
                    <button class="w-full py-3 border border-slate-200 text-slate-600 font-bold rounded-xl text-sm hover:bg-slate-50 transition">
                        Edit Profile
                    </button>
                </section>

                <section class="bg-indigo-900 p-8 rounded-3xl text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="font-bold text-lg mb-2">Need Help?</h4>
                        <p class="text-indigo-200 text-sm mb-6 leading-relaxed">Having trouble with your enrollment or have questions about the review? Contact Shiela Maris.</p>
                        <a href="mailto:shielamariscuevas@gmail.com" class="inline-block bg-white text-indigo-900 px-4 py-2 rounded-lg font-bold text-xs">Email Coordinator</a>
                    </div>
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/20 rounded-full blur-2xl"></div>
                </section>
            </div>
            
        </div>
    </main>

</body>
</html>