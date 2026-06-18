<?php

class AhpController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $service = new AhpService($this->db);
        $result = $service->calculate();
        $this->view('pages/ahp', $result + ['title' => 'Perbandingan AHP']);
    }

    public function calculate(): void
    {
        $this->requireAuth();
        verify_csrf();
        $service = new AhpService($this->db);
        $criteria = $service->getKriteria();
        $matrix = $_POST['matrix'] ?? [];
        $errors = $this->validateMatrix($criteria, $matrix);
        if ($errors) {
            with_form_errors($errors, 'Lengkapi seluruh nilai perbandingan sebelum menghitung AHP.');
            redirect('ahp');
        }

        try {
            $service->savePairwise($matrix);
            $result = $service->calculate();
            if ($service->isConsistent($result['cr'])) {
                $service->saveWeights($result['weights']);
                flash_message('success', 'Matriks Konsisten', 'Matriks konsisten. Bobot AHP dapat digunakan untuk proses SAW.');
            } else {
                flash_message('warning', 'Matriks Belum Konsisten', 'Matriks belum konsisten. Silakan perbaiki nilai perbandingan.');
            }
            $this->logActivity('Perbandingan AHP', 'Hitung AHP', 'CR = ' . number_format($result['cr'], 4));
        } catch (Throwable $e) {
            flash_message('error', 'Perhitungan Gagal', 'Perhitungan AHP gagal. Periksa kembali data perbandingan.');
        }
        redirect('ahp');
    }

    private function validateMatrix(array $criteria, array $matrix): array
    {
        $allowed = [1,2,3,4,5,6,7,8,9,1/2,1/3,1/4,1/5,1/6,1/7,1/8,1/9];
        $errors = [];
        foreach ($criteria as $i) {
            foreach ($criteria as $j) {
                $key = 'matrix.' . $i['id_kriteria'] . '.' . $j['id_kriteria'];
                $value = $matrix[$i['id_kriteria']][$j['id_kriteria']] ?? null;
                if ($value === null || $value === '') {
                    $errors[$key] = 'Nilai perbandingan wajib diisi.';
                    continue;
                }
                if (!is_numeric($value) || (float) $value <= 0) {
                    $errors[$key] = 'Nilai perbandingan tidak valid.';
                    continue;
                }
                if ($i['id_kriteria'] === $j['id_kriteria'] && abs((float) $value - 1.0) > 0.00001) {
                    $errors[$key] = 'Diagonal matriks wajib bernilai 1.';
                    continue;
                }
                $valid = false;
                foreach ($allowed as $candidate) {
                    if (abs((float) $value - $candidate) < 0.00001) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid) {
                    $errors[$key] = 'Gunakan skala Saaty 1-9 atau reciprocal 1/2 sampai 1/9.';
                }
            }
        }
        return $errors;
    }
}
