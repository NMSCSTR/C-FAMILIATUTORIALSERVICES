<?php include 'db.php'; ?>
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
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-extrabold tracking-tight text-blue-700">C-Familia<span class="text-blue-400">.</span></h1>
            <div class="hidden md:flex space-x-8 font-medium text-slate-600">
                <a href="#announcements" class="hover:text-blue-600 transition">Announcements</a>
                <a href="#passers" class="hover:text-blue-600 transition">Passers</a>
                <a href="#contact" class="hover:text-blue-600 transition">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="login.php" class="text-slate-600 font-medium px-4">Login</a>
                <a href="register.php" class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-md">Join Us</a>
            </div>
        </div>
    </nav>

    <header class="relative bg-slate-900 py-24 lg:py-32 overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80" alt="Students studying" class="w-full h-full object-cover">
        </div>
        <div class="relative max-w-7xl mx-auto px-6 text-center lg:text-left grid lg:grid-cols-2 items-center gap-12">
            <div>
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
                <div class="bg-white/10 backdrop-blur-lg p-8 rounded-3xl border border-white/20">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">✓</div>
                        <p class="text-white text-lg font-semibold">95% Passing Rate in 2025</p>
                    </div>
                    <div class="space-y-4">
                        <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 w-[95%]"></div>
                        </div>
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
            <div class="p-6 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition relative overflow-hidden">
                <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] px-3 py-1 font-bold uppercase tracking-widest">New</span>
                <p class="text-blue-600 font-bold text-sm mb-2">March 25, 2026</p>
                <h4 class="text-xl font-bold mb-3">Summer Review Enrollment</h4>
                <p class="text-slate-600 mb-4">Early bird registration is now open for the April intensive review sessions. Save 10% before April 5th!</p>
                <a href="#" class="text-blue-600 font-semibold hover:underline">Read Details →</a>
            </div>
            <div class="p-6 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md transition">
                <p class="text-blue-600 font-bold text-sm mb-2">March 20, 2026</p>
                <h4 class="text-xl font-bold mb-3">Mock Exam Schedule</h4>
                <p class="text-slate-600 mb-4">The final mock exam for board examinees will be held this coming Saturday at the Ozamiz branch.</p>
                <a href="#" class="text-blue-600 font-semibold hover:underline">Read Details →</a>
            </div>
        </div>
    </section>

    <section id="posts" class="py-16 px-6 max-w-7xl mx-auto border-t border-slate-100">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <span class="w-2 h-8 bg-indigo-600 rounded-full"></span>
                <h3 class="text-3xl font-bold">Educational Tips & Updates</h3>
            </div>
            <a href="#" class="text-blue-600 font-semibold hover:text-blue-800 transition">View All Posts →</a>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&q=80&w=600" alt="Study Tips" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-xs font-bold rounded uppercase">Study Hacks</span>
                        <span class="text-slate-400 text-xs">5 min read</span>
                    </div>
                    <h4 class="text-xl font-bold mb-2 hover:text-blue-600 cursor-pointer">5 Effective Ways to Retain Information Faster</h4>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-2">Mastering the art of active recall can significantly decrease your study time while increasing your exam scores...</p>
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold">CF</div>
                        <p class="text-xs font-semibold text-slate-700">By C-Familia Admin</p>
                    </div>
                </div>
            </article>

            <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1454165833767-027508000401?auto=format&fit=crop&q=80&w=600" alt="Exam Prep" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-1 bg-green-50 text-green-600 text-xs font-bold rounded uppercase">Exam Prep</span>
                        <span class="text-slate-400 text-xs">8 min read</span>
                    </div>
                    <h4 class="text-xl font-bold mb-2 hover:text-blue-600 cursor-pointer">What to Expect During the Licensure Exam</h4>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-2">A comprehensive guide on the requirements, dos, and don'ts for your upcoming board examination day...</p>
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold">CF</div>
                        <p class="text-xs font-semibold text-slate-700">By C-Familia Admin</p>
                    </div>
                </div>
            </article>

            <article class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=600" alt="Online Learning" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-1 bg-orange-50 text-orange-600 text-xs font-bold rounded uppercase">Tech</span>
                        <span class="text-slate-400 text-xs">4 min read</span>
                    </div>
                    <h4 class="text-xl font-bold mb-2 hover:text-blue-600 cursor-pointer">Top 3 Apps to Keep You Organized</h4>
                    <p class="text-slate-600 text-sm mb-4 line-clamp-2">Staying organized is half the battle. We've curated a list of the best apps to track your study progress...</p>
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold">CF</div>
                        <p class="text-xs font-semibold text-slate-700">By C-Familia Admin</p>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section id="passers" class="py-20 bg-slate-100 px-6">
        <div class="max-w-7xl mx-auto text-center mb-12">
            <h3 class="text-4xl font-extrabold mb-4">Our Hall of Fame</h3>
            <p class="text-slate-600">Celebrating the hard work and success of our C-Familia board passers.</p>
        </div>
        <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6 text-center">
            <?php 
                // Sample data - replace with a query like: SELECT * FROM passers LIMIT 10
                $passers = ["Juan Dela Cruz", "Maria Santos", "Elena Reyes", "Roberto Diaz", "Sarah Geronimo", "Mark Abad", "Liza Soberano", "Enrique Gil", "Piolo Jose", "Bea Alonzo"];
                foreach($passers as $name): 
            ?>
            <div class="p-4 bg-white rounded-xl shadow-sm border border-slate-200">
                <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto mb-3 flex items-center justify-center">
                    <span class="text-blue-700 font-bold"><?= substr($name, 0, 1) ?></span>
                </div>
                <p class="font-bold text-slate-800"><?= $name ?></p>
                <p class="text-xs text-blue-600 uppercase font-semibold mt-1">LPT Passer</p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer id="contact" class="bg-white border-t border-slate-200 pt-16 pb-8 px-6">
        <div class="max-w-7xl mx-auto grid md:grid-cols-3 gap-12 mb-12">
            <div>
                <h4 class="font-bold text-xl mb-4">C-Familia</h4>
                <p class="text-slate-600 italic">"Join our family, and together, we'll pave the way towards your success"</p>
            </div>
            <div>
                <h4 class="font-bold text-xl mb-4">Locations</h4>
                <p class="text-slate-600">📍 Ozamiz, Philippines</p>
                <p class="text-slate-600">📍 Oroquieta City</p>
            </div>
            <div>
                <h4 class="font-bold text-xl mb-4">Contact Us</h4>
                <p class="text-slate-600">📞 0910 167 6805</p>
                <p class="text-slate-600">📧 shielamariscuevas@gmail.com</p>
            </div>
        </div>
        <div class="text-center text-slate-400 text-sm border-t border-slate-100 pt-8">
            &copy; <?= date("Y") ?> C-Familia Tutorial Services. All rights reserved.
        </div>
    </footer>

</body>
</html>