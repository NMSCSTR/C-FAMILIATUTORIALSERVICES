<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
include 'db.php';

$error = "";

if (isset($_POST['login'])) {
    csrf_verify();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            log_activity($conn, 'login.success', 'Successful login', [
                'user_id' => (int) $user['id'],
                'user_role' => $user['role'],
            ]);

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        }

        log_activity($conn, 'login.failed', 'Invalid password attempt');
        $error = "Invalid email or password.";
    } else {
        log_activity($conn, 'login.failed', 'Login attempt for unknown account');
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Login | C-Familia</title>
    <script>
        // Inline check to prevent flashing during initial load
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- Left Side Panel (Branding & Features, Desktop Only) -->
    <div class="lg:col-span-5 xl:col-span-6 hidden lg:flex flex-col justify-between p-12 bg-gradient-to-tr from-slate-950 via-blue-950 to-indigo-950 relative overflow-hidden text-white border-r border-slate-900/40">
        <!-- Floating Animated Glowing Blobs -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-600/20 rounded-full filter blur-[80px] animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-600/15 rounded-full filter blur-[80px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        
        <!-- Header: Back to main site -->
        <div class="relative z-10">
            <a href="index.php" class="inline-flex items-center gap-2 text-sm text-slate-300 hover:text-white font-semibold transition group">
                <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to main site
            </a>
        </div>

        <!-- Middle Content -->
        <div class="relative z-10 my-auto max-w-lg">
            <div class="flex items-center gap-4 mb-8">
                <img src="cuevaslogo.jpg" alt="Logo" class="w-14 h-14 rounded-2xl shadow-xl border border-white/10 p-1 bg-white object-contain">
                <div>
                    <h1 class="text-xl font-bold tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-300">C-FAMILIA</h1>
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-widest">Tutorial Services</p>
                </div>
            </div>

            <h2 class="text-4xl xl:text-5xl font-extrabold leading-tight tracking-tight mb-6 bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-100 to-slate-400">
                Unlock Your Potential.<br>Achieve Your License.
            </h2>
            <p class="text-slate-400 text-base leading-relaxed mb-8">
                Access your personalized review dashboard, monitor class schedules, track academic milestones, and submit payment records securely.
            </p>

            <!-- Feature Bullet Lists -->
            <ul class="space-y-4 text-sm text-slate-300">
                <li class="flex items-center gap-3">
                    <div class="p-1 rounded-full bg-blue-500/10 text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span>Proven Board Exam Review Track Record</span>
                </li>
                <li class="flex items-center gap-3">
                    <div class="p-1 rounded-full bg-blue-500/10 text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span>Comprehensive Dashboard & Learning Resources</span>
                </li>
                <li class="flex items-center gap-3">
                    <div class="p-1 rounded-full bg-blue-500/10 text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span>Seamless Online Payment Verification Workflow</span>
                </li>
            </ul>
        </div>

        <!-- Footer Metric / Slogan -->
        <div class="relative z-10 border-t border-slate-900 pt-6">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Serving future professionals</p>
        </div>
    </div>

    <!-- Right Side Panel (Form Card) -->
    <div class="lg:col-span-7 xl:col-span-6 flex items-center justify-center p-6 sm:p-12 relative overflow-hidden">
        
        <!-- Mobile/Tablet Background elements -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-500/10 rounded-full filter blur-[80px] lg:hidden"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full filter blur-[80px] lg:hidden"></div>

        <div class="w-full max-w-md bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl p-8 sm:p-10 rounded-[2.5rem] border border-slate-200/50 dark:border-slate-800/40 shadow-2xl shadow-slate-900/5 dark:shadow-black/20 relative z-10 transition-all duration-300 hover:shadow-blue-900/5">
            
            <!-- Mobile Logo / Navigation -->
            <div class="flex items-center justify-between mb-8 lg:hidden">
                <a href="index.php" class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition group">
                    <svg class="w-3.5 h-3.5 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to site
                </a>
                <img src="cuevaslogo.jpg" alt="Logo" class="w-10 h-10 rounded-xl shadow p-0.5 bg-white object-contain">
            </div>

            <!-- Header -->
            <div class="mb-8">
                <!-- Large Screen Logo (Centered) -->
                <div class="hidden lg:block mb-5">
                    <a href="index.php" class="inline-block transition hover:scale-105">
                        <img src="cuevaslogo.jpg" alt="Logo" class="w-16 h-16 rounded-2xl shadow-md border border-slate-100 p-1.5 bg-white object-contain">
                    </a>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Login your account</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Log in to your C-Familia account to continue</p>
            </div>

            <!-- Error Alerts -->
            <?php if(isset($error)): ?>
                <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 p-4 rounded-2xl text-sm mb-6 border border-red-100 dark:border-red-950/30 font-medium">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST" id="login-form" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="login" value="1">
                <!-- Email Input -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1">Email Address</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required placeholder="you@example.com" <?= isset($error) ? 'aria-invalid="true"' : '' ?>
                               class="w-full pl-12 pr-5 py-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1">Password</label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input type="password" name="password" id="password-input" required placeholder="••••••••" autocomplete="current-password" aria-describedby="capslock-note"
                               class="w-full pl-12 pr-12 py-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                        <p id="capslock-note" role="status" class="hidden mt-1.5 ml-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">⚠ Caps Lock is on</p>
                        
                        <!-- Toggle visibility button -->
                        <button type="button" id="password-toggle-btn" 
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none transition-colors p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80">
                            <!-- Eye Open -->
                            <svg id="eye-open-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <!-- Eye Closed -->
                            <svg id="eye-closed-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="login" id="submit-btn" 
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-2xl hover:from-blue-700 hover:to-indigo-700 transition duration-300 shadow-lg shadow-blue-500/20 dark:shadow-blue-900/30 transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                    <!-- Spinner Icon (hidden by default) -->
                    <svg id="spinner-icon" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="btn-text">Sign In</span>
                </button>
            </form>
            
            <!-- Bottom Navigation -->
            <div class="mt-8 text-center">
                <p class="text-slate-500 dark:text-slate-400 text-sm">
                    Don't have an account? <a href="register.php" class="text-blue-600 dark:text-blue-400 font-extrabold hover:underline transition">Join the family</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Scripting for interactivity -->
    <script>
        const passwordInput = document.getElementById('password-input');
        const toggleBtn = document.getElementById('password-toggle-btn');
        const eyeOpenIcon = document.getElementById('eye-open-icon');
        const eyeClosedIcon = document.getElementById('eye-closed-icon');
        const loginForm = document.getElementById('login-form');
        const submitBtn = document.getElementById('submit-btn');
        const spinnerIcon = document.getElementById('spinner-icon');
        const btnText = document.getElementById('btn-text');

        // Password visibility toggler
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                
                if (isPassword) {
                    eyeOpenIcon.classList.add('hidden');
                    eyeClosedIcon.classList.remove('hidden');
                } else {
                    eyeOpenIcon.classList.remove('hidden');
                    eyeClosedIcon.classList.add('hidden');
                }
            });
        }

        // Caps Lock hint
        (function () {
            const note = document.getElementById('capslock-note');
            const handler = function (e) {
                if (typeof e.getModifierState === 'function') {
                    note.classList.toggle('hidden', !e.getModifierState('CapsLock'));
                }
            };
            passwordInput.addEventListener('keydown', handler);
            passwordInput.addEventListener('keyup', handler);
            passwordInput.addEventListener('blur', function () { note.classList.add('hidden'); });
        })();

        // Form submit loading feedback
        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                // Disable button and show spinner
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                spinnerIcon.classList.remove('hidden');
                btnText.textContent = 'Signing in...';
            });
        }
    </script>
</body>
</html>