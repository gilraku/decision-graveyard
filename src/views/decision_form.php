<?php
$oob    = $oob ?? false;
$errors = $errors ?? [];
$isEdit = $mode === 'edit';
$p      = $isEdit ? 'edit-' : '';
$ym = fn(string $k) => !empty($data[$k]) ? substr($data[$k], 0, 7) : date('Y-m');
$oldestYear = min((int) substr($ym('born_on'), 0, 4), (int) date('Y') - 10);
$years = range((int) date('Y'), $oldestYear);
$err = fn(string $f) => isset($errors[$f])
    ? '<span class="field__error" id="' . $p . $f . '-err">' . e($errors[$f]) . '</span>' : '';
$aria = fn(string $f) => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $p . $f . '-err"' : '';
?>
<form method="post" action="decisions.php" id="<?= $isEdit ? 'edit-form' : 'bury-form' ?>"
      class="<?= $isEdit ? 'edit-form' : 'bury-form' ?>"<?= $oob ? ' hx-swap-oob="true"' : '' ?>
      hx-post="decisions.php"
      <?php if ($isEdit): ?>hx-target="#card-<?= (int) $id ?>" hx-swap="outerHTML"
      <?php else: ?>hx-target="#results" hx-swap="outerHTML" hx-include="#sort-select"<?php endif; ?>>
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int) $id ?>"><?php endif; ?>

    <div class="field field--title">
        <label for="<?= $p ?>title">Keputusan atau rencana</label>
        <input type="text" id="<?= $p ?>title" name="title" maxlength="100" required<?= $isEdit ? ' autofocus' : '' ?>
               placeholder="cth. Belajar Rust" value="<?= e($data['title'] ?? '') ?>"<?= $aria('title') ?>>
        <?= $err('title') ?>
    </div>

    <div class="field field--cause">
        <label for="<?= $p ?>cause">Penyebab kematian</label>
        <input type="text" id="<?= $p ?>cause" name="cause_of_death" maxlength="255" required
               placeholder="cth. Kebanyakan side project" value="<?= e($data['cause_of_death'] ?? '') ?>"<?= $aria('cause_of_death') ?>>
        <?= $err('cause_of_death') ?>
    </div>

    <div class="field field--category">
        <label for="<?= $p ?>category">Jenis nisan</label>
        <select id="<?= $p ?>category" name="category">
            <?php foreach (CATEGORIES as $key => $cat): ?>
                <option value="<?= e($key) ?>"<?= ($data['category'] ?? 'rip') === $key ? ' selected' : '' ?>><?= e($cat['label']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php foreach (['born' => 'Bulan mulai', 'died' => 'Bulan berhenti'] as $f => $legend):
        [$selY, $selM] = [(int) substr($ym($f . '_on'), 0, 4), substr($ym($f . '_on'), 5, 2)]; ?>
    <fieldset class="field field--<?= $f ?>">
        <legend><?= $legend ?></legend>
        <div class="born-pair">
            <select id="<?= $p . $f ?>" name="<?= $f ?>_month" aria-label="Bulan"<?= $aria($f . '_on') ?>>
                <?php foreach (BULAN as $i => $nama): $val = sprintf('%02d', $i + 1); ?>
                    <option value="<?= $val ?>"<?= $selM === $val ? ' selected' : '' ?>><?= $nama ?></option>
                <?php endforeach; ?>
            </select>
            <select id="<?= $p . $f ?>-year" name="<?= $f ?>_year" aria-label="Tahun">
                <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>"<?= $selY === $y ? ' selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?= $err($f . '_on') ?>
    </fieldset>
    <?php endforeach; ?>

    <div class="field field--submit">
        <?php if ($isEdit): ?>
            <button type="button" class="btn btn--ghost" data-close-modal>Batal</button>
            <button type="submit" class="btn">Simpan nisan</button>
        <?php else: ?>
            <button type="submit" class="btn">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4l6 6"/><path d="M17 7l-7.5 7.5"/><path d="M9.5 14.5L5 19c-1 1-2.5-.5-1.5-1.5L8 13z" fill="currentColor"/></svg>
                Kuburkan
            </button>
        <?php endif; ?>
    </div>
</form>
