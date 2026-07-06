<?php
session_start();
include 'db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch User Data
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Helper function for visual file identification with Midnight theme classes
function getFileStyle($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    switch (strtolower($ext)) {
        case 'pdf': return ['label' => 'PDF', 'bg' => 'bg-rose-950/30', 'text' => 'text-rose-400', 'border' => 'hover:border-rose-900/40'];
        case 'doc':
        case 'docx': return ['label' => 'DOC', 'bg' => 'bg-blue-950/30', 'text' => 'text-blue-400', 'border' => 'hover:border-blue-900/40'];
        case 'zip':
        case 'rar': return ['label' => 'ZIP', 'bg' => 'bg-amber-950/30', 'text' => 'text-amber-400', 'border' => 'hover:border-amber-900/40'];
        default: return ['label' => 'FILE', 'bg' => 'bg-slate-900', 'text' => 'text-slate-400', 'border' => 'hover:border-slate-800'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Resources | C-Familia</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; background-color: #020617; }
        .sidebar-link-active { background: linear-gradient(90deg, #2563eb, #4f46e5); color: #ffffff; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; }
        .resource-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .resource-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); }
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
                <a href="student_resources.php" class="flex items-center gap-3.5 px-6 py-4 rounded-xl font-bold transition-all sidebar-link-active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Resources</span>
                </a>
                <a href="student_profile.php" class="flex items-center gap-3.5 px-6 py-4 text-slate-400 hover:text-white hover:bg-slate-800/40 border border-transparent rounded-xl font-semibold transition-all group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Account</span>
                </a>
            </nav>

            <div class="p-6 border-t border-slate-800/80 border-slate-100">
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
                        <h2 class="text-xl font-black text-white tracking-tight">Learning Resources</h2>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em]">Materials & Handouts</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <img src="<?= $user['profile_pic'] ? 'uploads/profiles/'.$user['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($user['firstname'].' '.$user['lastname']).'&background=2563eb&color=fff' ?>" 
                         class="w-10 h-10 rounded-full object-cover ring-2 ring-blue-900/40">
                    <div class="hidden sm:block">
                        <span class="text-xs font-bold text-white block"><?= $user['firstname'] ?> <?= $user['middlename'] ?> <?= $user['lastname'] ?></span>
                        <span class="text-[10px] font-semibold text-blue-400">Active Student</span>
                    </div>
                </div>
            </header>

            <div class="p-4 sm:p-10 max-w-12xl mx-auto space-y-10">
                <div class="flex flex-col md:flex-row gap-6 items-center justify-between">
                    <div class="relative w-full md:w-96 group">
                        <span class="absolute inset-y-0 left-4 flex items-center text-slate-500 group-focus-within:text-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" placeholder="Search resources..." 
                               class="w-full pl-12 pr-4 py-4 rounded-2xl border border-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium bg-slate-900/40 placeholder:text-slate-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php 
                    $resources_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");
                    if(mysqli_num_rows($resources_query) > 0):
                        while($res = mysqli_fetch_assoc($resources_query)):
                            $style = getFileStyle($res['file_path'] ?? '');
                    ?>
                    <div class="bg-slate-900/60 p-6 sm:p-8 rounded-[2.5rem] border border-slate-800 resource-card flex flex-col <?= $style['border'] ?> backdrop-blur-xl">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-14 h-14 <?= $style['bg'] ?> <?= $style['text'] ?> rounded-2xl flex items-center justify-center text-[10px] font-black tracking-tighter border border-slate-800/80 shadow-inner">
                                <?= $style['label'] ?>
                            </div>
                            <span class="px-3 py-1 bg-slate-950 text-slate-500 text-[9px] font-black rounded-lg uppercase tracking-widest border border-slate-800/60">Handout</span>
                        </div>

                        <h4 class="font-black text-white mb-2 text-lg leading-tight tracking-tight"><?= $res['title'] ?></h4>
                        <p class="text-sm text-slate-400 mb-8 line-clamp-3 leading-relaxed"><?= $res['content'] ?></p>
                        
                        <div class="mt-auto pt-6 border-t border-slate-800/60 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black uppercase text-slate-600 tracking-widest">Added on</span>
                                <span class="text-xs font-bold text-slate-400"><?= date('M d, Y', strtotime($res['created_at'])) ?></span>
                            </div>
                            
                            <?php if($res['file_path']): ?>
                                <a href="uploads/resources/<?= $res['file_path'] ?>" download 
                                   class="px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-[11px] font-black rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-blue-950/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    DOWNLOAD
                                </a>
                            <?php else: ?>
                                <span class="text-[10px] font-bold text-slate-600 italic">No file attached</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                    <div class="col-span-full py-32 text-center bg-slate-900/60 rounded-[3rem] border border-slate-800 backdrop-blur-xl">
                        <div class="text-4xl mb-4 opacity-40">📂</div>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No materials uploaded yet.</p>
                        <p class="text-slate-600 text-sm mt-1">Check back later for new learning resources.</p>
                    </div>
                    <?php endif; ?>
                </div>
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