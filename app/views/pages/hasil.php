<?php
$best = $ranked[0] ?? null;
$canCalculateSaw = (bool) ($sawReadiness['ready'] ?? true);
$sawMessages = $sawReadiness['messages'] ?? [];
$sawWarnings = $sawReadiness['warnings'] ?? [];
?>
<form method="post" action="<?= url('saw/hitung') ?>" class="page-head" data-confirm="Data ranking lama akan dihitung ulang. Apakah Anda yakin ingin melanjutkan?" data-loading-text="Menghitung ranking SAW..."><?= csrf_field() ?><div><h1>Hasil Ranking SAW</h1><p class="muted">Hasil akhir perankingan menu berdasarkan metode SAW.</p></div><button class="btn btn-primary <?= $canCalculateSaw ? '' : 'is-disabled' ?>" type="submit" <?= $canCalculateSaw ? '' : 'disabled' ?>>Hitung SAW</button></form>
<?php if (!$canCalculateSaw): ?>
    <div class="spk-alert spk-alert-warning" style="margin-bottom:20px">
        <strong>SAW belum bisa dihitung.</strong>
        <p><?= e(implode(' ', $sawMessages)) ?></p>
    </div>
<?php endif; ?>
<?php if ($canCalculateSaw && $sawWarnings): ?>
    <div class="spk-alert spk-alert-warning" style="margin-bottom:20px">
        <strong>Hasil SAW perlu diperbarui.</strong>
        <p><?= e(implode(' ', $sawWarnings)) ?></p>
    </div>
<?php endif; ?>
<?php if (!$best || !$best['menu']): ?>
    <div class="card panel"><?php $title='Belum ada hasil ranking.'; $message='Silakan hitung SAW terlebih dahulu setelah data penilaian dan bobot AHP valid.'; require app_path('views/components/empty-state.php'); ?></div>
<?php else: ?>
<div class="notice success">
    <div style="font-size:44px">#1</div>
    <div><b>Rekomendasi Menu Terbaik</b><h1 style="margin:4px 0"><?= e($best['menu']['kode'] . ' - ' . $best['menu']['nama_menu']) ?></h1><p>Berdasarkan hasil SAW, menu dengan nilai Vi tertinggi menjadi rekomendasi utama.</p></div>
    <div style="margin-left:auto"><h1 class="text-green"><?= e(number_format((float) $best['value'], 3)) ?></h1><span class="badge green"><?= e($best['status']) ?></span></div>
</div>
<?php endif; ?>
<div class="grid-2" style="margin-top:20px">
    <div class="card panel"><h2>Top Ranking Menu</h2><?php if (!$ranked): ?><?php $title='Belum ada hasil ranking.'; $message='Silakan hitung SAW terlebih dahulu.'; require app_path('views/components/empty-state.php'); ?><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>Ranking</th><th>Kode</th><th>Nama Menu</th><th>Nilai Vi</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($ranked as $row): if (!$row['menu']) continue; ?><tr><td><strong><?= e($row['rank']) ?></strong></td><td><?= e($row['menu']['kode']) ?></td><td><?= e($row['menu']['nama_menu']) ?></td><td><strong><?= e(number_format($row['value'], 3)) ?></strong></td><td><span class="badge <?= $row['value'] >= 0.7 ? 'green' : 'orange' ?>"><?= e($row['status']) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?></div>
    <div class="card panel"><h2>Perbandingan Nilai Akhir (Vi)</h2><div class="bar-chart"><?php foreach (array_slice($ranked, 0, 6) as $row): ?><div class="chart-bar" style="height:<?= min(100, max(8, $row['value'] * 100)) ?>%"><span><?= e(number_format($row['value'], 3)) ?></span></div><?php endforeach; ?></div></div>
</div>
<div class="grid-2" style="margin-top:20px">
    <div class="card panel"><h2>Matriks Keputusan Ternormalisasi (Rij)</h2><div class="table-wrap"><table class="data-table"><thead><tr><th>Menu</th><?php foreach ($criteria as $criterion): ?><th><?= e($criterion['kode']) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ($menus as $menu): ?><tr><td><?= e($menu['kode']) ?></td><?php foreach ($criteria as $criterion): ?><td><?= e(number_format($normalized[$menu['id_menu']][$criterion['id_kriteria']] ?? 0, 3)) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div></div>
    <div class="card panel"><h2>Kontribusi Bobot x Rij Menu Terbaik</h2><?php if ($best && $best['menu']): foreach ($criteria as $criterion): $rij = $normalized[$best['menu']['id_menu']][$criterion['id_kriteria']] ?? 0; ?><p><?= e($criterion['kode'] . ' - ' . $criterion['nama']) ?> <strong style="float:right"><?= e(number_format($rij * $criterion['bobot'], 4)) ?></strong></p><?php endforeach; ?><hr><p>Jumlah Vi <strong class="text-green" style="float:right;font-size:24px"><?= e(number_format($best['value'], 3)) ?></strong></p><?php else: ?><p class="muted">Hitung SAW untuk melihat kontribusi bobot.</p><?php endif; ?><p><a class="btn btn-outline" href="<?= url('laporan/pdf') ?>" data-loading-link>Download PDF</a> <a class="btn btn-outline" href="<?= url('laporan/excel') ?>" data-loading-link>Download Excel</a></p></div>
</div>
