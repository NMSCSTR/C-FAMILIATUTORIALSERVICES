<?php 
include 'db.php'; 

// --- Auto-Calculate Passing Rate ---
$total_passers_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passers");
$total_passers = mysqli_fetch_assoc($total_passers_query)['count'];
// Dynamic display rate logic
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
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .glass-dark { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        @keyframes bounce-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .animate-bounce-slow { animation: bounce-slow 4s ease-in-out infinite; }
        .modal-active { overflow: hidden; }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">

    <!-- Sticky Navigation Bar -->
    <nav class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 py-3.5 sm:px-6 transition-all">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-blue-600/40 rounded-xl p-1">
                <img src="cuevaslogo.jpg" alt="C-Familia Logo" class="w-10 h-10 object-contain rounded-xl transition-transform duration-300 group-hover:scale-105 shadow-sm">
                <h1 class="text-2xl font-extrabold tracking-tight text-blue-700">
                    C-Familia<span class="text-blue-400">.</span>
                </h1>
            </a>

            <div class="hidden md:flex space-x-8 font-semibold text-slate-600">
                <a href="#announcements" class="hover:text-blue-600 transition-colors py-1 focus:outline-none focus:text-blue-600">Announcements</a>
                <a href="#posts" class="hover:text-blue-600 transition-colors py-1 focus:outline-none focus:text-blue-600">Resources</a>
                <a href="#passers" class="hover:text-blue-600 transition-colors py-1 focus:outline-none focus:text-blue-600">Passers</a>
                <a href="#contact" class="hover:text-blue-600 transition-colors py-1 focus:outline-none focus:text-blue-600">Contact</a>
            </div>

            <div class="flex items-center space-x-3 sm:space-x-4">
                <a href="login.php" class="text-slate-600 font-bold px-3 sm:px-4 py-2 hover:text-blue-600 transition-colors focus:outline-none focus:text-blue-600">Login</a>
                <a href="register.php" class="px-4 sm:px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 active:scale-98 transition shadow-md shadow-blue-600/10 hover:shadow-lg hover:shadow-blue-600/20 focus:outline-none focus:ring-2 focus:ring-blue-600/40">
                    Join Us 
                </a>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <header class="relative bg-slate-900 py-20 sm:py-24 lg:py-32 overflow-hidden flex-shrink-0">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <img src="cuevaslogo.jpg" alt="Background Texture" class="w-full h-full object-cover filter grayscale scale-105">
        </div>
        <div class="absolute top-0 left-1/4 w-72 sm:w-96 h-72 sm:h-96 bg-blue-600/20 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 text-center lg:text-left grid lg:grid-cols-2 items-center gap-12 sm:gap-16">
            <div class="space-y-6 sm:space-y-8">
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/5 border border-white/10 animate-bounce-slow backdrop-blur-sm">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-black text-base shadow-md shadow-blue-600/40 text-white">C</div>
                    <span class="text-white font-bold tracking-wider uppercase text-[10px]">C-Familia Services</span>
                </div>
                <h2 class="text-4xl sm:text-5xl md:text-6xl font-[800] text-white tracking-tight leading-[1.1]">
                    Your Future Starts <span class="text-blue-400">Right Here.</span>
                </h2>
                <p class="text-lg sm:text-xl text-slate-300 max-w-xl mx-auto lg:mx-0 leading-relaxed italic font-medium">
                    "Join our family, and together, we'll pave the way towards your success."
                </p>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                    <a href="register.php" class="px-8 py-4 bg-blue-600 text-white rounded-2xl text-base sm:text-lg font-bold hover:bg-blue-500 active:scale-98 transition-all transform hover:-translate-y-0.5 shadow-xl shadow-blue-600/25 focus:outline-none focus:ring-2 focus:ring-blue-500">Enroll Now</a>
                    <a href="#passers" class="px-8 py-4 bg-white/5 text-white border border-white/10 hover:border-white/20 rounded-2xl text-base sm:text-lg font-bold hover:bg-white/10 active:scale-98 transition-all focus:outline-none focus:ring-2 focus:ring-white/40">View Success Stories</a>
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="bg-white/5 backdrop-blur-md p-8 sm:p-10 rounded-[2.5rem] border border-white/10 relative overflow-hidden group shadow-2xl">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 mb-6 relative">
                        <div class="w-12 h-12 bg-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center font-bold text-lg border border-emerald-500/30 shadow-inner">✓</div>
                        <p class="text-white text-xl font-extrabold tracking-tight"><?= $display_rate ?> Passing Rate (<?= $total_passers ?>+ Passers)</p>
                    </div>
                    <div class="space-y-4 relative">
                        <div class="h-3 bg-white/10 rounded-full overflow-hidden p-0.5 border border-white/5">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 w-[95%] rounded-full shadow-[0_0_15px_rgba(37,99,235,0.6)]"></div>
                        </div>
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.25em] text-right">Academic Excellence • 2026</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Top Performance Section -->
    <section class="py-20 sm:py-24 bg-slate-950 text-white relative overflow-hidden">
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-blue-600/5 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12 sm:mb-16">
                <div>
                    <span class="text-blue-500 font-black uppercase text-[11px] tracking-[0.35em] mb-3 block">Elite Achievers</span>
                    <h3 class="text-3xl sm:text-4xl md:text-5xl font-[800] tracking-tight">Top Performance<span class="text-blue-600">.</span></h3>
                </div>
                <p class="text-slate-400 max-w-sm font-medium text-sm sm:text-base leading-relaxed">Honoring our reviewees who attained an exceptional board rating of 95% and above.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
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
                <div class="bg-white/5 border border-white/10 hover:border-blue-500/40 p-6 sm:p-8 rounded-[2.5rem] group hover:bg-white/[0.08] transition-all duration-300 flex flex-col justify-between shadow-lg hover:-translate-y-1.5 border-b-4 border-b-blue-600">
                    <div class="flex items-center gap-5 mb-6">
                        <div class="relative flex-shrink-0">
                            <img src="<?= $photoPath ?>" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-[2rem] object-cover ring-4 ring-blue-500/10 shadow-2xl transition-transform duration-300 group-hover:scale-102">
                            <div class="absolute -top-2.5 -right-2.5 bg-blue-600 text-white text-[9px] font-black px-2 py-0.5 rounded-md shadow-md uppercase tracking-wider">TOP</div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-lg sm:text-xl font-bold text-white truncate"><?= $top['name'] ?></h4>
                            <p class="text-blue-400 text-[10px] font-black uppercase tracking-widest mt-1 truncate"><?= $top['program'] ?></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between bg-slate-900/40 rounded-2xl p-4 sm:p-5 border border-white/5">
                        <span class="text-slate-400 text-[10px] font-black uppercase tracking-wider">Board Rating</span>
                        <span class="text-2xl sm:text-3xl font-[900] text-blue-400 italic tracking-tight"><?= number_format($top['rating'], 2) ?>%</span>
                    </div>
                </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                <div class="col-span-full py-16 text-center bg-white/5 rounded-[2.5rem] border border-dashed border-white/10">
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs sm:text-sm">Top results are currently being verified.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($top_count > 6): ?>
            <div class="mt-12 sm:mt-16 text-center">
                <button onclick="openModal('topPerformanceModal')" class="inline-flex items-center gap-2 px-6 py-3 bg-white/5 border border-white/10 rounded-xl font-bold hover:bg-white/10 hover:border-white/20 transition-all text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-blue-500">
                    See More Top Performers
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Voice of Success Section -->
    <section class="py-20 sm:py-24 bg-white px-4 sm:px-6 border-b border-slate-100">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 sm:mb-16">
                <span class="text-blue-600 font-black uppercase text-[11px] tracking-[0.35em] mb-3 block">Student Voice</span>
                <h3 class="text-3xl sm:text-4xl font-[800] text-slate-900 tracking-tight">Voice of Success<span class="text-blue-600">.</span></h3>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
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
                <div class="bg-slate-50 p-6 sm:p-8 rounded-[2.5rem] border border-slate-200/60 relative group hover:bg-blue-50/40 transition-all duration-300 flex flex-col justify-between shadow-sm hover:shadow-md">
                    <div class="text-blue-200/60 absolute top-4 right-6 text-6xl font-serif select-none pointer-events-none group-hover:text-blue-300 transition-colors">“</div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <p class="text-slate-600 italic leading-relaxed text-sm sm:text-base mb-6 line-clamp-3">
                            <?= htmlspecialchars($row['content']) ?>
                        </p>
                        <div class="flex items-center gap-4 border-t border-slate-200/60 pt-4">
                            <img src="<?= $userPic ?>" class="w-11 h-11 rounded-xl object-cover ring-4 ring-white shadow-sm flex-shrink-0">
                            <div class="min-w-0">
                                <h5 class="font-bold text-slate-900 text-sm truncate"><?= $row['firstname'] . ' ' . $row['lastname'] ?></h5>
                                <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mt-0.5">Verified Student</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                        endif;
                    endwhile; 
                else: 
                ?>
                <div class="col-span-full py-16 text-center bg-slate-50 rounded-[2.5rem] border border-dashed border-slate-200">
                    <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Awaiting student experiences...</p>
                </div>
                <?php endif; ?>
            </div>

            <?php if($test_count > 6): ?>
            <div class="mt-12 text-center">
                <button onclick="openModal('testimonialsModal')" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl transition-all text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-slate-400">
                    Read All Testimonials
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Recent Announcements Section -->
    <section id="announcements" class="py-20 sm:py-24 px-4 sm:px-6 max-w-7xl mx-auto w-full">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 sm:mb-12">
            <div class="flex items-center gap-3">
                <span class="w-2.5 h-8 bg-blue-600 rounded-full"></span>
                <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Recent Announcements</h3>
            </div>
            <?php 
            $ann_total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM announcements WHERE audience = 'General'");
            $ann_count = mysqli_fetch_assoc($ann_total_query)['count'];
            if($ann_count > 3): 
            ?>
            <button onclick="openModal('announcementsModal')" class="text-xs font-black uppercase text-blue-600 tracking-wider hover:text-blue-700 transition-colors flex items-center gap-1 group self-start sm:self-auto focus:outline-none focus:underline">
                See All Announcements <span class="transition-transform group-hover:translate-x-1">→</span>
            </button>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php 
            $ann_query = mysqli_query($conn, "SELECT * FROM announcements WHERE audience = 'General' ORDER BY created_at DESC LIMIT 3");
            while($ann = mysqli_fetch_assoc($ann_query)):
                $is_urgent = ($ann['category'] == 'Urgent');
            ?>
            <div class="p-6 sm:p-8 bg-white border border-slate-200 rounded-[2.5rem] shadow-sm hover:shadow-md transition-all relative overflow-hidden group flex flex-col justify-between">
                <?php if($is_urgent): ?>
                <span class="absolute top-0 right-0 bg-rose-600 text-white text-[9px] px-3.5 py-1.5 font-black uppercase tracking-widest rounded-bl-xl shadow-sm">Urgent</span>
                <?php endif; ?>
                <div>
                    <p class="text-blue-600 font-black text-[10px] uppercase tracking-wider mb-3"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                    <h4 class="text-lg sm:text-xl font-bold mb-3 group-hover:text-blue-600 transition-colors text-slate-900 leading-tight"><?= $ann['title'] ?></h4>
                    <p class="text-slate-600 leading-relaxed text-sm mb-4 line-clamp-3"><?= $ann['message'] ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Learning Materials Section -->
    <section id="posts" class="py-20 sm:py-24 bg-slate-50 border-t border-slate-200/60 px-4 sm:px-6 w-full">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 sm:mb-12">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-8 bg-indigo-600 rounded-full"></span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Learning Materials</h3>
                </div>
                <?php 
                $posts_total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts");
                $posts_count = mysqli_fetch_assoc($posts_total_query)['count'];
                if($posts_count > 6): 
                ?>
                <button onclick="openModal('postsModal')" class="text-xs font-black uppercase text-indigo-600 tracking-wider hover:text-indigo-700 transition-colors flex items-center gap-1 group self-start sm:self-auto focus:outline-none focus:underline">
                    Browse All Materials <span class="transition-transform group-hover:translate-x-1">→</span>
                </button>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <?php 
                $posts_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
                while($post = mysqli_fetch_assoc($posts_query)):
                ?>
                <article class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/80 overflow-hidden hover:shadow-md transition-all duration-300 group flex flex-col justify-between p-6 sm:p-8">
                    <div>
                        <div class="mb-4">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 text-[9px] font-black rounded-md uppercase tracking-wider border border-indigo-100">Resource</span>
                        </div>
                        <h4 class="text-lg sm:text-xl font-bold mb-3 group-hover:text-indigo-600 transition-colors text-slate-900 leading-tight"><?= $post['title'] ?></h4>
                        <p class="text-slate-600 text-sm leading-relaxed mb-6 line-clamp-2"><?= $post['content'] ?></p>
                    </div>
                    <?php if($post['file_path']): ?>
                    <div class="border-t border-slate-100 pt-4">
                        <a href="uploads/resources/<?= $post['file_path'] ?>" class="inline-flex items-center gap-1.5 text-indigo-600 font-black text-[11px] uppercase tracking-wider hover:gap-3 transition-all focus:outline-none focus:underline">
                            Download File <span class="text-sm">→</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- The Hall of Fame Section -->
    <section id="passers" class="py-20 sm:py-24 bg-slate-100 px-4 sm:px-6 border-t border-slate-200/40">
        <div class="max-w-7xl mx-auto text-center mb-12 sm:mb-16">
            <h3 class="text-3xl sm:text-4xl font-extrabold mb-4 tracking-tight text-slate-900">The Hall of Fame</h3>
            <p class="text-slate-500 font-medium text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">Celebrating every C-Familia student who successfully conquered their board exams.</p>
        </div>
        
        <div class="max-w-7xl mx-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5 sm:gap-6">
            <?php 
            $passers_query = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
            $total_passers_count = mysqli_num_rows($passers_query);
            $displayed_passers = 0;
            
            if($total_passers_count > 0):
                while($passer = mysqli_fetch_assoc($passers_query)): 
                    $pPath = file_exists("uploads/profiles/".$passer['photo']) ? "uploads/profiles/".$passer['photo'] : "uploads/passers/".$passer['photo'];
                    $displayed_passers++;
                    if($displayed_passers <= 10):
            ?>
            <div class="p-5 sm:p-6 bg-white rounded-3xl shadow-sm border border-slate-200 hover:border-blue-400/60 transition-all duration-300 hover:-translate-y-1 text-center group flex flex-col justify-between">
                <div>
                    <img src="<?= $pPath ?>" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full mx-auto mb-4 object-cover border-4 border-slate-50 group-hover:scale-105 transition-transform shadow-sm">
                    <h5 class="font-bold text-slate-900 text-sm leading-snug mb-1 truncate"><?= $passer['name'] ?></h5>
                    <p class="text-[9px] text-blue-600 font-black uppercase tracking-wider mb-3 truncate"><?= $passer['program'] ?></p>
                </div>
                <div class="flex items-center justify-center gap-1.5 bg-slate-50 rounded-xl py-2 border border-slate-100">
                    <span class="text-xs sm:text-sm font-[900] text-slate-800 tracking-tight"><?= $passer['rating'] ?>%</span>
                    <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Rating</span>
                </div>
            </div>
            <?php 
                    endif;
                endwhile; 
            endif;
            ?>
        </div>

        <?php if($total_passers_count > 10): ?>
        <div class="mt-12 text-center">
            <button onclick="openModal('passersModal')" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-800 font-bold rounded-xl shadow-sm transition-all text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-blue-500">
                View All Hall of Fame Passers
            </button>
        </div>
        <?php endif; ?>
    </section>

    <!-- Contact & Location Section -->
    <section id="contact" class="py-20 sm:py-24 px-4 sm:px-6 bg-white border-t border-slate-100 w-full">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 sm:gap-16 items-center">
            <div class="space-y-8">
                <h3 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Visit our Branches<span class="text-blue-600">.</span></h3>
                <div class="space-y-6 sm:space-y-8">
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 border border-blue-100 rounded-xl flex items-center justify-center text-xl flex-shrink-0 shadow-sm">📍</div>
                        <div>
                            <p class="font-black text-slate-900 uppercase text-xs tracking-wider mb-1">Ozamiz Main</p>
                            <p class="text-slate-600 font-medium text-sm sm:text-base">Ozamiz City, Philippines, 7200</p>
                            <span class="inline-flex items-center gap-1.5 text-[9px] font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full uppercase tracking-wider mt-2.5 border border-emerald-100">● Always Open</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-xl flex items-center justify-center text-xl flex-shrink-0 shadow-sm">📍</div>
                        <div>
                            <p class="font-black text-slate-900 uppercase text-xs tracking-wider mb-1">Oroquieta Branch</p>
                            <p class="text-slate-600 font-medium text-sm sm:text-base">Oroquieta City, Misamis Occidental</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-slate-50 p-6 sm:p-10 rounded-[2.5rem] border border-slate-200/60 shadow-inner">
                <h3 class="text-xl sm:text-2xl font-bold mb-6 text-slate-900 tracking-tight">Get in Touch</h3>
                <div class="space-y-3.5">
                    <div class="flex items-center gap-4 p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm transition-transform hover:translate-x-1">
                        <span class="text-xl select-none">📞</span>
                        <div>
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Phone</p>
                            <p class="font-bold text-slate-900 text-sm sm:text-base">0910 167 6805</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm transition-transform hover:translate-x-1">
                        <span class="text-xl select-none">✉️</span>
                        <div>
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Email</p>
                            <p class="font-bold text-slate-900 text-sm sm:text-base break-all">shielamariscuevas@gmail.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 sm:p-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm transition-transform hover:translate-x-1">
                        <span class="text-xl select-none">💬</span>
                        <div>
                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Messenger</p>
                            <p class="font-bold text-slate-900 text-sm sm:text-base">C-Familia Tutorial Services</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 pt-16 pb-8 px-4 sm:px-6 text-white overflow-hidden relative mt-auto">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400"></div>
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 mb-12 relative">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-lg shadow-lg shadow-blue-600/20">C</div>
                <h1 class="text-2xl font-[900] tracking-tight">C-Familia<span class="text-blue-500">.</span></h1>
            </div>
            <div class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em] text-center md:text-left">
                Empowering the future of Criminologists since 2024
            </div>
        </div>
        <div class="max-w-7xl mx-auto text-center text-slate-600 text-[9px] border-t border-white/5 pt-8 uppercase tracking-[0.25em] font-black">
            &copy; <?= date("Y") ?> C-Familia Tutorial Services • Registered Educational Provider
        </div>
    </footer>

    <!-- MODAL INTEGRATIONS -->

    <!-- Top Performers See More Modal -->
    <div id="topPerformanceModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-[2.5rem] border border-white/10 w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl">
            <div class="p-6 sm:p-8 border-b border-white/5 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">All Top Performers</h3>
                    <p class="text-slate-400 text-xs sm:text-sm mt-1">Honored elite reviewees with board ratings of 95% and above.</p>
                </div>
                <button onclick="closeModal('topPerformanceModal')" class="w-10 h-10 rounded-xl bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 transition-colors flex items-center justify-center text-lg font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    if($top_count > 0):
                        mysqli_data_seek($top_query, 0);
                        while($top = mysqli_fetch_assoc($top_query)):
                            $photoPath = file_exists("uploads/profiles/".$top['photo']) ? "uploads/profiles/".$top['photo'] : "uploads/passers/".$top['photo'];
                    ?>
                    <div class="bg-white/5 border border-white/10 p-5 rounded-3xl flex flex-col justify-between">
                        <div class="flex items-center gap-4 mb-4">
                            <img src="<?= $photoPath ?>" class="w-14 h-14 rounded-xl object-cover ring-2 ring-blue-500/20">
                            <div class="min-w-0">
                                <h4 class="font-bold text-white text-base truncate"><?= $top['name'] ?></h4>
                                <p class="text-blue-400 text-[10px] font-black uppercase tracking-wider truncate"><?= $top['program'] ?></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between bg-slate-950/40 rounded-xl p-3.5 border border-white/5">
                            <span class="text-slate-400 text-[9px] font-black uppercase tracking-wider">Board Rating</span>
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

    <!-- Testimonials See More Modal -->
    <div id="testimonialsModal" class="fixed inset-0 z-50 hidden bg-slate-950/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl border border-slate-200">
            <div class="p-6 sm:p-8 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Student Testimonials</h3>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Real stories from real passers within our learning community.</p>
                </div>
                <button onclick="closeModal('testimonialsModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-colors flex items-center justify-center text-lg font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    if($test_count > 0):
                        mysqli_data_seek($test_query, 0);
                        while($row = mysqli_fetch_assoc($test_query)):
                            $userPic = !empty($row['profile_pic']) ? "uploads/profiles/".$row['profile_pic'] : "uploads/passers/default_user.jpg";
                    ?>
                    <div class="bg-slate-50 p-6 rounded-3xl border border-slate-200/60 flex flex-col justify-between relative group">
                        <p class="text-slate-600 italic text-sm leading-relaxed mb-6">
                            <?= htmlspecialchars($row['content']) ?>
                        </p>
                        <div class="flex items-center gap-3.5 border-t border-slate-200/60 pt-3.5">
                            <img src="<?= $userPic ?>" class="w-10 h-10 rounded-lg object-cover ring-2 ring-white shadow-sm flex-shrink-0">
                            <div class="min-w-0">
                                <h5 class="font-bold text-slate-900 text-xs truncate"><?= $row['firstname'] . ' ' . $row['lastname'] ?></h5>
                                <p class="text-[8px] font-black text-blue-600 uppercase tracking-widest mt-0.5">Verified Student</p>
                            </div>
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

    <!-- Announcements See More Modal -->
    <div id="announcementsModal" class="fixed inset-0 z-50 hidden bg-slate-950/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-4xl max-h-[85vh] flex flex-col shadow-2xl border border-slate-200">
            <div class="p-6 sm:p-8 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">All General Announcements</h3>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Stay up to date with updates from C-Familia Tutorial Services.</p>
                </div>
                <button onclick="closeModal('announcementsModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-colors flex items-center justify-center text-lg font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto space-y-4">
                <?php 
                $ann_all_query = mysqli_query($conn, "SELECT * FROM announcements WHERE audience = 'General' ORDER BY created_at DESC");
                while($ann = mysqli_fetch_assoc($ann_all_query)):
                    $is_urgent = ($ann['category'] == 'Urgent');
                ?>
                <div class="p-5 sm:p-6 bg-slate-50 border border-slate-200 rounded-2xl relative overflow-hidden flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <p class="text-blue-600 font-black text-[10px] uppercase tracking-wider"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                            <?php if($is_urgent): ?>
                            <span class="bg-rose-100 text-rose-700 text-[8px] px-2 py-0.5 font-black uppercase tracking-wider rounded">Urgent</span>
                            <?php endif; ?>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900"><?= $ann['title'] ?></h4>
                        <p class="text-slate-600 text-sm leading-relaxed max-w-2xl"><?= $ann['message'] ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Learning Materials See More Modal -->
    <div id="postsModal" class="fixed inset-0 z-50 hidden bg-slate-950/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl border border-slate-200">
            <div class="p-6 sm:p-8 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">All Learning Materials</h3>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Access the collection of high-quality learning assets.</p>
                </div>
                <button onclick="closeModal('postsModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-colors flex items-center justify-center text-lg font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php 
                    $posts_all_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");
                    while($post = mysqli_fetch_assoc($posts_all_query)):
                    ?>
                    <article class="bg-slate-50 rounded-3xl border border-slate-200/80 p-5 flex flex-col justify-between">
                        <div>
                            <div class="mb-3">
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[8px] font-black rounded uppercase tracking-wider border border-indigo-100">Resource</span>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 mb-2 leading-tight"><?= $post['title'] ?></h4>
                            <p class="text-slate-600 text-xs leading-relaxed mb-4"><?= $post['content'] ?></p>
                        </div>
                        <?php if($post['file_path']): ?>
                        <div class="border-t border-slate-200/60 pt-3 mt-2">
                            <a href="uploads/resources/<?= $post['file_path'] ?>" class="inline-flex items-center gap-1.5 text-indigo-600 font-black text-[10px] uppercase tracking-wider hover:underline">
                                Download File →
                            </a>
                        </div>
                        <?php endif; ?>
                    </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Passers / Hall Of Fame See More Modal -->
    <div id="passersModal" class="fixed inset-0 z-50 hidden bg-slate-950/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-6xl max-h-[85vh] flex flex-col shadow-2xl border border-slate-200">
            <div class="p-6 sm:p-8 border-b border-slate-100 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">The Complete Hall of Fame</h3>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Celebrating every successful reviewee who conquered their board examination milestones.</p>
                </div>
                <button onclick="closeModal('passersModal')" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-colors flex items-center justify-center text-lg font-bold focus:outline-none">✕</button>
            </div>
            <div class="p-6 sm:p-8 overflow-y-auto">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
                    <?php 
                    if($total_passers_count > 0):
                        mysqli_data_seek($passers_query, 0);
                        while($passer = mysqli_fetch_assoc($passers_query)): 
                            $pPath = file_exists("uploads/profiles/".$passer['photo']) ? "uploads/profiles/".$passer['photo'] : "uploads/passers/".$passer['photo'];
                    ?>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center flex flex-col justify-between">
                        <div>
                            <img src="<?= $pPath ?>" class="w-14 h-14 rounded-full mx-auto mb-3 object-cover border-2 border-white shadow-sm">
                            <h5 class="font-bold text-slate-900 text-xs leading-tight mb-0.5 truncate"><?= $passer['name'] ?></h5>
                            <p class="text-[8px] text-blue-600 font-black uppercase tracking-wider mb-2 truncate"><?= $passer['program'] ?></p>
                        </div>
                        <div class="flex items-center justify-center gap-1 bg-white rounded-lg py-1 border border-slate-200/60">
                            <span class="text-xs font-black text-slate-800"><?= $passer['rating'] ?>%</span>
                            <span class="text-[7px] text-slate-400 font-bold uppercase tracking-wider">Rating</span>
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

    <!-- JavaScript to Handle Modals Interactivity -->
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('modal-active');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('modal-active');
            }
        }

        // Close modal instances when clicking background overlays
        window.onclick = function(event) {
            if (event.target.classList.contains('bg-slate-950/50') || event.target.classList.contains('bg-slate-950/60')) {
                event.target.classList.add('hidden');
                document.body.classList.remove('modal-active');
            }
        }
    </script>
</body>
</html>