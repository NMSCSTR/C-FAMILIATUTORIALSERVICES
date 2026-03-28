<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";

if (isset($_POST['update_profile'])) {
    $new_name = mysqli_real_escape_string($conn, $_POST['name']);
    
    if ($_FILES['profile_pic']['name']) {
        $filename = time() . '_' . $_FILES['profile_pic']['name'];
        if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], 'uploads/profiles/' . $filename)) {
            mysqli_query($conn, "UPDATE users SET profile_pic = '$filename' WHERE id = '$user_id'");
        }
    }

    mysqli_query($conn, "UPDATE users SET name = '$new_name' WHERE id = '$user_id'");
    $_SESSION['username'] = $new_name;
    $success_msg = "Your profile has been updated successfully!";
}

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
    <title>Edit Profile | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <div class="flex">
        <aside class="w-64 bg-white border-r border-slate-200 hidden lg:flex flex-col h-screen sticky top-0">
            <div class="p-8">
                <a href="student_dashboard.php" class="flex items-center gap-3">
                    <img src="cuevaslogo.jpg" class="w-10 h-10 rounded-xl" alt="Logo">
                    <span class="font-bold text-blue-700 text-xl">C-Familia</span>
                </a>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="student_dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-2xl font-semibold transition-all">
                    <span>🏠</span> Dashboard
                </a>
                <a href="student_resources.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 rounded-2xl font-semibold transition-all">
                    <span>📚</span> My Resources
                </a>
                <a href="student_profile.php" class="flex items-center gap-3 px-4 py-3 bg-blue-50 text-blue-700 rounded-2xl font-bold">
                    <span>👤</span> Profile Settings
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-6 md:p-12">
            <div class="max-w-3xl mx-auto">
                
                <div class="mb-8">
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Account Settings</h1>
                    <p class="text-slate-500 mt-1">Manage your public profile and account details.</p>
                </div>

                <?php if($success_msg): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-100 text-green-700 rounded-2xl flex items-center gap-3 animate-bounce">
                    <span class="text-xl">✅</span>
                    <span class="font-bold text-sm"><?= $success_msg ?></span>
                </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                    
                    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-50">
                            <h3 class="font-bold text-slate-800">Profile Picture</h3>
                        </div>
                        <div class="p-8 flex flex-col md:flex-row items-center gap-8">
                            <div class="relative group">
                                <img id="preview" src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=random' ?>" 
                                     class="w-32 h-32 rounded-[2.5rem] border-4 border-slate-50 shadow-inner object-cover bg-slate-100">
                                <label class="absolute -bottom-2 -right-2 bg-blue-600 text-white p-3 rounded-2xl shadow-lg cursor-pointer hover:bg-blue-700 transition-all hover:scale-110">
                                    <span>📸</span>
                                    <input type="file" name="profile_pic" class="hidden" onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
                                </label>
                            </div>
                            <div class="text-center md:text-left">
                                <p class="text-sm font-bold text-slate-700">Upload a new photo</p>
                                <p class="text-xs text-slate-400 mt-1 uppercase font-black tracking-widest">JPG, PNG or GIF • Max 2MB</p>
                                <button type="button" onclick="document.querySelector('input[type=file]').click()" class="mt-4 text-xs font-bold text-blue-600 hover:text-blue-800">Choose file from device</button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-full">
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1 tracking-widest">Full Name</label>
                                <input type="text" name="name" value="<?= $user['name'] ?>" required 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all font-semibold text-slate-700">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1 tracking-widest">Email Address</label>
                                <input type="email" value="<?= $user['email'] ?>" disabled 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-100 bg-slate-100 text-slate-400 outline-none font-semibold cursor-not-allowed">
                                <p class="text-[9px] text-slate-400 mt-2 ml-1">Email cannot be changed. Contact admin for assistance.</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1 tracking-widest">Account Type</label>
                                <div class="px-6 py-4 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 font-bold text-sm flex items-center gap-2">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    Official Student
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 pt-6 border-t border-slate-50 flex justify-end gap-4">
                            <a href="student_dashboard.php" class="px-8 py-4 rounded-2xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition">Cancel</a>
                            <button type="submit" name="update_profile" 
                                    class="px-10 py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-200 hover:-translate-y-1 active:translate-y-0">
                                Save Updates
                            </button>
                        </div>
                    </div>

                </form>

                <div class="mt-12 p-8 bg-indigo-900 rounded-[2.5rem] text-white relative overflow-hidden shadow-2xl">
                    <div class="relative z-10">
                        <h4 class="font-bold text-lg">Need help with your data?</h4>
                        <p class="text-indigo-200 text-sm mt-1 max-w-sm">If your name is misspelled or your enrollment date is incorrect, please reach out to 
shielamariscuevas@gmail.com.</p>
                    </div>
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>