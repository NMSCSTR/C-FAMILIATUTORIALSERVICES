<?php
session_start();
include 'db.php';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Login | C-Familia</title>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <a href="index.php inline-block">
                <img src="cuevaslogo.jpg" alt="Logo" class="w-20 h-20 mx-auto rounded-2xl shadow-lg mb-4 object-contain bg-white p-2">
            </a>
            <h2 class="text-3xl font-extrabold text-slate-800">Welcome Back</h2>
            <p class="text-slate-500 mt-2">Login to your C-Familia account</p>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-slate-100">
            <?php if(isset($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100 font-medium">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Email Address</label>
                    <input type="email" name="email" required placeholder="name@company.com"
                           class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                           class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                </div>
                <button type="submit" name="login" 
                        class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/20 transform active:scale-[0.98]">
                    Sign In
                </button>
            </form>
            
            <div class="mt-8 text-center">
                <p class="text-slate-500 text-sm">
                    Don't have an account? <a href="register.php" class="text-blue-600 font-bold hover:underline">Join the family</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>