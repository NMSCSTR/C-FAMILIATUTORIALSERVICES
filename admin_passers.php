<?php
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/uploads.php';
secure_session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';
require_once __DIR__ . '/lib/thumbs.php';

$current_page = basename($_SERVER['PHP_SELF']);

// --- Logic to Add Passer ---
if (isset($_POST['add_passer'])) {
    csrf_verify();

    $name = trim(!empty($_POST['custom_name']) ? $_POST['custom_name'] : ($_POST['name'] ?? ''));
    $program = trim($_POST['program'] ?? '');
    $batch = trim($_POST['batch'] ?? '');
    $rating = filter_var($_POST['rating'] ?? '', FILTER_VALIDATE_FLOAT);
    $exam_date = trim($_POST['exam_date'] ?? '');
    $photo_name = trim($_POST['existing_photo'] ?? '');

    if ($name === '' || mb_strlen($name) > 255 || $program === '' || $batch === '' || $rating === false || $rating < 0 || $rating > 100) {
        header("Location: admin_passers.php?error=invalid");
        exit();
    }

    if (!empty($_FILES['photo']['name'])) {
        [$ok, $photo_name, $upload_error] = store_uploaded_file(
            $_FILES['photo'],
            'uploads/passers/',
            ['jpg', 'jpeg', 'png', 'gif', 'webp']
        );

        if (!$ok) {
            header("Location: admin_passers.php?error=upload");
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO passers (name, program, batch, rating, exam_date, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdss", $name, $program, $batch, $rating, $exam_date, $photo_name);

    if ($stmt->execute()) {
        log_activity($conn, 'passer.create', "Added passer: $name ($program)", [
            'entity_type' => 'passer',
            'entity_id' => $stmt->insert_id,
        ]);
        header("Location: admin_passers.php?posted=1");
        exit();
    }

    error_log('Passer insert failed: ' . $stmt->error);
    header("Location: admin_passers.php?error=failed");
    exit();
}

// --- Logic to Delete Passer ---
if (isset($_POST['delete_id'])) {
    csrf_verify();

    $id = intval($_POST['delete_id']);
    $result = mysqli_query($conn, "SELECT photo, name FROM passers WHERE id = '" . intval($id) . "' LIMIT 1");
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        if ($data['photo'] != 'default_user.jpg' && is_file("uploads/passers/" . $data['photo'])) {
            @unlink("uploads/passers/" . $data['photo']);
        }

        $stmt = $conn->prepare("DELETE FROM passers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        log_activity($conn, 'passer.delete', null, [
            'entity_type' => 'passer',
            'entity_id' => $id,
            'entity_label' => $data['name'] ?? 'Unknown passer',
        ]);
    }

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
    <link rel="stylesheet" href="assets/app.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Manage Passers | C-Familia Admin</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.01em; background-color: #020617; } /* slate-950 */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; } /* slate-800 */
    </style>
</head>
<body class="bg-slate-950 text-white antialiased">

    <div class="flex min-h-screen relative">
        <?php include 'aside.php';?>
        
        <div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

        <main class="flex-1 p-4 md:p-8 lg:p-12">
            <div class="max-w-12xl mx-auto">
                
                <header class="mb-8 md:mb-12 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-[800] text-white tracking-tight">Hall of Fame Registry</h2>
                        <p class="text-slate-400 mt-1">Select an active system user or manually record details for legacy system alumni.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-slate-900/60 border border-slate-800 rounded-2xl shadow-sm ml-4 shrink-0 backdrop-blur-xl">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                        </svg>
                    </button>
                </header>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <div class="lg:col-span-5">
                        <div class="bg-slate-900/60 p-6 md:p-8 rounded-[2.5rem] border border-slate-800 sticky top-12 backdrop-blur-xl">
                            
                            <div class="mb-6 text-center">
                                <div class="relative inline-block">
                                    <img id="previewPhoto" src="assets/img/avatar-placeholder.svg" alt="Passer photo preview" class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-800 shadow-md transition-all duration-300">
                                    <div class="absolute -bottom-1 -right-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-1.5 rounded-xl shadow-lg">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>

                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="name" id="studentName">
                                <input type="hidden" name="existing_photo" id="existingPhoto" value="default_user.jpg">

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Option A: Link System Account</label>
                                    <select id="studentSelector" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold appearance-none text-white">
                                        <option value="" data-photo="default_user.jpg" class="bg-slate-900 text-white">-- Choose a Student --</option>
                                        <?php while($s = mysqli_fetch_assoc($students_query)): ?>
                                            <option value="<?= htmlspecialchars($s['firstname'] . ' ' . $s['lastname'], ENT_QUOTES, 'UTF-8') ?>" data-photo="<?= htmlspecialchars(!empty($s['profile_pic']) ? $s['profile_pic'] : 'default_user.jpg', ENT_QUOTES, 'UTF-8') ?>" class="bg-slate-900 text-white">
                                                <?= htmlspecialchars($s['lastname'] . ', ' . $s['firstname'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="relative flex py-1 items-center">
                                    <div class="flex-grow border-t border-slate-800"></div>
                                    <span class="flex-shrink mx-4 text-[9px] font-black uppercase text-slate-600 tracking-widest">OR</span>
                                    <div class="flex-grow border-t border-slate-800"></div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Option B: Legacy Student Name</label>
                                    <input type="text" name="custom_name" id="customName" placeholder="Enter old student full name manually" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold text-white placeholder:text-slate-600">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Program</label>
                                        <input type="text" name="program" placeholder="BSIT" required class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold text-white placeholder:text-slate-600">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Rating (%)</label>
                                        <input type="number" step="0.01" name="rating" placeholder="95.5" required class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold text-white placeholder:text-slate-600">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Batch Year</label>
                                        <input type="text" name="batch" value="<?= date('Y') ?>" required class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold text-white">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Examination Date</label>
                                        <input type="date" name="exam_date" required class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold text-slate-400">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Photo Upload</label>
                                    <div class="bg-slate-950 border border-slate-800 p-3 rounded-2xl">
                                        <input type="file" name="photo" id="filePhotoInput" class="text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gradient-to-r file:from-blue-600 file:to-indigo-600 file:text-white cursor-pointer w-full">
                                    </div>
                                </div>

                                <button type="submit" name="add_passer" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-[0.15em] shadow-xl shadow-blue-950/40 transition-all mt-2">Publish Profile</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");
                            if (mysqli_num_rows($res) === 0): ?>
                            <div class="bg-slate-900/40 border-2 border-dashed border-slate-800 rounded-[2.5rem] p-16 text-center backdrop-blur-xl col-span-full">
                                <p class="text-slate-500 font-bold text-sm uppercase tracking-widest">No passers yet — publish the first success story.</p>
                            </div>
                            <?php else: ?>
                            <?php while($p = mysqli_fetch_assoc($res)):
                                if (file_exists("uploads/passers/" . $p['photo'])) {
                                    $computedPath = "uploads/passers/" . $p['photo'];
                                } elseif (!empty($p['photo']) && file_exists("uploads/profiles/" . $p['photo'])) {
                                    $computedPath = "uploads/profiles/" . $p['photo'];
                                } else {
                                    $computedPath = "assets/img/avatar-placeholder.svg";
                                }
                            ?>
                            <div class="bg-slate-900/60 p-6 rounded-[2.5rem] border border-slate-800 text-center group hover:border-blue-500/50 transition-all relative flex flex-col justify-between items-center backdrop-blur-xl">
                                <form method="POST" action="" class="absolute top-5 right-5" onsubmit="return confirm('Delete this passer permanently?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="delete_id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" class="p-2 text-slate-500 hover:text-red-400 transition-colors md:opacity-0 md:group-hover:opacity-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                
                                <div class="w-full">
                                    <img src="<?= htmlspecialchars(thumb_url($computedPath, 320), ENT_QUOTES, 'UTF-8') ?>" alt="Portrait of <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" onerror="this.onerror=null;this.src='assets/img/avatar-placeholder.svg'" loading="lazy" decoding="async" width="80" height="80" class="w-20 h-20 rounded-[2rem] mx-auto object-cover border-4 border-slate-950 mb-3 shadow-sm">
                                    <h5 class="font-bold text-white leading-snug px-2"><?= htmlspecialchars($p['name']) ?></h5>
                                    <div class="flex items-center justify-center gap-2 mt-2 flex-wrap">
                                        <span class="text-[9px] font-black bg-blue-950/20 text-blue-400 px-2 py-0.5 rounded-lg border border-blue-900/30"><?= $p['rating'] ?>%</span>
                                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-tight"><?= htmlspecialchars($p['program']) ?></span>
                                    </div>
                                </div>

                                <?php if(!empty($p['exam_date']) && $p['exam_date'] !== '0000-00-00'): ?>
                                    <div class="w-full mt-4 pt-3 border-t border-slate-800/60 text-[10px] text-slate-500 font-medium">
                                        Exam: <?= date('M Y', strtotime($p['exam_date'])) ?> • Batch <?= htmlspecialchars($p['batch']) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-full mt-4 pt-3 border-t border-slate-800/60 text-[10px] text-slate-500 font-medium">
                                        Batch Year: <?= htmlspecialchars($p['batch']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                            <?php endif; ?>
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
                previewPhoto.src = (photo === 'default_user.jpg') ? 'assets/img/avatar-placeholder.svg' : 'uploads/profiles/' + photo;
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
                    previewPhoto.src = 'assets/img/avatar-placeholder.svg';
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
            previewPhoto.src = 'assets/img/avatar-placeholder.svg';
        }

        filePhotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) { previewPhoto.src = e.target.result; }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Menu Toggle Logic
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu'); 
        const sidebar = document.getElementById('mobileSidebar'); 
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

        // Setup SweetAlert2 Notifications Custom Dark Config Mixin
        const customSwalMixin = Swal.mixin({
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#2563eb'
        });

        const Toast = customSwalMixin.mixin({
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
    </script>
</body>
</html>