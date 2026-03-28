<?php
include 'db.php';

$message = "";
$error = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // 2. Check if email already exists
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email is already registered!";
        } else {
            // 3. Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student'; // Default role for new signups

            $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";
            
            if (mysqli_query($conn, $sql)) {
                $message = "Registration successful! You can now <a href='login.php' class='underline font-bold'>Login</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Join C-Familia | Registration</title>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen py-10 px-4">
    <div class="max-w-md w-full bg-white p-8 rounded-3xl shadow-xl border border-slate-100">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-800">Create Account</h2>
            <p class="text-slate-500 mt-2">Join our family and start your journey.</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100 font-medium">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if($message): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-6 border border-green-100 font-medium">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-600 mb-1">Full Name</label>
                <input type="text" name="name" required placeholder="Juan Dela Cruz"
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50/50">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-600 mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="juan@example.com"
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50/50">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Password</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50/50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Confirm</label>
                    <input type="password" name="confirm_password" required 
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50/50">
                </div>
            </div>

            <button type="submit" name="register" 
                    class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200 transform hover:-translate-y-0.5 active:scale-95">
                Register Now
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p class="text-slate-500 text-sm">
                Already have an account? <a href="login.php" class="text-blue-600 font-bold hover:underline">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>