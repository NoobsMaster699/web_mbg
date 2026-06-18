<?php

class KriteriaController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $criteria = $this->db->query('SELECT * FROM tbl_kriteria ORDER BY id_kriteria')->fetchAll();
        $lastCr = $this->db->query('SELECT cr_value FROM tbl_hasil ORDER BY tanggal_hitung DESC LIMIT 1')->fetchColumn();
        $this->view('pages/kriteria', compact('criteria', 'lastCr') + ['title' => 'Data Kriteria']);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();
        $data = $this->payload(null);
        try {
            $stmt = $this->db->prepare('INSERT INTO tbl_kriteria (kode, nama, jenis, bobot, deskripsi, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute($data);
            $this->logActivity('Data Kriteria', 'Tambah Kriteria', 'Menambahkan kriteria ' . $data[1]);
            $this->flashWeightStatus('Data kriteria berhasil ditambahkan.');
        } catch (PDOException $e) {
            with_form_errors(['kode' => 'Kode kriteria sudah digunakan atau data tidak dapat disimpan.'], 'Gagal simpan. Periksa kembali data kriteria.');
            redirect('kriteria');
        }
        redirect('kriteria');
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        verify_csrf();
        $kriteriaId = (int) $id;
        if ($kriteriaId <= 0) {
            flash_message('error', 'Gagal', 'ID kriteria tidak valid.');
            redirect('kriteria');
        }
        $data = $this->payload($kriteriaId);
        $data[] = $kriteriaId;
        try {
            $stmt = $this->db->prepare('UPDATE tbl_kriteria SET kode=?, nama=?, jenis=?, bobot=?, deskripsi=?, status=? WHERE id_kriteria=?');
            $stmt->execute($data);
            $this->logActivity('Data Kriteria', 'Edit Kriteria', 'Mengubah kriteria ' . $data[1]);
            $this->flashWeightStatus('Data kriteria berhasil diperbarui.');
        } catch (PDOException $e) {
            with_form_errors(['kode' => 'Kode kriteria sudah digunakan atau data tidak dapat diperbarui.'], 'Gagal simpan. Periksa kembali data kriteria.');
            redirect('kriteria');
        }
        redirect('kriteria');
    }

    private function payload(?int $id): array
    {
        $kode = trim($_POST['kode'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jenis = $_POST['jenis'] ?? '';
        $bobotRaw = trim((string) ($_POST['bobot'] ?? ''));
        $status = $_POST['status'] ?? '';
        $errors = [];

        if ($kode === '') $errors['kode'] = 'Kode kriteria wajib diisi.';
        if ($nama === '') $errors['nama'] = 'Nama kriteria wajib diisi.';
        if (!in_array($jenis, ['benefit', 'cost'], true)) $errors['jenis'] = 'Jenis hanya boleh benefit atau cost.';
        if ($bobotRaw === '' || !is_numeric($bobotRaw) || (float) $bobotRaw < 0 || (float) $bobotRaw > 1) $errors['bobot'] = 'Bobot harus angka 0 sampai 1.';
        if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors['status'] = 'Status hanya boleh aktif atau nonaktif.';

        if ($kode !== '') {
            $sql = 'SELECT COUNT(*) FROM tbl_kriteria WHERE kode = ?' . ($id ? ' AND id_kriteria <> ?' : '');
            $stmt = $this->db->prepare($sql);
            $stmt->execute($id ? [$kode, $id] : [$kode]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['kode'] = 'Kode kriteria sudah digunakan.';
            }
        }

        if ($errors) {
            with_form_errors($errors, 'Gagal simpan. Periksa kembali data kriteria.');
            redirect('kriteria');
        }

        return [$kode, $nama, $jenis, (float) $bobotRaw, trim($_POST['deskripsi'] ?? ''), $status];
    }

    private function flashWeightStatus(string $successMessage): void
    {
        $sum = (float) $this->db->query("SELECT COALESCE(SUM(bobot),0) FROM tbl_kriteria WHERE status = 'aktif'")->fetchColumn();
        if (abs($sum - 1.0) > 0.001) {
            flash_message('warning', 'Tersimpan Dengan Catatan', 'Total bobot belum sama dengan 1.000. Periksa kembali bobot kriteria.');
            return;
        }
        flash_message('success', 'Berhasil', $successMessage);
    }
}
