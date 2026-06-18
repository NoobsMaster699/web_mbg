<?php
$oldInput = old_input_data();
$postedScores = is_array($oldInput['nilai'] ?? null) ? $oldInput['nilai'] : [];
$hasPostedScores = array_key_exists('nilai', $oldInput);
$criteriaCount = count($criteria);
$menuCount = count($menus);
$totalNeeded = $menuCount * $criteriaCount;
$savedValueCount = 0;

foreach ($menus as $menu) {
    foreach ($criteria as $criterion) {
        if (array_key_exists($menu['id_menu'], $scores) && array_key_exists($criterion['id_kriteria'], $scores[$menu['id_menu']])) {
            $savedValueCount++;
        }
    }
}

$formatScore = function (mixed $value): string {
    if ($value === null || $value === '') {
        return '';
    }

    return number_format((float) $value, 3, '.', '');
};

$formatWeight = fn(mixed $value): string => number_format((float) $value, 3, '.', '');
$formatPercent = fn(mixed $value): string => number_format((float) $value * 100, 1, '.', '');
$fieldErrorId = fn(string $field): string => 'error-' . str_replace(['[', ']', '.', ' '], '-', $field);

$valueFor = function (int $menuId, int $criterionId) use ($hasPostedScores, $postedScores, $scores, $formatScore): string {
    if ($hasPostedScores) {
        return isset($postedScores[$menuId]) && array_key_exists($criterionId, $postedScores[$menuId])
            ? trim((string) $postedScores[$menuId][$criterionId])
            : '';
    }

    return isset($scores[$menuId]) && array_key_exists($criterionId, $scores[$menuId])
        ? $formatScore($scores[$menuId][$criterionId])
        : '';
};

$isValidScore = function (string $value): bool {
    if ($value === '') {
        return false;
    }

    return is_numeric($value)
        && (float) $value >= 0
        && (float) $value <= 1
        && preg_match('/^\d+(?:\.\d{1,3})?$/', $value);
};

$rows = [];
$summary = ['valid' => 0, 'incomplete' => 0, 'invalid' => 0];

foreach ($menus as $menu) {
    $menuId = (int) $menu['id_menu'];
    $values = [];
    $sum = 0.0;
    $hasEmpty = false;
    $hasInvalid = false;
    $hasPositiveValue = false;
    $validValueCount = 0;

    foreach ($criteria as $criterion) {
        $criterionId = (int) $criterion['id_kriteria'];
        $value = $valueFor($menuId, $criterionId);
        $values[$criterionId] = $value;

        if ($value === '') {
            $hasEmpty = true;
            continue;
        }

        if (!$isValidScore($value)) {
            $hasInvalid = true;
            continue;
        }

        $numericValue = (float) $value;
        $sum += $numericValue;
        $validValueCount++;

        if ($numericValue > 0) {
            $hasPositiveValue = true;
        }
    }

    if ($hasInvalid) {
        $status = 'invalid';
    } elseif ($hasEmpty || !$hasPositiveValue || $validValueCount < $criteriaCount) {
        $status = 'incomplete';
    } else {
        $status = 'valid';
    }

    $summary[$status]++;
    $rows[$menuId] = [
        'values' => $values,
        'status' => $status,
        'average' => $validValueCount === $criteriaCount ? number_format($sum / max($criteriaCount, 1), 3, '.', '') : '-',
        'row_error' => field_error('row.' . $menuId),
    ];
}

$progress = $menuCount > 0 ? (int) round(($summary['valid'] / $menuCount) * 100) : 0;
$globalStatus = 'success';
$globalMessage = 'Semua baris valid. Penilaian siap disimpan dan digunakan untuk proses SAW.';
if ($summary['invalid'] > 0) {
    $globalStatus = 'error';
    $globalMessage = 'Terdapat nilai tidak valid. Periksa kembali nilai penilaian.';
} elseif ($summary['incomplete'] > 0) {
    $globalStatus = 'warning';
    $globalMessage = 'Masih ada menu yang belum lengkap dinilai. Lengkapi nilai C1-C5 sebelum menyimpan.';
}
$canSubmit = $menuCount > 0 && $criteriaCount > 0 && $summary['valid'] === $menuCount;
$statusLabels = [
    'valid' => ['class' => 'spk-badge-valid', 'text' => 'Valid'],
    'incomplete' => ['class' => 'spk-badge-warning', 'text' => 'Belum lengkap'],
    'invalid' => ['class' => 'spk-badge-error', 'text' => 'Tidak valid'],
];
?>

<div class="page-head">
    <div>
        <h1>Matriks Penilaian (Xij)</h1>
        <p class="muted">Masukkan nilai kinerja setiap menu terhadap kriteria. Skala 0.000 sampai 1.000.</p>
    </div>
    <button class="btn btn-outline" type="button" data-demo-alert>Import Excel</button>
</div>

<div class="notice info"><b>Informasi Penilaian</b> Nilai wajib numerik antara 0 sampai 1, maksimal 3 desimal. C2 adalah kriteria cost, nilai lebih kecil menunjukkan biaya lebih baik.</div>

<?php if (!$menus): ?>
    <div class="card panel" style="margin-top:20px"><?php $title='Belum ada data menu.'; $message='Tambahkan data menu terlebih dahulu.'; require app_path('views/components/empty-state.php'); ?></div>
<?php elseif (!$criteria): ?>
    <div class="card panel" style="margin-top:20px"><?php $title='Belum ada data kriteria.'; $message='Tambahkan data kriteria terlebih dahulu.'; require app_path('views/components/empty-state.php'); ?></div>
<?php else: ?>
    <?php if ($savedValueCount === 0): ?>
        <div class="spk-alert spk-alert-warning" style="margin-top:20px">Belum ada penilaian. Silakan isi nilai Xij pada setiap menu.</div>
    <?php endif; ?>

    <form method="post" action="<?= url('penilaian/simpan') ?>" data-validate-penilaian data-loading-text="Menyimpan...">
        <?= csrf_field() ?>
        <div class="card panel" style="margin-top:20px">
            <h2>Matriks Keputusan Xij (Menu x Kriteria)</h2>
            <div class="table-wrap">
                <table class="data-table spk-penilaian-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Menu</th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th>
                                    <?= e($criterion['kode'] . ' ' . $criterion['nama']) ?>
                                    <?php if ($criterion['jenis'] === 'cost'): ?>
                                        <span class="spk-th-note">Cost: nilai lebih kecil lebih baik.</span>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                            <th>Rata-rata</th>
                            <th>Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $index => $menu): ?>
                            <?php
                            $menuId = (int) $menu['id_menu'];
                            $row = $rows[$menuId];
                            $statusMeta = $statusLabels[$row['status']];
                            ?>
                            <tr data-penilaian-row>
                                <td><?= $index + 1 ?></td>
                                <td><span class="badge gray"><?= e($menu['kode']) ?></span> <?= e($menu['nama_menu']) ?></td>
                                <?php foreach ($criteria as $criterion): ?>
                                    <?php
                                    $criterionId = (int) $criterion['id_kriteria'];
                                    $field = 'nilai.' . $menuId . '.' . $criterionId;
                                    $fieldError = field_error($field);
                                    $errorId = $fieldErrorId($field);
                                    $inputClass = 'input score-input spk-score-input' . ($fieldError ? ' spk-input-error input-error' : '');
                                    ?>
                                    <td>
                                        <input
                                            class="<?= e($inputClass) ?>"
                                            name="nilai[<?= e($menuId) ?>][<?= e($criterionId) ?>]"
                                            value="<?= e($row['values'][$criterionId]) ?>"
                                            type="number"
                                            min="0"
                                            max="1"
                                            step="0.001"
                                            placeholder="0.000"
                                            required
                                            <?= $fieldError ? 'aria-invalid="true" aria-describedby="' . e($errorId) . '"' : '' ?>
                                        >
                                        <span class="spk-field-error" id="<?= e($errorId) ?>" data-input-error <?= $fieldError ? '' : 'hidden' ?>><?= e($fieldError ?: 'Nilai harus berada pada rentang 0.000 sampai 1.000.') ?></span>
                                    </td>
                                <?php endforeach; ?>
                                <td><strong data-row-average><?= e($row['average']) ?></strong></td>
                                <td>
                                    <span class="spk-row-badge <?= e($statusMeta['class']) ?>" data-row-status><?= e($statusMeta['text']) ?></span>
                                    <?php if ($row['row_error']): ?>
                                        <span class="spk-field-error" data-row-error><?= e($row['row_error']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="spk-alert alert alert-<?= e($globalStatus) ?> validation-status" role="status" data-validation-status>
                <span class="alert-icon"><?= $globalStatus === 'success' ? '&#10003;' : '!' ?></span>
                <div>
                    <strong>Status Penilaian</strong>
                    <p><?= e($globalMessage) ?></p>
                </div>
            </div>

            <button class="btn btn-primary <?= $canSubmit ? '' : 'is-disabled' ?>" type="submit" style="margin-top:18px" <?= $canSubmit ? '' : 'disabled' ?>>Simpan Penilaian</button>
        </div>
    </form>
<?php endif; ?>

<div class="grid-2" style="margin-top:20px">
    <div class="card panel">
        <h2>Daftar Kriteria</h2>
        <?php if (!$criteria): ?>
            <?php $title='Belum ada data kriteria.'; $message='Tambahkan data kriteria terlebih dahulu.'; require app_path('views/components/empty-state.php'); ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Kriteria</th>
                            <th>Jenis</th>
                            <th>Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criteria as $criterion): ?>
                            <tr>
                                <td><strong><?= e($criterion['kode']) ?></strong></td>
                                <td><?= e($criterion['nama']) ?></td>
                                <td><span class="badge <?= $criterion['jenis'] === 'cost' ? 'orange' : 'green' ?>"><?= e(ucfirst($criterion['jenis'])) ?></span></td>
                                <td><strong><?= e($formatWeight($criterion['bobot'])) ?></strong> <span class="muted">(<?= e($formatPercent($criterion['bobot'])) ?>%)</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="spk-criteria-notes">
                <span>Benefit: semakin besar nilai semakin baik.</span>
                <span>Cost: semakin kecil nilai semakin baik.</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="card panel">
        <h2>Ringkasan Status Penilaian</h2>
        <div class="spk-summary-grid" data-penilaian-summary>
            <div><h2 data-summary-total-menu><?= e($menuCount) ?></h2><p>Total Menu</p></div>
            <div><h2 data-summary-total-criteria><?= e($criteriaCount) ?></h2><p>Total Kriteria</p></div>
            <div><h2 data-summary-valid><?= e($summary['valid']) ?></h2><p>Menu Valid</p></div>
            <div><h2 data-summary-incomplete><?= e($summary['incomplete']) ?></h2><p>Belum Lengkap</p></div>
            <div><h2 data-summary-invalid><?= e($summary['invalid']) ?></h2><p>Tidak Valid</p></div>
            <div><h2><span data-summary-progress><?= e($progress) ?></span>%</h2><p>Progress Penilaian</p></div>
            <div class="spk-summary-wide"><h2><span data-summary-saved><?= e($savedValueCount) ?></span> / <span data-summary-needed><?= e($totalNeeded) ?></span></h2><p>Nilai Tersimpan</p></div>
        </div>
    </div>
</div>
