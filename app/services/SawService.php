<?php

class SawService
{
    public function __construct(private PDO $db)
    {
    }

    public function getMatriksKeputusan(): array
    {
        $menus = $this->db->query("SELECT * FROM tbl_menu WHERE status = 'aktif' ORDER BY id_menu")->fetchAll();
        $criteria = $this->getBobotKriteria();
        $rows = $this->db->query('SELECT * FROM tbl_penilaian')->fetchAll();
        $values = [];
        foreach ($rows as $row) {
            $values[$row['id_menu']][$row['id_kriteria']] = (float) $row['nilai_xij'];
        }

        $matrix = [];
        foreach ($menus as $menu) {
            foreach ($criteria as $criterion) {
                $matrix[$menu['id_menu']][$criterion['id_kriteria']] = $values[$menu['id_menu']][$criterion['id_kriteria']] ?? 0;
            }
        }

        return ['menus' => $menus, 'criteria' => $criteria, 'matrix' => $matrix];
    }

    public function getBobotKriteria(): array
    {
        return $this->db->query("SELECT * FROM tbl_kriteria WHERE status = 'aktif' ORDER BY id_kriteria")->fetchAll();
    }

    public function normalizeDecisionMatrix(array $matrix, array $criteria): array
    {
        $normalized = [];
        foreach ($criteria as $criterion) {
            $id = $criterion['id_kriteria'];
            $column = array_column($matrix, $id);
            $max = max($column ?: [0]);
            $min = min(array_filter($column, fn ($v) => $v > 0) ?: [0]);
            foreach ($matrix as $menuId => $row) {
                $xij = (float) ($row[$id] ?? 0);
                if ($criterion['jenis'] === 'cost') {
                    $normalized[$menuId][$id] = $xij > 0 ? $min / $xij : 0;
                } else {
                    $normalized[$menuId][$id] = $max > 0 ? $xij / $max : 0;
                }
            }
        }
        return $normalized;
    }

    public function calculatePreferenceValues(array $normalizedMatrix, array $criteria): array
    {
        $weights = [];
        foreach ($criteria as $criterion) {
            $weights[$criterion['id_kriteria']] = (float) $criterion['bobot'];
        }

        $values = [];
        foreach ($normalizedMatrix as $menuId => $row) {
            $values[$menuId] = 0;
            foreach ($row as $criterionId => $rij) {
                $values[$menuId] += ($weights[$criterionId] ?? 0) * $rij;
            }
        }
        return $values;
    }

    public function rankAlternatives(array $values, array $menus): array
    {
        arsort($values);
        $menuMap = [];
        foreach ($menus as $menu) {
            $menuMap[$menu['id_menu']] = $menu;
        }

        $rank = 1;
        $ranked = [];
        foreach ($values as $menuId => $value) {
            $ranked[] = [
                'rank' => $rank++,
                'menu' => $menuMap[$menuId] ?? null,
                'value' => $value,
                'status' => $this->category($value),
            ];
        }
        return $ranked;
    }

    public function calculate(?float $crValue = null): array
    {
        $data = $this->getMatriksKeputusan();
        $normalized = $this->normalizeDecisionMatrix($data['matrix'], $data['criteria']);
        $values = $this->calculatePreferenceValues($normalized, $data['criteria']);
        $ranked = $this->rankAlternatives($values, $data['menus']);

        return $data + compact('normalized', 'values', 'ranked', 'crValue');
    }

    public function saveResults(array $rankedResults, ?float $crValue): void
    {
        $this->db->exec('DELETE FROM tbl_hasil');
        $stmt = $this->db->prepare('INSERT INTO tbl_hasil (id_menu, nilai_vi, ranking, cr_value, tanggal_hitung) VALUES (?, ?, ?, ?, NOW())');
        foreach ($rankedResults as $row) {
            if ($row['menu']) {
                $stmt->execute([$row['menu']['id_menu'], $row['value'], $row['rank'], $crValue]);
            }
        }
    }

    public function category(float $value): string
    {
        if ($value >= 0.85) {
            return 'Rekomendasi Utama';
        }
        if ($value >= 0.70) {
            return 'Baik';
        }
        return 'Alternatif';
    }
}

