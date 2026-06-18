<?php

class DashboardController extends Controller
{
    public function homepage(): void
    {
        $this->view('pages/homepage', ['title' => 'SPK MBG'], 'guest');
    }

    public function index(): void
    {
        $this->requireAuth();
        $stats = [
            'menu' => (int) $this->db->query('SELECT COUNT(*) FROM tbl_menu')->fetchColumn(),
            'kriteria' => (int) $this->db->query('SELECT COUNT(*) FROM tbl_kriteria')->fetchColumn(),
            'cr' => $this->db->query('SELECT cr_value FROM tbl_hasil ORDER BY tanggal_hitung DESC LIMIT 1')->fetchColumn(),
            'vi' => $this->db->query('SELECT MAX(nilai_vi) FROM tbl_hasil')->fetchColumn(),
        ];
        $top = $this->db->query('SELECT h.*, m.kode, m.nama_menu FROM tbl_hasil h JOIN tbl_menu m ON m.id_menu = h.id_menu ORDER BY h.ranking LIMIT 5')->fetchAll();
        $criteria = $this->db->query('SELECT * FROM tbl_kriteria ORDER BY id_kriteria')->fetchAll();
        $activities = $this->db->query('SELECT * FROM tbl_aktivitas ORDER BY created_at DESC LIMIT 5')->fetchAll();
        $process = (new ProcessStatusService($this->db))->flow();
        $this->view('pages/dashboard', compact('stats', 'top', 'criteria', 'activities', 'process') + ['title' => 'Dashboard']);
    }
}
