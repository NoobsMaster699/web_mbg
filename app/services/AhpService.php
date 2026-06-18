<?php

class AhpService
{
    public function __construct(private PDO $db)
    {
    }

    public function getKriteria(): array
    {
        return $this->db->query("SELECT * FROM tbl_kriteria WHERE status = 'aktif' ORDER BY id_kriteria")->fetchAll();
    }

    public function getPairwiseMatrix(): array
    {
        $criteria = $this->getKriteria();
        $rows = $this->db->query('SELECT * FROM tbl_perbandingan')->fetchAll();
        $values = [];
        foreach ($rows as $row) {
            $values[$row['id_kriteria_i']][$row['id_kriteria_j']] = (float) $row['nilai_saaty'];
        }

        $matrix = [];
        foreach ($criteria as $i) {
            foreach ($criteria as $j) {
                $matrix[$i['id_kriteria']][$j['id_kriteria']] = $values[$i['id_kriteria']][$j['id_kriteria']] ?? ($i['id_kriteria'] === $j['id_kriteria'] ? 1.0 : 1.0);
            }
        }

        return $matrix;
    }

    public function calculate(): array
    {
        $criteria = $this->getKriteria();
        $matrix = $this->getPairwiseMatrix();
        $columnSums = $this->columnSums($matrix);
        $normalized = $this->normalizeMatrix($matrix, $columnSums);
        $weights = $this->calculateWeights($normalized);
        $lambdaMax = $this->calculateLambdaMax($columnSums, $weights);
        $ci = $this->calculateCI($lambdaMax, count($criteria));
        $ri = $this->randomIndex(count($criteria));
        $cr = $this->calculateCR($ci, $ri);

        return compact('criteria', 'matrix', 'columnSums', 'normalized', 'weights', 'lambdaMax', 'ci', 'ri', 'cr');
    }

    public function savePairwise(array $posted): void
    {
        $criteria = $this->getKriteria();
        $stmt = $this->db->prepare('REPLACE INTO tbl_perbandingan (id_kriteria_i, id_kriteria_j, nilai_saaty) VALUES (?, ?, ?)');
        foreach ($criteria as $i) {
            foreach ($criteria as $j) {
                $value = (float) ($posted[$i['id_kriteria']][$j['id_kriteria']] ?? 1);
                if ($i['id_kriteria'] === $j['id_kriteria']) {
                    $value = 1;
                }
                if ($value <= 0) {
                    $value = 1;
                }
                $stmt->execute([$i['id_kriteria'], $j['id_kriteria'], $value]);
            }
        }
    }

    public function normalizeMatrix(array $matrix, ?array $columnSums = null): array
    {
        $columnSums = $columnSums ?: $this->columnSums($matrix);
        $normalized = [];
        foreach ($matrix as $i => $row) {
            foreach ($row as $j => $value) {
                $normalized[$i][$j] = $columnSums[$j] > 0 ? $value / $columnSums[$j] : 0;
            }
        }
        return $normalized;
    }

    public function calculateWeights(array $normalizedMatrix): array
    {
        $weights = [];
        foreach ($normalizedMatrix as $i => $row) {
            $weights[$i] = array_sum($row) / max(count($row), 1);
        }
        return $weights;
    }

    public function calculateLambdaMax(array $columnSums, array $weights): float
    {
        $lambda = 0.0;
        foreach ($columnSums as $id => $sum) {
            $lambda += $sum * ($weights[$id] ?? 0);
        }
        return $lambda;
    }

    public function calculateCI(float $lambdaMax, int $n): float
    {
        return $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0;
    }

    public function calculateCR(float $ci, float $ri): float
    {
        return $ri > 0 ? $ci / $ri : 0;
    }

    public function saveWeights(array $weights): void
    {
        $stmt = $this->db->prepare('UPDATE tbl_kriteria SET bobot = ? WHERE id_kriteria = ?');
        foreach ($weights as $id => $weight) {
            $stmt->execute([$weight, $id]);
        }
    }

    public function isConsistent(float $cr): bool
    {
        return $cr <= 0.1;
    }

    private function columnSums(array $matrix): array
    {
        $sums = [];
        foreach ($matrix as $row) {
            foreach ($row as $j => $value) {
                $sums[$j] = ($sums[$j] ?? 0) + $value;
            }
        }
        return $sums;
    }

    private function randomIndex(int $n): float
    {
        return [1 => 0, 2 => 0, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49][$n] ?? 1.49;
    }
}

