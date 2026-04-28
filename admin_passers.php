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
    $photo_name = "default_user.jpg";

    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/passers/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $photo_name);
    }

    mysqli_query($conn, "INSERT INTO passers (name, program, batch, photo) VALUES ('$name', '$program', '$batch', '$photo_name')");
    header("Location: admin_passers.php?success=added");
    exit();
}

// --- Logic to Delete Passer ---
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Optional: Delete physical file from folder
    $result = mysqli_query($conn, "SELECT photo FROM passers WHERE id = '$id'");
    $data = mysqli_fetch_assoc($result);
    if ($data && $data['photo'] != 'default_user.jpg') {
        @unlink("uploads/passers/" . $data['photo']);
    }

    mysqli_query($conn, "DELETE FROM passers WHERE id = '$id'");
    header("Location: admin_passers.php?success=deleted");
    exit();
}
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
                        <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Passers Hall of Fame</h2>
                        <p class="text-slate-500 mt-1">Add successful candidates to be featured on the landing page.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-white border border-slate-200 rounded-2xl shadow-sm ml-4">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-4">
                        <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-200 sticky top-10">
                            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                Register New Passer
                            </h3>
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Full Name</label>
                                    <input type="text" name="name" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Program</label>
                                    <input type="text" name="program" placeholder="e.g. BS Criminology" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Batch Year</label>
                                    <input type="text" name="batch" placeholder="e.g. 2025" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-2 block px-1">Student Photo</label>
                                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl">
                                        <input type="file" name="photo" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white cursor-pointer w-full">
                                    </div>
                                </div>
                                <button type="submit" name="add_passer" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-600/20 hover:bg-blue-700 transition-all mt-2">Add to Hall of Fame</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-8">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-6">Hall of Fame Gallery</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
                            if(mysqli_num_rows($res) > 0):
                                while($p = mysqli_fetch_assoc($res)):
                            ?>
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 text-center group hover:border-blue-200 hover:shadow-lg transition-all relative">
                                <button onclick="confirmDelete(<?= $p['id'] ?>)" class="absolute top-4 right-4 p-2 text-slate-300 hover:text-red-500 transition-colors md:opacity-0 md:group-hover:opacity-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>

                                <div class="relative inline-block mb-4">
                                    <div class="absolute inset-0 bg-blue-600 rounded-[2rem] blur-xl opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                    <img src="uploads/passers/<?= $p['photo'] ?>" class="relative w-24 h-24 rounded-[2rem] mx-auto object-cover border-4 border-slate-50 shadow-sm transition-transform duration-500 group-hover:scale-105">
                                </div>
                                <h5 class="font-bold text-slate-900 text-md leading-tight"><?= $p['name'] ?></h5>
                                <p class="text-[10px] text-blue-600 font-black uppercase mt-1.5 tracking-widest"><?= $p['program'] ?></p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 tracking-tighter bg-slate-50 inline-block px-3 py-1 rounded-full">Batch <?= $p['batch'] ?></p>
                            </div>
                            <?php endwhile; else: ?>
                            <div class="col-span-full py-20 text-center bg-white border-2 border-dashed border-slate-100 rounded-[3rem]">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">No success stories yet.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Menu Toggle
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

        // Delete Confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This passer will be removed from the Hall of Fame.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, delete it!',
                customClass: {
                    confirmButton: 'rounded-xl px-6 py-3',
                    cancelButton: 'rounded-xl px-6 py-3'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `admin_passers.php?delete=${id}`;
                }
            })
        }

        // Success Alerts
        <?php if(isset($_GET['success'])): ?>
            const type = "<?= $_GET['success'] ?>";
            if (type === 'added') {
                Swal.fire({ icon: 'success', title: 'Passer Registered!', text: 'Successfully added to Hall of Fame.', timer: 2500, showConfirmButton: false });
            } else if (type === 'deleted') {
                Swal.fire({ icon: 'success', title: 'Removed', text: 'Passer record has been deleted.', timer: 2000, showConfirmButton: false });
            }
        <?php endif; ?>
    </script>
</body>
</html>