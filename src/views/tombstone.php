<?php
$cat = CATEGORIES[$category] ?? CATEGORIES['rip'];
?>
<div class="tombstone" aria-hidden="true">
    <img class="tombstone__base" src="<?= asset('img/' . $cat['base'] . '.webp') ?>" alt="" loading="lazy">
    <?php if ($cat['icon']): ?>
        <img class="tombstone__icon" src="<?= asset('img/' . $cat['icon'] . '.webp') ?>" alt="" loading="lazy">
    <?php endif; ?>
</div>
