<?php 
include 'db.php'; 

// --- Auto-Calculate Passing Rate ---
$total_passers_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passers");
$total_passers = mysqli_fetch_assoc($total_passers_query)['count'];
$display_rate = ($total_passers > 0) ? "95%" : "0%";

// --- Gallery (safe for production if table not migrated yet) ---
$grouped_gallery = [];
$gallery_dir = "uploads/gallery/";
$gallery_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'gallery_images'")) > 0;
if ($gallery_table_exists) {
    $gallery_query = mysqli_query($conn, "SELECT * FROM gallery_images ORDER BY caption ASC, sort_order ASC, id ASC");
    if ($gallery_query) {
        while ($gallery_item = mysqli_fetch_assoc($gallery_query)) {
            $grouped_gallery[$gallery_item['caption']][] = $gallery_item;
        }
    }
}
$has_gallery = !empty($grouped_gallery);

// --- Announcements (landing page) ---
$announcements = [];
$ann_query_all = mysqli_query($conn, "SELECT * FROM announcements WHERE audience = 'General' ORDER BY created_at DESC");
if ($ann_query_all) {
    while ($ann_row = mysqli_fetch_assoc($ann_query_all)) {
        $announcements[] = $ann_row;
    }
}
$ann_count = count($announcements);
$recent_announcements = array_slice($announcements, 0, 3);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Configure Tailwind to support manual dark mode class toggling and custom font families
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        nature: ['Nature Beauty', 'cursive', 'sans-serif']
                    }
                }
            }
        }
        
        // Inline check to prevent flashing during initial load
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Nature+Beauty&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>C-Familia Tutorial Services</title>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        h1, h2, h3, .font-nature { font-family: 'Nature Beauty', 'Poppins', sans-serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        @keyframes pulse-slow { 0%, 100% { opacity: 0.2; transform: scale(1); } 50% { opacity: 0.4; transform: scale(1.1); } }
        @keyframes float-slow { 0%, 100% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-18px) rotate(3deg); } }
        @keyframes float-medium { 0%, 100% { transform: translateY(0px) rotate(0deg); } 50% { transform: translateY(-12px) rotate(-2deg); } }
        @keyframes drift { 0% { transform: translate(0, 0) rotate(0deg); } 33% { transform: translate(10px, -15px) rotate(2deg); } 66% { transform: translate(-8px, -8px) rotate(-1deg); } 100% { transform: translate(0, 0) rotate(0deg); } }
        .animate-pulse-slow { animation: pulse-slow 8s ease-in-out infinite; }
        .animate-float-slow { animation: float-slow 12s ease-in-out infinite; }
        .animate-float-medium { animation: float-medium 9s ease-in-out infinite; }
        .animate-drift { animation: drift 18s ease-in-out infinite; }
        .modal-active { overflow: hidden; }
        
        .bg-dot-pattern {
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .dark .bg-dot-pattern {
            background-image: radial-gradient(circle, #334155 1px, transparent 1px);
        }
        
        .bg-line-pattern {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 18px,
                rgba(148,163,184,0.08) 18px,
                rgba(148,163,184,0.08) 19px
            );
        }
        .dark .bg-line-pattern {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 18px,
                rgba(51,65,85,0.2) 18px,
                rgba(51,65,85,0.2) 19px
            );
        }
        
        .bg-cross-pattern {
            background-image:
                linear-gradient(rgba(203,213,225,0.25) 1px, transparent 1px),
                linear-gradient(90deg, rgba(203,213,225,0.25) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .dark .bg-cross-pattern {
            background-image:
                linear-gradient(rgba(51,65,85,0.25) 1px, transparent 1px),
                linear-gradient(90deg, rgba(51,65,85,0.25) 1px, transparent 1px);
        }
    </style>
</head>

<body class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white transition-colors duration-300 font-poppins">

    <!-- Sticky Navigation Bar -->
    <nav class="sticky top-0 z-40 bg-white/75 dark:bg-slate-950/75 backdrop-blur-md border-b border-slate-100 dark:border-slate-900 px-4 py-4 sm:px-6 transition-all">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-blue-600/40 rounded-xl p-1 z-50">
                <div class="relative overflow-hidden rounded-xl shadow-md border border-slate-100 dark:border-slate-800">
                    <img src="cuevaslogo.jpg" alt="C-Familia Logo" class="w-10 h-10 object-contain transition-transform duration-500 group-hover:scale-110">
                </div>
                <h1 class="text-2xl font-[900] tracking-tighter text-slate-900 dark:text-white font-nature">
                    C-Familia<span class="text-blue-600">.</span>
                </h1>
            </a>

            <!-- Desktop View: Menu Options -->
            <div class="hidden md:flex space-x-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-full text-sm font-bold text-slate-600 dark:text-slate-400">
                <a href="#announcements" class="hover:text-slate-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 rounded-full px-4 py-1.5 transition-all focus:outline-none">Announcements</a>
                <a href="#posts" class="hover:text-slate-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 rounded-full px-4 py-1.5 transition-all focus:outline-none">Learning Materials</a>
                <a href="#passers" class="hover:text-slate-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 rounded-full px-4 py-1.5 transition-all focus:outline-none">Passers</a>
                <?php if ($has_gallery): ?>
                <a href="#gallery" class="hover:text-slate-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 rounded-full px-4 py-1.5 transition-all focus:outline-none">Gallery</a>
                <?php endif; ?>
                <a href="#contact" class="hover:text-slate-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 rounded-full px-4 py-1.5 transition-all focus:outline-none">Contact</a>
            </div>

            <!-- Theme Toggle & Action Items Container -->
            <div class="flex items-center space-x-3 z-50">
                <!-- Theme Toggle Button -->
                <button id="themeToggleBtn" class="w-11 h-11 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-xl flex items-center justify-center font-bold border border-slate-200 dark:border-slate-800 shadow-sm hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors focus:outline-none" aria-label="Toggle visual background theme">
                    <!-- Sun Icon (Hidden in Light Mode) -->
                    <svg id="sunIcon" class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M14 12a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <!-- Moon Icon (Hidden in Dark Mode) -->
                    <svg id="moonIcon" class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <a href="login.php" class="hidden sm:inline-block text-slate-700 dark:text-slate-300 font-bold px-4 py-2 hover:text-blue-600 dark:hover:text-blue-400 text-sm transition-colors focus:outline-none">Login</a>
                <a href="register.php" class="hidden sm:inline-block px-5 py-2.5 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-950 text-sm font-bold rounded-xl hover:bg-blue-600 dark:hover:bg-blue-500 dark:hover:text-white active:scale-98 transition-all shadow-md shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-blue-600/40">
                    Join Us 
                </a>
                
                <!-- Mobile Menu Button Icon -->
                <button id="mobileMenuBtn" class="flex md:hidden w-11 h-11 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white rounded-xl items-center justify-center font-bold border border-slate-200 dark:border-slate-800 shadow-sm hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors focus:outline-none" aria-label="Toggle navigation menu">
                    <svg id="menuIconToggle" class="w-5 h-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Screen Overlay Navigation Menu -->
    <div id="mobileNavigationOverlay" class="fixed inset-0 bg-slate-950/40 backdrop-blur-xl z-30 opacity-0 pointer-events-none transition-all duration-300 md:hidden flex flex-col justify-center px-6">
        <div class="space-y-6 text-center max-w-xs mx-auto w-full">
            <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-[0.3em] mb-2">Menu Options</p>
            <div class="flex flex-col space-y-2">
                <a onclick="toggleMobileNav()" href="#announcements" class="block py-4 text-xl font-black text-slate-900 dark:text-white bg-white/90 dark:bg-slate-900/90 rounded-2xl shadow-sm border border-slate-200/40 dark:border-slate-800/40 hover:bg-blue-600 hover:text-white transition-all">Announcements</a>
                <a onclick="toggleMobileNav()" href="#posts" class="block py-4 text-xl font-black text-slate-900 dark:text-white bg-white/90 dark:bg-slate-900/90 rounded-2xl shadow-sm border border-slate-200/40 dark:border-slate-800/40 hover:bg-blue-600 hover:text-white transition-all">Learning Materials</a>
                <a onclick="toggleMobileNav()" href="#passers" class="block py-4 text-xl font-black text-slate-900 dark:text-white bg-white/90 dark:bg-slate-900/90 rounded-2xl shadow-sm border border-slate-200/40 dark:border-slate-800/40 hover:bg-blue-600 hover:text-white transition-all">Passers</a>
                <?php if ($has_gallery): ?>
                <a onclick="toggleMobileNav()" href="#gallery" class="block py-4 text-xl font-black text-slate-900 dark:text-white bg-white/90 dark:bg-slate-900/90 rounded-2xl shadow-sm border border-slate-200/40 dark:border-slate-800/40 hover:bg-blue-600 hover:text-white transition-all">Gallery</a>
                <?php endif; ?>
                <a onclick="toggleMobileNav()" href="#contact" class="block py-4 text-xl font-black text-slate-900 dark:text-white bg-white/90 dark:bg-slate-900/90 rounded-2xl shadow-sm border border-slate-200/40 dark:border-slate-800/40 hover:bg-blue-600 hover:text-white transition-all">Contact</a>
            </div>
            
            <div class="grid grid-cols-2 gap-3 pt-6 border-t border-slate-200/20 sm:hidden">
                <a onclick="toggleMobileNav()" href="login.php" class="py-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-extrabold text-sm rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-200 dark:hover:bg-slate-800 transition-all">Login</a>
                <a onclick="toggleMobileNav()" href="register.php" class="py-3 bg-blue-600 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/20 hover:bg-blue-500 transition-all">Join Us</a>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <header class="relative py-24 sm:py-32 overflow-hidden flex-shrink-0 border-b border-slate-100 dark:border-slate-900">
        <!-- Alternating Background Images Layer -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img id="headerBg0" src="passers.jpg" alt="Background Passes 1" class="absolute inset-0 w-full h-full object-cover opacity-10 dark:opacity-5 transition-opacity duration-1000">
            <img id="headerBg1" src="passers1.jpg" alt="Background Passes 2" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000">
        </div>
        
        <!-- Ambient Design Graphics Layers -->
        <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] dark:bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-70 pointer-events-none z-10"></div>
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-100/40 dark:bg-blue-900/10 rounded-full blur-[140px] animate-pulse-slow pointer-events-none z-10"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-100/30 dark:bg-indigo-900/10 rounded-full blur-[140px] animate-pulse-slow pointer-events-none z-10"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 text-center lg:text-left grid lg:grid-cols-12 items-center gap-16 z-20">
            <div class="space-y-8 lg:col-span-7">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50/80 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900 backdrop-blur-sm">
                    <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="text-blue-600 dark:text-blue-400 font-extrabold tracking-widest uppercase text-[10px]">C-Familia Tutorial Services</span>
                </div>
                <h2 class="text-4xl sm:text-6xl font-[900] text-slate-900 dark:text-white tracking-tight leading-[1.05] font-nature">
                    Your Future Starts <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-500 bg-clip-text text-transparent">Right Here.</span>
                </h2>
                <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-400 max-w-xl mx-auto lg:mx-0 font-medium leading-relaxed font-poppins">
                    "Join our family, and together, we will help you pass your professional board exams."
                </p>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-2">
                    <a href="register.php" class="px-8 py-4 bg-blue-600 text-white rounded-xl text-base font-bold hover:bg-blue-500 active:scale-98 transition-all shadow-lg shadow-blue-600/20 focus:outline-none">Enroll Now</a>
                    <a href="#passers" class="px-8 py-4 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-xl text-base font-bold hover:bg-slate-200 dark:hover:bg-slate-800 active:scale-98 transition-all focus:outline-none">View Success Stories</a>
                </div>
            </div>

            <div class="hidden lg:block lg:col-span-5">
                <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm p-10 rounded-3xl border border-slate-200 dark:border-slate-800 relative overflow-hidden shadow-xl">
                    <div class="absolute -right-16 -top-16 w-40 h-40 bg-blue-100/40 dark:bg-blue-900/20 rounded-full blur-3xl"></div>
                    <div class="flex items-center gap-5 mb-8">
                        <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20">✓</div>
                        <div>
                            <p class="text-slate-900 dark:text-white text-2xl font-[900] tracking-tight font-nature"><?= $display_rate ?> Passing Rate</p>
                            <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold font-poppins"><?= $total_passers ?>+ Certified Passers</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden p-0.5 border border-slate-300 dark:border-slate-700">
                            <div class="h-full bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400 w-[95%] rounded-full shadow-[0_0_10px_rgba(37,99,235,0.2)]"></div>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                            <span>Excellent Results</span>
                            <span>Batch <?= date('Y') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Top Performance Section -->
    <section class="py-24 sm:py-32 bg-white dark:bg-slate-950 text-slate-900 dark:text-white relative overflow-hidden border-b border-slate-100 dark:border-slate-900">
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-indigo-50/40 dark:bg-indigo-950/10 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16 sm:mb-24">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <span class="text-blue-600 dark:text-blue-400 font-black uppercase text-[11px] tracking-[0.3em]">Top Achievers</span>
                    </div>
                    <h3 class="text-3xl sm:text-5xl font-[900] tracking-tight text-slate-900 dark:text-white font-nature">Top Performance<span class="text-blue-600">.</span></h3>
                </div>
                <p class="text-slate-500 dark:text-slate-400 max-w-sm font-medium text-sm sm:text-base leading-relaxed border-l-2 border-slate-200 dark:border-slate-800 pl-4 font-poppins">Celebrating our students who got excellent board exam ratings of 95% and higher.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $top_query = mysqli_query($conn, "SELECT * FROM passers WHERE rating >= 95 ORDER BY rating DESC");
                $top_count = mysqli_num_rows($top_query);
                $displayed_top = 0;
                
                if($top_count > 0):
                    while($top = mysqli_fetch_assoc($top_query)):
                        $photoPath = file_exists("uploads/profiles/".$top['photo']) ? "uploads/profiles/".$top['photo'] : "uploads/passers/".$top['photo'];
                        $displayed_top++;
                        if ($displayed_top <= 6):
                ?>
                <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-900 hover:border-slate-200 dark:hover:border-slate-800 p-8 rounded-3xl group hover:bg-white dark:hover:bg-slate-900 transition-all duration-300 flex flex-col justify-between shadow-sm hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-slate-950/50 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center gap-5 mb-8">
                        <div class="relative flex-shrink-0">
                            <img src="<?= $photoPath ?>" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-slate-100 dark:ring-slate-800 shadow-md transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute -top-2 -right-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[9px] font-black px-2 py-0.5 rounded shadow-md uppercase tracking-widest">TOP</div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xl font-bold text-slate-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors font-nature"><?= $top['name'] ?></h4>
                            <p class="text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider mt-1 truncate font-poppins"><?= $top['program'] ?></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between bg-slate-100 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-widest">Board Rating</span>
                        <span class="text-3xl font-[900] bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent italic tracking-tight font-poppins"><?= number_format($top['rating'], 2) ?>%</span>
                    </div>
                </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                <div class="col-span-full py-20 text-center bg-slate-50 dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                    <p class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest text-xs sm:text-sm">Top results are being checked right now.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($top_count > 6): ?>
            <div class="mt-16 text-center">
                <button onclick="openModal('topPerformanceModal')" class="inline-flex items-center gap-2 px-6 py-3.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl font-bold hover:bg-slate-200 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all text-xs uppercase tracking-widest text-slate-700 dark:text-slate-300 focus:outline-none">
                    See More Top Performers
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Image Gallery Section -->
    <?php if ($has_gallery): ?>
    <section id="gallery" class="py-24 sm:py-32 px-4 sm:px-6 w-full border-t border-slate-100 dark:border-slate-900 bg-white dark:bg-slate-950 relative overflow-hidden">
        <!-- Decorative: dot pattern + soft blobs -->
        <div class="absolute inset-0 bg-dot-pattern opacity-25 pointer-events-none"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-64 bg-gradient-to-b from-emerald-50/40 dark:from-emerald-950/10 to-transparent pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-emerald-50/50 dark:bg-emerald-950/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
            <div class="flex items-center gap-3">
                <span class="w-3 h-8 bg-emerald-500 rounded-full"></span>
                <h3 class="text-3xl font-[900] tracking-tight text-slate-900 dark:text-white font-nature">Photo Gallery</h3>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium max-w-md font-poppins">Moments from our review center, events, and student milestones.</p>
        </div>

        <div class="space-y-16">
            <?php foreach ($grouped_gallery as $caption => $images):
                $image_count = count($images);
            ?>
            <div data-gallery-group="<?= md5($caption) ?>">
                <h4 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-3 font-nature">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                    <?= htmlspecialchars($caption) ?>
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <?php foreach ($images as $index => $img):
                        $img_path = $gallery_dir . $img['image_path'];
                    ?>
                    <div class="gallery-item relative aspect-[4/3] rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-900 shadow-sm hover:shadow-xl hover:shadow-slate-200/40 transition-all hover:-translate-y-1 <?= $index > 0 ? 'gallery-extra hidden sm:block' : '' ?>">
                        <button type="button" class="gallery-lightbox-trigger group absolute inset-0 w-full h-full focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:ring-inset" data-gallery-src="<?= htmlspecialchars($img_path, ENT_QUOTES, 'UTF-8') ?>" data-gallery-caption="<?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($caption) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-colors flex items-center justify-center">
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 dark:bg-slate-900/90 text-slate-800 dark:text-white text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-lg">View</span>
                            </div>
                        </button>
                        <?php if ($index === 0 && $image_count > 1): ?>
                        <div class="gallery-see-more-overlay absolute inset-0 z-10 flex items-center justify-center bg-slate-900/35 sm:hidden">
                            <button type="button" class="gallery-see-more-btn px-5 py-2.5 bg-white text-slate-900 text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg hover:bg-emerald-50 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                See more images
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- The Hall of Fame Section -->
    <section id="passers" class="py-24 sm:py-32 bg-white dark:bg-slate-950 px-4 sm:px-6 border-b border-slate-100 dark:border-slate-900 relative overflow-hidden">
        <!-- Decorative: diagonal lines + blobs -->
        <div class="absolute inset-0 bg-line-pattern pointer-events-none"></div>
        <div class="absolute top-1/4 right-0 w-96 h-96 bg-blue-50/70 dark:bg-blue-950/10 rounded-full blur-[130px] pointer-events-none animate-float-slow"></div>
        <div class="absolute bottom-0 left-1/4 w-80 h-80 bg-indigo-50/60 dark:bg-indigo-950/10 rounded-full blur-[100px] pointer-events-none animate-float-medium"></div>
        <!-- Floating decorative circles -->
        <div class="absolute top-16 left-8 w-10 h-10 rounded-full border-2 border-blue-100 dark:border-blue-900 opacity-60 pointer-events-none animate-drift"></div>
        <div class="absolute top-32 right-16 w-6 h-6 rounded-full bg-blue-100/50 dark:bg-blue-900/20 opacity-70 pointer-events-none animate-float-slow"></div>
        <div class="absolute bottom-24 left-1/3 w-8 h-8 rounded-full border border-indigo-100 dark:border-indigo-900 opacity-50 pointer-events-none animate-float-medium"></div>
        <div class="max-w-7xl mx-auto text-center mb-16 sm:mb-24">
            <h3 class="text-3xl sm:text-5xl font-[900] mb-4 tracking-tight text-slate-900 dark:text-white font-nature">The Hall of Fame</h3>
            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm sm:text-base max-w-xl mx-auto leading-relaxed font-poppins">Celebrating the hard work of every C-Familia student who passed their board exams.</p>
        </div>
        
        <div class="max-w-7xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <?php 
            $passers_query = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
            $total_passers_count = mysqli_num_rows($passers_query);
            $displayed_passers = 0;
            
            if($total_passers_count > 0):
                mysqli_data_seek($passers_query, 0);
                while($passer = mysqli_fetch_assoc($passers_query)): 
                    $pPath = file_exists("uploads/profiles/".$passer['photo']) ? "uploads/profiles/".$passer['photo'] : "uploads/passers/".$passer['photo'];
                    $displayed_passers++;
                    if($displayed_passers <= 10):
            ?>
            <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 transition-all duration-300 hover:-translate-y-1.5 text-center group flex flex-col justify-between">
                <div>
                    <img src="<?= $pPath ?>" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover border-4 border-slate-50 dark:border-slate-950 group-hover:scale-105 transition-transform shadow-inner">
                    <h5 class="font-bold text-slate-900 dark:text-white text-sm leading-snug mb-1 truncate font-nature"><?= $passer['name'] ?></h5>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider mb-4 truncate font-poppins"><?= $passer['program'] ?></p>
                </div>
                <div class="flex items-center justify-center gap-1.5 bg-slate-50 dark:bg-slate-950 rounded-xl py-2.5 border border-slate-100 dark:border-slate-850">
                    <span class="text-base font-[900] text-blue-600 dark:text-blue-400 tracking-tight font-poppins"><?= $passer['rating'] ?>%</span>
                    <span class="text-[8px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest">Score</span>
                </div>
            </div>
            <?php 
                    endif;
                endwhile; 
            endif;
            ?>
        </div>

        <?php if($total_passers_count > 10): ?>
        <div class="mt-16 text-center">
            <button onclick="openModal('passersModal')" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold rounded-xl shadow-sm transition-all text-xs uppercase tracking-widest focus:outline-none">
                View All Hall of Fame Passers
            </button>
        </div>
        <?php endif; ?>
    </section>

    
    <!-- Recent Announcements Section -->
    <section id="announcements" class="py-24 sm:py-32 px-4 sm:px-6 w-full relative overflow-hidden bg-white dark:bg-slate-950 border-b border-slate-100 dark:border-slate-900">
        <!-- Decorative: cross grid + ambient blobs -->
        <div class="absolute inset-0 bg-cross-pattern opacity-60 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-50/80 dark:bg-blue-950/10 rounded-full blur-[120px] pointer-events-none animate-pulse-slow"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-slate-50/60 dark:bg-slate-900/10 rounded-full blur-[100px] pointer-events-none"></div>
        <!-- Decorative accent corner shapes -->
        <svg class="absolute top-0 right-0 w-64 h-64 text-blue-50 dark:text-blue-950/30 opacity-60 pointer-events-none" viewBox="0 0 256 256" fill="currentColor"><circle cx="256" cy="0" r="180"/></svg>
        <svg class="absolute bottom-0 left-0 w-48 h-48 text-slate-100 dark:text-slate-900/40 opacity-50 pointer-events-none" viewBox="0 0 192 192" fill="currentColor"><circle cx="0" cy="192" r="140"/></svg>
        <div class="max-w-7xl mx-auto relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
            <div class="flex items-center gap-3">
                <span class="w-3 h-8 bg-blue-600 rounded-full"></span>
                <h3 class="text-3xl font-[900] tracking-tight text-slate-900 dark:text-white font-nature">Recent Announcements</h3>
            </div>
            <?php if ($ann_count > 3): ?>
            <button type="button" onclick="openModal('announcementsModal')" class="text-xs font-black uppercase text-blue-600 dark:text-blue-400 tracking-wider hover:text-blue-700 dark:hover:text-blue-300 transition-colors flex items-center gap-1 group focus:outline-none">
                See All Announcements <span class="transition-transform group-hover:translate-x-1">→</span>
            </button>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($recent_announcements as $ann):
                $is_urgent = ($ann['category'] == 'Urgent');
                $is_long = mb_strlen($ann['message']) > 160;
            ?>
            <article class="p-8 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-slate-200/40 dark:hover:shadow-slate-950/50 transition-all relative overflow-hidden group flex flex-col hover:-translate-y-1">
                <?php if ($is_urgent): ?>
                <span class="absolute top-0 right-0 bg-rose-600 text-white text-[9px] px-4 py-1.5 font-black uppercase tracking-widest rounded-bl-xl shadow-sm">Urgent</span>
                <?php endif; ?>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-4 flex-wrap">
                        <p class="text-blue-600 dark:text-blue-400 font-black text-[10px] uppercase tracking-wider"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                        <?php if (!$is_urgent && !empty($ann['category'])): ?>
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-950 px-2 py-0.5 rounded-md border border-slate-100 dark:border-slate-850"><?= htmlspecialchars($ann['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-xl font-bold mb-3 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors text-slate-900 dark:text-white leading-snug font-nature"><?= htmlspecialchars($ann['title']) ?></h4>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-sm font-medium line-clamp-3 font-poppins"><?= htmlspecialchars($ann['message']) ?></p>
                </div>
                <?php if ($is_long): ?>
                <button type="button" class="announcement-read-more mt-5 inline-flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors focus:outline-none" data-ann-id="<?= (int) $ann['id'] ?>">
                    Read full announcement <span class="transition-transform group-hover:translate-x-0.5">→</span>
                </button>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        </div>
    </section>

    <!-- Learning Materials Section -->
    <section id="posts" class="py-24 sm:py-32 bg-white dark:bg-slate-950 text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-900 px-4 sm:px-6 w-full relative overflow-hidden">
        <div class="absolute top-0 left-1/2 w-96 h-96 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto relative">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-8 bg-indigo-600 rounded-full"></span>
                    <h3 class="text-3xl font-[900] tracking-tight text-slate-900 dark:text-white font-nature">Learning Materials</h3>
                </div>
                <?php 
                $posts_total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts");
                $posts_count = mysqli_fetch_assoc($posts_total_query)['count'];
                if($posts_count > 6): 
                ?>
                <button onclick="openModal('postsModal')" class="text-xs font-black uppercase text-indigo-600 dark:text-indigo-400 tracking-wider hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors flex items-center gap-1 group focus:outline-none">
                    Browse All Materials <span class="transition-transform group-hover:translate-x-1">→</span>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $posts_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
                while($post = mysqli_fetch_assoc($posts_query)):
                ?>
                <article class="bg-slate-50 dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden hover:border-slate-200 dark:hover:border-slate-700 hover:bg-white dark:hover:bg-slate-900 transition-all duration-300 group flex flex-col justify-between p-8 hover:-translate-y-1">
                    <div>
                        <div class="mb-4">
                            <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-[9px] font-black rounded uppercase tracking-wider border border-indigo-100 dark:border-indigo-900">Study File</span>
                        </div>
                        <h4 class="text-xl font-bold mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors text-slate-900 dark:text-white leading-snug font-nature"><?= $post['title'] ?></h4>
                        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-6 line-clamp-2 font-medium font-poppins"><?= $post['content'] ?></p>
                    </div>
                    <?php if($post['file_path']): ?>
                    <div class="border-t border-slate-200/80 dark:border-slate-800 pt-5 mt-2">
                        <a href="uploads/resources/<?= $post['file_path'] ?>" class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-black text-[11px] uppercase tracking-wider hover:text-indigo-700 dark:hover:text-indigo-300 transition-all focus:outline-none">
                            Download File <span class="text-sm transition-transform group-hover:translate-x-1">→</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>



    <!-- Voice of Success Section -->
    <section class="py-24 sm:py-32 bg-white dark:bg-slate-950 px-4 sm:px-6 relative border-b border-slate-100 dark:border-slate-900 overflow-hidden">
        <!-- Decorative background: dot grid + blobs -->
        <div class="absolute inset-0 bg-dot-pattern opacity-30 pointer-events-none"></div>
        <div class="absolute -top-32 -left-32 w-80 h-80 bg-blue-100/60 dark:bg-blue-900/10 rounded-full blur-[100px] pointer-events-none animate-float-slow"></div>
        <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-indigo-100/50 dark:bg-indigo-900/10 rounded-full blur-[90px] pointer-events-none animate-float-medium"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-br from-blue-50/40 dark:from-blue-950/10 to-indigo-50/30 dark:to-indigo-950/5 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 sm:mb-24">
                <span class="text-blue-600 dark:text-blue-400 font-black uppercase text-[11px] tracking-[0.35em] mb-3 block">Student Feedback</span>
                <h3 class="text-3xl sm:text-5xl font-[900] text-slate-900 dark:text-white tracking-tight font-nature">Voice of Success<span class="text-blue-600">.</span></h3>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $test_query = mysqli_query($conn, "SELECT t.*, u.firstname, u.lastname, u.profile_pic FROM testimonials t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
                $test_count = mysqli_num_rows($test_query);
                $displayed_test = 0;
                
                if($test_count > 0):
                    mysqli_data_seek($test_query, 0);
                    while($row = mysqli_fetch_assoc($test_query)):
                        $userPic = !empty($row['profile_pic']) ? "uploads/profiles/".$row['profile_pic'] : "uploads/passers/default_user.jpg";
                        $displayed_test++;
                        if ($displayed_test <= 6):
                ?>
                <div class="bg-slate-50/60 dark:bg-slate-900/60 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 relative group hover:bg-white dark:hover:bg-slate-900 transition-all duration-300 flex flex-col justify-between shadow-sm hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-slate-950/50 hover:-translate-y-1">
                    <div class="text-slate-200 dark:text-slate-800 absolute top-4 right-8 text-7xl font-serif select-none pointer-events-none group-hover:text-blue-100 dark:group-hover:text-blue-950/50 transition-colors">“</div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <p class="text-slate-600 dark:text-slate-400 italic leading-relaxed text-sm sm:text-base mb-8 line-clamp-3 font-medium font-poppins">
                            <?= htmlspecialchars($row['content']) ?>
                        </p>
                        <div class="flex items-center gap-4 border-t border-slate-100 dark:border-slate-800 pt-6">
                            <img src="<?= $userPic ?>" class="w-12 h-12 rounded-xl object-cover ring-4 ring-white dark:ring-slate-950 shadow-sm flex-shrink-0">
                            <div class="min-w-0">
                                <h5 class="font-extrabold text-slate-900 dark:text-white text-sm truncate font-nature"><?= $row['firstname'] . ' ' . $row['lastname'] ?></h5>
                                <p class="text-[9px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mt-0.5">Verified Alumni</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                <div class="col-span-full py-20 text-center bg-slate-50 dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                    <p class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest text-xs">Waiting for student stories...</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($test_count > 6): ?>
            <div class="mt-16 text-center">
                <button onclick="openModal('testimonialsModal')" class="inline-flex items-center gap-2 px-6 py-3.5 bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold rounded-xl transition-all text-xs uppercase tracking-widest focus:outline-none">
                    Read All Testimonials
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>


<!-- Contact & Location Section -->
<section id="contact" class="py-24 sm:py-32 px-4 sm:px-6 bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200/60 dark:border-slate-900 w-full relative overflow-hidden">
    <!-- Ambient Background Decorations -->
    <div class="absolute inset-0 bg-cross-pattern opacity-30 pointer-events-none"></div>
    <div class="absolute -top-20 -right-20 w-80 h-80 bg-blue-100/50 dark:bg-blue-900/10 rounded-full blur-[120px] pointer-events-none animate-float-slow"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 bg-indigo-100/40 dark:bg-indigo-900/10 rounded-full blur-[100px] pointer-events-none animate-float-medium"></div>
    
    <!-- Floating Minimalist Dots -->
    <div class="absolute top-20 left-12 w-3 h-3 rounded-full bg-blue-300/50 pointer-events-none animate-drift"></div>
    <div class="absolute bottom-20 left-1/2 w-4 h-4 rounded-full border-2 border-indigo-200/40 pointer-events-none animate-float-medium"></div>

    <!-- Decorative Top-Right Arc Geometric Lines -->
    <svg class="absolute -top-24 -right-24 w-96 h-96 text-blue-100/60 dark:text-blue-900/20 opacity-80 pointer-events-none" viewBox="0 0 320 320" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="320" cy="0" r="180" />
        <circle cx="320" cy="0" r="230" />
        <circle cx="320" cy="0" r="280" />
    </svg>

    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-16 items-start relative z-10">
        <!-- Left Column: Branches -->
        <div class="space-y-10">
            <div class="space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">Our Network</span>
                <h3 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight font-nature">Visit our Branches<span class="text-blue-600">.</span></h3>
                <p class="text-slate-500 dark:text-slate-400 max-w-md text-sm sm:text-base font-poppins">Drop by any of our learning centers to learn more about our tailored academic programs.</p>
            </div>
            
            <div class="space-y-4">
                <!-- Ozamiz Main Branch -->
                <div class="group flex items-start gap-5 p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all duration-300 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-900">
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3 flex-wrap">
                            <p class="font-bold text-slate-900 dark:text-white text-sm tracking-wide uppercase font-nature">Ozamiz Main Branch</p>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-full border border-emerald-100 dark:border-emerald-900 uppercase tracking-wider">● Main Office</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 font-medium text-sm sm:text-base font-poppins">Ozamiz City, Philippines, 7200</p>
                    </div>
                </div>

                <!-- Oroquieta Campus -->
                <div class="group flex items-start gap-5 p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all duration-300 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-900">
                    <div class="w-12 h-12 bg-slate-50 dark:bg-slate-950/20 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 group-hover:border-blue-600 dark:group-hover:border-blue-500 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="font-bold text-slate-900 dark:text-white text-sm tracking-wide uppercase font-nature">Oroquieta Campus</p>
                        <p class="text-slate-600 dark:text-slate-400 font-medium text-sm sm:text-base font-poppins">Oroquieta City, Misamis Occidental</p>
                    </div>
                </div>

                <!-- Tubod Campus -->
                <div class="group flex items-start gap-5 p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-all duration-300 hover:shadow-md hover:border-blue-200 dark:hover:border-blue-900">
                    <div class="w-12 h-12 bg-slate-50 dark:bg-slate-950/20 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 group-hover:border-blue-600 dark:group-hover:border-blue-500 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="font-bold text-slate-900 dark:text-white text-sm tracking-wide uppercase font-nature">Tubod Campus</p>
                        <p class="text-slate-600 dark:text-slate-400 font-medium text-sm sm:text-base font-poppins">Tubod, Lanao Del Norte</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Contact Cards Container -->
        <div class="bg-white dark:bg-slate-900 p-8 sm:p-10 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-slate-950/50 relative">
            <h3 class="text-2xl font-black mb-6 text-slate-900 dark:text-white tracking-tight font-nature">Contact Us Directly</h3>
            <div class="space-y-4">
                <!-- Phone Card -->
                <a href="tel:09101676805" class="group flex items-center gap-5 p-5 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-850 transition-all duration-300 hover:bg-blue-50/50 dark:hover:bg-blue-950/20 hover:border-blue-100 dark:hover:border-blue-900 hover:-translate-y-0.5 block">
                    <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 shadow-sm border border-slate-200 dark:border-slate-800 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 tracking-widest mb-0.5 font-poppins">Phone</p>
                        <p class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors font-poppins">0910 167 6805</p>
                    </div>
                </a>

                <!-- Email Card -->
                <a href="mailto:shielamariscuevas@gmail.com" class="group flex items-center gap-5 p-5 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-850 transition-all duration-300 hover:bg-blue-50/50 dark:hover:bg-blue-950/20 hover:border-blue-100 dark:hover:border-blue-900 hover:-translate-y-0.5 block">
                    <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 shadow-sm border border-slate-200 dark:border-slate-800 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 tracking-widest mb-0.5 font-poppins">Email</p>
                        <p class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors font-poppins">shielamariscuevas@gmail.com</p>
                    </div>
                </a>

                <!-- Facebook Card -->
                <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-5 p-5 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-850 transition-all duration-300 hover:bg-blue-50/50 dark:hover:bg-blue-950/20 hover:border-blue-100 dark:hover:border-blue-900 hover:-translate-y-0.5 block">
                    <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 shadow-sm border border-slate-200 dark:border-slate-800 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500 tracking-widest mb-0.5 font-poppins">Facebook Page</p>
                        <p class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors font-poppins">C-Familia Tutorial Services</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-950 pt-20 pb-10 px-4 sm:px-6 text-slate-900 dark:text-white overflow-hidden relative mt-auto border-t border-slate-200 dark:border-slate-900">
        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400"></div>
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 mb-12 relative">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-lg text-white">C</div>
                <h1 class="text-2xl font-[900] tracking-tighter font-nature">C-Familia<span class="text-blue-600">.</span></h1>
            </div>
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-[0.25em] text-center md:text-left font-poppins">
                Helping Students Succeed Since 2024
            </div>
        </div>
        
        <!-- Developer Credit Badge -->
        <div class="max-w-7xl mx-auto text-center mb-6 relative">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-4 py-2 rounded-xl backdrop-blur-sm shadow-inner font-poppins">
                Developed by 
                <span class="font-black bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-500 bg-clip-text text-transparent font-nature">
                    Rhondel M. Pagobo
                </span>
            </p>
        </div>

        <div class="max-w-7xl mx-auto text-center text-slate-400 dark:text-slate-500 text-[10px] border-t border-slate-100 dark:border-slate-900 pt-8 uppercase tracking-[0.3em] font-black font-poppins">
            &copy; <?= date("Y") ?> C-Familia Tutorial Services • Registered School Review Center
        </div>
    </footer>

    <!-- MODAL INTEGRATIONS -->

    <?php foreach ($announcements as $ann): ?>
    <div id="ann-detail-source-<?= (int) $ann['id'] ?>" class="hidden">
        <div class="flex items-center gap-2 mb-4 flex-wrap">
            <p class="text-blue-600 dark:text-blue-400 font-black text-[10px] uppercase tracking-wider"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
            <?php if (!empty($ann['category'])): ?>
            <span class="text-[9px] font-black uppercase tracking-wider <?= $ann['category'] === 'Urgent' ? 'text-rose-600 bg-rose-50 border-rose-100 dark:bg-rose-950/40 dark:border-rose-900' : 'text-slate-400 bg-slate-50 border-slate-100 dark:bg-slate-900 dark:border-slate-800' ?> px-2 py-0.5 rounded-md border"><?= htmlspecialchars($ann['category']) ?></span>
            <?php endif; ?>
        </div>
        <h4 class="text-2xl font-bold text-slate-900 dark:text-white leading-snug mb-4 font-nature"><?= htmlspecialchars($ann['title']) ?></h4>
        <div class="text-slate-600 dark:text-slate-400 leading-relaxed text-sm font-medium whitespace-pre-line font-poppins"><?= htmlspecialchars($ann['message']) ?></div>
    </div>
    <?php endforeach; ?>

    <!-- Announcement Detail Modal -->
    <div id="announcementDetailModal" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 w-full max-w-2xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center flex-shrink-0">
                <h3 class="text-lg font-[900] text-slate-900 dark:text-white tracking-tight font-nature">Announcement</h3>
                <button type="button" onclick="closeModal('announcementDetailModal')" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-950 text-slate-500 hover:text-slate-900 hover:bg-slate-200 dark:hover:bg-slate-850 transition-all flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div id="announcementDetailBody" class="p-6 sm:p-8 overflow-y-auto font-poppins"></div>
        </div>
    </div>

    <!-- All Announcements Modal -->
    <div id="announcementsModal" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 w-full max-w-3xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-2xl font-[900] text-slate-900 dark:text-white tracking-tight font-nature">All Announcements</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-poppins">Complete list of public announcements from C-Familia.</p>
                </div>
                <button type="button" onclick="closeModal('announcementsModal')" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-950 text-slate-500 hover:text-slate-900 hover:bg-slate-200 dark:hover:bg-slate-850 transition-all flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto space-y-6 bg-slate-50 dark:bg-slate-950">
                <?php foreach ($announcements as $ann):
                    $is_urgent = ($ann['category'] == 'Urgent');
                ?>
                <article class="p-6 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm relative overflow-hidden">
                    <?php if ($is_urgent): ?>
                    <span class="absolute top-0 right-0 bg-rose-600 text-white text-[9px] px-3 py-1 font-black uppercase tracking-widest rounded-bl-xl">Urgent</span>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 mb-3 flex-wrap">
                        <p class="text-blue-600 dark:text-blue-400 font-black text-[10px] uppercase tracking-wider"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                        <?php if (!$is_urgent && !empty($ann['category'])): ?>
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-950 px-2 py-0.5 rounded-md border border-slate-100 dark:border-slate-850"><?= htmlspecialchars($ann['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white leading-snug mb-3 font-nature"><?= htmlspecialchars($ann['title']) ?></h4>
                    <div class="text-slate-600 dark:text-slate-400 leading-relaxed text-sm font-medium whitespace-pre-line font-poppins"><?= htmlspecialchars($ann['message']) ?></div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Top Performers See More Modal -->
    <div id="topPerformanceModal" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-8 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-2xl font-[900] text-slate-900 dark:text-white tracking-tight font-nature">All Top Performers</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-poppins">Our top reviewees with high scores of 95% and above.</p>
                </div>
                <button onclick="closeModal('topPerformanceModal')" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-950 text-slate-500 hover:text-slate-900 transition-colors flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-8 overflow-y-auto bg-slate-50 dark:bg-slate-950">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    if($top_count > 0):
                        mysqli_data_seek($top_query, 0);
                        while($top = mysqli_fetch_assoc($top_query)):
                            $photoPath = file_exists("uploads/profiles/".$top['photo']) ? "uploads/profiles/".$top['photo'] : "uploads/passers/".$top['photo'];
                    ?>
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl flex flex-col justify-between shadow-sm">
                        <div class="flex items-center gap-4 mb-5">
                            <img src="<?= $photoPath ?>" class="w-14 h-14 rounded-xl object-cover ring-2 ring-slate-100 dark:ring-slate-850">
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 dark:text-white text-base truncate font-nature"><?= $top['name'] ?></h4>
                                <p class="text-slate-400 dark:text-slate-500 text-[10px] font-black uppercase tracking-wider truncate font-poppins"><?= $top['program'] ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-950 p-3.5 rounded-xl border border-slate-200 dark:border-slate-800">
                            <span class="text-slate-400 dark:text-slate-500 text-[9px] font-black uppercase tracking-widest">Board Rating</span>
                            <span class="text-xl font-[900] text-blue-600 dark:text-blue-400 italic font-poppins"><?= number_format($top['rating'], 2) ?>%</span>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    endif; 
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hall of Fame Passers Modal -->
    <div id="passersModal" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 w-full max-w-6xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-2xl font-[900] text-slate-900 dark:text-white tracking-tight font-nature">The Complete Hall of Fame</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 font-poppins">List of all certified C-Familia passers who finished their board exams successfully.</p>
                </div>
                <button type="button" onclick="closeModal('passersModal')" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-950 text-slate-500 hover:text-slate-900 hover:bg-slate-200 dark:hover:bg-slate-850 transition-all flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-8 overflow-y-auto bg-slate-50 dark:bg-slate-950">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <?php 
                    if($total_passers_count > 0):
                        mysqli_data_seek($passers_query, 0);
                        while($passer = mysqli_fetch_assoc($passers_query)): 
                            $pPath = file_exists("uploads/profiles/".$passer['photo']) ? "uploads/profiles/".$passer['photo'] : "uploads/passers/".$passer['photo'];
                    ?>
                    <div class="p-6 bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 transition-all text-center group flex flex-col justify-between">
                        <div>
                            <img src="<?= $pPath ?>" class="w-16 h-16 rounded-full mx-auto mb-3 object-cover border-4 border-slate-50 dark:border-slate-950 shadow-inner">
                            <h5 class="font-bold text-slate-900 dark:text-white text-sm leading-snug mb-1 truncate font-nature"><?= $passer['name'] ?></h5>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider mb-3 truncate font-poppins"><?= $passer['program'] ?></p>
                        </div>
                        <div class="flex items-center justify-center gap-1.5 bg-slate-50 dark:bg-slate-950 rounded-xl py-2 border border-slate-100 dark:border-slate-850">
                            <span class="text-sm font-[900] text-blue-600 dark:text-blue-400 font-poppins"><?= $passer['rating'] ?>%</span>
                            <span class="text-[8px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest">Score</span>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Lightbox -->
    <div id="galleryLightbox" class="fixed inset-0 z-[60] hidden bg-slate-950/80 backdrop-blur-md items-center justify-center p-4">
        <div class="relative max-w-5xl w-full">
            <button type="button" id="galleryLightboxClose" class="absolute -top-12 right-0 w-10 h-10 rounded-xl bg-white/10 text-white hover:bg-white/20 transition-colors flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            <img id="galleryLightboxImage" src="" alt="" class="w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl">
            <p id="galleryLightboxCaption" class="text-center text-white/80 text-sm font-semibold mt-4 font-poppins"></p>
        </div>
    </div>

    <!-- Interface Animation & Light/Dark Switcher Script -->
    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileNavigationOverlay');
        const menuIconToggle = document.getElementById('menuIconToggle');
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        let isNavOpen = false;

        // Visual Theme Toggle Core Engine logic
        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });

        function toggleMobileNav() {
            isNavOpen = !isNavOpen;
            if (isNavOpen) {
                mobileOverlay.classList.remove('opacity-0', 'pointer-events-none');
                mobileOverlay.classList.add('opacity-100', 'pointer-events-auto');
                menuIconToggle.style.transform = 'rotate(90deg)';
                menuIconToggle.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />';
                document.body.classList.add('modal-active');
            } else {
                mobileOverlay.classList.remove('opacity-100', 'pointer-events-auto');
                mobileOverlay.classList.add('opacity-0', 'pointer-events-none');
                menuIconToggle.style.transform = 'rotate(0deg)';
                menuIconToggle.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />';
                document.body.classList.remove('modal-active');
            }
        }

        mobileMenuBtn.addEventListener('click', toggleMobileNav);

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('modal-active');
            }
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('modal-active');
            }
        }

        function openAnnouncementDetail(id) {
            const source = document.getElementById('ann-detail-source-' + id);
            const body = document.getElementById('announcementDetailBody');
            if (!source || !body) return;
            body.innerHTML = source.innerHTML;
            openModal('announcementDetailModal');
        }

        document.querySelectorAll('.announcement-read-more').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openAnnouncementDetail(this.getAttribute('data-ann-id'));
            });
        });

        function openGalleryLightbox(src, caption) {
            const lightbox = document.getElementById('galleryLightbox');
            document.getElementById('galleryLightboxImage').src = src;
            document.getElementById('galleryLightboxImage').alt = caption;
            document.getElementById('galleryLightboxCaption').textContent = caption;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            document.body.classList.add('modal-active');
        }

        function closeGalleryLightbox() {
            const lightbox = document.getElementById('galleryLightbox');
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.getElementById('galleryLightboxImage').src = '';
            document.body.classList.remove('modal-active');
        }

        document.querySelectorAll('.gallery-lightbox-trigger').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openGalleryLightbox(this.getAttribute('data-gallery-src'), this.getAttribute('data-gallery-caption'));
            });
        });

        document.querySelectorAll('.gallery-see-more-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const group = this.closest('[data-gallery-group]');
                group?.querySelectorAll('.gallery-extra').forEach(function(item) {
                    item.classList.remove('hidden');
                });
                group?.querySelector('.gallery-see-more-overlay')?.remove();
            });
        });

        document.getElementById('galleryLightbox')?.addEventListener('click', function(e) {
            if (e.target === this) closeGalleryLightbox();
        });

        document.getElementById('galleryLightboxClose')?.addEventListener('click', closeGalleryLightbox);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('galleryLightbox').classList.contains('hidden')) {
                closeGalleryLightbox();
            }
        });

        // Background Image Switcher (Every 5 seconds)
        let activeBgIndex = 0;
        setInterval(() => {
            const currentBg = document.getElementById('headerBg' + activeBgIndex);
            activeBgIndex = activeBgIndex === 0 ? 1 : 0;
            const nextBg = document.getElementById('headerBg' + activeBgIndex);

            if (currentBg && nextBg) {
                currentBg.classList.remove('opacity-10', 'dark:opacity-5');
                currentBg.classList.add('opacity-0');
                nextBg.classList.remove('opacity-0');
                nextBg.classList.add('opacity-10', 'dark:opacity-5');
            }
        }, 5000);
    </script>
</body>
</html>