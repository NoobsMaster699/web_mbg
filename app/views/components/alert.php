<?php
$icons = [
    'success' => '&#10003;',
    'error' => '!',
    'warning' => '!',
    'info' => 'i',
];
?>
<div class="alert alert-<?= e($type) ?>" role="alert" data-alert>
    <span class="alert-icon" aria-hidden="true"><?= $icons[$type] ?? 'i' ?></span>
    <div class="alert-body">
        <strong><?= e($title) ?></strong>
        <p><?= e($message) ?></p>
    </div>
    <button class="alert-close" type="button" aria-label="Tutup alert" data-alert-close>&times;</button>
</div>
