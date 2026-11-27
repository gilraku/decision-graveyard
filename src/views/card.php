<?php
$isNew      = $isNew ?? false;
$isBuried   = $d['status'] === 'buried';
$titleId    = 'card-title-' . (int) $d['id'];
?>
<article class="card<?= $isNew ? ' is-new' : '' ?><?= $isBuried ? '' : ' is-alive' ?>"
         id="card-<?= (int) $d['id'] ?>" aria-labelledby="<?= $titleId ?>">
    <?= view('tombstone', ['category' => $d['category']]) ?>

    <div class="card__body">
        <h3 class="card__title" id="<?= $titleId ?>"><?= e($d['title']) ?></h3>
        <p class="card__dates">
            <?= month_label($d['born_on']) ?> — <?= month_label($d['died_on']) ?>
            <?php if (!$isBuried): ?><span class="tag">hidup lagi</span><?php endif; ?>
        </p>

        <p class="card__cause">
            <strong>Penyebab kematian:</strong>
            <span><?= e($d['cause_of_death']) ?></span>
        </p>
    </div>

    <div class="card__actions">
            <?php if ($isBuried): ?>
                <form method="post" action="decisions.php" class="inline"
                      hx-post="decisions.php" hx-target="closest .card" hx-swap="delete swap:450ms">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="resurrect">
                    <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                    <button type="submit" class="link-btn" title="Bangkitkan lagi">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.4-5.7"/><path d="M20 3v5h-5"/></svg>
                        Bangkitkan
                    </button>
                </form>
            <?php else: ?>
                <form method="post" action="decisions.php" class="inline"
                      hx-post="decisions.php" hx-target="closest .card" hx-swap="delete swap:450ms">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="rebury">
                    <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                    <button type="submit" class="link-btn" title="Kubur lagi">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h16"/><path d="M7 20v-9a5 5 0 0 1 10 0v9"/><path d="M12 9v6M9.5 11.5h5"/></svg>
                        Kubur lagi
                    </button>
                </form>
            <?php endif; ?>

            <span class="sep" aria-hidden="true"></span>

            <a class="link-btn" href="index.php?<?= e(http_build_query(['view' => $isBuried ? 'graveyard' : 'archive', 'edit' => (int) $d['id']])) ?>"
               hx-get="decisions.php?action=edit&amp;id=<?= (int) $d['id'] ?>" hx-target="#modal-body" hx-swap="innerHTML"
               title="Ubah tulisan di nisan">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>
                Ubah
            </a>

            <span class="sep" aria-hidden="true"></span>

            <form method="post" action="decisions.php" class="inline"
                  hx-post="decisions.php" hx-target="closest .card" hx-swap="delete swap:450ms"
                  hx-confirm="“<?= e($d['title']) ?>” akan dihapus permanen dan tidak bisa dibangkitkan lagi.">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                <button type="submit" class="link-btn link-btn--danger" title="Hapus permanen">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/></svg>
                    Lupakan
                </button>
            </form>
    </div>
</article>
