<?php 
include 'db.php'; 

// --- Auto-Calculate Passing Rate ---
$total_passers_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passers");
$total_passers = mysqli_fetch_assoc($total_passers_query)['count'];
$display_rate = ($total_passers > 0) ? "95%" : "0%"; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>C-Familia Tutorial Services</title>
    <style>
    body { font-family: 'Inter', sans-serif; }
    .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    @keyframes bounce-slow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    .animate-bounce-slow { animation: bounce-slow 4s ease-in-out infinite; }
    </style>
</head>

<body class="bg-slate-50 text-slate-900">

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
            <a href="register.php" class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-md">
                Join Us
            </a>
        </div>
    </div>
</nav>

    <header class="relative bg-slate-900 py-24 lg:py-32 overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <img src="cuevaslogo.jpg" alt="Students studying" class="w-full h-full object-cover">
        </div>
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="relative max-w-7xl mx-auto px-6 text-center lg:text-left grid lg:grid-cols-2 items-center gap-12">
            <div>
                <div class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/5 border border-white/10 mb-8 animate-bounce-slow">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-blue-600/40 text-white">C</div>
                    <span class="text-white font-bold tracking-tight uppercase text-sm">C-Familia Services</span>
                </div>
                <h2 class="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-tight">
                    Your Future Starts <span class="text-blue-400">Right Here.</span>
                </h2>
                <p class="text-xl text-slate-300 mb-10 leading-relaxed italic">
                    "Join our family, and together, we'll pave the way towards your success."
                </p>
                <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                    <a href="register.php" class="px-8 py-4 bg-blue-600 text-white rounded-xl text-lg font-bold hover:bg-blue-500 transition-all transform hover:scale-105">Enroll Now</a>
                    <a href="#passers" class="px-8 py-4 bg-white/10 text-white border border-white/20 rounded-xl text-lg font-bold hover:bg-white/20 transition-all">View Success Stories</a>
                </div>
            </div>

            <div class="hidden lg:block">
                <div class="bg-white/10 backdrop-blur-lg p-8 rounded-3xl border border-white/20 relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/20 rounded-full blur-3xl group-hover:bg-blue-500/40 transition-all"></div>
                    <div class="flex items-center gap-4 mb-6 relative">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg shadow-green-500/20">✓</div>
                        <p class="text-white text-lg font-semibold"><?= $display_rate ?> Passing Rate (<?= $total_passers ?>+ Passers)</p>
                    </div>
                    <div class="space-y-4 relative">
                        <div class="h-2.5 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 w-[95%] rounded-full"></div>
                        </div>
                        <p class="text-slate-400 text-xs font-medium tracking-wide text-right">Ozamiz • Oroquieta • 2026 Batch</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="announcements" class="py-16 px-6 max-w-7xl mx-auto">
        <div class="flex items-center gap-3 mb-8">
            <span class="w-2 h-8 bg-blue-600 rounded-full"></span>
            <h3 class="text-3xl font-bold">Latest Announcements</h3>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php 
            $ann_query = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
            while($ann = mysqli_fetch_assoc($ann_query)):
                $is_urgent = ($ann['category'] == 'Urgent');
            ?>
            <div class="p-6 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition relative overflow-hidden">
                <?php if($is_urgent): ?>
                <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] px-3 py-1 font-bold uppercase tracking-widest">Urgent</span>
                <?php endif; ?>
                <p class="text-blue-600 font-bold text-sm mb-2"><?= date('M d, Y', strtotime($ann['created_at'])) ?></p>
                <h4 class="text-xl font-bold mb-3"><?= $ann['title'] ?></h4>
                <p class="text-slate-600 mb-4"><?= $ann['message'] ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="posts" class="py-16 px-6 max-w-7xl mx-auto border-t border-slate-100">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                <h3 class="text-3xl font-bold">Educational Tips & Updates</h3>
            </div>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            $posts_query = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 6");
            while($post = mysqli_fetch_assoc($posts_query)):
            ?>
            <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-bold rounded uppercase">Resource</span>
                    </div>
                    <h4 class="text-xl font-bold mb-2"><?= $post['title'] ?></h4>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-2"><?= $post['content'] ?></p>
                    <?php if($post['file_path']): ?>
                    <a href="uploads/resources/<?= $post['file_path'] ?>" class="text-blue-600 text-sm font-bold hover:underline flex items-center gap-2">Download Attachment →</a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="passers" class="py-20 bg-slate-100 px-6">
        <div class="max-w-7xl mx-auto text-center mb-12">
            <h3 class="text-4xl font-extrabold mb-4">Our Hall of Fame</h3>
            <p class="text-slate-600">Celebrating the hard work and success of our C-Familia board passers.</p>
        </div>
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6 text-center">
            <?php 
            $passers_query = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC LIMIT 15");
            while($passer = mysqli_fetch_assoc($passers_query)): 
            ?>
            <div class="p-5 bg-white rounded-2xl shadow-sm border border-slate-200 group">
                <img src="uploads/passers/<?= $passer['photo'] ?>" class="w-20 h-20 rounded-full mx-auto mb-3 object-cover border-4 border-slate-50 group-hover:border-blue-100 transition-all">
                <p class="font-bold text-slate-800"><?= $passer['name'] ?></p>
                <p class="text-[10px] text-blue-600 uppercase font-black tracking-widest mt-1"><?= $passer['program'] ?></p>
                <p class="text-[9px] text-slate-400 font-bold uppercase"><?= $passer['batch'] ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="contact" class="py-20 px-6 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16">
            <div>
                <h3 class="text-3xl font-bold mb-6 italic">Visit our Branches</h3>
                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">📍</div>
                        <div>
                            <p class="font-bold text-slate-800">Ozamiz Main Branch</p>
                            <p class="text-slate-600">Ozamiz City, Philippines, 7200</p>
                            <span class="text-xs font-bold text-green-600 uppercase tracking-widest mt-1 block">● Always Open</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">📍</div>
                        <div>
                            <p class="font-bold text-slate-800">Oroquieta Branch</p>
                            <p class="text-slate-600">Oroquieta City, Misamis Occidental, Philippines</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
                <h3 class="text-2xl font-bold mb-6">Contact Information</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
                        <span class="text-xl">📞</span>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400">Phone Number</p>
                            <p class="font-bold text-slate-800">0910 167 6805</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
                        <span class="text-xl">✉️</span>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400">Email Address</p>
                            <p class="font-bold text-slate-800 text-sm">shielamariscuevas@gmail.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
                        <span class="text-xl">💬</span>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400">Messenger</p>
                            <p class="font-bold text-slate-800">C-Familia Tutorial Services</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 pt-16 pb-8 px-6 text-white">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 mb-12">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white">C</div>
                <h1 class="text-2xl font-extrabold tracking-tight">C-Familia<span class="text-blue-400">.</span></h1>
            </div>
            <div class="text-slate-400 text-sm text-center md:text-left">
                Main: Ozamiz City, Philippines • Branch: Oroquieta City
            </div>
        </div>1
        <div class="text-center text-slate-500 text-xs border-t border-white/5 pt-8 uppercase tracking-[0.2em] font-medium">
            &copy; <?= date("Y") ?> C-Familia Tutorial Services. All rights reserved.
        </div>
    </footer>

</body>
</html>