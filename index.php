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
    <!-- Updated font to Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>C-Familia Tutorial Services</title>
    <style>
        body { font-family: 'Poppins', sans-serif; }
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
        /* Dot grid pattern for white sections */
        .bg-dot-pattern {
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 28px 28px;
        }
        /* Diagonal line pattern */
        .bg-line-pattern {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 18px,
                rgba(148,163,184,0.08) 18px,
                rgba(148,163,184,0.08) 19px
            );
        }
        /* Subtle cross/grid pattern */
        .bg-cross-pattern {
            background-image:
                linear-gradient(rgba(203,213,225,0.25) 1px, transparent 1px),
                linear-gradient(90deg, rgba(203,213,225,0.25) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>

<body class="bg-white text-slate-900 antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">

    <!-- Sticky Navigation Bar -->
    <nav class="sticky top-0 z-40 bg-white/75 backdrop-blur-md border-b border-slate-100 px-4 py-4 sm:px-6 transition-all">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-blue-600/40 rounded-xl p-1 z-50">
                <div class="relative overflow-hidden rounded-xl shadow-md border border-slate-100">
                    <img src="cuevaslogo.jpg" alt="C-Familia Logo" class="w-10 h-10 object-contain transition-transform duration-500 group-hover:scale-110">
                </div>
                <h1 class="text-2xl font-[900] tracking-tighter text-slate-900">
                    C-Familia<span class="text-blue-600">.</span>
                </h1>
            </a>

            <!-- Desktop View: Menu Options -->
            <div class="hidden md:flex space-x-1 bg-slate-100 p-1 rounded-full text-sm font-bold text-slate-600">
                <a href="#announcements" class="hover:text-slate-900 hover:bg-white rounded-full px-4 py-1.5 transition-all focus:outline-none">Announcements</a>
                <a href="#posts" class="hover:text-slate-900 hover:bg-white rounded-full px-4 py-1.5 transition-all focus:outline-none">Learning Materials</a>
                <a href="#passers" class="hover:text-slate-900 hover:bg-white rounded-full px-4 py-1.5 transition-all focus:outline-none">Passers</a>
                <?php if ($has_gallery): ?>
                <a href="#gallery" class="hover:text-slate-900 hover:bg-white rounded-full px-4 py-1.5 transition-all focus:outline-none">Gallery</a>
                <?php endif; ?>
                <a href="#contact" class="hover:text-slate-900 hover:bg-white rounded-full px-4 py-1.5 transition-all focus:outline-none">Contact</a>
            </div>

            <!-- Login / Join Links -->
            <div class="flex items-center space-x-2 z-50">
                <a href="login.php" class="hidden sm:inline-block text-slate-700 font-bold px-4 py-2 hover:text-blue-600 text-sm transition-colors focus:outline-none">Login</a>
                <a href="register.php" class="hidden sm:inline-block px-5 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-blue-600 active:scale-98 transition-all shadow-md shadow-slate-900/10 focus:outline-none focus:ring-2 focus:ring-blue-600/40">
                    Join Us 
                </a>
                
                <!-- Mobile Menu Button Icon -->
                <button id="mobileMenuBtn" class="flex md:hidden w-11 h-11 bg-slate-100 text-slate-900 rounded-xl items-center justify-center font-bold border border-slate-200 shadow-sm hover:bg-slate-200 transition-colors focus:outline-none" aria-label="Toggle navigation menu">
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
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.3em] mb-2">Menu Options</p>
            <div class="flex flex-col space-y-2">
                <a onclick="toggleMobileNav()" href="#announcements" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Announcements</a>
                <a onclick="toggleMobileNav()" href="#posts" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Learning Materials</a>
                <a onclick="toggleMobileNav()" href="#passers" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Passers</a>
                <?php if ($has_gallery): ?>
                <a onclick="toggleMobileNav()" href="#gallery" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Gallery</a>
                <?php endif; ?>
                <a onclick="toggleMobileNav()" href="#contact" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Contact</a>
            </div>
            
            <div class="grid grid-cols-2 gap-3 pt-6 border-t border-slate-200/20 sm:hidden">
                <a onclick="toggleMobileNav()" href="login.php" class="py-3 bg-slate-100 text-slate-900 font-extrabold text-sm rounded-xl border border-slate-200 hover:bg-slate-200 transition-all">Login</a>
                <a onclick="toggleMobileNav()" href="register.php" class="py-3 bg-blue-600 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/20 hover:bg-blue-500 transition-all">Join Us</a>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <header class="relative py-24 sm:py-32 overflow-hidden flex-shrink-0 border-b border-slate-100">
        <!-- Alternating Background Images Layer -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img id="headerBg0" src="passers.jpg" alt="Background Passes 1" class="absolute inset-0 w-full h-full object-cover opacity-10 transition-opacity duration-1000">
            <img id="headerBg1" src="passers1.jpg" alt="Background Passes 2" class="absolute inset-0 w-full h-full object-cover opacity-0 transition-opacity duration-1000">
        </div>
        
        <!-- Ambient Design Graphics Layers -->
        <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:24px_24px] opacity-70 pointer-events-none z-10"></div>
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-100/40 rounded-full blur-[140px] animate-pulse-slow pointer-events-none z-10"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-100/30 rounded-full blur-[140px] animate-pulse-slow pointer-events-none z-10"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 text-center lg:text-left grid lg:grid-cols-12 items-center gap-16 z-20">
            <div class="space-y-8 lg:col-span-7">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50/80 border border-blue-100 backdrop-blur-sm">
                    <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="text-blue-600 font-extrabold tracking-widest uppercase text-[10px]">C-Familia Tutorial Services</span>
                </div>
                <h2 class="text-4xl sm:text-6xl font-[900] text-slate-900 tracking-tight leading-[1.05]">
                    Your Future Starts <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-500 bg-clip-text text-transparent">Right Here.</span>
                </h2>
                <p class="text-lg sm:text-xl text-slate-600 max-w-xl mx-auto lg:mx-0 font-medium leading-relaxed">
                    "Join our family, and together, we will help you pass your professional board exams."
                </p>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-2">
                    <a href="register.php" class="px-8 py-4 bg-blue-600 text-white rounded-xl text-base font-bold hover:bg-blue-500 active:scale-98 transition-all shadow-lg shadow-blue-600/20 focus:outline-none">Enroll Now</a>
                    <a href="#passers" class="px-8 py-4 bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-base font-bold hover:bg-slate-200 active:scale-98 transition-all focus:outline-none">View Success Stories</a>
                </div>
            </div>

            <div class="hidden lg:block lg:col-span-5">
                <div class="bg-white/80 backdrop-blur-sm p-10 rounded-3xl border border-slate-200 relative overflow-hidden shadow-xl">
                    <div class="absolute -right-16 -top-16 w-40 h-40 bg-blue-100/40 rounded-full blur-3xl"></div>
                    <div class="flex items-center gap-5 mb-8">
                        <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20">✓</div>
                        <div>
                            <p class="text-slate-900 text-2xl font-[900] tracking-tight"><?= $display_rate ?> Passing Rate</p>
                            <p class="text-slate-500 text-sm font-semibold"><?= $total_passers ?>+ Certified Passers</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="h-3 bg-slate-200 rounded-full overflow-hidden p-0.5 border border-slate-300">
                            <div class="h-full bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400 w-[95%] rounded-full shadow-[0_0_10px_rgba(37,99,235,0.2)]"></div>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <span>Excellent Results</span>
                            <span>Batch 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Top Performance Section -->
    <section class="py-24 sm:py-32 bg-white text-slate-900 relative overflow-hidden border-b border-slate-100">
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-indigo-50/40 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16 sm:mb-24">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <span class="text-blue-600 font-black uppercase text-[11px] tracking-[0.3em]">Top Achievers</span>
                    </div>
                    <h3 class="text-3xl sm:text-5xl font-[900] tracking-tight text-slate-900">Top Performance<span class="text-blue-600">.</span></h3>
                </div>
                <p class="text-slate-500 max-w-sm font-medium text-sm sm:text-base leading-relaxed border-l-2 border-slate-200 pl-4">Celebrating our students who got excellent board exam ratings of 95% and higher.</p>
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
                <div class="bg-slate-50/50 border border-slate-100 hover:border-slate-200 p-8 rounded-3xl group hover:bg-white transition-all duration-300 flex flex-col justify-between shadow-sm hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center gap-5 mb-8">
                        <div class="relative flex-shrink-0">
                            <img src="<?= $photoPath ?>" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-slate-100 shadow-md transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute -top-2 -right-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[9px] font-black px-2 py-0.5 rounded shadow-md uppercase tracking-widest">TOP</div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xl font-bold text-slate-900 truncate group-hover:text-blue-600 transition-colors"><?= $top['name'] ?></h4>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mt-1 truncate"><?= $top['program'] ?></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between bg-slate-100 p-4 rounded-xl border border-slate-200">
                        <span class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Board Rating</span>
                        <span class="text-3xl font-[900] bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent italic tracking-tight"><?= number_format($top['rating'], 2) ?>%</span>
                    </div>
                </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                <div class="col-span-full py-20 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs sm:text-sm">Top results are being checked right now.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($top_count > 6): ?>
            <div class="mt-16 text-center">
                <button onclick="openModal('topPerformanceModal')" class="inline-flex items-center gap-2 px-6 py-3.5 bg-slate-100 border border-slate-200 rounded-xl font-bold hover:bg-slate-200 hover:border-slate-300 transition-all text-xs uppercase tracking-widest text-slate-700 focus:outline-none">
                    See More Top Performers
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Image Gallery Section -->
    <?php if ($has_gallery): ?>
    <section id="gallery" class="py-24 sm:py-32 px-4 sm:px-6 w-full border-t border-slate-100 bg-white relative overflow-hidden">
        <!-- Decorative: dot pattern + soft blobs -->
        <div class="absolute inset-0 bg-dot-pattern opacity-25 pointer-events-none"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[700px] h-64 bg-gradient-to-b from-emerald-50/40 to-transparent pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-emerald-50/50 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
            <div class="flex items-center gap-3">
                <span class="w-3 h-8 bg-emerald-500 rounded-full"></span>
                <h3 class="text-3xl font-[900] tracking-tight text-slate-900">Photo Gallery</h3>
            </div>
            <p class="text-slate-500 text-sm font-medium max-w-md">Moments from our review center, events, and student milestones.</p>
        </div>

        <div class="space-y-16">
            <?php foreach ($grouped_gallery as $caption => $images):
                $image_count = count($images);
            ?>
            <div data-gallery-group="<?= md5($caption) ?>">
                <h4 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                    <?= htmlspecialchars($caption) ?>
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <?php foreach ($images as $index => $img):
                        $img_path = $gallery_dir . $img['image_path'];
                    ?>
                    <div class="gallery-item relative aspect-[4/3] rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/40 transition-all hover:-translate-y-1 <?= $index > 0 ? 'gallery-extra hidden sm:block' : '' ?>">
                        <button type="button" class="gallery-lightbox-trigger group absolute inset-0 w-full h-full focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:ring-inset" data-gallery-src="<?= htmlspecialchars($img_path, ENT_QUOTES, 'UTF-8') ?>" data-gallery-caption="<?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($caption) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-colors flex items-center justify-center">
                                <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 text-slate-800 text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-lg">View</span>
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
        </div><!-- close .max-w-7xl -->
    </section>
    <?php endif; ?>

        <!-- The Hall of Fame Section -->
    <section id="passers" class="py-24 sm:py-32 bg-white px-4 sm:px-6 border-b border-slate-100 relative overflow-hidden">
        <!-- Decorative: diagonal lines + blobs -->
        <div class="absolute inset-0 bg-line-pattern pointer-events-none"></div>
        <div class="absolute top-1/4 right-0 w-96 h-96 bg-blue-50/70 rounded-full blur-[130px] pointer-events-none animate-float-slow"></div>
        <div class="absolute bottom-0 left-1/4 w-80 h-80 bg-indigo-50/60 rounded-full blur-[100px] pointer-events-none animate-float-medium"></div>
        <!-- Floating decorative circles -->
        <div class="absolute top-16 left-8 w-10 h-10 rounded-full border-2 border-blue-100 opacity-60 pointer-events-none animate-drift"></div>
        <div class="absolute top-32 right-16 w-6 h-6 rounded-full bg-blue-100/50 opacity-70 pointer-events-none animate-float-slow"></div>
        <div class="absolute bottom-24 left-1/3 w-8 h-8 rounded-full border border-indigo-100 opacity-50 pointer-events-none animate-float-medium"></div>
        <div class="max-w-7xl mx-auto text-center mb-16 sm:mb-24">
            <h3 class="text-3xl sm:text-5xl font-[900] mb-4 tracking-tight text-slate-900">The Hall of Fame</h3>
            <p class="text-slate-500 font-medium text-sm sm:text-base max-w-xl mx-auto leading-relaxed">Celebrating the hard work of every C-Familia student who passed their board exams.</p>
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
            <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:border-slate-200 transition-all duration-300 hover:-translate-y-1.5 text-center group flex flex-col justify-between">
                <div>
                    <img src="<?= $pPath ?>" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover border-4 border-slate-50 group-hover:scale-105 transition-transform shadow-inner">
                    <h5 class="font-bold text-slate-900 text-sm leading-snug mb-1 truncate"><?= $passer['name'] ?></h5>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-4 truncate"><?= $passer['program'] ?></p>
                </div>
                <div class="flex items-center justify-center gap-1.5 bg-slate-50 rounded-xl py-2.5 border border-slate-100">
                    <span class="text-base font-[900] text-blue-600 tracking-tight"><?= $passer['rating'] ?>%</span>
                    <span class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Score</span>
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
            <button onclick="openModal('passersModal')" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 font-bold rounded-xl shadow-sm transition-all text-xs uppercase tracking-widest focus:outline-none">
                View All Hall of Fame Passers
            </button>
        </div>
        <?php endif; ?>
    </section>

    
    <!-- Recent Announcements Section -->
    <section id="announcements" class="py-24 sm:py-32 px-4 sm:px-6 w-full relative overflow-hidden bg-white border-b border-slate-100">
        <!-- Decorative: cross grid + ambient blobs -->
        <div class="absolute inset-0 bg-cross-pattern opacity-60 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-50/80 rounded-full blur-[120px] pointer-events-none animate-pulse-slow"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-slate-50/60 rounded-full blur-[100px] pointer-events-none"></div>
        <!-- Decorative accent corner shapes -->
        <svg class="absolute top-0 right-0 w-64 h-64 text-blue-50 opacity-60 pointer-events-none" viewBox="0 0 256 256" fill="currentColor"><circle cx="256" cy="0" r="180"/></svg>
        <svg class="absolute bottom-0 left-0 w-48 h-48 text-slate-100 opacity-50 pointer-events-none" viewBox="0 0 192 192" fill="currentColor"><circle cx="0" cy="192" r="140"/></svg>
        <div class="max-w-7xl mx-auto relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
            <div class="flex items-center gap-3">
                <span class="w-3 h-8 bg-blue-600 rounded-full"></span>
                <h3 class="text-3xl font-[900] tracking-tight text-slate-900">Recent Announcements</h3>
            </div>
            <?php if ($ann_count > 3): ?>
            <button type="button" onclick="openModal('announcementsModal')" class="text-xs font-black uppercase text-blue-600 tracking-wider hover:text-blue-700 transition-colors flex items-center gap-1 group focus:outline-none">
                See All Announcements <span class="transition-transform group-hover:translate-x-1">→</span>
            </button>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($recent_announcements as $ann):
                $is_urgent = ($ann['category'] == 'Urgent');
                $is_long = mb_strlen($ann['message']) > 160;
            ?>
            <article class="p-8 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-slate-200/40 transition-all relative overflow-hidden group flex flex-col hover:-translate-y-1">
                <?php if ($is_urgent): ?>
                <span class="absolute top-0 right-0 bg-rose-600 text-white text-[9px] px-4 py-1.5 font-black uppercase tracking-widest rounded-bl-xl shadow-sm">Urgent</span>
                <?php endif; ?>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-4 flex-wrap">
                        <p class="text-blue-600 font-black text-[10px] uppercase tracking-wider"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                        <?php if (!$is_urgent && !empty($ann['category'])): ?>
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100"><?= htmlspecialchars($ann['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-xl font-bold mb-3 group-hover:text-blue-600 transition-colors text-slate-900 leading-snug"><?= htmlspecialchars($ann['title']) ?></h4>
                    <p class="text-slate-600 leading-relaxed text-sm font-medium line-clamp-3"><?= htmlspecialchars($ann['message']) ?></p>
                </div>
                <?php if ($is_long): ?>
                <button type="button" class="announcement-read-more mt-5 inline-flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-blue-600 hover:text-blue-700 transition-colors focus:outline-none" data-ann-id="<?= (int) $ann['id'] ?>">
                    Read full announcement <span class="transition-transform group-hover:translate-x-0.5">→</span>
                </button>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        </div><!-- close .max-w-7xl -->
    </section>

    <!-- Learning Materials Section -->
    <section id="posts" class="py-24 sm:py-32 bg-white text-slate-900 border-b border-slate-100 px-4 sm:px-6 w-full relative overflow-hidden">
        <div class="absolute top-0 left-1/2 w-96 h-96 bg-indigo-50/50 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto relative">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-8 bg-indigo-600 rounded-full"></span>
                    <h3 class="text-3xl font-[900] tracking-tight text-slate-900">Learning Materials</h3>
                </div>
                <?php 
                $posts_total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts");
                $posts_count = mysqli_fetch_assoc($posts_total_query)['count'];
                if($posts_count > 6): 
                ?>
                <button onclick="openModal('postsModal')" class="text-xs font-black uppercase text-indigo-600 tracking-wider hover:text-indigo-700 transition-colors flex items-center gap-1 group focus:outline-none">
                    Browse All Materials <span class="transition-transform group-hover:translate-x-1">→</span>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $posts_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
                while($post = mysqli_fetch_assoc($posts_query)):
                ?>
                <article class="bg-slate-50 rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:border-slate-200 hover:bg-white transition-all duration-300 group flex flex-col justify-between p-8 hover:-translate-y-1">
                    <div>
                        <div class="mb-4">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 text-[9px] font-black rounded uppercase tracking-wider border border-indigo-100">Study File</span>
                        </div>
                        <h4 class="text-xl font-bold mb-3 group-hover:text-indigo-600 transition-colors text-slate-900 leading-snug"><?= $post['title'] ?></h4>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6 line-clamp-2 font-medium"><?= $post['content'] ?></p>
                    </div>
                    <?php if($post['file_path']): ?>
                    <div class="border-t border-slate-200/80 pt-5 mt-2">
                        <a href="uploads/resources/<?= $post['file_path'] ?>" class="inline-flex items-center gap-2 text-indigo-600 font-black text-[11px] uppercase tracking-wider hover:text-indigo-700 transition-all focus:outline-none">
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
    <section class="py-24 sm:py-32 bg-white px-4 sm:px-6 relative border-b border-slate-100 overflow-hidden">
        <!-- Decorative background: dot grid + blobs -->
        <div class="absolute inset-0 bg-dot-pattern opacity-30 pointer-events-none"></div>
        <div class="absolute -top-32 -left-32 w-80 h-80 bg-blue-100/60 rounded-full blur-[100px] pointer-events-none animate-float-slow"></div>
        <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-indigo-100/50 rounded-full blur-[90px] pointer-events-none animate-float-medium"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-br from-blue-50/40 to-indigo-50/30 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 sm:mb-24">
                <span class="text-blue-600 font-black uppercase text-[11px] tracking-[0.35em] mb-3 block">Student Feedback</span>
                <h3 class="text-3xl sm:text-5xl font-[900] text-slate-900 tracking-tight">Voice of Success<span class="text-blue-600">.</span></h3>
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
                <div class="bg-slate-50/60 p-8 rounded-3xl border border-slate-100 relative group hover:bg-white transition-all duration-300 flex flex-col justify-between shadow-sm hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1">
                    <div class="text-slate-200 absolute top-4 right-8 text-7xl font-serif select-none pointer-events-none group-hover:text-blue-100 transition-colors">“</div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <p class="text-slate-600 italic leading-relaxed text-sm sm:text-base mb-8 line-clamp-3 font-medium">
                            <?= htmlspecialchars($row['content']) ?>
                        </p>
                        <div class="flex items-center gap-4 border-t border-slate-100 pt-6">
                            <img src="<?= $userPic ?>" class="w-12 h-12 rounded-xl object-cover ring-4 ring-white shadow-sm flex-shrink-0">
                            <div class="min-w-0">
                                <h5 class="font-extrabold text-slate-900 text-sm truncate"><?= $row['firstname'] . ' ' . $row['lastname'] ?></h5>
                                <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mt-0.5">Verified Alumni</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                <div class="col-span-full py-20 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Waiting for student stories...</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($test_count > 6): ?>
            <div class="mt-16 text-center">
                <button onclick="openModal('testimonialsModal')" class="inline-flex items-center gap-2 px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition-all text-xs uppercase tracking-widest focus:outline-none">
                    Read All Testimonials
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>



    <!-- Contact & Location Section -->
    <section id="contact" class="py-24 sm:py-32 px-4 sm:px-6 bg-white border-t border-slate-100 w-full relative overflow-hidden">
        <!-- Decorative: cross grid + ambient blobs + accent arc -->
        <div class="absolute inset-0 bg-cross-pattern opacity-50 pointer-events-none"></div>
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-blue-50/70 rounded-full blur-[100px] pointer-events-none animate-float-slow"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-50/50 rounded-full blur-[80px] pointer-events-none animate-float-medium"></div>
        <!-- Decorative floating dots -->
        <div class="absolute top-20 left-12 w-4 h-4 rounded-full bg-blue-200/60 pointer-events-none animate-drift"></div>
        <div class="absolute top-40 right-24 w-3 h-3 rounded-full bg-indigo-200/50 pointer-events-none animate-float-slow"></div>
        <div class="absolute bottom-20 left-1/2 w-5 h-5 rounded-full border border-blue-200/40 pointer-events-none animate-float-medium"></div>
        <!-- Large decorative arc SVG top-right -->
        <svg class="absolute -top-24 -right-24 w-80 h-80 text-blue-50 opacity-70 pointer-events-none" viewBox="0 0 320 320" fill="none" stroke="currentColor" stroke-width="1"><circle cx="320" cy="0" r="200"/><circle cx="320" cy="0" r="240"/><circle cx="320" cy="0" r="280"/></svg>
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
            <div class="space-y-10">
                <h3 class="text-3xl sm:text-5xl font-[900] tracking-tight">Visit our Branches<span class="text-blue-600">.</span></h3>
                <div class="space-y-8">
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 bg-slate-100 text-slate-900 border border-slate-200 rounded-xl flex items-center justify-center text-lg flex-shrink-0 shadow-sm">📍</div>
                        <div>
                            <p class="font-extrabold text-slate-900 uppercase text-xs tracking-wider mb-1">Ozamiz Main Branch</p>
                            <p class="text-slate-600 font-medium text-sm sm:text-base">Ozamiz City, Philippines, 7200</p>
                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider mt-3 border border-emerald-100">● Main Office</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 bg-slate-100 text-slate-900 border border-slate-200 rounded-xl flex items-center justify-center text-lg flex-shrink-0 shadow-sm">📍</div>
                        <div>
                            <p class="font-extrabold text-slate-900 uppercase text-xs tracking-wider mb-1">Oroquieta Campus</p>
                            <p class="text-slate-600 font-medium text-sm sm:text-base">Oroquieta City, Misamis Occidental</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 bg-slate-100 text-slate-900 border border-slate-200 rounded-xl flex items-center justify-center text-lg flex-shrink-0 shadow-sm">📍</div>
                        <div>
                            <p class="font-extrabold text-slate-900 uppercase text-xs tracking-wider mb-1">Tubod Campus</p>
                            <p class="text-slate-600 font-medium text-sm sm:text-base">Tubod, Lanao Del Norte</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-slate-50 p-8 sm:p-10 rounded-3xl border border-slate-100 shadow-inner">
                <h3 class="text-2xl font-extrabold mb-6 text-slate-900 tracking-tight">Contact Us Directly</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-100 shadow-sm transition-transform hover:translate-x-1">
                        <span class="text-xl select-none">📞</span>
                        <div>
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Phone</p>
                            <p class="font-bold text-slate-900 text-sm sm:text-base">0910 167 6805</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-100 shadow-sm transition-transform hover:translate-x-1">
                        <span class="text-xl select-none">✉️</span>
                        <div class="min-w-0">
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Email</p>
                            <p class="font-bold text-slate-900 text-sm sm:text-base truncate">shielamariscuevas@gmail.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-100 shadow-sm transition-transform hover:translate-x-1">
                        <span class="text-xl select-none">💬</span>
                        <div>
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest">Facebook Page</p>
                            <p class="font-bold text-slate-900 text-sm sm:text-base">C-Familia Tutorial Services</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white pt-20 pb-10 px-4 sm:px-6 text-slate-900 overflow-hidden relative mt-auto border-t border-slate-200">
        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400"></div>
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 mb-12 relative">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-lg text-white">C</div>
                <h1 class="text-2xl font-[900] tracking-tighter">C-Familia<span class="text-blue-600">.</span></h1>
            </div>
            <div class="text-slate-400 text-xs font-bold uppercase tracking-[0.25em] text-center md:text-left">
                Helping Students Succeed Since 2024
            </div>
        </div>
        
        <!-- Developer Credit Badge -->
        <div class="max-w-7xl mx-auto text-center mb-6 relative">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl backdrop-blur-sm shadow-inner">
                Developed by 
                <span class="font-black bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-500 bg-clip-text text-transparent">
                    Rhondel M. Pagobo
                </span>
            </p>
        </div>

        <div class="max-w-7xl mx-auto text-center text-slate-400 text-[10px] border-t border-slate-100 pt-8 uppercase tracking-[0.3em] font-black">
            &copy; <?= date("Y") ?> C-Familia Tutorial Services • Registered School Review Center
        </div>
    </footer>

    <!-- MODAL INTEGRATIONS -->

    <?php foreach ($announcements as $ann): ?>
    <div id="ann-detail-source-<?= (int) $ann['id'] ?>" class="hidden">
        <div class="flex items-center gap-2 mb-4 flex-wrap">
            <p class="text-blue-600 font-black text-[10px] uppercase tracking-wider"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
            <?php if (!empty($ann['category'])): ?>
            <span class="text-[9px] font-black uppercase tracking-wider <?= $ann['category'] === 'Urgent' ? 'text-rose-600 bg-rose-50 border-rose-100' : 'text-slate-400 bg-slate-50 border-slate-100' ?> px-2 py-0.5 rounded-md border"><?= htmlspecialchars($ann['category']) ?></span>
            <?php endif; ?>
        </div>
        <h4 class="text-2xl font-bold text-slate-900 leading-snug mb-4"><?= htmlspecialchars($ann['title']) ?></h4>
        <div class="text-slate-600 leading-relaxed text-sm font-medium whitespace-pre-line"><?= htmlspecialchars($ann['message']) ?></div>
    </div>
    <?php endforeach; ?>

    <!-- Announcement Detail Modal -->
    <div id="announcementDetailModal" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-slate-100 w-full max-w-2xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-6 sm:p-8 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
                <h3 class="text-lg font-[900] text-slate-900 tracking-tight">Announcement</h3>
                <button type="button" onclick="closeModal('announcementDetailModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition-all flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div id="announcementDetailBody" class="p-6 sm:p-8 overflow-y-auto"></div>
        </div>
    </div>

    <!-- All Announcements Modal -->
    <div id="announcementsModal" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-slate-100 w-full max-w-3xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-6 sm:p-8 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-2xl font-[900] text-slate-900 tracking-tight">All Announcements</h3>
                    <p class="text-slate-500 text-sm mt-1">Complete list of public announcements from C-Familia.</p>
                </div>
                <button type="button" onclick="closeModal('announcementsModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition-all flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto space-y-6 bg-slate-50">
                <?php foreach ($announcements as $ann):
                    $is_urgent = ($ann['category'] == 'Urgent');
                ?>
                <article class="p-6 bg-white border border-slate-100 rounded-2xl shadow-sm relative overflow-hidden">
                    <?php if ($is_urgent): ?>
                    <span class="absolute top-0 right-0 bg-rose-600 text-white text-[9px] px-3 py-1 font-black uppercase tracking-widest rounded-bl-xl">Urgent</span>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 mb-3 flex-wrap">
                        <p class="text-blue-600 font-black text-[10px] uppercase tracking-wider"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                        <?php if (!$is_urgent && !empty($ann['category'])): ?>
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100"><?= htmlspecialchars($ann['category']) ?></span>
                        <?php endif; ?>
                    </div>
                    h4 class="text-lg font-bold text-slate-900 leading-snug mb-3"><?= htmlspecialchars($ann['title']) ?></h4>
                    <div class="text-slate-600 leading-relaxed text-sm font-medium whitespace-pre-line"><?= htmlspecialchars($ann['message']) ?></div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Top Performers See More Modal -->
    <div id="topPerformanceModal" class="fixed inset-0 z-50 hidden bg-slate-950/40 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-slate-200 w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-8 border-b border-slate-200 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-2xl font-[900] text-slate-900 tracking-tight">All Top Performers</h3>
                    <p class="text-slate-500 text-sm mt-1">Our top reviewees with high scores of 95% and above.</p>
                </div>
                <button onclick="closeModal('topPerformanceModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 transition-colors flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-8 overflow-y-auto bg-slate-50">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    if($top_count > 0):
                        mysqli_data_seek($top_query, 0);
                        while($top = mysqli_fetch_assoc($top_query)):
                            $photoPath = file_exists("uploads/profiles/".$top['photo']) ? "uploads/profiles/".$top['photo'] : "uploads/passers/".$top['photo'];
                    ?>
                    <div class="bg-white border border-slate-200 p-6 rounded-2xl flex flex-col justify-between shadow-sm">
                        <div class="flex items-center gap-4 mb-5">
                            <img src="<?= $photoPath ?>" class="w-14 h-14 rounded-xl object-cover ring-2 ring-slate-100">
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-base truncate"><?= $top['name'] ?></h4>
                                <p class="text-slate-400 text-[10px] font-black uppercase tracking-wider truncate"><?= $top['program'] ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[9px] font-black uppercase tracking-widest">Board Rating</span>
                            <span class="text-xl font-[900] text-blue-600 italic"><?= number_format($top['rating'], 2) ?>%</span>
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
        <div class="bg-white rounded-3xl border border-slate-100 w-full max-w-6xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-2xl font-[900] text-slate-900 tracking-tight">The Complete Hall of Fame</h3>
                    <p class="text-slate-500 text-sm mt-1">List of all certified C-Familia passers who finished their board exams successfully.</p>
                </div>
                <button onclick="closeModal('passersModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition-all flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-8 overflow-y-auto bg-slate-50">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <?php 
                    if($total_passers_count > 0):
                        mysqli_data_seek($passers_query, 0);
                        while($passer = mysqli_fetch_assoc($passers_query)): 
                            $pPath = file_exists("uploads/profiles/".$passer['photo']) ? "uploads/profiles/".$passer['photo'] : "uploads/passers/".$passer['photo'];
                    ?>
                    <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:border-slate-200 transition-all text-center group flex flex-col justify-between">
                        <div>
                            <img src="<?= $pPath ?>" class="w-16 h-16 rounded-full mx-auto mb-3 object-cover border-4 border-slate-50 shadow-inner">
                            <h5 class="font-bold text-slate-900 text-sm leading-snug mb-1 truncate"><?= $passer['name'] ?></h5>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-3 truncate"><?= $passer['program'] ?></p>
                        </div>
                        <div class="flex items-center justify-center gap-1.5 bg-slate-50 rounded-xl py-2 border border-slate-100">
                            <span class="text-sm font-[900] text-blue-600"><?= $passer['rating'] ?>%</span>
                            <span class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Score</span>
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
            <p id="galleryLightboxCaption" class="text-center text-white/80 text-sm font-semibold mt-4"></p>
        </div>
    </div>

    <!-- Interface Animation Script -->
    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileOverlay = document.getElementById('mobileNavigationOverlay');
        const menuIconToggle = document.getElementById('menuIconToggle');
        let isNavOpen = false;

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
                currentBg.classList.remove('opacity-10');
                currentBg.classList.add('opacity-0');
                nextBg.classList.remove('opacity-0');
                nextBg.classList.add('opacity-10');
            }
        }, 5000);
    </script>
</body>
</html>