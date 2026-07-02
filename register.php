<?php
include 'db.php';
$message = ""; $error = "";

if (isset($_POST['register'])) {
    // Sanitize multi-part name inputs
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Sanitize newly added inputs
    $birthday = mysqli_real_escape_string($conn, $_POST['birthday']);
    $cellphone_no = mysqli_real_escape_string($conn, $_POST['cellphone_no']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $parents_name_guardian = mysqli_real_escape_string($conn, $_POST['parents_name_guardian']);
    $parents_phone_no = mysqli_real_escape_string($conn, $_POST['parents_phone_no']);
    $fb_messenger_account = mysqli_real_escape_string($conn, $_POST['fb_messenger_account']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email is already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';
            
            // Updated SQL query to include the new columns
            $sql = "INSERT INTO users (firstname, middlename, lastname, email, password, role, birthday, cellphone_no, address, parents_name_guardian, parents_phone_no, fb_messenger_account) 
                    VALUES ('$firstname', '$middlename', '$lastname', '$email', '$hashed_password', '$role', '$birthday', '$cellphone_no', '$address', '$parents_name_guardian', '$parents_phone_no', '$fb_messenger_account')";
            
            if (mysqli_query($conn, $sql)) {
                $new_user_id = mysqli_insert_id($conn);
                log_activity($conn, 'register', "New student account: $firstname $lastname ($email)", [
                    'user_id' => $new_user_id,
                    'user_role' => 'student',
                    'entity_type' => 'user',
                    'entity_id' => $new_user_id,
                ]);
                $message = "Registration successful! You can now <a href='login.php' class='underline font-bold'>Login</a>.";
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Join C-Familia | Registration</title>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen py-10 px-4">
    <div class="max-w-xl w-full">
        <div class="text-center mb-8">
            <img src="cuevaslogo.jpg" alt="Logo" class="w-16 h-16 mx-auto rounded-xl shadow-md mb-4 object-contain bg-white p-2">
            <h2 class="text-3xl font-[800] text-slate-800 tracking-tight">Create Account</h2>
            <p class="text-slate-500 mt-1">Join the family and start your journey.</p>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-slate-100">
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">First Name</label>
                        <input type="text" name="firstname" required placeholder="Juan"
                               class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Middle Name</label>
                        <input type="text" name="middlename" placeholder="Dela"
                               class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Last Name</label>
                        <input type="text" name="lastname" required placeholder="Cruz"
                               class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Birthday</label>
                        <input type="date" name="birthday" required
                               class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Cellphone #</label>
                        <input type="text" name="cellphone_no" required placeholder="09123456789"
                               class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Email Address</label>
                    <input type="email" name="email" required placeholder="juan@example.com"
                           class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">FB / Messenger Link or Username</label>
                    <input type="text" name="fb_messenger_account" placeholder="https://m.me/username"
                           class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Full Address</label>
                    <textarea name="address" required placeholder="House No., Street, Barangay, City, Province" rows="2"
                              class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium resize-none"></textarea>
                </div>

                <div class="pt-2 border-t border-dashed border-slate-200">
                    <p class="text-xs font-black uppercase text-blue-500 tracking-wider mb-3">Guardian Details</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Parent / Guardian Name</label>
                            <input type="text" name="parents_name_guardian" required placeholder="Maria Dela Cruz"
                                   class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Parent Contact #</label>
                            <input type="text" name="parents_phone_no" required placeholder="09987654321"
                                   class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Password</label>
                        <input type="password" name="password" required 
                               class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-2 ml-1">Confirm</label>
                        <input type="password" name="confirm_password" required 
                               class="w-full px-5 py-4 rounded-2xl border border-slate-100 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-blue-500/5 outline-none transition font-medium">
                    </div>
                </div>

                <button type="submit" name="register" 
                        class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/20 transform active:scale-[0.98] mt-4">
                    Register Now
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-50 text-center">
                <p class="text-slate-500 text-sm font-medium">
                    Already have an account? <a href="login.php" class="text-blue-600 font-bold hover:underline">Login here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>