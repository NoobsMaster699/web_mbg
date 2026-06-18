<?php

class RiwayatController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $allowedModules = ['Autentikasi','Data Menu','Data Kriteria','Penilaian','Perbandingan AHP','Hasil Ranking SAW','Laporan','Setup'];
        $modul = $_GET['modul'] ?? '';
        if ($modul !== '' && !in_array($modul, $allowedModules, true)) {
            flash_message('error', 'Filter Tidak Valid', 'Jenis aktivitas tidak valid.');
            $modul = '';
        } elseif ($modul !== '') {
            flash_message('info', 'Filter Diterapkan', 'Filter aktivitas diterapkan.');
        }
        $sql = 'SELECT a.*, u.username FROM tbl_aktivitas a LEFT JOIN tbl_users u ON u.id_user = a.user_id WHERE 1=1';
        $params = [];
        if ($modul !== '') {
            $sql .= ' AND a.modul = ?';
            $params[] = $modul;
        }
        $sql .= ' ORDER BY a.created_at DESC LIMIT 50';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $activities = $stmt->fetchAll();
        $counts = [
            'total' => (int) $this->db->query('SELECT COUNT(*) FROM tbl_aktivitas')->fetchColumn(),
            'login' => (int) $this->db->query("SELECT COUNT(*) FROM tbl_aktivitas WHERE aksi = 'Login'")->fetchColumn(),
            'data' => (int) $this->db->query("SELECT COUNT(*) FROM tbl_aktivitas WHERE modul IN ('Data Menu','Data Kriteria','Penilaian')")->fetchColumn(),
            'export' => (int) $this->db->query("SELECT COUNT(*) FROM tbl_aktivitas WHERE aksi LIKE 'Export%'")->fetchColumn(),
        ];
        if (!$activities) {
            flash_message('info', 'Tidak Ada Aktivitas', 'Tidak ada aktivitas pada periode ini.');
        }
        $this->view('pages/riwayat', compact('activities', 'counts', 'modul') + ['title' => 'Riwayat Aktivitas']);
    }
}
