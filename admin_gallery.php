<?php
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/uploads.php';
secure_session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$target_dir = "uploads/gallery/";
$gallery_table_exists = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'gallery_images'")) > 0;

// --- Add gallery images ---
if ($gallery_table_exists && isset($_POST['add_gallery'])) {
    csrf_verify();

    $caption = trim($_POST['caption'] ?? '');
    if ($caption === '') {
        header("Location: admin_gallery.php?error=empty_caption");
        exit();
    }

    $uploaded = 0;

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $index => $name) {
            $file = [
                'name' => $_FILES['images']['name'][$index],
                'type' => $_FILES['images']['type'][$index],
                'tmp_name' => $_FILES['images']['tmp_name'][$index],
                'error' => $_FILES['images']['error'][$index],
                'size' => $_FILES['images']['size'][$index],
            ];

            [$ok, $image_name] = store_uploaded_file($file, $target_dir, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if (!$ok) {
                continue;
            }

            $sort_order = (int) $index;
            $stmt = $conn->prepare("INSERT INTO gallery_images (caption, image_path, sort_order) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $caption, $image_name, $sort_order);
            if ($stmt->execute()) {
                $uploaded++;
            }
        }
    }

    if ($uploaded > 0) {
        log_activity($conn, 'gallery.create', "Added gallery images", [
            'entity_type' => 'gallery',
        ]);
        header("Location: admin_gallery.php?posted=1&count=$uploaded");
    } else {
        header("Location: admin_gallery.php?error=no_images");
    }
    exit();
}

// --- Delete single image ---
if ($gallery_table_exists && isset($_POST['delete_id'])) {
    csrf_verify();

    $id = intval($_POST['delete_id']);
    $result = mysqli_query($conn, "SELECT image_path, caption FROM gallery_images WHERE id = '" . intval($id) . "' LIMIT 1");
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        if (is_file($target_dir . $data['image_path'])) {
            @unlink($target_dir . $data['image_path']);
        }

        $stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        log_activity($conn, 'gallery.delete', null, [
            'entity_type' => 'gallery',
            'entity_id' => $id,
            'entity_label' => $data['caption'] ?: ($data['image_path'] ?? 'Unknown image'),
        ]);
    }

    header("Location: admin_gallery.php?deleted=1");
    exit();
}

// --- Delete all images under a caption ---
if ($gallery_table_exists && isset($_POST['delete_caption'])) {
    csrf_verify();

    $caption = trim($_POST['delete_caption'] ?? '');
    if ($caption !== '') {
        $result = mysqli_query($conn, "SELECT image_path FROM gallery_images WHERE caption = '" . mysqli_real_escape_string($conn, $caption) . "'");
        while ($row = mysqli_fetch_assoc($result)) {
            if (is_file($target_dir . $row['image_path'])) {
                @unlink($target_dir . $row['image_path']);
            }
        }

        $stmt = $conn->prepare("DELETE FROM gallery_images WHERE caption = ?");
        $stmt->bind_param("s", $caption);
        $stmt->execute();

        log_activity($conn, 'gallery.delete_caption', null, [
            'entity_type' => 'gallery',
        ]);
    }

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
    <link rel="stylesheet" href="assets/app.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <title>Image Gallery | C-Familia Admin</title>
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
                        <h2 class="text-3xl font-[800] text-white tracking-tight">Image Gallery</h2>
                        <p class="text-slate-400 mt-1">Upload one or more images with a caption. Images are grouped and displayed on the landing page by caption.</p>
                    </div>
                    <button id="openMenu" class="lg:hidden p-3 bg-slate-900/60 border border-slate-800 rounded-2xl shadow-sm ml-4 shrink-0 backdrop-blur-xl">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                        </svg>
                    </button>
                </header>

                <?php if (!$gallery_table_exists): ?>
                <div class="mb-8 bg-red-950/20 border border-red-900/30 text-red-400 rounded-[2rem] p-6 md:p-8 backdrop-blur-xl">
                    <h3 class="font-bold text-lg mb-2">Database setup required</h3>
                    <p class="text-sm leading-relaxed mb-4">Run this SQL once on the production database before uploading gallery images:</p>
                    <pre class="text-xs bg-slate-950 border border-slate-800 text-slate-300 rounded-xl p-4 overflow-x-auto">CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `caption` (`caption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;</pre>
                    <p class="text-xs text-slate-500 mt-3">The file is also saved at <code class="bg-slate-950 text-slate-400 border border-slate-800 px-1.5 py-0.5 rounded">migrations/add_gallery_images.sql</code>. The landing page will stay unchanged until this is applied.</p>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    <div class="lg:col-span-5">
                        <div class="bg-slate-900/60 p-6 md:p-8 rounded-[2.5rem] border border-slate-800 sticky top-12 backdrop-blur-xl">
                            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5" <?= $gallery_table_exists ? '' : 'inert' ?>>
                                <?= csrf_field() ?>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Caption</label>
                                    <input type="text" name="caption" required placeholder="e.g. Batch 2026 Graduation, Campus Tour" class="w-full px-5 py-3.5 bg-slate-950 border border-slate-800 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition text-sm font-semibold text-white placeholder:text-slate-600">
                                    <p class="text-[10px] text-slate-500 mt-2 px-1">All uploaded images in this batch will share this caption.</p>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500 mb-1.5 block px-1">Images (1 or more)</label>
                                    <div class="bg-slate-950 border border-slate-800 p-3 rounded-2xl">
                                        <input type="file" name="images[]" id="galleryImages" accept="image/jpeg,image/png,image/gif,image/webp" multiple required class="text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gradient-to-r file:from-blue-600 file:to-indigo-600 file:text-white cursor-pointer w-full">
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-2 px-1">JPG, PNG, GIF, or WEBP. Max 5MB each.</p>
                                </div>

                                <div id="previewGrid" class="hidden grid grid-cols-3 gap-2"></div>

                                <button type="submit" name="add_gallery" <?= $gallery_table_exists ? '' : 'disabled' ?> class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 rounded-2xl font-black uppercase text-xs tracking-[0.15em] shadow-xl shadow-blue-950/40 transition-all disabled:opacity-40 disabled:cursor-not-allowed">Publish to Gallery</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-8">
                        <?php if (empty($grouped_gallery)): ?>
                            <div class="bg-slate-900/40 p-12 rounded-[2.5rem] border border-slate-800 text-center backdrop-blur-xl">
                                <p class="text-slate-500 font-semibold text-sm">No gallery images yet. Upload your first set above.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($grouped_gallery as $caption => $images): ?>
                            <div class="bg-slate-900/60 p-6 md:p-8 rounded-[2.5rem] border border-slate-800 backdrop-blur-xl">
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div>
                                        <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($caption) ?></h3>
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-1"><?= count($images) ?> image<?= count($images) > 1 ? 's' : '' ?></p>
                                    </div>
                                    <form method="POST" action="" class="shrink-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="delete_caption" value="<?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" onclick="return confirmAction(event, 'Delete this entire caption group and its images?')" class="text-[10px] font-black uppercase text-red-400 hover:text-red-500 tracking-wider">Delete Group</button>
                                    </form>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <?php foreach ($images as $img): ?>
                                    <div class="relative group rounded-2xl overflow-hidden border border-slate-800 aspect-square">
                                        <img src="<?= $target_dir . htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($caption) ?>" class="w-full h-full object-cover">
                                        <form method="POST" action="" class="absolute top-2 right-2">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="delete_id" value="<?= (int) $img['id'] ?>">
                                            <button type="submit" onclick="return confirmAction(event, 'Delete this image?')" class="p-1.5 bg-slate-900/90 text-slate-400 hover:text-red-400 rounded-lg shadow-sm opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all border border-slate-800 backdrop-blur-md">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
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
        // Setup SweetAlert2 Custom Dark Config Mixin
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
            timerProgressBar: true
        });

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
                    img.className = 'w-full h-20 object-cover rounded-xl border border-slate-800';
                    previewGrid.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });


        function confirmAction(event, message) {
            event.preventDefault();
            const form = event.target;
            customSwalMixin.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#1e293b'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }
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
    </script>
</body>
</html>