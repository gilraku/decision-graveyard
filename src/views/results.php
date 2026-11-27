<?php
$newId = $newId ?? null;
?>
<div id="results">
    <div class="grid" id="grid">
        <?php foreach ($items as $d): ?>
            <?= view('card', ['d' => $d, 'isNew' => $newId === (int) $d['id']]) ?>
        <?php endforeach; ?>
    </div>
    <?= view('empty', ['view' => $view, 'show' => count($items) === 0]) ?>
</div>
