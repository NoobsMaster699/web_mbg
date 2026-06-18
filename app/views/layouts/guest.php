<?php require app_path('views/layouts/header.php'); ?>
<?php $flashMessage = consume_flash_message(); ?>
<?php if ($flashMessage): ?><div class="floating-flash"><?php render_alert($flashMessage); ?></div><?php endif; ?>
<?= $content ?>
<div class="toast-container" id="toast-container" aria-live="polite" aria-atomic="true"></div>
<div class="confirm-modal-backdrop" data-confirm-backdrop hidden></div>
<div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title" data-confirm-modal hidden>
    <div class="confirm-modal-card">
        <div class="confirm-modal-icon" aria-hidden="true">!</div>
        <div>
            <h2 id="confirm-modal-title">Konfirmasi Aksi</h2>
            <p data-confirm-message>Data yang sudah diproses mungkin akan berubah. Apakah Anda yakin ingin melanjutkan?</p>
        </div>
        <div class="confirm-modal-actions">
            <button class="btn btn-outline" type="button" data-confirm-cancel>Batal</button>
            <button class="btn btn-primary danger-action" type="button" data-confirm-accept>Ya, Lanjutkan</button>
        </div>
    </div>
</div>
<?php if ($flashMessage): ?>
<script type="application/json" id="flash-message-json"><?= json_encode($flashMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<?php endif; ?>
<script src="<?= versioned_asset('js/app.js') ?>"></script>
</body>
</html>
