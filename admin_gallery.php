<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$target_dir = "uploads/gallery/";
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$max_file_size = 5 * 1024 * 1024;
$gallery_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'gallery_images'")) > 0;

function is_valid_gallery_image($file, $allowed_extensions, $max_file_size) {
    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['name'])) {
        return false;
    }
    if ($file['size'] > $max_file_size) {
        return false;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return in_array($ext, $allowed_extensions, true);
}

// --- Add gallery images ---
if ($gallery_table_exists && isset($_POST['add_gallery'])) {
    $caption = trim($_POST['caption'] ?? '');
    if ($caption === '') {
        header("Location: admin_gallery.php?error=empty_caption");
        exit();
    }

    $caption = mysqli_real_escape_string($conn, $caption);
    $uploaded = 0;

    if (!empty($_FILES['images']['name'][0])) {
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        foreach ($_FILES['images']['name'] as $index => $name) {
            $file = [
                'name' => $_FILES['images']['name'][$index],
                'type' => $_FILES['images']['type'][$index],
                'tmp_name' => $_FILES['images']['tmp_name'][$index],
                'error' => $_FILES['images']['error'][$index],
                'size' => $_FILES['images']['size'][$index],
            ];

            if (!is_valid_gallery_image($file, $allowed_extensions, $max_file_size)) {
                continue;
            }

            $image_name = time() . "_" . $index . "_" . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $target_dir . $image_name)) {
                $image_name = mysqli_real_escape_string($conn, $image_name);
                $sort_order = (int) $index;
                mysqli_query($conn, "INSERT INTO gallery_images (caption, image_path, sort_order) VALUES ('$caption', '$image_name', $sort_order)");
                $uploaded++;
            }
        }
    }

    if ($uploaded > 0) {
        log_activity($conn, 'gallery.create', "Added $uploaded image(s) under caption: $caption", [
            'entity_type' => 'gallery',
        ]);
        header("Location: admin_gallery.php?posted=1&count=$uploaded");
    } else {
        header("Location: admin_gallery.php?error=no_images");
    }
    exit();
}

// --- Delete single image ---
if ($gallery_table_exists && isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $result = mysqli_query($conn, "SELECT image_path FROM gallery_images WHERE id = '$id'");
    $data = mysqli_fetch_assoc($result);

    if ($data && file_exists($target_dir . $data['image_path'])) {
        @unlink($target_dir . $data['image_path']);
    }
    mysqli_query($conn, "DELETE FROM gallery_images WHERE id = '$id'");
    log_activity($conn, 'gallery.delete', "Deleted gallery image #$id", [
        'entity_type' => 'gallery',
        'entity_id' => (int) $id,
    ]);
    header("Location: admin_gallery.php?deleted=1");
    exit();
}

// --- Delete all images under a caption ---
if ($gallery_table_exists && isset($_GET['delete_caption'])) {
    $caption = mysqli_real_escape_string($conn, $_GET['delete_caption']);
    $result = mysqli_query($conn, "SELECT image_path FROM gallery_images WHERE caption = '$caption'");
    while ($row = mysqli_fetch_assoc($result)) {
        if (file_exists($target_dir . $row['image_path'])) {
            @unlink($target_dir . $row['image_path']);
        }
    }
    mysqli_query($conn, "DELETE FROM gallery_images WHERE caption = '$caption'");
    log_activity($conn, 'gallery.delete_caption', "Deleted all gallery images for caption: $caption", [
        'entity_type' => 'gallery',
    ]);
    header("Location: admin_gallery.php?deleted=1");
    exit();
}

// Fetch all images grouped by caption
$grouped_gallery = [];
if ($gallery_table_exists) {
    $gallery_query = mysqli_query($conn, "SELECT * FROM gallery_images ORDER BY caption ASC, sort_order ASC, id ASC");
    while ($item = mysqli_fetch_assoc($gallery_query)) {
        $grouped_gallery[$item['caption']][] = $item;
    }
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
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Image Gallery | C-Familia Admin</title>
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
                        <h2 class="text-3xl font-[800] text-slate-900 tracking-tight">Image Gallery</h2>
                        <p class="text-slate-500 mt-1">Upload one or more images with a caption. Images are grouped and displayed on the landing page by caption.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-white border border-slate-200 rounded-2xl shadow-sm ml-4 shrink-0">
                        <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                        </svg>
                    </button>
                </header>

                <?php if (!$gallery_table_exists): ?>
                <div class="mb-8 bg-amber-50 border border-amber-200 text-amber-900 rounded-[2rem] p-6 md:p-8">
                    <h3 class="font-bold text-lg mb-2">Database setup required</h3>
                    <p class="text-sm leading-relaxed mb-4">Run this SQL once on the production database before uploading gallery images:</p>
                    <pre class="text-xs bg-white border border-amber-100 rounded-xl p-4 overflow-x-auto">CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `caption` (`caption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;</pre>
                    <p class="text-xs text-amber-700 mt-3">The file is also saved at <code class="bg-white px-1.5 py-0.5 rounded">migrations/add_gallery_images.sql</code>. The landing page will stay unchanged until this is applied.</p>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    <div class="lg:col-span-5">
                        <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-200 sticky top-12">
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5" <?= $gallery_table_exists ? '' : 'inert' ?>>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Caption</label>
                                    <input type="text" name="caption" required placeholder="e.g. Batch 2026 Graduation, Campus Tour" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 outline-none transition text-sm font-semibold">
                                    <p class="text-[10px] text-slate-400 mt-2 px-1">All uploaded images in this batch will share this caption.</p>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-400 mb-1.5 block px-1">Images (1 or more)</label>
                                    <div class="bg-slate-50 border border-slate-100 p-3 rounded-2xl">
                                        <input type="file" name="images[]" id="galleryImages" accept="image/jpeg,image/png,image/gif,image/webp" multiple required class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-blue-600 transition-colors cursor-pointer w-full">
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-2 px-1">JPG, PNG, GIF, or WEBP. Max 5MB each.</p>
                                </div>

                                <div id="previewGrid" class="hidden grid grid-cols-3 gap-2"></div>

                                <button type="submit" name="add_gallery" <?= $gallery_table_exists ? '' : 'disabled' ?> class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-[0.15em] shadow-xl hover:bg-blue-600 transition-all disabled:opacity-40 disabled:cursor-not-allowed">Publish to Gallery</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-8">
                        <?php if (empty($grouped_gallery)): ?>
                            <div class="bg-white p-12 rounded-[2.5rem] border border-slate-100 text-center">
                                <p class="text-slate-400 font-semibold text-sm">No gallery images yet. Upload your first set above.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($grouped_gallery as $caption => $images): ?>
                            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100">
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($caption) ?></h3>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1"><?= count($images) ?> image<?= count($images) > 1 ? 's' : '' ?></p>
                                    </div>
                                    <button onclick="confirmDeleteCaption(<?= json_encode($caption) ?>)" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 tracking-wider shrink-0">Delete Group</button>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <?php foreach ($images as $img): ?>
                                    <div class="relative group rounded-2xl overflow-hidden border border-slate-100 aspect-square">
                                        <img src="<?= $target_dir . htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($caption) ?>" class="w-full h-full object-cover">
                                        <button onclick="confirmDelete(<?= (int) $img['id'] ?>)" class="absolute top-2 right-2 p-1.5 bg-white/90 text-slate-400 hover:text-red-500 rounded-lg shadow-sm opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script>
        const openBtn = document.getElementById('openMenu');
        const closeBtn = document.getElementById('closeMenu');
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar(state) {
            if (state) {
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

        const galleryImages = document.getElementById('galleryImages');
        const previewGrid = document.getElementById('previewGrid');

        galleryImages?.addEventListener('change', function() {
            previewGrid.innerHTML = '';
            if (!this.files || !this.files.length) {
                previewGrid.classList.add('hidden');
                return;
            }
            previewGrid.classList.remove('hidden');
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-20 object-cover rounded-xl border border-slate-100';
                    previewGrid.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2800,
            timerProgressBar: true
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('posted') === '1') {
            const count = urlParams.get('count') || '1';
            Toast.fire({ icon: 'success', title: `${count} image(s) added to gallery` });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (urlParams.get('deleted') === '1') {
            Toast.fire({ icon: 'info', title: 'Gallery item removed' });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (urlParams.get('error') === 'empty_caption') {
            Toast.fire({ icon: 'error', title: 'Please enter a caption' });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (urlParams.get('error') === 'no_images') {
            Toast.fire({ icon: 'error', title: 'Please upload at least one valid image' });
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Remove Image?',
                text: 'This image will be removed from the public gallery.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = `?delete=${id}`; }
            });
        }

        function confirmDeleteCaption(caption) {
            Swal.fire({
                title: 'Remove Entire Group?',
                text: `All images under "${caption}" will be deleted.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Yes, delete all'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = `?delete_caption=${encodeURIComponent(caption)}`; }
            });
        }
    </script>
</body>
</html>
