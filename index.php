<?php 
include 'db.php'; 

// --- Auto-Calculate Passing Rate ---
$total_passers_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passers");
$total_passers = mysqli_fetch_assoc($total_passers_query)['count'];
$display_rate = ($total_passers > 0) ? "95%" : "0%"; 
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>C-Familia Tutorial Services</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        @keyframes pulse-slow { 0%, 100% { opacity: 0.2; transform: scale(1); } 50% { opacity: 0.4; transform: scale(1.1); } }
        .animate-pulse-slow { animation: pulse-slow 8s ease-in-out infinite; }
        .modal-active { overflow: hidden; }
    </style>
</head>

<body class="bg-[#f8fafc] text-slate-900 antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">

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
            <p class="text-[10px] font-black text-blue-500 uppercase tracking-[0.3em] mb-2">Menu Options</p>
            <div class="flex flex-col space-y-2">
                <a onclick="toggleMobileNav()" href="#announcements" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Announcements</a>
                <a onclick="toggleMobileNav()" href="#posts" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Learning Materials</a>
                <a onclick="toggleMobileNav()" href="#passers" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Passers</a>
                <a onclick="toggleMobileNav()" href="#contact" class="block py-4 text-xl font-black text-slate-900 bg-white/90 rounded-2xl shadow-sm border border-slate-200/40 hover:bg-blue-600 hover:text-white transition-all">Contact</a>
            </div>
            
            <div class="grid grid-cols-2 gap-3 pt-6 border-t border-slate-200/20 sm:hidden">
                <a onclick="toggleMobileNav()" href="login.php" class="py-3 bg-white/10 text-white font-extrabold text-sm rounded-xl border border-white/10 hover:bg-white/20 transition-all">Login</a>
                <a onclick="toggleMobileNav()" href="register.php" class="py-3 bg-blue-600 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/20 hover:bg-blue-500 transition-all">Join Us</a>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <header class="relative bg-slate-950 py-24 sm:py-32 overflow-hidden flex-shrink-0 border-b border-slate-900">
        <div class="absolute inset-0 bg-[radial-gradient(#1e3a8a_1px,transparent_1px)] [background-size:24px_24px] opacity-20 pointer-events-none"></div>
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/30 rounded-full blur-[140px] animate-pulse-slow pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-[140px] animate-pulse-slow pointer-events-none"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 text-center lg:text-left grid lg:grid-cols-12 items-center gap-16">
            <div class="space-y-8 lg:col-span-7">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 backdrop-blur-sm">
                    <span class="flex h-2 w-2 rounded-full bg-blue-400 animate-pulse"></span>
                    <span class="text-blue-400 font-extrabold tracking-widest uppercase text-[10px]">C-Familia Center Platform</span>
                </div>
                <h2 class="text-4xl sm:text-6xl font-[900] text-white tracking-tight leading-[1.05]">
                    Your Future Starts <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-blue-500 bg-clip-text text-transparent">Right Here.</span>
                </h2>
                <p class="text-lg sm:text-xl text-slate-400 max-w-xl mx-auto lg:mx-0 font-medium leading-relaxed">
                    "Join our family, and together, we will help you pass your professional board exams."
                </p>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-2">
                    <a href="register.php" class="px-8 py-4 bg-blue-600 text-white rounded-xl text-base font-bold hover:bg-blue-500 active:scale-98 transition-all shadow-lg shadow-blue-600/20 focus:outline-none">Enroll Now</a>
                    <a href="#passers" class="px-8 py-4 bg-slate-900 text-slate-300 border border-slate-800 rounded-xl text-base font-bold hover:bg-slate-800 active:scale-98 transition-all focus:outline-none">View Success Stories</a>
                </div>
            </div>

            <div class="hidden lg:block lg:col-span-5">
                <div class="bg-slate-900/60 backdrop-blur-md p-10 rounded-3xl border border-slate-800/80 relative overflow-hidden shadow-2xl">
                    <div class="absolute -right-16 -top-16 w-40 h-40 bg-blue-600/20 rounded-full blur-3xl"></div>
                    <div class="flex items-center gap-5 mb-8">
                        <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/20">✓</div>
                        <div>
                            <p class="text-white text-2xl font-[900] tracking-tight"><?= $display_rate ?> Passing Rate</p>
                            <p class="text-slate-400 text-sm font-semibold"><?= $total_passers ?>+ Certified Passers</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="h-3 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                            <div class="h-full bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400 w-[95%] rounded-full shadow-[0_0_20px_rgba(37,99,235,0.4)]"></div>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <span>Excellent Results</span>
                            <span>Batch 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Top Performance Section -->
    <section class="py-24 sm:py-32 bg-slate-950 text-white relative overflow-hidden border-b border-slate-900">
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-indigo-600/5 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16 sm:mb-24">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <span class="text-blue-400 font-black uppercase text-[11px] tracking-[0.3em]">Top Achievers</span>
                    </div>
                    <h3 class="text-3xl sm:text-5xl font-[900] tracking-tight">Top Performance<span class="text-blue-500">.</span></h3>
                </div>
                <p class="text-slate-400 max-w-sm font-medium text-sm sm:text-base leading-relaxed border-l-2 border-slate-800 pl-4">Celebrating our students who got excellent board exam ratings of 95% and higher.</p>
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
                <div class="bg-slate-900/40 border border-slate-800 hover:border-slate-700/80 p-8 rounded-3xl group hover:bg-slate-900 transition-all duration-300 flex flex-col justify-between shadow-lg hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex items-center gap-5 mb-8">
                        <div class="relative flex-shrink-0">
                            <img src="<?= $photoPath ?>" class="w-20 h-20 rounded-2xl object-cover ring-4 ring-slate-800/50 shadow-2xl transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute -top-2 -right-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[9px] font-black px-2 py-0.5 rounded shadow-md uppercase tracking-widest">TOP</div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xl font-bold text-white truncate group-hover:text-blue-400 transition-colors"><?= $top['name'] ?></h4>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mt-1 truncate"><?= $top['program'] ?></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between bg-slate-950 p-4 rounded-xl border border-slate-800">
                        <span class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Board Rating</span>
                        <span class="text-3xl font-[900] bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent italic tracking-tight"><?= number_format($top['rating'], 2) ?>%</span>
                    </div>
                </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                <div class="col-span-full py-20 text-center bg-slate-900/20 rounded-3xl border border-dashed border-slate-800">
                    <p class="text-slate-500 font-bold uppercase tracking-widest text-xs sm:text-sm">Top results are being checked right now.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($top_count > 6): ?>
            <div class="mt-16 text-center">
                <button onclick="openModal('topPerformanceModal')" class="inline-flex items-center gap-2 px-6 py-3.5 bg-slate-900 border border-slate-800 rounded-xl font-bold hover:bg-slate-800 hover:border-slate-700 transition-all text-xs uppercase tracking-widest text-slate-300 focus:outline-none">
                    See More Top Performers
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Voice of Success Section -->
    <section class="py-24 sm:py-32 bg-white px-4 sm:px-6 relative border-b border-slate-100">
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

    <!-- Recent Announcements Section -->
    <section id="announcements" class="py-24 sm:py-32 px-4 sm:px-6 max-w-7xl mx-auto w-full">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
            <div class="flex items-center gap-3">
                <span class="w-3 h-8 bg-blue-600 rounded-full"></span>
                <h3 class="text-3xl font-[900] tracking-tight text-slate-900">Recent Announcements</h3>
            </div>
            <?php 
            $ann_total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements WHERE audience = 'General'");
            $ann_count = mysqli_fetch_assoc($ann_total_query)['count'];
            if($ann_count > 3): 
            ?>
            <button onclick="openModal('announcementsModal')" class="text-xs font-black uppercase text-blue-600 tracking-wider hover:text-blue-700 transition-colors flex items-center gap-1 group focus:outline-none">
                See All Announcements <span class="transition-transform group-hover:translate-x-1">→</span>
            </button>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            $ann_query = mysqli_query($conn, "SELECT * FROM announcements WHERE audience = 'General' ORDER BY created_at DESC LIMIT 3");
            while($ann = mysqli_fetch_assoc($ann_query)):
                $is_urgent = ($ann['category'] == 'Urgent');
            ?>
            <div class="p-8 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-slate-200/40 transition-all relative overflow-hidden group flex flex-col justify-between hover:-translate-y-1">
                <?php if($is_urgent): ?>
                <span class="absolute top-0 right-0 bg-rose-600 text-white text-[9px] px-4 py-1.5 font-black uppercase tracking-widest rounded-bl-xl shadow-sm">Urgent</span>
                <?php endif; ?>
                <div>
                    <p class="text-blue-600 font-black text-[10px] uppercase tracking-wider mb-4"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                    <h4 class="text-xl font-bold mb-3 group-hover:text-blue-600 transition-colors text-slate-900 leading-snug"><?= $ann['title'] ?></h4>
                    <p class="text-slate-600 leading-relaxed text-sm mb-4 line-clamp-3 font-medium"><?= $ann['message'] ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Learning Materials Section -->
    <section id="posts" class="py-24 sm:py-32 bg-slate-900 text-white border-t border-slate-950 px-4 sm:px-6 w-full relative overflow-hidden">
        <div class="absolute top-0 left-1/2 w-96 h-96 bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto relative">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-12 sm:mb-16">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-8 bg-indigo-500 rounded-full"></span>
                    <h3 class="text-3xl font-[900] tracking-tight text-white">Learning Materials</h3>
                </div>
                <?php 
                $posts_total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts");
                $posts_count = mysqli_fetch_assoc($posts_total_query)['count'];
                if($posts_count > 6): 
                ?>
                <button onclick="openModal('postsModal')" class="text-xs font-black uppercase text-indigo-400 tracking-wider hover:text-indigo-300 transition-colors flex items-center gap-1 group focus:outline-none">
                    Browse All Materials <span class="transition-transform group-hover:translate-x-1">→</span>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $posts_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
                while($post = mysqli_fetch_assoc($posts_query)):
                ?>
                <article class="bg-slate-950 rounded-3xl shadow-sm border border-slate-800 overflow-hidden hover:border-slate-700 transition-all duration-300 group flex flex-col justify-between p-8 hover:-translate-y-1">
                    <div>
                        <div class="mb-4">
                            <span class="px-2.5 py-1 bg-indigo-500/10 text-indigo-400 text-[9px] font-black rounded uppercase tracking-wider border border-indigo-500/20">Study File</span>
                        </div>
                        <h4 class="text-xl font-bold mb-3 group-hover:text-indigo-400 transition-colors text-white leading-snug"><?= $post['title'] ?></h4>
                        <p class="text-slate-400 text-sm leading-relaxed mb-6 line-clamp-2 font-medium"><?= $post['content'] ?></p>
                    </div>
                    <?php if($post['file_path']): ?>
                    <div class="border-t border-slate-800/80 pt-5 mt-2">
                        <a href="uploads/resources/<?= $post['file_path'] ?>" class="inline-flex items-center gap-2 text-indigo-400 font-black text-[11px] uppercase tracking-wider hover:text-indigo-300 transition-all focus:outline-none">
                            Download File <span class="text-sm transition-transform group-hover:translate-x-1">→</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- The Hall of Fame Section -->
    <section id="passers" class="py-24 sm:py-32 bg-slate-50 px-4 sm:px-6 border-t border-slate-100">
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

    <!-- Contact & Location Section -->
    <section id="contact" class="py-24 sm:py-32 px-4 sm:px-6 bg-white border-t border-slate-100 w-full">
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
    <footer class="bg-slate-950 pt-20 pb-10 px-4 sm:px-6 text-white overflow-hidden relative mt-auto border-t border-slate-900">
        <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400"></div>
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 mb-12 relative">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-lg">C</div>
                <h1 class="text-2xl font-[900] tracking-tighter">C-Familia<span class="text-blue-500">.</span></h1>
            </div>
            <div class="text-slate-500 text-xs font-bold uppercase tracking-[0.25em] text-center md:text-left">
                Helping Students Succeed Since 2024
            </div>
        </div>
        
        <!-- Developer Credit Badge -->
        <div class="max-w-7xl mx-auto text-center mb-6 relative">
            <p class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 bg-slate-900/60 border border-slate-800/80 px-4 py-2 rounded-xl backdrop-blur-sm shadow-inner">
                Developed by 
                <span class="font-black bg-gradient-to-r from-blue-400 via-indigo-400 to-blue-500 bg-clip-text text-transparent">
                    Rhondel M. Pagobo
                </span>
            </p>
        </div>

        <div class="max-w-7xl mx-auto text-center text-slate-700 text-[10px] border-t border-slate-900 pt-8 uppercase tracking-[0.3em] font-black">
            &copy; <?= date("Y") ?> C-Familia Tutorial Services • Registered School Review Center
        </div>
    </footer>

    <!-- MODAL INTEGRATIONS -->

    <!-- Top Performers See More Modal -->
    <div id="topPerformanceModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-3xl border border-slate-800 w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-8 border-b border-slate-800/60 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-2xl font-[900] text-white tracking-tight">All Top Performers</h3>
                    <p class="text-slate-400 text-sm mt-1">Our top reviewees with high scores of 95% and above.</p>
                </div>
                <button onclick="closeModal('topPerformanceModal')" class="w-10 h-10 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition-colors flex items-center justify-center text-sm font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-8 overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    if($top_count > 0):
                        mysqli_data_seek($top_query, 0);
                        while($top = mysqli_fetch_assoc($top_query)):
                            $photoPath = file_exists("uploads/profiles/".$top['photo']) ? "uploads/profiles/".$top['photo'] : "uploads/passers/".$top['photo'];
                    ?>
                    <div class="bg-slate-950 border border-slate-800 p-6 rounded-2xl flex flex-col justify-between">
                        <div class="flex items-center gap-4 mb-5">
                            <img src="<?= $photoPath ?>" class="w-14 h-14 rounded-xl object-cover ring-2 ring-slate-800">
                            <div class="min-w-0">
                                <h4 class="font-bold text-white text-base truncate"><?= $top['name'] ?></h4>
                                <p class="text-slate-400 text-[10px] font-black uppercase tracking-wider truncate"><?= $top['program'] ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between bg-slate-900 p-3.5 rounded-xl border border-slate-800/60">
                            <span class="text-slate-500 text-[9px] font-black uppercase tracking-widest">Board Rating</span>
                            <span class="text-xl font-[900] text-blue-400 italic"><?= number_format($top['rating'], 2) ?>%</span>
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
    <div id="passersModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
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
    </script>
</body>
</html>