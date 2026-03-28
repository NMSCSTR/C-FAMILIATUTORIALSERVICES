<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Handle Upload
if (isset($_POST['add_passer'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $photo_name = "default_user.jpg";

    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/passers/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $photo_name);
    }

    mysqli_query($conn, "INSERT INTO passers (name, program, batch, photo) VALUES ('$name', '$program', '$batch', '$photo_name')");
    header("Location: admin_passers.php?success=1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Manage Passers | Admin</title>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex min-h-screen">
    <?php include 'sidebar_template.php'; // I recommend putting your sidebar in a separate file to save time! ?>

    <main class="flex-1 p-10">
        <div class="max-w-5xl mx-auto">
            <header class="mb-10">
                <h2 class="text-3xl font-extrabold text-slate-900">Passers Hall of Fame</h2>
                <p class="text-slate-500">Add successful candidates to be featured on the landing page.</p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm h-fit">
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="text" name="name" placeholder="Full Name" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500/10">
                        <input type="text" name="program" placeholder="Program (e.g. ICT)" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none">
                        <input type="text" name="batch" placeholder="Batch Year" required class="w-full px-4 py-3 bg-slate-50 border rounded-xl outline-none">
                        <input type="file" name="photo" class="text-xs">
                        <button type="submit" name="add_passer" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold">Add to List</button>
                    </form>
                </div>

                <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
                    while($p = mysqli_fetch_assoc($res)):
                    ?>
                    <div class="bg-white p-4 rounded-2xl border text-center group">
                        <img src="uploads/passers/<?= $p['photo'] ?>" class="w-20 h-20 rounded-full mx-auto object-cover mb-3 border-4 border-slate-50 group-hover:border-blue-100 transition-all">
                        <h5 class="font-bold text-slate-900 text-sm leading-tight"><?= $p['name'] ?></h5>
                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1"><?= $p['batch'] ?></p>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>