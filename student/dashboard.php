<?php
include 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

// Check Enrollment
$enroll_query = mysqli_query($conn, "SELECT * FROM enrollments WHERE user_id = $user_id");
$enrollment = mysqli_fetch_assoc($enroll_query);

// Handle Enrollment Click
if(isset($_POST['enroll'])){
    mysqli_query($conn, "INSERT INTO enrollments (user_id, program_type, status) VALUES ($user_id, 'Criminology Review', 'pending')");
    header("Refresh:0");
}

// Handle Payment Submission
if(isset($_POST['pay'])){
    $amount = $_POST['amount'];
    $ref = $_POST['ref'];
    mysqli_query($conn, "INSERT INTO payments (user_id, amount, reference_number, status) VALUES ($user_id, $amount, '$ref', 'pending')");
    header("Refresh:0");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Student Dashboard</title>
</head>
<body class="bg-gray-50">
    <nav class="p-4 bg-blue-600 text-white flex justify-between">
        <span class="font-bold">C-Familia Student Portal</span>
        <a href="logout.php">Logout</a>
    </nav>

    <main class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow rounded-lg">
        <h2 class="text-2xl font-bold mb-4">Welcome, <?php echo $_SESSION['name']; ?>!</h2>
        
        <div class="mb-8 p-4 border rounded">
            <h3 class="text-lg font-semibold mb-2">Enrollment Status</h3>
            <?php if(!$enrollment): ?>
                <form method="POST"><button name="enroll" class="bg-green-600 text-white px-4 py-2 rounded">Enroll Now</button></form>
            <?php else: ?>
                <p>Program: <span class="font-bold"><?php echo $enrollment['program_type']; ?></span></p>
                <p>Status: <span class="px-2 py-1 bg-yellow-200 rounded"><?php echo $enrollment['status']; ?></span></p>
            <?php endif; ?>
        </div>

        <?php if($enrollment): ?>
        <div class="mb-8 p-4 border rounded">
            <h3 class="text-lg font-semibold mb-2">Submit Payment (GCash)</h3>
            <form method="POST" class="flex gap-4">
                <input type="number" name="amount" placeholder="Amount" class="border p-2 rounded w-full" required>
                <input type="text" name="ref" placeholder="Reference Number" class="border p-2 rounded w-full" required>
                <button name="pay" class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="p-4 border rounded">
            <h3 class="text-lg font-semibold mb-2">Payment History</h3>
            <table class="w-full text-left border-collapse">
                <thead><tr class="bg-gray-100"><th>Date</th><th>Amount</th><th>Ref #</th><th>Status</th></tr></thead>
                <tbody>
                    <?php
                    $payments = mysqli_query($conn, "SELECT * FROM payments WHERE user_id = $user_id");
                    while($row = mysqli_fetch_assoc($payments)): ?>
                    <tr class="border-b">
                        <td><?php echo $row['created_at']; ?></td>
                        <td>₱<?php echo $row['amount']; ?></td>
                        <td><?php echo $row['reference_number']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>