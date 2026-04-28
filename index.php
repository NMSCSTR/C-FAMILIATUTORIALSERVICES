<?php 
include 'db.php'; 

// --- Auto-Calculate Passing Rate ---
$total_passers_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passers");
$total_passers = mysqli_fetch_assoc($total_passers_query)['count'];
// Dynamic display rate logic
$display_rate = ($total_passers > 0) ? "95%" : "0%"; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>C-Familia Tutorial Services</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        @keyframes bounce-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .animate-bounce-slow { animation: bounce-slow 4s ease-in-out infinite; }
        html { scroll-behavior: smooth; }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased">

<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200 px-6 py-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3 group">
            <img src="cuevaslogo.jpg" alt="C-Familia Logo" class="w-10 h-10 object-contain rounded-lg transition-transform group-hover:scale-110">
            <h1 class="text-2xl font-extrabold tracking-tight text-blue-700">
                C-Familia<span class="text-blue-400">.</span>
            </h1>
        </a>

        <div class="hidden md:flex space-x-8 font-medium text-slate-600">
            <a href="#announcements" class="hover:text-blue-600 transition">Announcements</a>
            <a href="#posts" class="hover:text-blue-600 transition">Resources</a>
            <a href="#passers" class="hover:text-blue-600 transition">Passers</a>
            <a href="#contact" class="hover:text-blue-600 transition">Contact</a>
        </div>

        <div class="flex items-center space-x-4">
            <a href="login.php" class="text-slate-600 font-medium px-4 hover:text-blue-600 transition">Login</a>
            <a href="register.php" class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-md shadow-blue-600/20">
                Join Us
            </a>
        </div>
    </div>
</nav>

    <header class="relative bg-slate-900 py-24 lg:py-32 overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <img src="cuevaslogo.jpg" alt="Background" class="w-full h-full object-cover">
        </div>
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="relative max-w-7xl mx-auto px-6 text-center lg:text-left grid lg:grid-cols-2 items-center gap-12">
            <div>
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/5 border border-white/10 mb-8 animate-bounce-slow">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/40 text-white">C</div>
                    <span class="text-white font-bold tracking-tight uppercase text-xs">C-Familia Services</span>
                </div>
                <h2 class="text-4xl md:text-6xl font-[800] text-white mb-6 leading-[1.1]">
                    Your Future Starts <span class="text-blue-400">Right Here.</span>
                </h2>
                <p class="text-xl text-slate-300 mb-10 leading-relaxed italic font-medium">
                    "Join our family, and together, we'll pave the way towards your success."
                </p>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                    <a href="register.php" class="px-8 py-4 bg-blue-600 text-white rounded-2xl text-lg font-bold hover:bg-blue-500 transition-all transform hover:scale-105 shadow-xl shadow-blue-600/25">Enroll Now</a>
                    <a href="#passers" class="px-8 py-4 bg-white/10 text-white border border-white/20 rounded-2xl text-lg font-bold hover:bg-white/20 transition-all">View Success Stories</a>
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="bg-white/10 backdrop-blur-lg p-8 rounded-[3rem] border border-white/20 relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/20 rounded-full blur-3xl group-hover:bg-blue-500/40 transition-all"></div>
                    <div class="flex items-center gap-4 mb-6 relative">
                        <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center text-white font-bold shadow-lg shadow-green-500/20">✓</div>
                        <p class="text-white text-lg font-bold"><?= $display_rate ?> Passing Rate (<?= $total_passers ?>+ Passers)</p>
                    </div>
                    <div class="space-y-4 relative">
                        <div class="h-3 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 w-[95%] rounded-full shadow-[0_0_20px_rgba(37,99,235,0.5)]"></div>
                        </div>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-[0.2em] text-right">Academic Excellence • 2026</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="py-24 bg-slate-950 text-white relative overflow-hidden">
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-blue-600/5 rounded-full blur-[150px]"></div>
        <div class="max-w-7xl mx-auto px-6 relative">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
                <div>
                    <span class="text-blue-500 font-black uppercase text-xs tracking-[0.4em] mb-3 block">Elite Achievers</span>
                    <h3 class="text-4xl md:text-5xl font-[800] tracking-tight">Top Performance<span class="text-blue-600">.</span></h3>
                </div>
                <p class="text-slate-400 max-w-sm font-medium">Honoring our reviewees who attained an exceptional board rating of 95% and above.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $top_query = mysqli_query($conn, "SELECT * FROM passers WHERE rating >= 95 ORDER BY rating DESC");
                if(mysqli_num_rows($top_query) > 0):
                    while($top = mysqli_fetch_assoc($top_query)):
                        // Check if photo is in profile_pics or passers folder
                        $photoPath = file_exists("uploads/profiles/".$top['photo']) ? "uploads/profiles/".$top['photo'] : "uploads/passers/".$top['photo'];
                ?>
                <div class="bg-white/5 border border-white/10 p-8 rounded-[3rem] group hover:bg-white/10 transition-all border-b-4 border-b-blue-600">
                    <div class="flex items-center gap-6 mb-8">
                        <div class="relative">
                            <img src="<?= $photoPath ?>" class="w-20 h-20 rounded-[2rem] object-cover ring-4 ring-blue-500/20 shadow-2xl">
                            <div class="absolute -top-3 -right-3 bg-blue-600 text-[10px] font-black px-2 py-1 rounded-lg shadow-xl animate-pulse">TOP</div>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white"><?= $top['name'] ?></h4>
                            <p class="text-blue-400 text-xs font-black uppercase tracking-widest mt-1"><?= $top['program'] ?></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between bg-slate-900/50 rounded-2xl p-5 border border-white/5">
                        <span class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Board Rating</span>
                        <span class="text-3xl font-[900] text-blue-500 italic"><?= number_format($top['rating'], 2) ?>%</span>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <div class="col-span-full py-12 text-center bg-white/5 rounded-[2rem] border border-dashed border-white/10">
                        <p class="text-slate-500 font-bold uppercase tracking-widest text-sm">Top results are currently being verified.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="announcements" class="py-24 px-6 max-w-7xl mx-auto">
        <div class="flex items-center gap-4 mb-12">
            <span class="w-2.5 h-10 bg-blue-600 rounded-full"></span>
            <h3 class="text-4xl font-extrabold tracking-tight">Recent Announcements</h3>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <?php 
            $ann_query = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
            while($ann = mysqli_fetch_assoc($ann_query)):
                $is_urgent = ($ann['category'] == 'Urgent');
            ?>
            <div class="p-8 bg-white border border-slate-200 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all relative overflow-hidden group">
                <?php if($is_urgent): ?>
                <span class="absolute top-0 right-0 bg-red-600 text-white text-[10px] px-4 py-1.5 font-black uppercase tracking-widest rounded-bl-2xl">Urgent</span>
                <?php endif; ?>
                <p class="text-blue-600 font-black text-[10px] uppercase tracking-widest mb-4"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                <h4 class="text-xl font-bold mb-4 group-hover:text-blue-600 transition-colors"><?= $ann['title'] ?></h4>
                <p class="text-slate-600 leading-relaxed text-sm mb-4"><?= $ann['message'] ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="posts" class="py-24 px-6 max-w-7xl mx-auto border-t border-slate-200">
        <div class="flex items-center gap-4 mb-12">
            <span class="w-2.5 h-10 bg-indigo-600 rounded-full"></span>
            <h3 class="text-4xl font-extrabold tracking-tight">Learning Materials</h3>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            $posts_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
            while($post = mysqli_fetch_assoc($posts_query)):
            ?>
            <article class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden hover:shadow-2xl transition-all duration-500 group">
                <div class="p-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black rounded-lg uppercase tracking-widest border border-indigo-100">Resource</span>
                    </div>
                    <h4 class="text-xl font-bold mb-3 group-hover:text-indigo-600 transition-colors"><?= $post['title'] ?></h4>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6 line-clamp-2"><?= $post['content'] ?></p>
                    <?php if($post['file_path']): ?>
                    <a href="uploads/resources/<?= $post['file_path'] ?>" class="inline-flex items-center gap-2 text-indigo-600 font-black text-xs uppercase tracking-widest hover:gap-4 transition-all">
                        Download File <span class="text-lg">→</span>
                    </a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="passers" class="py-24 bg-slate-100 px-6">
        <div class="max-w-7xl mx-auto text-center mb-16">
            <h3 class="text-4xl font-extrabold mb-4 tracking-tight">The Hall of Fame</h3>
            <p class="text-slate-500 font-medium max-w-2xl mx-auto">Celebrating every C-Familia student who successfully conquered their board exams.</p>
        </div>
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <?php 
            $passers_query = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC LIMIT 20");
            while($passer = mysqli_fetch_assoc($passers_query)): 
                $pPath = file_exists("uploads/profiles/".$passer['photo']) ? "uploads/profiles/".$passer['photo'] : "uploads/passers/".$passer['photo'];
            ?>
            <div class="p-6 bg-white rounded-[2rem] shadow-sm border border-slate-200 group hover:border-blue-300 transition-all text-center">
                <img src="<?= $pPath ?>" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover border-4 border-slate-50 group-hover:scale-110 transition-transform shadow-md">
                <h5 class="font-bold text-slate-900 text-sm leading-tight mb-1"><?= $passer['name'] ?></h5>
                <p class="text-[9px] text-blue-600 font-black uppercase tracking-tighter mb-1"><?= $passer['program'] ?></p>
                <div class="flex items-center justify-center gap-1.5">
                    <span class="text-[10px] font-[900] text-slate-800"><?= $passer['rating'] ?>%</span>
                    <span class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Rating</span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="contact" class="py-24 px-6 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <h3 class="text-4xl font-extrabold mb-8 tracking-tight">Visit our Branches<span class="text-blue-600">.</span></h3>
                <div class="space-y-8">
                    <div class="flex items-start gap-6">
                        <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 shadow-lg shadow-blue-600/10">📍</div>
                        <div>
                            <p class="font-black text-slate-900 uppercase text-xs tracking-widest mb-1">Ozamiz Main</p>
                            <p class="text-slate-600 font-medium">Ozamiz City, Philippines, 7200</p>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-green-600 bg-green-50 px-3 py-1 rounded-full uppercase tracking-widest mt-2 border border-green-100">● Always Open</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-6">
                        <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 shadow-lg shadow-indigo-600/10">📍</div>
                        <div>
                            <p class="font-black text-slate-900 uppercase text-xs tracking-widest mb-1">Oroquieta Branch</p>
                            <p class="text-slate-600 font-medium">Oroquieta City, Misamis Occidental</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 p-10 rounded-[3rem] border border-slate-200/60 shadow-inner">
                <h3 class="text-2xl font-bold mb-8">Get in Touch</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm transition-transform hover:translate-x-2">
                        <span class="text-2xl">📞</span>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Phone</p>
                            <p class="font-bold text-slate-900">0910 167 6805</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm transition-transform hover:translate-x-2">
                        <span class="text-2xl">✉️</span>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Email</p>
                            <p class="font-bold text-slate-900">shielamariscuevas@gmail.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-slate-200 shadow-sm transition-transform hover:translate-x-2">
                        <span class="text-2xl">💬</span>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Messenger</p>
                            <p class="font-bold text-slate-900">C-Familia Tutorial Services</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-950 pt-20 pb-10 px-6 text-white overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-indigo-500 to-blue-400"></div>
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-10 mb-16 relative">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-xl shadow-blue-600/20">C</div>
                <h1 class="text-3xl font-[900] tracking-tighter">C-Familia<span class="text-blue-500">.</span></h1>
            </div>
            <div class="text-slate-500 text-sm font-bold uppercase tracking-[0.2em] text-center md:text-left">
                Empowering the future of Criminologists since 2024
            </div>
        </div>
        <div class="max-w-7xl mx-auto text-center text-slate-600 text-[10px] border-t border-white/5 pt-10 uppercase tracking-[0.3em] font-black">
            &copy; <?= date("Y") ?> C-Familia Tutorial Services • Registered Educational Provider
        </div>
    </footer>

</body>
</html>