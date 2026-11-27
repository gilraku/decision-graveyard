<?php
$oob = $oob ?? false;
?>
<div class="empty" id="empty-state"<?= $show ? '' : ' hidden' ?><?= $oob ? ' hx-swap-oob="true"' : '' ?>>
    <img src="<?= asset('img/' . ($view === 'archive' ? 'lantern' : 'raven') . '.webp') ?>" alt="" width="110">
    <?php if ($view === 'archive'): ?>
        <p>Belum ada rencana yang dibangkitkan.<br>Klik <em>Bangkitkan</em> pada kartu di <a href="index.php?view=graveyard">halaman Kuburan</a> untuk menghidupkannya lagi.</p>
    <?php else: ?>
        <p>Belum ada yang dikubur.<br>Tulis rencana yang batal di form atas, lalu klik <em>Kuburkan</em>.</p>
    <?php endif; ?>
</div>
