<div class="grid-4">
    <div class="card metric"><div class="micon">M</div><div><h2><?= e((string) $stats['menu']) ?></h2><b>Total Menu</b><p class="muted">Data menu dalam sistem</p></div></div>
    <div class="card metric"><div class="micon">K</div><div><h2><?= e((string) $stats['kriteria']) ?></h2><b>Total Kriteria</b><p class="muted">Kriteria aktif dan nonaktif</p></div></div>
    <div class="card metric"><div class="micon">CR</div><div><h2 class="text-green"><?= e(number_format((float) ($stats['cr'] ?? 0), 3)) ?></h2><b>CR Terakhir</b><p class="muted">Konsistensi AHP</p></div></div>
    <div class="card metric"><div class="micon">Vi</div><div><h2 class="text-green"><?= e(number_format((float) ($stats['vi'] ?? 0), 3)) ?></h2><b>Nilai Tertinggi Vi</b><p class="muted">Menu terbaik terakhir</p></div></div>
</div>
<div class="card panel spk-flow" style="margin-top:20px">
    <h2>Alur Proses AHP - SAW</h2>
    <p class="muted">SAW dapat dihitung setelah penilaian lengkap dan AHP memiliki CR <= 0,1.</p>
    <div class="spk-flow-grid">
        <?php foreach ($process as $step): ?>
            <?php
            $statusClass = match ($step['status']) {
                'Selesai', 'Valid' => 'spk-status-success',
                'Tidak valid', 'Tidak Valid', 'Tidak Konsisten' => 'spk-status-error',
                'Terkunci' => 'spk-status-locked',
                default => 'spk-status-warning',
            };
            ?>
            <div class="spk-step">
                <span class="spk-status <?= e($statusClass) ?>"><?= e($step['status']) ?></span>
                <strong><?= e($step['name']) ?></strong>
                <p><?= e($step['detail']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="grid-2" style="margin-top:20px">
    <div class="card panel">
        <h2>Tren Nilai Rata-rata Menu</h2>
        <div class="bar-chart"><?php foreach ([58, 62, 65, 70, 74, 84] as $height): ?><div class="chart-bar" style="height:<?= $height ?>%"><span>0.<?= $height ?>0</span></div><?php endforeach; ?></div>
    </div>
    <div class="card panel">
        <h2>Distribusi Bobot Kriteria (AHP)</h2>
        <div class="grid-2"><div class="donut" style="width:190px;height:190px"></div><div>
            <?php foreach ($criteria as $criterion): ?><p><span class="badge <?= $criterion['jenis'] === 'cost' ? 'orange' : 'green' ?>"><?= e($criterion['nama']) ?></span> <strong><?= e(number_format((float) $criterion['bobot'], 3)) ?></strong></p><?php endforeach; ?>
        </div></div>
        <div class="notice">CR terakhir <?= e(number_format((float) ($stats['cr'] ?? 0), 4)) ?>. Nilai <= 0.1 berarti konsisten.</div>
    </div>
</div>
<div class="grid-2" style="margin-top:20px">
    <div class="card panel">
        <h2>Top Ranking Menu Bulan Ini</h2>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Ranking</th><th>Kode</th><th>Nama Menu</th><th>Nilai Vi</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($top as $row): ?><tr><td><strong><?= e($row['ranking']) ?></strong></td><td><?= e($row['kode']) ?></td><td><?= e($row['nama_menu']) ?></td><td><strong><?= e(number_format((float) $row['nilai_vi'], 3)) ?></strong></td><td><span class="badge green">Rekomendasi</span></td></tr><?php endforeach; ?>
            <?php if (!$top): ?><tr><td colspan="5">Belum ada hasil SAW. Jalankan perhitungan pada halaman Hasil Ranking.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <div class="card panel">
        <h2>Tahapan Analisis</h2>
        <div class="progress-line"><?php foreach (['Data','AHP','CR','SAW','Laporan'] as $step): ?><div class="pstep"><div class="pcircle"><?= e($step === 'CR' ? '<=0,1' : 'OK') ?></div><b><?= e($step) ?></b></div><?php endforeach; ?></div>
        <h2>Aktivitas Terbaru</h2>
        <?php foreach ($activities as $activity): ?><p><?= e(date('H:i', strtotime($activity['created_at']))) ?> - <?= e($activity['aksi']) ?> <span class="muted"><?= e($activity['modul']) ?></span></p><?php endforeach; ?>
        <?php if (!$activities): ?><p class="muted">Belum ada aktivitas.</p><?php endif; ?>
    </div>
</div>
