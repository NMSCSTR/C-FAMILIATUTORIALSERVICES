<?php
/**
 * Shared admin <head> partial.
 *
 * Set before including:
 *   $page_title      (string, required-ish) - rendered before " | C-Familia Admin"
 *   $load_sweetalert (bool) - enables SweetAlert2
 *   $load_charts     (bool) - enables Chart.js (deferred)
 */
if (!isset($page_title)) {
    $page_title = 'Admin';
}
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | C-Familia Admin</title>
    <link rel="stylesheet" href="assets/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/ui.js"></script>
<?php if (!empty($load_sweetalert)): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php endif; ?>
<?php if (!empty($load_charts)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<?php endif; ?>
