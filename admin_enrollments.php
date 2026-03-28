<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

// Logic to Approve Enrollment
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    mysqli_query($conn, "UPDATE enrollments SET status = 'enrolled' WHERE id = '$id'");
    header("Location: admin_enrollments.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Manage Enrollments | Admin</title>
</head>
<body class="bg-slate-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold mb-8">Pending Enrollments</h2>
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-900 text-white">
                    <tr>
                        <th class="p-4">Student Name</th>
                        <th class="p-4">Program</th>
                        <th class="p-4">Batch</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    // JOIN users table to get the student's name
                    $sql = "SELECT enrollments.*, users.name FROM enrollments 
                            JOIN users ON enrollments.user_id = users.id 
                            WHERE status = 'pending'";
                    $result = mysqli_query($conn, $sql);
                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr class="hover:bg-slate-50">
                        <td class="p-4 font-bold text-slate-800"><?= $row['name'] ?></td>
                        <td class="p-4"><?= $row['program_type'] ?></td>
                        <td class="p-4 text-slate-500 text-sm"><?= $row['batch'] ?></td>
                        <td class="p-4"><span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold uppercase"><?= $row['status'] ?></span></td>
                        <td class="p-4 text-right">
                            <a href="?approve=<?= $row['id'] ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-green-700">Approve</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>