<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$view = $_GET['view'] ?? 'graveyard';
$view = in_array($view, ['graveyard', 'archive', 'about'], true) ? $view : 'graveyard';
$sort = $_GET['sort'] ?? 'latest';
$sort = array_key_exists($sort, DecisionRepository::SORTS) ? $sort : 'latest';

$status = $view === 'archive' ? 'resurrected' : 'buried';
$items  = $view === 'about' ? [] : $repo->all($status, $sort);
$count  = $view === 'about' ? 0 : count($items);

$editItem = isset($_GET['edit']) ? $repo->find((int) $_GET['edit']) : null;

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$nav = ['graveyard' => 'Kuburan', 'archive' => 'Hidup lagi', 'about' => 'Tentang'];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Decision Graveyard</title>
    <meta name="htmx-config" content='{"noSwap":[204,304,"4xx","5xx"]}'>
    <meta name="description" content="Tempat istirahat rencana yang hampir jadi.">
    <link rel="icon" href="<?= asset('img/tombstone-rip.webp') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400&family=Special+Elite&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script src="<?= asset('js/htmx.min.js') ?>" defer></script>
    <script src="<?= asset('js/app.js') ?>" defer></script>
</head>
<body hx-headers:inherited='{"X-CSRF-Token": "<?= e(csrf_token()) ?>"}'>

<header class="site-header">
    <div class="wrap site-header__inner">
        <a class="brand" href="index.php">
            <img class="brand__stone" src="<?= asset('img/tombstone-rip.webp') ?>" alt="">
            <span class="brand__text">
                <span class="brand__title">Decision Graveyard</span>
                <img class="brand__underline" src="<?= asset('img/underline-red.webp') ?>" alt="">
                <span class="brand__tagline">Tempat istirahat rencana yang hampir jadi.</span>
            </span>
        </a>

        <nav class="nav" aria-label="Utama">
            <?php foreach ($nav as $key => $label): ?>
                <a href="index.php?view=<?= $key ?>"<?= $view === $key ? ' class="is-active" aria-current="page"' : '' ?>><?= $label ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="scene" aria-hidden="true">
            <img class="scene__clouds" src="<?= asset('img/clouds.webp') ?>" alt="">
            <div class="scene__art">
                <img class="scene__hill" src="<?= asset('img/graveyard-scene.webp') ?>" alt="">
                <img class="scene__cat" src="<?= asset('img/cat.webp') ?>" alt="">
            </div>
        </div>
    </div>
</header>

<main class="wrap">

<?php if ($view === 'about'): ?>

    <section class="about">
        <h1 class="section-title">Tentang</h1>
        <div class="about__grid">
            <div class="about__text">
                <p>Setiap orang punya kuburan kecil berisi rencana yang hampir jadi: kursus yang berhenti di video ketiga,
                   langganan gym yang cuma dipakai dua kali, startup yang mati di tahap nama domain.</p>
                <p><strong>Decision Graveyard</strong> adalah tempat untuk mengubur keputusan itu dengan layak,
                   lengkap dengan penyebab kematiannya. Menulisnya membantu kita jujur kenapa sesuatu gagal,
                   dan kadang, sesuatu pantas dibangkitkan lagi.</p>
                <ul class="about__list">
                    <li><b>Kuburkan</b> menyimpan keputusan baru ke database.</li>
                    <li><b>Kuburan</b> menampilkan semua yang sudah dikubur.</li>
                    <li><b>Ubah</b> mengubah tulisan di nisan.</li>
                    <li><b>Bangkitkan</b> memindahkannya ke halaman Hidup lagi, tanda rencana itu dijalankan kembali.</li>
                    <li><b>Lupakan</b> menghapusnya selamanya.</li>
                </ul>
                <p class="muted">Dibangun dengan HTML5, CSS, JavaScript, HTMX, PHP native, dan PostgreSQL.</p>
            </div>
            <img class="about__art" src="<?= asset('img/shovel.webp') ?>" alt="">
        </div>
    </section>

<?php else: ?>

    <?php if ($view === 'graveyard'): ?>
        <section class="bury" aria-labelledby="bury-title">
            <h2 class="section-title" id="bury-title">Kuburkan keputusan baru</h2>
            <div class="panel">
                <?= view('decision_form', ['mode' => 'create', 'data' => []]) ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="yard" aria-labelledby="yard-title">
        <div class="yard__head">
            <h1 class="section-title" id="yard-title"><?= $view === 'archive' ? 'Bangkit dari kubur' : 'Di pemakaman' ?></h1>
            <?= view('count', ['count' => $count]) ?>

            <label class="sort">
                <span>Urutkan:</span>
                <select id="sort-select" name="sort"
                        hx-get="decisions.php?action=list&amp;view=<?= $view ?>" hx-target="#results" hx-swap="outerHTML"
                        onchange="if(!window.htmx){location='index.php?view=<?= $view ?>&sort='+this.value}">
                    <?php foreach (DecisionRepository::SORTS as $key => $s): ?>
                        <option value="<?= $key ?>"<?= $sort === $key ? ' selected' : '' ?>><?= e($s['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <?= view('results', ['items' => $items, 'view' => $view]) ?>
    </section>

<?php endif; ?>
</main>

<footer class="site-footer">
    <div class="site-footer__ground" aria-hidden="true"></div>
    <p>Di sini terbaring setiap “nanti aja”.</p>
</footer>

<dialog class="modal" id="modal" aria-labelledby="modal-title" closedby="any"<?= $editItem ? ' open data-server-open' : '' ?>>
    <button class="modal__close" type="button" data-close-modal aria-label="Tutup">&times;</button>
    <div id="modal-body">
        <?php if ($editItem): ?>
            <h1 class="modal__title" id="modal-title">Ubah nisan</h1>
            <?= view('decision_form', ['mode' => 'edit', 'data' => $editItem, 'id' => (int) $editItem['id']]) ?>
        <?php endif; ?>
    </div>
</dialog>

<dialog class="modal modal--confirm" id="confirm" aria-labelledby="confirm-title" aria-describedby="confirm-text" closedby="any">
    <img src="<?= asset('img/skull.webp') ?>" alt="" class="confirm__art">
    <h1 class="modal__title" id="confirm-title">Lupakan selamanya?</h1>
    <p id="confirm-text"></p>
    <form method="dialog" class="confirm__actions">
        <button class="btn btn--ghost" value="cancel" autofocus>Biarkan terkubur</button>
        <button class="btn btn--danger" value="ok">Lupakan</button>
    </form>
</dialog>

<div class="toast" id="toast" popover="manual" aria-hidden="true"></div>
<div class="visually-hidden" id="announcer" role="status" aria-live="polite" data-flash="<?= $flash ? e($flash) : '' ?>"></div>

</body>
</html>
