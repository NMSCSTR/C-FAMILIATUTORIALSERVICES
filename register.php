<?php
require_once __DIR__ . '/lib/csrf.php';
secure_session_start();
include 'db.php';
$message = ""; $error = "";
$error_field = null;
$old = [];

if (isset($_POST['register'])) {
    csrf_verify();

    $old = [
        'firstname'             => trim($_POST['firstname'] ?? ''),
        'middlename'            => trim($_POST['middlename'] ?? ''),
        'lastname'              => trim($_POST['lastname'] ?? ''),
        'email'                 => strtolower(trim($_POST['email'] ?? '')),
        'birthday'              => trim($_POST['birthday'] ?? ''),
        'cellphone_no'          => trim($_POST['cellphone_no'] ?? ''),
        'address'               => trim($_POST['address'] ?? ''),
        'parents_name_guardian' => trim($_POST['parents_name_guardian'] ?? ''),
        'parents_phone_no'      => trim($_POST['parents_phone_no'] ?? ''),
        'fb_messenger_account'  => trim($_POST['fb_messenger_account'] ?? ''),
    ];

    $firstname = $old['firstname'];
    $middlename = $old['middlename'];
    $lastname = $old['lastname'];
    $email = $old['email'];
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $birthday = trim($_POST['birthday'] ?? '');
    $cellphone_no = trim($_POST['cellphone_no'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $parents_name_guardian = trim($_POST['parents_name_guardian'] ?? '');
    $parents_phone_no = trim($_POST['parents_phone_no'] ?? '');
    $fb_messenger_account = trim($_POST['fb_messenger_account'] ?? '');

    if ($firstname === '' || $lastname === '' || $email === '') {
        $error = "Please fill in all required fields.";
        $error_field = 'required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
        $error_field = 'email';
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
        $error_field = 'password';
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
        $error_field = 'password';
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email is already registered!";
            $error_field = 'email';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';

            $stmt = $conn->prepare("INSERT INTO users (firstname, middlename, lastname, email, password, role, birthday, cellphone_no, address, parents_name_guardian, parents_phone_no, fb_messenger_account)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                $stmt->bind_param(
                    "ssssssssssss",
                    $firstname,
                    $middlename,
                    $lastname,
                    $email,
                    $hashed_password,
                    $role,
                    $birthday,
                    $cellphone_no,
                    $address,
                    $parents_name_guardian,
                    $parents_phone_no,
                    $fb_messenger_account
                );

                if ($stmt->execute()) {
                    $new_user_id = $stmt->insert_id;
                    log_activity($conn, 'register', "New student account registered", [
                        'user_id' => $new_user_id,
                        'user_role' => 'student',
                        'entity_type' => 'user',
                        'entity_id' => $new_user_id,
                    ]);
                    $message = "Registration successful! You can now <a href='login.php' class='underline font-bold'>Login</a>.";
                } else {
                    error_log('Registration failed: ' . $stmt->error);
                    $error = "Registration failed. Please try again.";
                }
            } else {
                error_log('Registration prepare failed: ' . $conn->error);
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

$field = function (string $key) use ($old): string {
    return htmlspecialchars($old[$key] ?? '', ENT_QUOTES, 'UTF-8');
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/app.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Join C-Familia | Registration</title>
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
    <div class="lg:col-span-5 xl:col-span-5 hidden lg:flex flex-col justify-between p-12 bg-gradient-to-tr from-slate-950 via-blue-950 to-indigo-950 relative overflow-hidden text-white border-r border-slate-900/40">
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
                Start Your Journey.<br>Join the Family.
            </h2>
            <p class="text-slate-400 text-base leading-relaxed mb-8">
                Create a student account to enroll in our specialized review programs, connect with expert instructors, and access your custom training materials.
            </p>

            <!-- Feature Bullet Lists -->
            <ul class="space-y-4 text-sm text-slate-300">
                <li class="flex items-center gap-3">
                    <div class="p-1 rounded-full bg-blue-500/10 text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span>Simple Online Student Registration</span>
                </li>
                <li class="flex items-center gap-3">
                    <div class="p-1 rounded-full bg-blue-500/10 text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span>Secure Parent & Guardian Coordination</span>
                </li>
                <li class="flex items-center gap-3">
                    <div class="p-1 rounded-full bg-blue-500/10 text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span>Immediate Access to Review Resources</span>
                </li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="relative z-10 border-t border-slate-900/60 pt-6">
            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Serving future professionals</p>
        </div>
    </div>

    <!-- Right Side Panel (Scrollable Card) -->
    <div class="lg:col-span-7 xl:col-span-7 flex items-center justify-center p-4 sm:p-8 lg:p-12 relative overflow-hidden bg-slate-50 dark:bg-slate-950 min-h-screen">
        
        <!-- Mobile/Tablet Background elements -->
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-500/10 rounded-full filter blur-[80px] lg:hidden"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full filter blur-[80px] lg:hidden"></div>

        <div class="w-full max-w-2xl bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl p-6 sm:p-10 rounded-[2.5rem] border border-slate-200/50 dark:border-slate-800/40 shadow-2xl shadow-slate-900/5 dark:shadow-black/20 relative z-10 transition-all duration-300 hover:shadow-blue-900/5 my-6">
            
            <!-- Mobile Logo / Navigation -->
            <div class="flex items-center justify-between mb-6 lg:hidden">
                <a href="index.php" class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold transition group">
                    <svg class="w-3.5 h-3.5 transform group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to site
                </a>
                <img src="cuevaslogo.jpg" alt="Logo" class="w-10 h-10 rounded-xl shadow p-0.5 bg-white object-contain">
            </div>

            <!-- Header -->
            <div class="mb-6">
                <!-- Large Screen Header navigation -->
                <div class="hidden lg:flex items-center justify-between mb-5">
                    <a href="index.php" class="inline-block transition hover:scale-105">
                        <img src="cuevaslogo.jpg" alt="Logo" class="w-14 h-14 rounded-2xl shadow-md border border-slate-100 p-1 bg-white object-contain">
                    </a>
                    <a href="login.php" class="text-sm text-blue-600 dark:text-blue-400 font-bold hover:underline transition">
                        Already registered? Login instead
                    </a>
                </div>
                <h2 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Create student account</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Join the C-Familia academic board review program</p>
            </div>

            <!-- Messages Alerts -->
            <?php if($error): ?>
                <div role="alert" class="flex items-start gap-3 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 p-4 rounded-2xl text-sm mb-6 border border-red-100 dark:border-red-950/30 font-medium">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <?php if($message): ?>
                <div role="status" class="flex items-start gap-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 p-4 rounded-2xl text-sm mb-6 border border-emerald-100 dark:border-emerald-950/30 font-medium">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?= $message ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="POST" id="register-form" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="register" value="1">
                
                <!-- Section: Personal Information -->
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4 uppercase tracking-wider">Personal Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- First Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="firstname">First Name</label>
                            <div class="relative group">
                                <input type="text" id="firstname" autocomplete="given-name" name="firstname" value="<?= $field('firstname') ?>" required placeholder="Juan"
                                       class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                            </div>
                        </div>
                        <!-- Middle Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="middlename">Middle Name</label>
                            <div class="relative group">
                                <input type="text" id="middlename" autocomplete="additional-name" name="middlename" value="<?= $field('middlename') ?>" placeholder="Dela"
                                       class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                            </div>
                        </div>
                        <!-- Last Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="lastname">Last Name</label>
                            <div class="relative group">
                                <input type="text" id="lastname" autocomplete="family-name" name="lastname" value="<?= $field('lastname') ?>" required placeholder="Cruz"
                                       class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Birthday -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="birthday">Birthday</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input type="date" id="birthday" autocomplete="bday" name="birthday" value="<?= $field('birthday') ?>" required
                                       class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-700 dark:text-white focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                            </div>
                        </div>
                        <!-- Cellphone -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="cellphone_no">Cellphone #</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input type="text" id="cellphone_no" name="cellphone_no" value="<?= $field('cellphone_no') ?>" required placeholder="09123456789"
                                       class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Contact Details -->
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4 uppercase tracking-wider">Contact & Account Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1">Email Address</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input type="email" name="email" value="<?= $field('email') ?>" required placeholder="juan@example.com" autocomplete="email"
                                       class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                            </div>
                        </div>
                        <!-- Messenger -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="fb_messenger_account">FB / Messenger Link</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <input type="text" id="fb_messenger_account" name="fb_messenger_account" value="<?= $field('fb_messenger_account') ?>" placeholder="https://m.me/username" autocomplete="url"
                                       class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="address">Full Address</label>
                        <div class="relative group">
                            <div class="absolute left-4 top-4 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <textarea id="address" name="address" required placeholder="House No., Street, Barangay, City, Province" rows="2" autocomplete="street-address"
                                      class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium resize-none"><?= $field('address') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: Guardian Information -->
                <div class="p-5 rounded-3xl bg-blue-50/40 dark:bg-blue-950/10 border border-blue-100/50 dark:border-blue-950/30">
                    <h3 class="text-xs font-black uppercase text-blue-600 dark:text-blue-400 tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Guardian Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Guardian Name -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="parents_name_guardian">Parent / Guardian Name</label>
                            <input type="text" id="parents_name_guardian" autocomplete="name" name="parents_name_guardian" value="<?= $field('parents_name_guardian') ?>" required placeholder="Maria Dela Cruz"
                                   class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                        </div>
                        <!-- Guardian Phone -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1" for="parents_phone_no">Guardian Contact #</label>
                            <input type="text" id="parents_phone_no" autocomplete="tel" name="parents_phone_no" value="<?= $field('parents_phone_no') ?>" required placeholder="09987654321"
                                   class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                        </div>
                    </div>
                </div>

                <!-- Section: Security -->
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4 uppercase tracking-wider">Security Credentials</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1">Password</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password" name="password" id="password-input" required minlength="8" placeholder="••••••••" autocomplete="new-password" aria-describedby="password-hint capslock-note"
                                       class="w-full pl-12 pr-12 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                                <p id="password-hint" class="mt-1.5 ml-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">At least 8 characters.</p>
                                <p id="capslock-note" role="status" class="hidden mt-1 ml-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">⚠ Caps Lock is on</p>
                                
                                <button type="button" id="password-toggle-btn" aria-label="Show password" aria-pressed="false" 
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none transition-colors p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80">
                                    <svg id="eye-open-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg id="eye-closed-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-2 ml-1">Confirm Password</label>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input type="password" name="confirm_password" id="confirm-password-input" required placeholder="••••••••" autocomplete="new-password" aria-describedby="match-note capslock-note-confirm"
                                       class="w-full pl-12 pr-12 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-950 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition duration-300 font-medium">
                                <p id="match-note" class="hidden mt-1.5 ml-1 text-[11px] font-bold text-rose-600 dark:text-rose-400">Passwords do not match yet.</p>
                                <p id="capslock-note-confirm" role="status" class="hidden mt-1 ml-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">⚠ Caps Lock is on</p>
                                
                                <button type="button" id="confirm-password-toggle-btn" aria-label="Show password" aria-pressed="false" 
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none transition-colors p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80">
                                    <svg id="confirm-eye-open-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg id="confirm-eye-closed-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="register" id="submit-btn" 
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-2xl hover:from-blue-700 hover:to-indigo-700 transition duration-300 shadow-lg shadow-blue-500/20 dark:shadow-blue-900/30 transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer mt-4">
                    <!-- Spinner Icon (hidden by default) -->
                    <svg id="spinner-icon" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="btn-text">Register Now</span>
                </button>
            </form>

            <!-- Bottom Navigation -->
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/60 text-center">
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">
                    Already have an account? <a href="login.php" class="text-blue-600 dark:text-blue-400 font-extrabold hover:underline transition">Login here</a>
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

        const confirmPasswordInput = document.getElementById('confirm-password-input');
        const confirmToggleBtn = document.getElementById('confirm-password-toggle-btn');
        const confirmEyeOpenIcon = document.getElementById('confirm-eye-open-icon');
        const confirmEyeClosedIcon = document.getElementById('confirm-eye-closed-icon');

        const registerForm = document.getElementById('register-form');
        const submitBtn = document.getElementById('submit-btn');
        const spinnerIcon = document.getElementById('spinner-icon');
        const btnText = document.getElementById('btn-text');

        // Password visibility toggler
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleBtn.setAttribute('aria-pressed', String(isPassword));
                toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                
                if (isPassword) {
                    eyeOpenIcon.classList.add('hidden');
                    eyeClosedIcon.classList.remove('hidden');
                } else {
                    eyeOpenIcon.classList.remove('hidden');
                    eyeClosedIcon.classList.add('hidden');
                }
            });
        }

        // Confirm Password visibility toggler
        if (confirmToggleBtn && confirmPasswordInput) {
            confirmToggleBtn.addEventListener('click', function () {
                const isPassword = confirmPasswordInput.getAttribute('type') === 'password';
                confirmPasswordInput.setAttribute('type', isPassword ? 'text' : 'password');
                confirmToggleBtn.setAttribute('aria-pressed', String(isPassword));
                confirmToggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                
                if (isPassword) {
                    confirmEyeOpenIcon.classList.add('hidden');
                    confirmEyeClosedIcon.classList.remove('hidden');
                } else {
                    confirmEyeOpenIcon.classList.remove('hidden');
                    confirmEyeClosedIcon.classList.add('hidden');
                }
            });
        }

        // Live password guidance
        const hintEl = document.getElementById('password-hint');
        const matchNote = document.getElementById('match-note');
        const HINT_DEFAULT = 'At least 8 characters.';

        function updatePasswordGuidance() {
            const pw = passwordInput.value;
            const confirm = confirmPasswordInput.value;

            const tooShort = pw.length > 0 && pw.length < 8;
            hintEl.textContent = tooShort ? (8 - pw.length) + ' more character' + (8 - pw.length === 1 ? '' : 's') + ' needed.' : HINT_DEFAULT;
            hintEl.classList.toggle('text-rose-600', tooShort);
            hintEl.classList.toggle('dark:text-rose-400', tooShort);
            hintEl.classList.toggle('text-slate-500', !tooShort);
            hintEl.classList.toggle('dark:text-slate-400', !tooShort);

            const mismatch = confirm.length > 0 && pw !== confirm;
            matchNote.classList.toggle('hidden', !mismatch);
            submitBtn.disabled = mismatch;
            submitBtn.classList.toggle('opacity-60', mismatch);
            submitBtn.classList.toggle('cursor-not-allowed', mismatch);
        }

        passwordInput.addEventListener('input', updatePasswordGuidance);
        confirmPasswordInput.addEventListener('input', updatePasswordGuidance);

        // Caps Lock hints
        function attachCapsLock(input, note) {
            if (!input || !note) return;
            const handler = function (e) {
                if (typeof e.getModifierState === 'function') {
                    note.classList.toggle('hidden', !e.getModifierState('CapsLock'));
                }
            };
            input.addEventListener('keydown', handler);
            input.addEventListener('keyup', handler);
            input.addEventListener('blur', function () { note.classList.add('hidden'); });
        }
        attachCapsLock(passwordInput, document.getElementById('capslock-note'));
        attachCapsLock(confirmPasswordInput, document.getElementById('capslock-note-confirm'));

        // Form submit loading feedback
        if (registerForm && submitBtn) {
            registerForm.addEventListener('submit', function () {
                // Disable button and show spinner
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                spinnerIcon.classList.remove('hidden');
                btnText.textContent = 'Creating account...';
            });
        }
    </script>
</body>
</html>