<?php
include 'db.php';

// --- Auto-Calculate Passing Rate ---
$total_passers_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM passers");
$total_passers = mysqli_fetch_assoc($total_passers_query)['count'];
$display_rate = ($total_passers > 0) ? "95%" : "0%";

// --- Top performers (rating >= 95) ---
$top_result  = mysqli_query($conn, "SELECT * FROM passers WHERE rating >= 95 ORDER BY rating DESC");
$top_all     = $top_result ? mysqli_fetch_all($top_result, MYSQLI_ASSOC) : [];
$top_featured = array_slice($top_all, 0, 6);
$top_more     = array_slice($top_all, 6);

// --- Recent passers (Hall of Fame strip) ---
$passers_result = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC LIMIT 20");
$passers_all    = $passers_result ? mysqli_fetch_all($passers_result, MYSQLI_ASSOC) : [];

// --- Testimonials ---
$test_result   = mysqli_query($conn, "SELECT t.*, u.firstname, u.lastname, u.profile_pic FROM testimonials t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 24");
$test_all      = $test_result ? mysqli_fetch_all($test_result, MYSQLI_ASSOC) : [];
$test_featured = array_slice($test_all, 0, 6);
$test_more     = array_slice($test_all, 6);

// --- Announcements ---
// Note: the previous query filtered on `audience`, a column that doesn't exist on
// the `announcements` table (see schema — only `category` exists). Filtering on it
// silently breaks the query. Ordering by category/created_at instead so announcements
// actually render.
$ann_result   = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 12");
$ann_all      = $ann_result ? mysqli_fetch_all($ann_result, MYSQLI_ASSOC) : [];
$ann_featured = array_slice($ann_all, 0, 3);
$ann_more     = array_slice($ann_all, 3);

// --- Posts / Learning materials ---
$posts_result   = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC LIMIT 18");
$posts_all      = $posts_result ? mysqli_fetch_all($posts_result, MYSQLI_ASSOC) : [];
$posts_featured = array_slice($posts_all, 0, 3);
$posts_more     = array_slice($posts_all, 3);

function photo_path($photo) {
    if (!$photo) return "uploads/passers/default_user.jpg";
    return file_exists("uploads/profiles/" . $photo) ? "uploads/profiles/" . $photo : "uploads/passers/" . $photo;
}

function render_passer_card($p) {
    $path = photo_path($p['photo']);
    ob_start();
    ?>
    <div class="passer-card group">
        <div class="passer-card__photo">
            <img src="<?= htmlspecialchars($path) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
        </div>
        <div class="passer-card__body">
            <h4 class="passer-card__name"><?= htmlspecialchars($p['name']) ?></h4>
            <p class="passer-card__program"><?= htmlspecialchars($p['program']) ?></p>
        </div>
        <div class="seal seal--sm">
            <span class="seal__text"><?= number_format($p['rating'], 2) ?></span>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function render_testimonial_card($t) {
    $pic = !empty($t['profile_pic']) ? "uploads/profiles/" . $t['profile_pic'] : "uploads/passers/default_user.jpg";
    ob_start();
    ?>
    <figure class="note-card">
        <span class="note-card__mark" aria-hidden="true">&ldquo;</span>
        <blockquote class="note-card__quote"><?= nl2br(htmlspecialchars($t['content'])) ?></blockquote>
        <figcaption class="note-card__author">
            <img src="<?= htmlspecialchars($pic) ?>" alt="" loading="lazy">
            <div>
                <span class="note-card__name"><?= htmlspecialchars($t['firstname'] . ' ' . $t['lastname']) ?></span>
                <span class="note-card__tag">Verified Student</span>
            </div>
        </figcaption>
    </figure>
    <?php
    return ob_get_clean();
}

function render_announcement_card($a) {
    $is_urgent = ($a['category'] == 'Urgent');
    ob_start();
    ?>
    <article class="pin-card <?= $is_urgent ? 'pin-card--urgent' : '' ?>">
        <span class="pin-card__pin" aria-hidden="true"></span>
        <div class="pin-card__meta">
            <span class="pin-card__category"><?= htmlspecialchars($a['category']) ?></span>
            <span class="pin-card__date"><?= date('M d, Y', strtotime($a['created_at'])) ?></span>
        </div>
        <h4 class="pin-card__title"><?= htmlspecialchars($a['title']) ?></h4>
        <p class="pin-card__message"><?= nl2br(htmlspecialchars($a['message'])) ?></p>
    </article>
    <?php
    return ob_get_clean();
}

function render_post_card($p) {
    ob_start();
    ?>
    <article class="folder-card">
        <span class="folder-card__tab">Resource</span>
        <h4 class="folder-card__title"><?= htmlspecialchars($p['title']) ?></h4>
        <p class="folder-card__excerpt"><?= htmlspecialchars($p['content']) ?></p>
        <?php if ($p['file_path']): ?>
        <a href="uploads/resources/<?= htmlspecialchars($p['file_path']) ?>" class="folder-card__link">
            Download file <span aria-hidden="true">&rarr;</span>
        </a>
        <?php endif; ?>
    </article>
    <?php
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;0,9..144,800;1,9..144,500;1,9..144,600&family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="shortcut icon" href="cuevaslogo.jpg" type="image/x-icon">
<title>C-Familia Tutorial Services</title>
<style>
  :root{
    --ink: #12213A;
    --ink-2: #1B2C4A;
    --parchment: #F7F3EA;
    --parchment-2: #EFE7D8;
    --brass: #C79A46;
    --brass-2: #E0B563;
    --clay: #A24936;
    --slate: #4B5468;
  }
  *{ scroll-behavior: smooth; }
  body{
    font-family:'Inter', sans-serif;
    background:var(--parchment);
    color:var(--ink);
    -webkit-font-smoothing:antialiased;
  }
  .font-display{ font-family:'Fraunces', serif; }
  .font-mono{ font-family:'IBM Plex Mono', monospace; }

  ::selection{ background: var(--brass); color:var(--ink); }

  /* texture */
  .grain{
    background-image: radial-gradient(rgba(247,243,234,.08) 1px, transparent 1px);
    background-size: 18px 18px;
  }

  /* eyebrow / folder-tab label */
  .tab-label{
    display:inline-flex; align-items:center; gap:.6rem;
    font-family:'IBM Plex Mono', monospace;
    font-size:.68rem; font-weight:600; letter-spacing:.28em; text-transform:uppercase;
  }
  .tab-label::before{
    content:''; width:1.6rem; height:2px; background:currentColor; opacity:.5;
  }

  /* seal / stamp */
  .seal{
    width:76px; height:76px; border-radius:9999px; flex-shrink:0;
    background: radial-gradient(circle at 32% 30%, var(--brass-2), var(--brass) 60%, #9c7530 100%);
    display:flex; align-items:center; justify-content:center;
    box-shadow: 0 8px 20px rgba(199,154,70,.35), inset 0 0 0 3px rgba(255,255,255,.28);
    position:relative;
  }
  .seal::before{
    content:''; position:absolute; inset:6px; border-radius:9999px;
    border:1.5px dashed rgba(18,33,58,.4);
  }
  .seal__text{
    font-family:'IBM Plex Mono', monospace; font-weight:700; color:var(--ink);
    font-size:.92rem; line-height:1; position:relative; z-index:1;
  }
  .seal--sm{ width:58px; height:58px; }
  .seal--sm .seal__text{ font-size:.72rem; }
  .seal--lg{ width:120px; height:120px; }
  .seal--lg .seal__text{ font-size:1.35rem; }
  .seal--lg::before{ inset:10px; }

  /* passer card */
  .passer-card{
    background:#fff; border:1px solid rgba(18,33,58,.08);
    border-radius:1.75rem; padding:1.75rem;
    display:flex; align-items:center; gap:1.1rem;
    transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
  }
  .passer-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 20px 40px -20px rgba(18,33,58,.25);
    border-color: rgba(199,154,70,.4);
  }
  .passer-card__photo{
    width:56px; height:56px; border-radius:9999px; overflow:hidden; flex-shrink:0;
    border:3px solid var(--parchment-2);
  }
  .passer-card__photo img{ width:100%; height:100%; object-fit:cover; }
  .passer-card__name{ font-weight:700; font-size:.95rem; line-height:1.25; }
  .passer-card__program{
    font-family:'IBM Plex Mono', monospace; font-size:.63rem; font-weight:600;
    letter-spacing:.08em; text-transform:uppercase; color:var(--clay); margin-top:.2rem;
  }
  .passer-card__body{ flex:1; min-width:0; }

  /* testimonial note card */
  .note-card{
    background: var(--ink-2); border-radius:1.75rem; padding:2.25rem;
    border:1px solid rgba(255,255,255,.08); position:relative; height:100%;
    display:flex; flex-direction:column;
    transition: transform .35s ease, border-color .35s ease;
  }
  .note-card:hover{ transform: translateY(-4px); border-color: rgba(199,154,70,.4); }
  .note-card__mark{
    font-family:'Fraunces', serif; font-size:3.5rem; color:var(--brass);
    opacity:.55; line-height:1; display:block; margin-bottom:.25rem;
  }
  .note-card__quote{
    color:#D8DEEA; font-style:italic; line-height:1.7; font-size:.95rem; flex:1;
  }
  .note-card__author{ display:flex; align-items:center; gap:.85rem; margin-top:1.75rem; }
  .note-card__author img{
    width:42px; height:42px; border-radius:.9rem; object-fit:cover;
  }
  .note-card__name{ display:block; font-weight:700; color:#fff; font-size:.85rem; }
  .note-card__tag{
    display:block; font-family:'IBM Plex Mono', monospace; font-size:.6rem;
    letter-spacing:.15em; text-transform:uppercase; color:var(--brass-2); margin-top:.15rem;
  }

  /* announcement pin card */
  .pin-card{
    background:#fff; border:1px solid rgba(18,33,58,.08); border-radius:1.5rem;
    padding:2rem; position:relative; overflow:hidden;
    transition: transform .3s ease, box-shadow .3s ease;
  }
  .pin-card:hover{ transform: translateY(-3px); box-shadow: 0 18px 36px -22px rgba(18,33,58,.3); }
  .pin-card__pin{
    position:absolute; top:1.25rem; right:1.5rem; width:10px; height:10px; border-radius:9999px;
    background: var(--brass); box-shadow: 0 0 0 4px rgba(199,154,70,.18);
  }
  .pin-card--urgent .pin-card__pin{ background: var(--clay); box-shadow: 0 0 0 4px rgba(162,73,54,.18); }
  .pin-card__meta{ display:flex; align-items:center; gap:.6rem; margin-bottom:.9rem; }
  .pin-card__category{
    font-family:'IBM Plex Mono', monospace; font-size:.62rem; font-weight:700;
    letter-spacing:.14em; text-transform:uppercase; color:var(--clay);
  }
  .pin-card--urgent .pin-card__category{ color:var(--clay); }
  .pin-card__date{ font-size:.7rem; color:var(--slate); }
  .pin-card__title{ font-family:'Fraunces', serif; font-weight:700; font-size:1.2rem; margin-bottom:.6rem; }
  .pin-card__message{ color:var(--slate); font-size:.88rem; line-height:1.65; }

  /* learning material folder card */
  .folder-card{
    background:#fff; border:1px solid rgba(18,33,58,.08); border-radius:1.5rem;
    padding:2rem; position:relative;
    transition: transform .3s ease, box-shadow .3s ease;
  }
  .folder-card:hover{ transform: translateY(-3px); box-shadow: 0 18px 36px -22px rgba(18,33,58,.3); }
  .folder-card__tab{
    display:inline-block; font-family:'IBM Plex Mono', monospace; font-size:.62rem; font-weight:700;
    letter-spacing:.14em; text-transform:uppercase; color:var(--ink);
    background: var(--parchment-2); padding:.35rem .75rem; border-radius:.6rem; margin-bottom:1rem;
  }
  .folder-card__title{ font-family:'Fraunces', serif; font-weight:700; font-size:1.15rem; margin-bottom:.65rem; }
  .folder-card__excerpt{
    color:var(--slate); font-size:.88rem; line-height:1.6; margin-bottom:1.25rem;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }
  .folder-card__link{
    display:inline-flex; align-items:center; gap:.5rem; font-family:'IBM Plex Mono', monospace;
    font-size:.7rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--clay);
    transition: gap .25s ease;
  }
  .folder-card__link:hover{ gap:.85rem; }

  /* modal */
  .modal{ display:none; }
  .modal.is-open{ display:flex; }
  .modal-panel{
    opacity:0; transform: translateY(24px) scale(.98);
    transition: opacity .3s ease, transform .3s ease;
  }
  .modal.is-open .modal-panel{ opacity:1; transform: translateY(0) scale(1); }
  .modal-backdrop{ opacity:0; transition: opacity .3s ease; }
  .modal.is-open .modal-backdrop{ opacity:1; }

  /* reveal on scroll */
  .reveal{ opacity:0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
  .reveal.is-in{ opacity:1; transform: translateY(0); }

  @media (prefers-reduced-motion: reduce){
    .reveal{ opacity:1; transform:none; transition:none; }
    .modal-panel, .modal-backdrop{ transition:none; }
  }

  /* mobile nav drawer */
  .drawer{ transition: transform .35s ease; transform: translateX(100%); }
  .drawer.is-open{ transform: translateX(0); }

  ::-webkit-scrollbar{ width:10px; }
  ::-webkit-scrollbar-track{ background: var(--parchment-2); }
  ::-webkit-scrollbar-thumb{ background: var(--brass); border-radius:9999px; }
</style>
</head>
<body class="antialiased">

<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[200] focus:bg-white focus:text-[--ink] focus:px-4 focus:py-2 focus:rounded-xl">Skip to content</a>

<nav class="sticky top-0 z-50 bg-[--parchment]/90 backdrop-blur-md border-b border-[--ink]/10 px-6 py-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3 group">
            <span class="w-11 h-11 rounded-full overflow-hidden ring-2 ring-[--brass] ring-offset-2 ring-offset-[--parchment] block transition-transform group-hover:scale-105">
                <img src="cuevaslogo.jpg" alt="C-Familia Logo" class="w-full h-full object-cover">
            </span>
            <span class="font-display text-2xl font-bold tracking-tight text-[--ink]">
                C-Familia<span class="text-[--brass]">.</span>
            </span>
        </a>

        <div class="hidden md:flex items-center gap-9 font-mono text-[.72rem] font-semibold uppercase tracking-[.15em] text-[--slate]">
            <a href="#top" class="hover:text-[--clay] transition-colors">Top Performance</a>
            <a href="#voices" class="hover:text-[--clay] transition-colors">Voices</a>
            <a href="#announcements" class="hover:text-[--clay] transition-colors">Announcements</a>
            <a href="#posts" class="hover:text-[--clay] transition-colors">Resources</a>
            <a href="#contact" class="hover:text-[--clay] transition-colors">Contact</a>
        </div>

        <div class="hidden md:flex items-center gap-3">
            <a href="login.php" class="text-[--ink] font-semibold text-sm px-4 py-2 hover:text-[--clay] transition-colors">Login</a>
            <a href="register.php" class="px-5 py-2.5 bg-[--ink] text-white text-sm font-semibold rounded-xl hover:bg-[--clay] transition-colors shadow-md">Join Us</a>
        </div>

        <button id="menuToggle" class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-[--ink]/15" aria-label="Open menu" aria-expanded="false">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M2 5h16M2 10h16M2 15h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
    </div>
</nav>

<div id="drawer" class="drawer fixed inset-y-0 right-0 z-[60] w-full max-w-xs bg-[--ink] text-white p-8 flex flex-col gap-2 shadow-2xl">
    <div class="flex justify-between items-center mb-8">
        <span class="font-display text-xl font-bold">Menu</span>
        <button id="drawerClose" aria-label="Close menu" class="w-9 h-9 flex items-center justify-center rounded-lg border border-white/15">
            <svg width="16" height="16" viewBox="0 0 16 16"><path d="M1 1l14 14M15 1L1 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </button>
    </div>
    <a href="#top" class="drawer-link py-3 border-b border-white/10 font-mono text-xs uppercase tracking-[.15em]">Top Performance</a>
    <a href="#voices" class="drawer-link py-3 border-b border-white/10 font-mono text-xs uppercase tracking-[.15em]">Voices</a>
    <a href="#announcements" class="drawer-link py-3 border-b border-white/10 font-mono text-xs uppercase tracking-[.15em]">Announcements</a>
    <a href="#posts" class="drawer-link py-3 border-b border-white/10 font-mono text-xs uppercase tracking-[.15em]">Resources</a>
    <a href="#contact" class="drawer-link py-3 border-b border-white/10 font-mono text-xs uppercase tracking-[.15em]">Contact</a>
    <div class="mt-8 flex flex-col gap-3">
        <a href="login.php" class="text-center py-3 rounded-xl border border-white/20 font-semibold">Login</a>
        <a href="register.php" class="text-center py-3 rounded-xl bg-[--brass] text-[--ink] font-semibold">Join Us</a>
    </div>
</div>
<div id="drawerBackdrop" class="fixed inset-0 z-[55] bg-black/50 hidden"></div>

<main id="main">

    <header class="relative bg-[--ink] grain py-24 lg:py-32 overflow-hidden">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-[--brass]/10 rounded-full blur-[130px]"></div>
        <div class="absolute bottom-0 right-0 w-[420px] h-[420px] bg-[--clay]/10 rounded-full blur-[140px]"></div>
        <div class="relative max-w-7xl mx-auto px-6 grid lg:grid-cols-2 items-center gap-14">
            <div>
                <span class="tab-label text-[--brass-2] mb-8">
                    <img src="cuevaslogo.jpg" class="w-6 h-6 rounded-full object-cover" alt="">
                    Criminology Review Center
                </span>
                <h2 class="font-display text-4xl md:text-6xl font-[800] text-white mb-6 leading-[1.08]">
                    Your future,<br><span class="italic font-medium text-[--brass-2]">filed and sealed.</span>
                </h2>
                <p class="text-lg text-slate-300 mb-10 leading-relaxed max-w-md">
                    "Join our family, and together, we'll pave the way towards your success."
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="register.php" class="px-8 py-4 bg-[--brass] text-[--ink] rounded-2xl text-base font-bold hover:bg-[--brass-2] transition-all hover:-translate-y-0.5 shadow-xl shadow-[--brass]/20">Enroll Now</a>
                    <a href="#top" class="px-8 py-4 bg-white/5 text-white border border-white/15 rounded-2xl text-base font-bold hover:bg-white/10 transition-all">View Success Stories</a>
                </div>
            </div>

            <div class="hidden lg:flex justify-center">
                <div class="bg-white/[.04] backdrop-blur-lg p-9 rounded-[2.5rem] border border-white/10 relative w-full max-w-sm">
                    <div class="flex items-center gap-5 mb-8">
                        <div class="seal seal--lg">
                            <span class="seal__text"><?= htmlspecialchars($display_rate) ?></span>
                        </div>
                        <div>
                            <p class="text-white font-display font-bold text-lg leading-tight">Passing Rate</p>
                            <p class="text-slate-400 text-sm mt-1"><?= (int)$total_passers ?>+ licensed passers on file</p>
                        </div>
                    </div>
                    <div class="h-2.5 bg-white/10 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-gradient-to-r from-[--brass] to-[--brass-2] w-[95%] rounded-full"></div>
                    </div>
                    <p class="tab-label text-slate-500 justify-end">Academic Excellence &middot; 2026</p>
                </div>
            </div>
        </div>
    </header>

    <!-- ===================== TOP PERFORMANCE ===================== -->
    <section id="top" class="py-24 md:py-28 bg-[--parchment] px-6">
        <div class="max-w-7xl mx-auto">
            <div class="reveal flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14">
                <div>
                    <span class="tab-label text-[--clay] mb-3">Dossier &middot; Elite Achievers</span>
                    <h3 class="font-display text-4xl md:text-5xl font-[800] tracking-tight text-[--ink]">Top Performance<span class="text-[--brass]">.</span></h3>
                </div>
                <p class="text-[--slate] max-w-sm">Honoring reviewees who attained an exceptional board rating of 95% and above.</p>
            </div>

            <?php if (count($top_featured) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($top_featured as $p): ?>
                <div class="reveal"><?= render_passer_card($p) ?></div>
                <?php endforeach; ?>
            </div>
            <?php if (count($top_more) > 0): ?>
            <div class="reveal text-center mt-12">
                <button type="button" data-modal-open="modal-top" class="inline-flex items-center gap-3 px-7 py-3.5 bg-[--ink] text-white rounded-xl font-semibold text-sm hover:bg-[--clay] transition-colors">
                    See <?= count($top_more) ?> more achiever<?= count($top_more) === 1 ? '' : 's' ?>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="py-14 text-center bg-white rounded-[2rem] border border-dashed border-[--ink]/15">
                <p class="text-[--slate] font-semibold uppercase tracking-widest text-sm">Top results are currently being verified.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===================== VOICE OF SUCCESS ===================== -->
    <section id="voices" class="py-24 md:py-28 bg-[--ink] grain px-6">
        <div class="max-w-7xl mx-auto">
            <div class="reveal text-center mb-14">
                <span class="tab-label text-[--brass-2] justify-center mb-3">Dossier &middot; Student Voice</span>
                <h3 class="font-display text-4xl md:text-5xl font-[800] text-white tracking-tight">Voice of Success<span class="text-[--brass]">.</span></h3>
            </div>

            <?php if (count($test_featured) > 0): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7">
                <?php foreach ($test_featured as $t): ?>
                <div class="reveal"><?= render_testimonial_card($t) ?></div>
                <?php endforeach; ?>
            </div>
            <?php if (count($test_more) > 0): ?>
            <div class="reveal text-center mt-12">
                <button type="button" data-modal-open="modal-voices" class="inline-flex items-center gap-3 px-7 py-3.5 bg-[--brass] text-[--ink] rounded-xl font-semibold text-sm hover:bg-[--brass-2] transition-colors">
                    Read <?= count($test_more) ?> more stor<?= count($test_more) === 1 ? 'y' : 'ies' ?>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="py-14 text-center bg-white/5 rounded-[2rem] border border-dashed border-white/15">
                <p class="text-slate-400 font-semibold uppercase tracking-widest text-xs">Awaiting student experiences&hellip;</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===================== ANNOUNCEMENTS ===================== -->
    <section id="announcements" class="py-24 md:py-28 px-6 max-w-7xl mx-auto">
        <div class="reveal flex items-end justify-between gap-6 mb-12 flex-wrap">
            <div>
                <span class="tab-label text-[--clay] mb-3">Dossier &middot; Bulletin</span>
                <h3 class="font-display text-4xl font-[800] tracking-tight text-[--ink]">Recent Announcements<span class="text-[--brass]">.</span></h3>
            </div>
            <?php if (count($ann_more) > 0): ?>
            <button type="button" data-modal-open="modal-announcements" class="font-mono text-xs font-bold uppercase tracking-[.15em] text-[--clay] hover:text-[--ink] transition-colors inline-flex items-center gap-2">
                See all <?= count($ann_all) ?> <span aria-hidden="true">&rarr;</span>
            </button>
            <?php endif; ?>
        </div>

        <?php if (count($ann_featured) > 0): ?>
        <div class="grid md:grid-cols-3 gap-7">
            <?php foreach ($ann_featured as $a): ?>
            <div class="reveal"><?= render_announcement_card($a) ?></div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="py-14 text-center bg-white rounded-[2rem] border border-dashed border-[--ink]/15">
            <p class="text-[--slate] font-semibold uppercase tracking-widest text-sm">No announcements yet.</p>
        </div>
        <?php endif; ?>
    </section>

    <!-- ===================== LEARNING MATERIALS ===================== -->
    <section id="posts" class="py-24 md:py-28 px-6 max-w-7xl mx-auto border-t border-[--ink]/10">
        <div class="reveal flex items-end justify-between gap-6 mb-12 flex-wrap">
            <div>
                <span class="tab-label text-[--clay] mb-3">Dossier &middot; Library</span>
                <h3 class="font-display text-4xl font-[800] tracking-tight text-[--ink]">Learning Materials<span class="text-[--brass]">.</span></h3>
            </div>
            <?php if (count($posts_more) > 0): ?>
            <button type="button" data-modal-open="modal-posts" class="font-mono text-xs font-bold uppercase tracking-[.15em] text-[--clay] hover:text-[--ink] transition-colors inline-flex items-center gap-2">
                Browse full library <span aria-hidden="true">&rarr;</span>
            </button>
            <?php endif; ?>
        </div>

        <?php if (count($posts_featured) > 0): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7">
            <?php foreach ($posts_featured as $p): ?>
            <div class="reveal"><?= render_post_card($p) ?></div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="py-14 text-center bg-white rounded-[2rem] border border-dashed border-[--ink]/15">
            <p class="text-[--slate] font-semibold uppercase tracking-widest text-sm">No resources uploaded yet.</p>
        </div>
        <?php endif; ?>
    </section>

    <!-- ===================== HALL OF FAME STRIP ===================== -->
    <?php if (count($passers_all) > 0): ?>
    <section id="passers" class="py-24 bg-[--parchment-2] px-6">
        <div class="max-w-7xl mx-auto">
            <div class="reveal text-center mb-14">
                <span class="tab-label text-[--clay] justify-center mb-3">Dossier &middot; Full Roster</span>
                <h3 class="font-display text-4xl font-[800] tracking-tight text-[--ink]">The Hall of Fame<span class="text-[--brass]">.</span></h3>
                <p class="text-[--slate] max-w-2xl mx-auto mt-4">Celebrating every C-Familia student who successfully conquered their board exams.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-5">
                <?php foreach ($passers_all as $passer): $pPath = photo_path($passer['photo']); ?>
                <div class="reveal p-5 bg-white rounded-[1.75rem] border border-[--ink]/8 hover:border-[--brass]/50 transition-colors text-center">
                    <img src="<?= htmlspecialchars($pPath) ?>" loading="lazy" class="w-16 h-16 rounded-full mx-auto mb-3 object-cover border-4 border-[--parchment]" alt="<?= htmlspecialchars($passer['name']) ?>">
                    <h5 class="font-bold text-[--ink] text-sm leading-tight mb-1"><?= htmlspecialchars($passer['name']) ?></h5>
                    <p class="font-mono text-[9px] text-[--clay] font-bold uppercase tracking-wider mb-1.5"><?= htmlspecialchars($passer['program']) ?></p>
                    <span class="font-mono text-[11px] font-bold text-[--ink]"><?= number_format($passer['rating'], 2) ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section id="contact" class="py-24 px-6 bg-[--parchment] border-t border-[--ink]/10">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <span class="tab-label text-[--clay] mb-3">Visit Us</span>
                <h3 class="font-display text-4xl font-[800] mb-8 tracking-tight text-[--ink]">Our Branches<span class="text-[--brass]">.</span></h3>
                <div class="space-y-7">
                    <div class="flex items-start gap-5">
                        <div class="w-13 h-13 w-[3.25rem] h-[3.25rem] bg-[--ink] text-[--brass-2] rounded-2xl flex items-center justify-center text-xl flex-shrink-0">📍</div>
                        <div>
                            <p class="font-mono font-bold text-[--ink] uppercase text-xs tracking-widest mb-1">Ozamiz Main</p>
                            <p class="text-[--slate] font-medium">Ozamiz City, Philippines, 7200</p>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-widest mt-2 border border-emerald-100">&#9679; Always Open</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-5">
                        <div class="w-[3.25rem] h-[3.25rem] bg-[--clay] text-white rounded-2xl flex items-center justify-center text-xl flex-shrink-0">📍</div>
                        <div>
                            <p class="font-mono font-bold text-[--ink] uppercase text-xs tracking-widest mb-1">Oroquieta Branch</p>
                            <p class="text-[--slate] font-medium">Oroquieta City, Misamis Occidental</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="reveal bg-white p-9 md:p-10 rounded-[2.5rem] border border-[--ink]/8 shadow-sm">
                <h3 class="font-display text-2xl font-bold mb-8 text-[--ink]">Get in Touch</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-5 p-5 bg-[--parchment] rounded-2xl transition-transform hover:translate-x-1.5">
                        <span class="text-2xl">📞</span>
                        <div>
                            <p class="font-mono text-[10px] font-bold uppercase text-[--slate] tracking-widest">Phone</p>
                            <p class="font-bold text-[--ink]">0910 167 6805</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-[--parchment] rounded-2xl transition-transform hover:translate-x-1.5">
                        <span class="text-2xl">✉️</span>
                        <div>
                            <p class="font-mono text-[10px] font-bold uppercase text-[--slate] tracking-widest">Email</p>
                            <p class="font-bold text-[--ink]">shielamariscuevas@gmail.com</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-[--parchment] rounded-2xl transition-transform hover:translate-x-1.5">
                        <span class="text-2xl">💬</span>
                        <div>
                            <p class="font-mono text-[10px] font-bold uppercase text-[--slate] tracking-widest">Messenger</p>
                            <p class="font-bold text-[--ink]">C-Familia Tutorial Services</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<footer class="bg-[--ink] pt-20 pb-10 px-6 text-white overflow-hidden relative">
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[--brass] via-[--brass-2] to-[--clay]"></div>
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-10 mb-16 relative">
        <div class="flex items-center gap-4">
            <span class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-[--brass] block">
                <img src="cuevaslogo.jpg" alt="" class="w-full h-full object-cover">
            </span>
            <span class="font-display text-3xl font-[900] tracking-tighter">C-Familia<span class="text-[--brass]">.</span></span>
        </div>
        <div class="font-mono text-slate-500 text-xs font-bold uppercase tracking-[.2em] text-center md:text-left">
            Empowering the future of Criminologists since 2024
        </div>
    </div>
    <div class="max-w-7xl mx-auto text-center text-slate-600 text-[10px] border-t border-white/10 pt-10 uppercase tracking-[.3em] font-bold">
        &copy; <?= date("Y") ?> C-Familia Tutorial Services &middot; Registered Educational Provider
    </div>
</footer>

<!-- ===================== MODALS ===================== -->

<!-- Top performance modal -->
<div id="modal-top" class="modal fixed inset-0 z-[100] items-start justify-center overflow-y-auto p-4 md:p-8" data-modal>
    <div class="modal-backdrop fixed inset-0 bg-[--ink]/85 backdrop-blur-sm" data-modal-close></div>
    <div class="modal-panel relative w-full max-w-5xl my-8 bg-[--parchment] rounded-[2rem] shadow-2xl">
        <div class="sticky top-0 flex items-center justify-between px-8 py-6 bg-[--parchment] border-b border-[--ink]/10 rounded-t-[2rem]">
            <div>
                <span class="tab-label text-[--clay] mb-1">Full Roster</span>
                <h3 class="font-display text-2xl font-bold text-[--ink]">All Elite Achievers</h3>
            </div>
            <button type="button" data-modal-close aria-label="Close" class="w-10 h-10 flex items-center justify-center rounded-xl border border-[--ink]/15 hover:bg-[--ink] hover:text-white transition-colors">
                <svg width="16" height="16" viewBox="0 0 16 16"><path d="M1 1l14 14M15 1L1 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php foreach ($top_all as $p): ?>
            <?= render_passer_card($p) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Voice of success modal -->
<div id="modal-voices" class="modal fixed inset-0 z-[100] items-start justify-center overflow-y-auto p-4 md:p-8" data-modal>
    <div class="modal-backdrop fixed inset-0 bg-[--ink]/85 backdrop-blur-sm" data-modal-close></div>
    <div class="modal-panel relative w-full max-w-5xl my-8 bg-[--ink] rounded-[2rem] shadow-2xl">
        <div class="sticky top-0 flex items-center justify-between px-8 py-6 bg-[--ink] border-b border-white/10 rounded-t-[2rem]">
            <div>
                <span class="tab-label text-[--brass-2] mb-1">Full Testimony</span>
                <h3 class="font-display text-2xl font-bold text-white">All Student Voices</h3>
            </div>
            <button type="button" data-modal-close aria-label="Close" class="w-10 h-10 flex items-center justify-center rounded-xl border border-white/15 text-white hover:bg-[--brass] hover:text-[--ink] transition-colors">
                <svg width="16" height="16" viewBox="0 0 16 16"><path d="M1 1l14 14M15 1L1 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div class="p-8 grid md:grid-cols-2 gap-6">
            <?php foreach ($test_all as $t): ?>
            <?= render_testimonial_card($t) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Announcements modal -->
<div id="modal-announcements" class="modal fixed inset-0 z-[100] items-start justify-center overflow-y-auto p-4 md:p-8" data-modal>
    <div class="modal-backdrop fixed inset-0 bg-[--ink]/85 backdrop-blur-sm" data-modal-close></div>
    <div class="modal-panel relative w-full max-w-4xl my-8 bg-[--parchment] rounded-[2rem] shadow-2xl">
        <div class="sticky top-0 flex items-center justify-between px-8 py-6 bg-[--parchment] border-b border-[--ink]/10 rounded-t-[2rem]">
            <div>
                <span class="tab-label text-[--clay] mb-1">Full Bulletin</span>
                <h3 class="font-display text-2xl font-bold text-[--ink]">All Announcements</h3>
            </div>
            <button type="button" data-modal-close aria-label="Close" class="w-10 h-10 flex items-center justify-center rounded-xl border border-[--ink]/15 hover:bg-[--ink] hover:text-white transition-colors">
                <svg width="16" height="16" viewBox="0 0 16 16"><path d="M1 1l14 14M15 1L1 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div class="p-8 grid md:grid-cols-2 gap-6">
            <?php foreach ($ann_all as $a): ?>
            <?= render_announcement_card($a) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Learning materials modal -->
<div id="modal-posts" class="modal fixed inset-0 z-[100] items-start justify-center overflow-y-auto p-4 md:p-8" data-modal>
    <div class="modal-backdrop fixed inset-0 bg-[--ink]/85 backdrop-blur-sm" data-modal-close></div>
    <div class="modal-panel relative w-full max-w-5xl my-8 bg-[--parchment] rounded-[2rem] shadow-2xl">
        <div class="sticky top-0 flex items-center justify-between px-8 py-6 bg-[--parchment] border-b border-[--ink]/10 rounded-t-[2rem]">
            <div>
                <span class="tab-label text-[--clay] mb-1">Full Library</span>
                <h3 class="font-display text-2xl font-bold text-[--ink]">All Learning Materials</h3>
            </div>
            <button type="button" data-modal-close aria-label="Close" class="w-10 h-10 flex items-center justify-center rounded-xl border border-[--ink]/15 hover:bg-[--ink] hover:text-white transition-colors">
                <svg width="16" height="16" viewBox="0 0 16 16"><path d="M1 1l14 14M15 1L1 15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </button>
        </div>
        <div class="p-8 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($posts_all as $p): ?>
            <?= render_post_card($p) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function(){
    // ---- Mobile drawer ----
    var menuToggle = document.getElementById('menuToggle');
    var drawer = document.getElementById('drawer');
    var drawerBackdrop = document.getElementById('drawerBackdrop');
    var drawerClose = document.getElementById('drawerClose');

    function openDrawer(){
        drawer.classList.add('is-open');
        drawerBackdrop.classList.remove('hidden');
        menuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeDrawer(){
        drawer.classList.remove('is-open');
        drawerBackdrop.classList.add('hidden');
        menuToggle.setAttribute('aria-expanded', 'false');
        if (!document.querySelector('.modal.is-open')) document.body.style.overflow = '';
    }
    menuToggle.addEventListener('click', openDrawer);
    drawerClose.addEventListener('click', closeDrawer);
    drawerBackdrop.addEventListener('click', closeDrawer);
    document.querySelectorAll('.drawer-link').forEach(function(a){
        a.addEventListener('click', closeDrawer);
    });

    // ---- Modals ----
    function openModal(id){
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        var closeBtn = modal.querySelector('[data-modal-close]');
        if (closeBtn) closeBtn.focus();
    }
    function closeModal(modal){
        modal.classList.remove('is-open');
        if (!drawer.classList.contains('is-open')) document.body.style.overflow = '';
    }
    document.querySelectorAll('[data-modal-open]').forEach(function(btn){
        btn.addEventListener('click', function(){
            openModal(btn.getAttribute('data-modal-open'));
        });
    });
    document.querySelectorAll('[data-modal-close]').forEach(function(el){
        el.addEventListener('click', function(){
            closeModal(el.closest('[data-modal]'));
        });
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape'){
            document.querySelectorAll('.modal.is-open').forEach(closeModal);
            if (drawer.classList.contains('is-open')) closeDrawer();
        }
    });

    // ---- Scroll reveal ----
    if ('IntersectionObserver' in window){
        var io = new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
                if (entry.isIntersecting){
                    entry.target.classList.add('is-in');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        document.querySelectorAll('.reveal').forEach(function(el){ io.observe(el); });
    } else {
        document.querySelectorAll('.reveal').forEach(function(el){ el.classList.add('is-in'); });
    }
})();
</script>

</body>
</html>