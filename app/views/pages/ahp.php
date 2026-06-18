<?php
$oldInput = old_input_data();
$oldMatrix = is_array($oldInput['matrix'] ?? null) ? $oldInput['matrix'] : [];
$hasOldMatrix = array_key_exists('matrix', $oldInput);
$isConsistent = $cr <= 0.1;
$saatyOptions = [
    ['value' => '0.111111', 'label' => '1/9'],
    ['value' => '0.125000', 'label' => '1/8'],
    ['value' => '0.142857', 'label' => '1/7'],
    ['value' => '0.166667', 'label' => '1/6'],
    ['value' => '0.200000', 'label' => '1/5'],
    ['value' => '0.250000', 'label' => '1/4'],
    ['value' => '0.333333', 'label' => '1/3'],
    ['value' => '0.500000', 'label' => '1/2'],
    ['value' => '1.000000', 'label' => '1'],
    ['value' => '2.000000', 'label' => '2'],
    ['value' => '3.000000', 'label' => '3'],
    ['value' => '4.000000', 'label' => '4'],
    ['value' => '5.000000', 'label' => '5'],
    ['value' => '6.000000', 'label' => '6'],
    ['value' => '7.000000', 'label' => '7'],
    ['value' => '8.000000', 'label' => '8'],
    ['value' => '9.000000', 'label' => '9'],
];
$optionValueFor = function (mixed $value) use ($saatyOptions): string {
    $numeric = (float) $value;
    foreach ($saatyOptions as $option) {
        if (abs($numeric - (float) $option['value']) < 0.00001) {
            return $option['value'];
        }
    }

    return number_format($numeric, 6, '.', '');
};
$matrixValue = function (int $rowId, int $colId) use ($hasOldMatrix, $oldMatrix, $matrix): string {
    if ($rowId === $colId) {
        return '1.000000';
    }

    if ($hasOldMatrix) {
        return (string) ($oldMatrix[$rowId][$colId] ?? '1.000000');
    }

    return (string) ($matrix[$rowId][$colId] ?? 1);
};
$guideIntensityOptions = [
    1 => '1 Sama penting',
    2 => '2 Nilai antara',
    3 => '3 Sedikit lebih penting',
    4 => '4 Nilai antara',
    5 => '5 Lebih penting',
    6 => '6 Nilai antara',
    7 => '7 Sangat penting',
    8 => '8 Nilai antara',
    9 => '9 Mutlak lebih penting',
];
$guideState = function (int $leftId, int $rightId) use ($matrixValue): array {
    $value = (float) $matrixValue($leftId, $rightId);
    if (abs($value - 1.0) < 0.00001) {
        return ['direction' => 'same', 'intensity' => 1];
    }

    if ($value > 1) {
        return ['direction' => 'left', 'intensity' => max(1, min(9, (int) round($value)))];
    }

    return ['direction' => 'right', 'intensity' => max(1, min(9, (int) round(1 / max($value, 0.111111))))];
};
$criteriaList = array_values($criteria);
?>

<div class="page-head">
    <div>
        <h1>Perbandingan Kriteria (Pairwise Comparison)</h1>
        <p class="muted">Bandingkan setiap kriteria berpasangan menggunakan skala Saaty 1-9.</p>
    </div>
</div>

<?php if (!$criteria): ?>
    <div class="card panel"><?php $title='Belum ada data kriteria.'; $message='Tambahkan kriteria aktif sebelum menghitung AHP.'; require app_path('views/components/empty-state.php'); ?></div>
<?php else: ?>
<form method="post" action="<?= url('ahp/hitung') ?>" data-confirm="Data yang sudah diproses mungkin akan berubah. Apakah Anda yakin ingin menghitung ulang AHP?" data-loading-text="Menghitung AHP...">
    <?= csrf_field() ?>
    <div class="card panel spk-wizard" style="margin-bottom:20px">
        <h2>Mode Panduan Perbandingan Kriteria</h2>
        <p class="muted">Dalam menentukan menu MBG terbaik, pilih kriteria mana yang lebih penting untuk setiap pasangan. Preview matriks AHP akan mengikuti jawaban ini.</p>
        <div class="spk-wizard-grid">
            <?php for ($a = 0; $a < count($criteriaList); $a++): ?>
                <?php for ($b = $a + 1; $b < count($criteriaList); $b++): ?>
                    <?php
                    $left = $criteriaList[$a];
                    $right = $criteriaList[$b];
                    $leftId = (int) $left['id_kriteria'];
                    $rightId = (int) $right['id_kriteria'];
                    $state = $guideState($leftId, $rightId);
                    ?>
                    <div class="spk-guide-pair">
                        <strong><?= e($left['kode'] . ' ' . $left['nama']) ?> vs <?= e($right['kode'] . ' ' . $right['nama']) ?></strong>
                        <label>
                            Mana yang lebih penting?
                            <select class="input ahp-guide-direction" data-left="<?= e($leftId) ?>" data-right="<?= e($rightId) ?>">
                                <option value="left" <?= $state['direction'] === 'left' ? 'selected' : '' ?>><?= e($left['nama']) ?> lebih penting</option>
                                <option value="same" <?= $state['direction'] === 'same' ? 'selected' : '' ?>>Sama penting</option>
                                <option value="right" <?= $state['direction'] === 'right' ? 'selected' : '' ?>><?= e($right['nama']) ?> lebih penting</option>
                            </select>
                        </label>
                        <label>
                            Tingkat kepentingan
                            <select class="input ahp-guide-intensity" data-left="<?= e($leftId) ?>" data-right="<?= e($rightId) ?>">
                                <?php foreach ($guideIntensityOptions as $value => $label): ?>
                                    <option value="<?= e((string) $value) ?>" <?= $state['intensity'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                <?php endfor; ?>
            <?php endfor; ?>
        </div>
    </div>
    <div class="grid-2" style="grid-template-columns:1.7fr .8fr">
        <div class="card panel">
            <h2>Preview Matriks AHP <span class="badge green">Saaty 1-9</span></h2>
            <p class="muted">Pilih skala Saaty dari dropdown. Nilai reciprocal ditampilkan sebagai pecahan, dan diagonal selalu 1.</p>
            <div class="table-wrap">
                <table class="data-table comparison-matrix">
                    <thead>
                        <tr>
                            <th>Kriteria</th>
                            <?php foreach ($criteria as $criterion): ?>
                                <th><?= e($criterion['kode'] . ' - ' . $criterion['nama']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criteria as $i): ?>
                            <tr>
                                <td class="cell-soft"><?= e($i['kode'] . ' - ' . $i['nama']) ?></td>
                                <?php foreach ($criteria as $j): ?>
                                    <?php
                                    $rowId = (int) $i['id_kriteria'];
                                    $colId = (int) $j['id_kriteria'];
                                    $field = 'matrix.' . $rowId . '.' . $colId;
                                    $currentValue = $optionValueFor($matrixValue($rowId, $colId));
                                    ?>
                                    <td>
                                        <?php if ($rowId === $colId): ?>
                                            <input
                                                class="input ahp-cell ahp-diagonal<?= e(field_error_class($field)) ?>"
                                                data-row="<?= e($rowId) ?>"
                                                data-col="<?= e($colId) ?>"
                                                name="matrix[<?= e($rowId) ?>][<?= e($colId) ?>]"
                                                value="1"
                                                readonly
                                                required<?= field_error_attrs($field) ?>
                                            >
                                        <?php else: ?>
                                            <select
                                                class="input ahp-cell ahp-select<?= e(field_error_class($field)) ?>"
                                                data-row="<?= e($rowId) ?>"
                                                data-col="<?= e($colId) ?>"
                                                name="matrix[<?= e($rowId) ?>][<?= e($colId) ?>]"
                                                required<?= field_error_attrs($field) ?>
                                            >
                                                <?php foreach ($saatyOptions as $option): ?>
                                                    <option value="<?= e($option['value']) ?>" <?= $currentValue === $option['value'] ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                        <?= field_error_html($field) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary" type="submit" style="margin-top:18px">Hitung AHP</button>
        </div>

        <div class="card panel">
            <h2>Analisis Konsistensi</h2>
            <div class="alert <?= $isConsistent ? 'alert-success' : 'alert-warning' ?>" role="status">
                <span class="alert-icon"><?= $isConsistent ? '&#10003;' : '!' ?></span>
                <div>
                    <strong>CR = <?= e(number_format($cr, 4)) ?></strong>
                    <p><?= $isConsistent ? 'Matriks konsisten. Bobot AHP dapat digunakan untuk proses SAW.' : 'Matriks belum konsisten. Silakan perbaiki nilai perbandingan.' ?></p>
                </div>
            </div>
            <p>Lambda max <strong style="float:right"><?= e(number_format($lambdaMax, 4)) ?></strong></p>
            <p>CI <strong style="float:right"><?= e(number_format($ci, 4)) ?></strong></p>
            <p>RI <strong style="float:right"><?= e(number_format($ri, 2)) ?></strong></p>
            <p>CR <strong class="<?= $isConsistent ? 'text-green' : '' ?>" style="float:right"><?= e(number_format($cr, 4)) ?></strong></p>
            <p>Status <span class="badge <?= $isConsistent ? 'green' : 'orange' ?>" style="float:right"><?= $isConsistent ? 'Konsisten' : 'Tidak Konsisten' ?></span></p>
        </div>
    </div>
</form>

<div class="grid-2" style="margin-top:20px">
    <div class="card panel">
        <h2>Matriks Ternormalisasi</h2>
        <div class="table-wrap">
            <table class="data-table">
                <tbody>
                    <?php foreach ($criteria as $i): ?>
                        <tr>
                            <td><?= e($i['kode'] . ' - ' . $i['nama']) ?></td>
                            <?php foreach ($criteria as $j): ?>
                                <td><?= e(number_format($normalized[$i['id_kriteria']][$j['id_kriteria']], 3)) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card panel">
        <h2>Bobot Prioritas Kriteria</h2>
        <div class="table-wrap">
            <table class="data-table">
                <tbody>
                    <?php foreach ($criteria as $criterion): ?>
                        <?php $w = $weights[$criterion['id_kriteria']] ?? 0; ?>
                        <tr>
                            <td><?= e($criterion['kode'] . ' - ' . $criterion['nama']) ?></td>
                            <td><?= e(number_format($w, 3)) ?></td>
                            <td><?= e(number_format($w * 100, 1)) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
