<?php

class ProcessStatusService
{
    public function __construct(private PDO $db)
    {
    }

    public function activeMenuCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM tbl_menu WHERE status = 'aktif'")->fetchColumn();
    }

    public function activeCriteriaCount(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM tbl_kriteria WHERE status = 'aktif'")->fetchColumn();
    }

    public function latestSawDate(): ?string
    {
        $value = $this->db->query('SELECT MAX(tanggal_hitung) FROM tbl_hasil')->fetchColumn();
        return $value ?: null;
    }

    public function latestAHPActivityDate(): ?string
    {
        $value = $this->db->query("SELECT MAX(created_at) FROM tbl_aktivitas WHERE modul = 'Perbandingan AHP'")->fetchColumn();
        return $value ?: null;
    }

    public function latestPenilaianActivityDate(): ?string
    {
        $value = $this->db->query("SELECT MAX(created_at) FROM tbl_aktivitas WHERE modul = 'Penilaian'")->fetchColumn();
        return $value ?: null;
    }

    public function currentCr(): ?float
    {
        try {
            $result = (new AhpService($this->db))->calculate();
            return isset($result['cr']) ? (float) $result['cr'] : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function penilaianIsComplete(): bool
    {
        $menuCount = $this->activeMenuCount();
        $criteriaCount = $this->activeCriteriaCount();
        if ($menuCount === 0 || $criteriaCount === 0) {
            return false;
        }

        $scoreCount = (int) $this->db->query('SELECT COUNT(*) FROM tbl_penilaian p JOIN tbl_menu m ON m.id_menu=p.id_menu AND m.status="aktif" JOIN tbl_kriteria k ON k.id_kriteria=p.id_kriteria AND k.status="aktif" WHERE p.nilai_xij >= 0 AND p.nilai_xij <= 1')->fetchColumn();
        if ($scoreCount < ($menuCount * $criteriaCount)) {
            return false;
        }

        $zeroRows = (int) $this->db->query('SELECT COUNT(*) FROM (SELECT m.id_menu, COALESCE(SUM(CASE WHEN k.id_kriteria IS NOT NULL AND p.nilai_xij > 0 THEN 1 ELSE 0 END), 0) positive_values FROM tbl_menu m LEFT JOIN tbl_penilaian p ON p.id_menu = m.id_menu LEFT JOIN tbl_kriteria k ON k.id_kriteria = p.id_kriteria AND k.status = "aktif" WHERE m.status = "aktif" GROUP BY m.id_menu HAVING positive_values = 0) invalid_rows')->fetchColumn();
        return $zeroRows === 0;
    }

    public function sawReadiness(): array
    {
        $messages = [];
        $warnings = [];
        $menuCount = $this->activeMenuCount();
        $criteriaCount = $this->activeCriteriaCount();

        if ($menuCount === 0) {
            $messages[] = 'Data menu aktif belum tersedia.';
        }

        if ($criteriaCount === 0) {
            $messages[] = 'Data kriteria aktif belum tersedia.';
        }

        if ($menuCount > 0 && $criteriaCount > 0 && !$this->penilaianIsComplete()) {
            $messages[] = 'Penilaian belum lengkap.';
        }

        $weightSum = (float) $this->db->query("SELECT COALESCE(SUM(bobot),0) FROM tbl_kriteria WHERE status = 'aktif'")->fetchColumn();
        if ($weightSum <= 0) {
            $messages[] = 'Bobot AHP belum tersedia.';
        }

        $pairwiseCount = (int) $this->db->query('SELECT COUNT(*) FROM tbl_perbandingan')->fetchColumn();
        if ($criteriaCount > 0 && $pairwiseCount < ($criteriaCount * $criteriaCount)) {
            $messages[] = 'AHP belum dihitung.';
        }

        $cr = $this->currentCr();
        if ($cr === null) {
            $messages[] = 'AHP belum dihitung.';
        } elseif ($cr > 0.1) {
            $messages[] = 'CR belum valid.';
        }

        $latestSaw = $this->latestSawDate();
        if ($latestSaw) {
            $latestAhp = $this->latestAHPActivityDate();
            $latestPenilaian = $this->latestPenilaianActivityDate();

            if ($latestAhp && strtotime($latestAhp) > strtotime($latestSaw)) {
                $warnings[] = 'Data AHP berubah. Hasil SAW perlu dihitung ulang.';
            }

            if ($latestPenilaian && strtotime($latestPenilaian) > strtotime($latestSaw)) {
                $warnings[] = 'Data penilaian berubah. Hasil SAW perlu dihitung ulang.';
            }
        }

        $messages = array_values(array_unique($messages));
        $warnings = array_values(array_unique($warnings));
        return ['ready' => $messages === [], 'messages' => $messages, 'warnings' => $warnings, 'cr' => $cr];
    }

    public function flow(): array
    {
        $menuCount = $this->activeMenuCount();
        $criteriaCount = $this->activeCriteriaCount();
        $penilaianReady = $this->penilaianIsComplete();
        $sawReadiness = $this->sawReadiness();
        $resultCount = (int) $this->db->query('SELECT COUNT(*) FROM tbl_hasil')->fetchColumn();
        $reportCount = (int) $this->db->query('SELECT COUNT(*) FROM tbl_riwayat_laporan')->fetchColumn();
        $sawWarnings = $sawReadiness['warnings'] ?? [];

        $cr = $sawReadiness['cr'];
        $ahpStatus = 'Belum dihitung';
        if ($cr !== null) {
            $ahpStatus = $cr <= 0.1 ? 'Valid' : 'Tidak valid';
        }

        return [
            ['name' => 'Data Menu', 'status' => $menuCount > 0 ? 'Selesai' : 'Belum lengkap', 'detail' => $menuCount . ' menu aktif/nonaktif'],
            ['name' => 'Data Kriteria', 'status' => $criteriaCount > 0 ? 'Selesai' : 'Belum lengkap', 'detail' => $criteriaCount . ' kriteria aktif'],
            ['name' => 'Penilaian', 'status' => $penilaianReady ? 'Selesai' : 'Belum lengkap', 'detail' => $penilaianReady ? 'Matriks Xij lengkap' : 'Lengkapi nilai Xij'],
            ['name' => 'AHP', 'status' => $ahpStatus, 'detail' => $cr === null ? 'CR belum tersedia' : 'CR ' . number_format($cr, 4)],
            ['name' => 'SAW', 'status' => ($resultCount > 0 && !$sawWarnings) ? 'Selesai' : ($sawReadiness['ready'] ? 'Belum dihitung' : 'Terkunci'), 'detail' => $sawWarnings ? implode(' ', $sawWarnings) : ($sawReadiness['ready'] ? 'Siap dihitung' : implode(' ', $sawReadiness['messages']))],
            ['name' => 'Laporan', 'status' => $resultCount > 0 ? ($reportCount > 0 ? 'Selesai' : 'Belum dihitung') : 'Terkunci', 'detail' => $resultCount > 0 ? 'Berdasarkan hasil SAW' : 'Hitung SAW terlebih dahulu'],
        ];
    }
}
