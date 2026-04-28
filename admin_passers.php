<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- Logic to Add Passer ---
if (isset($_POST['add_passer'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $photo_name = mysqli_real_escape_string($conn, $_POST['existing_photo']);

    // If a new photo is uploaded, it overrides the auto-fetched one
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/passers/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $photo_name);
    }

    $sql = "INSERT INTO passers (name, program, batch, rating, photo) VALUES ('$name', '$program', '$batch', '$rating', '$photo_name')";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_passers.php?success=added");
        exit();
    }
}

// --- Logic to Delete Passer ---
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $result = mysqli_query($conn, "SELECT photo FROM passers WHERE id = '$id'");
    $data = mysqli_fetch_assoc($result);
    // Only delete if it's not a default image and exists in the passers folder
    if ($data && $data['photo'] != 'default_user.jpg' && file_exists("uploads/passers/" . $data['photo'])) {
        @unlink("uploads/passers/" . $data['photo']);
    }
    mysqli_query($conn, "DELETE FROM passers WHERE id = '$id'");
    header("Location: admin_passers.php?success=deleted");
    exit();
}

// Fetch all students for the dynamic dropdown
$students_query = mysqli_query($conn, "SELECT id, firstname, lastname, profile_pic FROM users WHERE role = 'student' ORDER BY lastname ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Manage Passers | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php';?>

        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-8 lg:p-12">
            <div class="max-w-6xl mx-auto">
                
                <header class="mb-10 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Hall of Fame Manager</h2>
                        <p class="text-slate-500 mt-1">Select a student to automatically generate their passer profile.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-white border border-slate-200 rounded-2xl shadow-sm ml-4">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-4">
                        <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-200 sticky top-10">
                            
                            <div class="mb-8 text-center">
                                <div class="relative inline-block">
                                    <img id="previewPhoto" src="uploads/passers/default_user.jpg" class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-50 shadow-md transition-all duration-500">
                                    <div class="absolute -bottom-2 -right-2 bg-blue-600 text-white p-1.5 rounded-xl shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                                <input type="hidden" name="name" id="studentName">
                                <input type="hidden" name="existing_photo" id="existingPhoto" value="default_user.jpg">

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Select Student</label>
                                    <select id="studentSelector" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold appearance-none">
                                        <option value="" data-photo="default_user.jpg">-- Choose a Student --</option>
                                        <?php while($s = mysqli_fetch_assoc($students_query)): ?>
                                            <option value="<?= $s['firstname'] . ' ' . $s['lastname'] ?>" data-photo="<?= !empty($s['profile_pic']) ? $s['profile_pic'] : 'default_user.jpg' ?>">
                                                <?= $s['lastname'] . ', ' . $s['firstname'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Program</label>
                                        <input type="text" name="program" placeholder="BSIT" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Rating (%)</label>
                                        <input type="number" step="0.01" name="rating" placeholder="95.5" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Batch Year</label>
                                    <input type="text" name="batch" value="<?= date('Y') ?>" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Custom Photo (Optional)</label>
                                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                                        <input type="file" name="photo" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white cursor-pointer w-full">
                                    </div>
                                </div>

                                <button type="submit" name="add_passer" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-600/20 hover:bg-blue-700 transition-all mt-2">Publish to Hall of Fame</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
                            while($p = mysqli_fetch_assoc($res)):
                            ?>
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 text-center group hover:border-blue-200 hover:shadow-lg transition-all relative">
                                <button onclick="confirmDelete(<?= $p['id'] ?>)" class="absolute top-4 right-4 p-2 text-slate-300 hover:text-red-500 transition-colors md:opacity-0 md:group-hover:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                <img src="uploads/profiles/<?= $p['photo'] ?>" class="w-20 h-20 rounded-[2rem] mx-auto object-cover border-4 border-slate-50 mb-3 shadow-sm">
                                <h5 class="font-bold text-slate-900 leading-tight"><?= $p['name'] ?></h5>
                                <div class="flex items-center justify-center gap-2 mt-2">
                                    <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg border border-blue-100"><?= $p['rating'] ?>%</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase"><?= $p['program'] ?></span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dynamic Selector Logic
        const studentSelector = document.getElementById('studentSelector');
        const previewPhoto = document.getElementById('previewPhoto');
        const studentNameInput = document.getElementById('studentName');
        const existingPhotoInput = document.getElementById('existingPhoto');

        studentSelector.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const photo = selectedOption.getAttribute('data-photo');
            const name = this.value;

            // Update Preview Image
            // We check if it's the default or a user profile pic
            if (photo === 'default_user.jpg') {
                previewPhoto.src = 'uploads/passers/default_user.jpg';
            } else {
                previewPhoto.src = 'uploads/profiles/' + photo;
            }

            // Update Hidden Inputs
            studentNameInput.value = name;
            existingPhotoInput.value = photo;
        });

        // Sidebar logic
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar(state) {
            if(state) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.add('opacity-100'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        openBtn?.addEventListener('click', () => toggleSidebar(true));
        closeBtn?.addEventListener('click', () => toggleSidebar(false));
        overlay?.addEventListener('click', () => toggleSidebar(false));

        function confirmDelete(id) {
            Swal.fire({
                title: 'Remove Passer?',
                text: "This record will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = `admin_passers.php?delete=${id}`; }
            })
        }

        <?php if(isset($_GET['success'])): ?>
            Swal.fire({ icon: 'success', title: 'Success!', timer: 2000, showConfirmButton: false });
        <?php endif; ?>
    </script>
</body>
</html>