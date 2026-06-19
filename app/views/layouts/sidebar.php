<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$items = [
    '/dashboard' => 'Dashboard',
    '/kriteria' => 'Data Kriteria',
    '/menu' => 'Data Menu',
    '/penilaian' => 'Penilaian',
    '/ahp' => 'Perbandingan AHP',
    '/hasil' => 'Hasil Ranking SAW',
    '/laporan' => 'Laporan',
    '/riwayat' => 'Riwayat Aktivitas',
];
?>
<aside class="sidebar">
    <a class="sidebar-logo" href="<?= url('dashboard') ?>"><img src="<?= asset('images/ui/logo-spk-mbg.svg') ?>" alt="SPK MBG"></a>
    <nav class="side-menu">
        <?php foreach ($items as $href => $label): ?>
            <a href="<?= e($href) ?>" class="<?= str_starts_with($path, $href) ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="side-user">
        <div class="avatar">A</div>
        <div><strong><?= e(current_user()['username'] ?? 'Admin') ?></strong><br><span class="muted" style="color:rgba(255,255,255,.7)">Administrator</span></div>
        <form method="post" action="<?= url('logout') ?>" style="margin-left:auto" data-confirm="Apakah Anda yakin ingin logout dari sistem?" data-loading-text="Keluar...">
            <?= csrf_field() ?>
            <button class="btn btn-soft" type="submit" style="padding:8px 10px">Keluar</button>
        </form>
    </div>
</aside>
