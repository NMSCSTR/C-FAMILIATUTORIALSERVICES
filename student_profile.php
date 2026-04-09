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
    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['profile_pic']['name']);
        if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], 'uploads/profiles/' . $filename)) {
            mysqli_query($conn, "UPDATE users SET profile_pic = '$filename' WHERE id = '$user_id'");
        }
    }

    mysqli_query($conn, "UPDATE users SET name = '$new_name' WHERE id = '$user_id'");
    $_SESSION['username'] = $new_name;
    $success_msg = "Profile updated successfully!";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Account Settings | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .sidebar-link-active { background-color: #f1f5f9; color: #4f46e5; border-left: 4px solid #4f46e5; }
        .glass-card { background: white; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04); }
    </style>
</head>
<body class="bg-[#fcfcfd] text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-white border-r border-slate-100 hidden lg:flex flex-col sticky top-0 h-screen z-50">
            <div class="p-8 pb-12 border-b border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-100">C</div>
                    <span class="font-extrabold text-slate-950 text-xl tracking-tight">C-Familia</span>
                </div>
            </div>
            
            <nav class="flex-1 pt-8 px-4 space-y-2">
                <a href="student_dashboard.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-600 hover:bg-slate-50 rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="student_resources.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-600 hover:bg-slate-50 rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Resources</span>
                </a>
                <a href="student_profile.php" class="flex items-center gap-3.5 px-6 py-4 rounded-xl font-bold transition-all sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Account</span>
                </a>
            </nav>

            <div class="p-6 border-t border-slate-100">
                <button onclick="confirmLogout()" class="w-full flex items-center gap-3 px-6 py-4 text-red-500 hover:bg-red-50 rounded-xl font-bold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </div>
        </aside>

        <main class="flex-1 min-w-0">
            <header class="bg-white/95 backdrop-blur-sm border-b border-slate-100 px-10 py-6 flex justify-between items-center sticky top-0 z-40">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Account Settings</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Manage your profile</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4f46e5&color=fff' ?>" 
                         class="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-50">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block"><?= $user['name'] ?></span>
                        <span class="text-[10px] font-semibold text-indigo-600">Active Student</span>
                    </div>
                </div>
            </header>

            <div class="p-10 max-w-4xl mx-auto">
                <?php if($success_msg): ?>
                <div class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 animate-pulse">
                    <span class="text-lg">✨</span>
                    <span class="font-bold text-sm"><?= $success_msg ?></span>
                </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <div class="glass-card rounded-[2.5rem] p-8 flex flex-col md:flex-row items-center gap-8">
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-[2.5rem] overflow-hidden ring-4 ring-slate-50 shadow-inner">
                                <img id="preview" src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4f46e5&color=fff' ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <label class="absolute -bottom-2 -right-2 bg-indigo-600 text-white p-3 rounded-2xl shadow-xl cursor-pointer hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <input type="file" name="profile_pic" class="hidden" onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
                            </label>
                        </div>
                        <div class="text-center md:text-left">
                            <h3 class="font-black text-slate-900 text-lg tracking-tight">Profile Photo</h3>
                            <p class="text-sm text-slate-500 mt-1 leading-relaxed">Recommended: Square JPG or PNG, max 2MB.</p>
                            <button type="button" onclick="document.querySelector('input[type=file]').click()" class="mt-4 text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition">Choose new file</button>
                        </div>
                    </div>

                    <div class="glass-card rounded-[2.5rem] p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="col-span-full">
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-3 block ml-1 tracking-widest">Full Display Name</label>
                                <input type="text" name="name" value="<?= $user['name'] ?>" required 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition-all font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-3 block ml-1 tracking-widest">Email Address</label>
                                <input type="email" value="<?= $user['email'] ?>" disabled 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-100 bg-slate-100 text-slate-400 font-bold cursor-not-allowed">
                                <p class="text-[9px] text-slate-400 mt-3 ml-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/></svg>
                                    Contact admin to change email
                                </p>
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-400 mb-3 block ml-1 tracking-widest">Account Type</label>
                                <div class="px-6 py-4 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-700 font-black text-xs flex items-center gap-3">
                                    <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                                    OFFICIAL STUDENT
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-50 flex items-center justify-end gap-4">
                            <a href="student_dashboard.php" class="px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition">Cancel</a>
                            <button type="submit" name="update_profile" 
                                    class="px-10 py-4 bg-indigo-600 text-white text-[11px] font-black rounded-2xl hover:bg-slate-900 transition-all shadow-xl shadow-indigo-100 uppercase tracking-widest">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-12 p-8 bg-slate-900 rounded-[2.5rem] text-white flex flex-col md:flex-row justify-between items-center gap-6 relative overflow-hidden">
                    <div class="relative z-10">
                        <h4 class="font-black text-xl tracking-tight">Need technical help?</h4>
                        <p class="text-slate-400 text-sm mt-1">For enrollment errors, email us at shielamariscuevas@gmail.com</p>
                    </div>
                    <a href="mailto:shielamariscuevas@gmail.com" class="relative z-10 px-6 py-3 bg-white/10 hover:bg-white/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Get Support</a>
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-600/20 rounded-full blur-3xl"></div>
                </div>
            </div>
        </main>
    </div>


    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be signed out of your account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5', // Indigo-600
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                customClass: {
                    title: 'font-extrabold text-slate-900',
                    confirmButton: 'rounded-xl font-bold px-6 py-3',
                    cancelButton: 'rounded-xl font-bold px-6 py-3 text-slate-600'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            })
        }
    </script>

</body>
</html>