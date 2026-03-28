<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Resources | C-Familia</title>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-white border-r border-slate-200 hidden md:flex flex-col">
            <div class="p-6 border-b border-slate-100">
                <a href="index.php" class="flex items-center gap-2">
                    <img src="cuevaslogo.jpg" class="w-8 h-8 rounded-lg" alt="Logo">
                    <span class="font-bold text-blue-700 text-lg">C-Familia</span>
                </a>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="student_dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-xl transition font-medium">
                    <span>🏠</span> Dashboard
                </a>
                <a href="student_resources.php" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-xl font-bold">
                    <span>📚</span> My Resources
                </a>
                <a href="student_profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-xl transition font-medium">
                    <span>👤</span> My Profile
                </a>
            </nav>
            <div class="p-4 border-t border-slate-100">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl transition font-medium">
                    <span>🚪</span> Logout
                </a>
            </div>
        </aside>

        <main class="flex-1">
            <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Learning Materials</h2>
                    <p class="text-xs text-slate-500 font-medium">Access your reviewer files and handouts</p>
                </div>
                <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=random' ?>" class="w-10 h-10 rounded-full border border-slate-200">
            </header>

            <div class="p-8">
                <div class="mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-700">Available Files</h3>
                    <div class="relative w-full md:w-72">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">🔍</span>
                        <input type="text" placeholder="Search resources..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    $resources_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");
                    if(mysqli_num_rows($resources_query) > 0):
                        while($res = mysqli_fetch_assoc($resources_query)):
                    ?>
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            📄
                        </div>
                        <h4 class="font-bold text-slate-800 mb-2"><?= $res['title'] ?></h4>
                        <p class="text-sm text-slate-500 mb-6 line-clamp-2"><?= $res['content'] ?></p>
                        
                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-slate-400"><?= date('M Y', strtotime($res['created_at'])) ?></span>
                            
                            <?php if($res['file_path']): ?>
                                <a href="uploads/resources/<?= $res['file_path'] ?>" download class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-blue-600 transition flex items-center gap-2">
                                    <span>📥</span> Download
                                </a>
                            <?php else: ?>
                                <span class="text-xs text-slate-300 italic">No attachment</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <div class="col-span-full py-20 text-center">
                        <p class="text-slate-400 font-medium italic">No resources uploaded yet. Check back later!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

</body>
</html>