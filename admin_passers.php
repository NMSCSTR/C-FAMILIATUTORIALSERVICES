<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- Logic to Add Passer ---
if (isset($_POST['add_passer'])) {
    // If a manual input name is supplied, it overrides the auto-selected user link
    $name = !empty($_POST['custom_name']) ? $_POST['custom_name'] : $_POST['name'];
    $name = mysqli_real_escape_string($conn, $name);
    
    $program = mysqli_real_escape_string($conn, $_POST['program']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $photo_name = mysqli_real_escape_string($conn, $_POST['existing_photo']);

    // Handle fresh manual file uploads
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/passers/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $photo_name);
    }

    $sql = "INSERT INTO passers (name, program, batch, rating, exam_date, photo) VALUES ('$name', '$program', '$batch', '$rating', '$exam_date', '$photo_name')";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_passers.php?posted=1");
        exit();
    }
}

// --- Logic to Delete Passer ---
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $result = mysqli_query($conn, "SELECT photo FROM passers WHERE id = '$id'");
    $data = mysqli_fetch_assoc($result);
    
    if ($data && $data['photo'] != 'default_user.jpg' && file_exists("uploads/passers/" . $data['photo'])) {
        @unlink("uploads/passers/" . $data['photo']);
    }
    mysqli_query($conn, "DELETE FROM passers WHERE id = '$id'");
    header("Location: admin_passers.php?deleted=1");
    exit();
}

// Fetch all regular student users for linking drop-downs
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
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
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
                
                <header class="mb-8 md:mb-12 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Hall of Fame Registry</h2>
                        <p class="text-slate-500 mt-1">Select an active system user or manually record details for legacy system alumni.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-white border border-slate-200 rounded-2xl shadow-sm ml-4 shrink-0">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                        </svg>
                    </button>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <div class="lg:col-span-5">
                        <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-200 sticky top-12">
                            
                            <div class="mb-6 text-center">
                                <div class="relative inline-block">
                                    <img id="previewPhoto" src="uploads/passers/default_user.jpg" class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-50 shadow-md transition-all duration-300">
                                    <div class="absolute -bottom-1 -right-1 bg-blue-600 text-white p-1.5 rounded-xl shadow-lg">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                                <input type="hidden" name="name" id="studentName">
                                <input type="hidden" name="existing_photo" id="existingPhoto" value="default_user.jpg">

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Option A: Link System Account</label>
                                    <select id="studentSelector" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold appearance-none">
                                        <option value="" data-photo="default_user.jpg">-- Choose a Student --</option>
                                        <?php while($s = mysqli_fetch_assoc($students_query)): ?>
                                            <option value="<?= $s['firstname'] . ' ' . $s['lastname'] ?>" data-photo="<?= !empty($s['profile_pic']) ? $s['profile_pic'] : 'default_user.jpg' ?>">
                                                <?= $s['lastname'] . ', ' . $s['firstname'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="relative flex py-1 items-center">
                                    <div class="flex-grow border-t border-slate-100"></div>
                                    <span class="flex-shrink mx-4 text-[9px] font-black uppercase text-slate-300 tracking-widest">OR</span>
                                    <div class="flex-grow border-t border-slate-100"></div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Option B: Legacy Student Name</label>
                                    <input type="text" name="custom_name" id="customName" placeholder="Enter old student full name manually" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Program</label>
                                        <input type="text" name="program" placeholder="BSIT" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Rating (%)</label>
                                        <input type="number" step="0.01" name="rating" placeholder="95.5" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Batch Year</label>
                                        <input type="text" name="batch" value="<?= date('Y') ?>" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Examination Date</label>
                                        <input type="date" name="exam_date" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold text-slate-600">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Photo Upload</label>
                                    <div class="bg-slate-50 border border-slate-100 p-3 rounded-2xl">
                                        <input type="file" name="photo" id="filePhotoInput" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-blue-600 transition-colors cursor-pointer w-full">
                                    </div>
                                </div>

                                <button type="submit" name="add_passer" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-[0.15em] shadow-xl hover:bg-blue-600 transition-all mt-2">Publish Profile</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
                            while($p = mysqli_fetch_assoc($res)):
                                if (file_exists("uploads/passers/" . $p['photo'])) {
                                    $computedPath = "uploads/passers/" . $p['photo'];
                                } elseif (!empty($p['photo']) && file_exists("uploads/profiles/" . $p['photo'])) {
                                    $computedPath = "uploads/profiles/" . $p['photo'];
                                } else {
                                    $computedPath = "uploads/passers/default_user.jpg";
                                }
                            ?>
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 text-center group hover:border-blue-200 hover:shadow-md transition-all relative flex flex-col justify-between items-center">
                                <button onclick="confirmDelete(<?= $p['id'] ?>)" class="absolute top-5 right-5 p-2 text-slate-300 hover:text-red-500 transition-colors md:opacity-0 md:group-hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                
                                <div class="w-full">
                                    <img src="<?= $computedPath ?>" class="w-20 h-20 rounded-[2rem] mx-auto object-cover border-4 border-slate-50 mb-3 shadow-sm">
                                    <h5 class="font-bold text-slate-900 leading-snug px-2"><?= htmlspecialchars($p['name']) ?></h5>
                                    <div class="flex items-center justify-center gap-2 mt-2 flex-wrap">
                                        <span class="text-[9px] font-black bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg border border-blue-100"><?= $p['rating'] ?>%</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight"><?= htmlspecialchars($p['program']) ?></span>
                                    </div>
                                </div>

                                <?php if(!empty($p['exam_date']) && $p['exam_date'] !== '0000-00-00'): ?>
                                    <div class="w-full mt-4 pt-3 border-t border-slate-50 text-[10px] text-slate-400 font-medium">
                                        Exam: <?= date('M Y', strtotime($p['exam_date'])) ?> • Batch <?= htmlspecialchars($p['batch']) ?>
                                    </div>
                                <?php deduction: else: ?>
                                    <div class="w-full mt-4 pt-3 border-t border-slate-50 text-[10px] text-slate-400 font-medium">
                                        Batch Year: <?= htmlspecialchars($p['batch']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        // Dropdown tracking interactions
        const studentSelector = document.getElementById('studentSelector');
        const customNameInput = document.getElementById('customName');
        const previewPhoto = document.getElementById('previewPhoto');
        const studentNameInput = document.getElementById('studentName');
        const existingPhotoInput = document.getElementById('existingPhoto');
        const filePhotoInput = document.getElementById('filePhotoInput');

        studentSelector.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const photo = selectedOption.getAttribute('data-photo');
            const name = this.value;

            if(name !== "") {
                customNameInput.value = "";
                customNameInput.placeholder = "Linked to system account...";
                customNameInput.classList.add('opacity-50');
                
                studentNameInput.value = name;
                existingPhotoInput.value = photo;
                previewPhoto.src = (photo === 'default_user.jpg') ? 'uploads/passers/default_user.jpg' : 'uploads/profiles/' + photo;
            } else {
                resetSelectionState();
            }
        });

        customNameInput.addEventListener('input', function() {
            if(this.value.trim() !== "") {
                studentSelector.selectedIndex = 0;
                studentNameInput.value = "";
                existingPhotoInput.value = "default_user.jpg";
                this.classList.remove('opacity-50');
                if(!filePhotoInput.files.length) {
                    previewPhoto.src = 'uploads/passers/default_user.jpg';
                }
            } else {
                customNameInput.placeholder = "Enter old student full name manually";
            }
        });

        function resetSelectionState() {
            customNameInput.placeholder = "Enter old student full name manually";
            customNameInput.classList.remove('opacity-50');
            studentNameInput.value = "";
            existingPhotoInput.value = "default_user.jpg";
            previewPhoto.src = 'uploads/passers/default_user.jpg';
        }

        filePhotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) { previewPhoto.src = e.target.result; }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Responsive Global Sidebar Toggle Mechanician (Unified with admin_announcements style layout target)
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu'); // Bound inside aside.php
        const sidebar = document.getElementById('mobileSidebar'); // ID expected from aside.php
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar(state) {
            if(state) {
                sidebar?.classList.remove('-translate-x-full');
                overlay?.classList.remove('hidden');
                setTimeout(() => overlay?.classList.add('opacity-100'), 10);
            } else {
                sidebar?.classList.add('-translate-x-full');
                overlay?.classList.remove('opacity-100');
                setTimeout(() => overlay?.classList.add('hidden'), 300);
            }
        }

        openBtn?.addEventListener('click', () => toggleSidebar(true));
        closeBtn?.addEventListener('click', () => toggleSidebar(false));
        overlay?.addEventListener('click', () => toggleSidebar(false));

        // Setup SweetAlert2 Notifications
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2800,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('posted') === '1') {
            Toast.fire({ icon: 'success', title: 'Passer registered successfully' });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (urlParams.get('deleted') === '1') {
            Toast.fire({ icon: 'info', title: 'Record removed successfully' });
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Remove Record?',
                text: "This profile entry will be dropped from the public Hall of Fame page.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#f1f5f9',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                customClass: {
                    title: 'font-extrabold text-slate-900',
                    confirmButton: 'rounded-xl font-bold px-6 py-3 text-sm',
                    cancelButton: 'rounded-xl font-bold px-6 py-3 text-sm text-slate-600'
                }
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = `?delete=${id}`; }
            });
        }
    </script>
</body>
</html>