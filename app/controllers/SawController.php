<?php

class SawController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $service = new SawService($this->db);
        $result = $service->calculate($this->lastCr());
        $saved = $this->db->query('SELECT h.*, m.id_menu, m.kode, m.nama_menu FROM tbl_hasil h JOIN tbl_menu m ON m.id_menu = h.id_menu ORDER BY h.ranking')->fetchAll();
        $result['ranked'] = $this->rankedFromSaved($saved, $service);
        $sawReadiness = (new ProcessStatusService($this->db))->sawReadiness();
        $this->view('pages/hasil', $result + compact('saved', 'sawReadiness') + ['title' => 'Hasil Ranking SAW']);
    }

    public function calculate(): void
    {
        $this->requireAuth();
        verify_csrf();
        $readiness = (new ProcessStatusService($this->db))->sawReadiness();
        if (!$readiness['ready']) {
            flash_message('warning', 'SAW Belum Siap', implode(' ', $readiness['messages']));
            redirect('hasil');
        }

        $cr = $this->lastCr();
        try {
            $service = new SawService($this->db);
            $result = $service->calculate($cr);
            $service->saveResults($result['ranked'], $cr);
            $this->logActivity('Hasil Ranking SAW', 'Hitung SAW', 'Memperbarui ranking menu');
            flash_message('success', 'Berhasil', 'Ranking menu berhasil dihitung.');
        } catch (Throwable $e) {
            flash_message('error', 'Gagal', 'Perhitungan SAW gagal. Periksa kembali data penilaian.');
        }
        redirect('hasil');
    }

    private function lastCr(): ?float
    {
        $value = $this->db->query('SELECT cr_value FROM tbl_hasil ORDER BY tanggal_hitung DESC LIMIT 1')->fetchColumn();
        if ($value === false) {
            $ahp = (new AhpService($this->db))->calculate();
            return isset($ahp['cr']) ? (float) $ahp['cr'] : null;
        }
        return (float) $value;
    }

    private function rankedFromSaved(array $saved, SawService $service): array
    {
        $ranked = [];
        foreach ($saved as $row) {
            $value = (float) $row['nilai_vi'];
            $ranked[] = [
                'rank' => (int) $row['ranking'],
                'menu' => [
                    'id_menu' => (int) $row['id_menu'],
                    'kode' => $row['kode'],
                    'nama_menu' => $row['nama_menu'],
                ],
                'value' => $value,
                'status' => $service->category($value),
            ];
        }

        return $ranked;
    }
}
