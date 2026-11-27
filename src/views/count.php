<?php
$oob = $oob ?? false;
?>
<span class="badge" id="count-badge"<?= $oob ? ' hx-swap-oob="true"' : '' ?>><?= $count ?> keputusan</span>
