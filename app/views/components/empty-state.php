<?php
$title = $title ?? 'Data belum tersedia.';
$message = $message ?? 'Silakan tambahkan data terlebih dahulu.';
$actionLabel = $actionLabel ?? null;
$actionUrl = $actionUrl ?? null;
?>
<div class="empty-state spk-empty-state">
    <div class="empty-state-icon" aria-hidden="true">∅</div>
    <h3><?= e($title) ?></h3>
    <p><?= e($message) ?></p>
    <?php if ($actionLabel && $actionUrl): ?>
        <a class="btn btn-primary" href="<?= e($actionUrl) ?>"><?= e($actionLabel) ?></a>
    <?php endif; ?>
</div>
