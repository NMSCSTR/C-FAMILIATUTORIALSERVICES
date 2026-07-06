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
    $first = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middle = mysqli_real_escape_string($conn, $_POST['middlename']);
    $last = mysqli_real_escape_string($conn, $_POST['lastname']);
    
    // Sanitize newly added fields
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $cellphone_no = mysqli_real_escape_string($conn, $_POST['cellphone_no']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $parents_name_guardian = mysqli_real_escape_string($conn, $_POST['parents_name_guardian']);
    $parents_phone_no = mysqli_real_escape_string($conn, $_POST['parents_phone_no']);
    $fb_messenger_account = mysqli_real_escape_string($conn, $_POST['fb_messenger_account']);
    
    // Handle Profile Picture Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['profile_pic']['name']);
        if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], 'uploads/profiles/' . $filename)) {
            mysqli_query($conn, "UPDATE users SET profile_pic = '$filename' WHERE id = '$user_id'");
        }
    }

    // Update profile columns including the new fields
    $update_query = "UPDATE users SET 
                    firstname = '$first', 
                    middlename = '$middle', 
                    lastname = '$last',
                    birthday = '$birthday',
                    cellphone_no = '$cellphone_no',
                    address = '$address',
                    parents_name_guardian = '$parents_name_guardian',
                    parents_phone_no = '$parents_phone_no',
                    fb_messenger_account = '$fb_messenger_account'
                    WHERE id = '$user_id'";
    
    if(mysqli_query($conn, $update_query)) {
        log_activity($conn, 'profile.update', 'Updated profile information', [
            'entity_type' => 'user',
            'entity_id' => (int) $user_id,
        ]);
        // Update session name for immediate UI feedback
        $_SESSION['username'] = $first . ' ' . $last;
        $success_msg = "Profile updated successfully!";
    }
}

// Fetch fresh data
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
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Account Settings | C-Familia</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; background-color: #020617; }
        .sidebar-link-active { background: linear-gradient(90deg, #2563eb, #4f46e5); color: #ffffff; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; }
        .glass-card { background: rgba(15, 23, 42, 0.6); border: 1px solid #1e293b; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); backdrop-filter: blur(24px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body class="text-white antialiased">

    <div class="flex min-h-screen relative overflow-x-hidden">
        
        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity duration-300"></div>

        <aside id="sidebarMenu" class="w-72 bg-slate-900/40 border-r border-slate-800/80 flex flex-col fixed inset-y-0 left-0 -translate-x-full lg:translate-x-0 lg:static h-screen z-50 transition-transform duration-300 ease-in-out backdrop-blur-2xl">
            <div class="p-8 pb-12 border-b border-slate-800/60 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-500/20">C</div>
                    <span class="font-extrabold text-white text-xl tracking-tight">C-Familia</span>
                </div>
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-xl bg-slate-800/60 border border-slate-700/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <nav class="flex-1 pt-8 px-4 space-y-2 overflow-y-auto custom-scrollbar text-sm">
                <a href="student_dashboard.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-400 hover:text-white hover:bg-slate-800/40 border border-transparent rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="student_resources.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-400 hover:text-white hover:bg-slate-800/40 border border-transparent rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Resources</span>
                </a>
                <a href="student_profile.php" class="flex items-center gap-3.5 px-6 py-4 rounded-xl font-bold transition-all sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Account</span>
                </a>
            </nav>

            <div class="p-6 border-t border-slate-800/80 mt-auto">
                <button onclick="confirmLogout()" class="w-full flex items-center gap-3 px-6 py-4 text-red-400 hover:bg-red-500/10 rounded-xl font-bold transition-all border border-transparent hover:border-red-500/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </div>
        </aside>

        <main class="flex-1 min-w-0 w-full">
            <header class="bg-slate-900/40 backdrop-blur-md border-b border-slate-800/80 px-6 sm:px-10 py-6 flex justify-between items-center sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-xl hover:bg-slate-800/60 transition-colors focus:outline-none" aria-label="Toggle Navigation Side Menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h2 class="text-xl font-black text-white tracking-tight">Account Settings</h2>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em]">Manage your profile</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['firstname'].' '.$user['lastname']).'&background=2563eb&color=fff' ?>" 
                         class="w-10 h-10 rounded-full object-cover ring-2 ring-blue-900/40">
                    <div class="hidden sm:block">
                        <span class="text-xs font-bold text-white block"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></span>
                        <span class="text-[10px] font-semibold text-blue-400">Active Student</span>
                    </div>
                </div>
            </header>

            <div class="p-4 sm:p-10 max-w-8xl mx-auto">
                <?php if($success_msg): ?>
                <div class="mb-8 p-4 bg-emerald-950/20 border border-emerald-900/30 text-emerald-400 rounded-2xl flex items-center gap-3">
                    <span class="text-lg">✨</span>
                    <span class="font-bold text-sm"><?= $success_msg ?></span>
                </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <div class="glass-card rounded-[2.5rem] p-6 sm:p-8 flex flex-col md:flex-row items-center gap-8">
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-[2.5rem] overflow-hidden ring-4 ring-slate-900 shadow-inner">
                                <img id="preview" src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['firstname'] . ' ' . $user['lastname']).'&background=2563eb&color=fff' ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <label class="absolute -bottom-2 -right-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-3 rounded-2xl shadow-xl cursor-pointer hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <input type="file" name="profile_pic" class="hidden" onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
                            </label>
                        </div>
                        <div class="text-center md:text-left">
                            <h3 class="font-black text-white text-lg tracking-tight">Profile Photo</h3>
                            <p class="text-sm text-slate-400 mt-1 leading-relaxed">Recommended: Square JPG or PNG, max 2MB.</p>
                            <p class="text-sm text-slate-500 mt-1 leading-relaxed">Recommended: It should be graduation picture.</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-[2.5rem] p-6 sm:p-10 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">First Name</label>
                                <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Middle Name</label>
                                <input type="text" name="middlename" value="<?= htmlspecialchars($user['middlename']) ?>" 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Last Name</label>
                                <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Birthday</label>
                                <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>" required 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Cellphone #</label>
                                <input type="text" name="cellphone_no" value="<?= htmlspecialchars($user['cellphone_no'] ?? '') ?>" required placeholder="0917XXXXXXX" 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                            </div>

                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">FB / Messenger Account</label>
                                <input type="text" name="fb_messenger_account" value="<?= htmlspecialchars($user['fb_messenger_account'] ?? '') ?>" placeholder="Profile link or username" 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Email Address</label>
                                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled 
                                       class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-900 text-slate-500 font-bold cursor-not-allowed">
                            </div>

                            <div class="md:col-span-1">
                                <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Account Type</label>
                                <div class="px-6 py-4 rounded-2xl bg-blue-950/20 border border-blue-900/30 text-blue-400 font-black text-xs flex items-center gap-3 h-[58px]">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                    OFFICIAL STUDENT
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Full Address</label>
                            <textarea name="address" required rows="2" placeholder="House No., Street, Barangay, City, Province" 
                                      class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white resize-none"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <div class="pt-6 border-t border-dashed border-slate-800">
                            <p class="text-xs font-black uppercase text-blue-400 tracking-wider mb-4">Parent / Guardian Information</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Parent / Guardian Name</label>
                                    <input type="text" name="parents_name_guardian" value="<?= htmlspecialchars($user['parents_name_guardian'] ?? '') ?>" required 
                                           class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-3 block ml-1 tracking-widest">Parent Phone Number</label>
                                    <input type="text" name="parents_phone_no" value="<?= htmlspecialchars($user['parents_phone_no'] ?? '') ?>" required 
                                           class="w-full px-6 py-4 rounded-2xl border border-slate-800 bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-white">
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-800/60 flex flex-col sm:flex-row items-center justify-end gap-4">
                            <a href="student_dashboard.php" class="w-full sm:w-auto text-center px-8 py-4 text-xs font-black uppercase tracking-widest text-slate-500 hover:text-slate-400 transition">Cancel</a>
                            <button type="submit" name="update_profile" 
                                    class="w-full sm:w-auto px-10 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-[11px] font-black rounded-2xl transition-all shadow-xl shadow-blue-950/40 uppercase tracking-widest">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const customSwalMixin = Swal.mixin({
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#2563eb'
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function confirmLogout() {
            customSwalMixin.fire({
                title: 'Are you sure?',
                text: "You will be signed out of your account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#1e293b',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                customClass: {
                    title: 'font-extrabold text-white',
                    confirmButton: 'rounded-xl font-bold px-6 py-3 text-sm',
                    cancelButton: 'rounded-xl font-bold px-6 py-3 text-sm text-slate-400'
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