<?php

class LaporanController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $period = $this->periodFromRequest(false);
        $rankings = $this->db->query('SELECT h.*, m.kode, m.nama_menu FROM tbl_hasil h JOIN tbl_menu m ON m.id_menu = h.id_menu ORDER BY h.ranking LIMIT 10')->fetchAll();
        $history = $this->db->query('SELECT * FROM tbl_riwayat_laporan ORDER BY tanggal DESC LIMIT 8')->fetchAll();
        $criteria = $this->db->query("SELECT * FROM tbl_kriteria WHERE status = 'aktif' ORDER BY id_kriteria")->fetchAll();
        $latestCr = $this->db->query('SELECT cr_value FROM tbl_hasil ORDER BY tanggal_hitung DESC LIMIT 1')->fetchColumn();
        if ($period['error']) {
            flash_message('error', 'Periode Tidak Valid', $period['error']);
        } elseif (!$rankings) {
            flash_message('info', 'Belum Ada Data', 'Belum ada data hasil untuk periode yang dipilih.');
        }
        $this->view('pages/laporan', compact('rankings', 'history', 'period', 'criteria', 'latestCr') + ['title' => 'Laporan']);
    }

    public function pdf(): void
    {
        $this->export('PDF');
    }

    public function excel(): void
    {
        $this->export('Excel');
    }

    private function export(string $format): void
    {
        $this->requireAuth();
        $period = $this->periodFromRequest(true);
        if ($period['error']) {
            flash_message('error', 'Periode Tidak Valid', $period['error']);
            redirect('laporan');
        }

        $best = $this->db->query('SELECT h.*, m.nama_menu FROM tbl_hasil h JOIN tbl_menu m ON m.id_menu = h.id_menu ORDER BY h.ranking LIMIT 1')->fetch();
        if (!$best) {
            flash_message('warning', 'Belum Ada Data', 'Belum ada data hasil untuk periode yang dipilih.');
            redirect('laporan');
        }

        $stmt = $this->db->prepare('INSERT INTO tbl_riwayat_laporan (tanggal, periode_dari, periode_sampai, menu_terbaik, nilai_vi, admin, file_path) VALUES (NOW(), ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$period['from'], $period['to'], $best['nama_menu'] ?? '-', $best['nilai_vi'] ?? 0, current_user()['username'] ?? 'admin', 'placeholder-' . strtolower($format)]);
        $this->logActivity('Laporan', 'Export ' . $format, 'Fitur export siap diintegrasikan');
        flash_message('success', 'Berhasil', 'Laporan berhasil diunduh. Fitur export ' . $format . ' siap diintegrasikan dengan library dokumen.');
        redirect('laporan');
    }

    private function periodFromRequest(bool $required): array
    {
        $from = $_GET['periode_dari'] ?? date('Y-m-01');
        $to = $_GET['periode_sampai'] ?? date('Y-m-d');
        $error = null;
        if ($required && ($from === '' || $to === '')) {
            $error = 'Tanggal awal dan tanggal akhir wajib diisi.';
        } elseif ($from !== '' && $to !== '') {
            $fromTime = strtotime($from);
            $toTime = strtotime($to);
            if (!$fromTime || !$toTime) {
                $error = 'Format tanggal tidak valid.';
            } elseif ($toTime < $fromTime) {
                $error = 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.';
            }
        }
        return ['from' => $from, 'to' => $to, 'error' => $error];
    }
}
