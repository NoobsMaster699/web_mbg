<?php

class MenuController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $search = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? '';
        $sql = 'SELECT m.*, COALESCE(MAX(h.nilai_vi), 0) nilai_vi FROM tbl_menu m LEFT JOIN tbl_hasil h ON h.id_menu = m.id_menu WHERE 1=1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (m.nama_menu LIKE ? OR m.lauk LIKE ? OR m.sayur LIKE ? OR m.buah LIKE ?)';
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($status !== '') {
            $sql .= ' AND m.status = ?';
            $params[] = $status;
        }
        $sql .= ' GROUP BY m.id_menu ORDER BY m.id_menu';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $menus = $stmt->fetchAll();
        $this->view('pages/data-menu', compact('menus', 'search', 'status') + ['title' => 'Data Menu']);
    }

    public function store(): void
    {
        $this->requireAuth();
        verify_csrf();
        $data = $this->validatedMenu('menu');
        try {
            $stmt = $this->db->prepare('INSERT INTO tbl_menu (kode, nama_menu, makanan_pokok, lauk, sayur, buah, berat_g, keterangan, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
            $stmt->execute($data);
            $this->logActivity('Data Menu', 'Tambah Menu', 'Menambahkan menu ' . $data[1]);
            flash_message('success', 'Berhasil', 'Data menu berhasil ditambahkan.');
        } catch (PDOException $e) {
            with_form_errors(['kode' => 'Kode menu sudah digunakan atau data tidak dapat disimpan.'], 'Gagal simpan. Periksa kembali data menu.');
            redirect('menu');
        }
        redirect('menu');
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        verify_csrf();
        $menuId = (int) $id;
        if ($menuId <= 0) {
            flash_message('error', 'Gagal', 'ID menu tidak valid.');
            redirect('menu');
        }
        $data = $this->validatedMenu('menu', $menuId);
        $data[] = $menuId;
        try {
            $stmt = $this->db->prepare('UPDATE tbl_menu SET kode=?, nama_menu=?, makanan_pokok=?, lauk=?, sayur=?, buah=?, berat_g=?, keterangan=?, status=?, updated_at=NOW() WHERE id_menu=?');
            $stmt->execute($data);
            $this->logActivity('Data Menu', 'Edit Menu', 'Mengubah menu ' . $data[1]);
            flash_message('success', 'Berhasil', 'Data menu berhasil diperbarui.');
        } catch (PDOException $e) {
            with_form_errors(['kode' => 'Kode menu sudah digunakan atau data tidak dapat diperbarui.'], 'Gagal simpan. Periksa kembali data menu.');
            redirect('menu');
        }
        redirect('menu');
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        verify_csrf();
        $menuId = (int) $id;
        if ($menuId <= 0) {
            flash_message('error', 'Gagal', 'ID menu tidak valid.');
            redirect('menu');
        }
        $stmt = $this->db->prepare("UPDATE tbl_menu SET status = 'nonaktif', updated_at = NOW() WHERE id_menu = ?");
        $stmt->execute([$menuId]);
        $this->logActivity('Data Menu', 'Nonaktif Menu', 'Menonaktifkan menu ID ' . $menuId);
        flash_message('success', 'Berhasil', 'Data menu berhasil dinonaktifkan.');
        redirect('menu');
    }

    private function validatedMenu(string $redirect, ?int $id = null): array
    {
        $kode = trim($_POST['kode'] ?? '');
        $nama = trim($_POST['nama_menu'] ?? '');
        $makananPokok = trim($_POST['makanan_pokok'] ?? '');
        $lauk = trim($_POST['lauk'] ?? '');
        $sayur = trim($_POST['sayur'] ?? '');
        $buah = trim($_POST['buah'] ?? '');
        $berat = trim((string) ($_POST['berat_g'] ?? ''));
        $status = $_POST['status'] ?? '';
        $errors = [];

        if ($kode === '') $errors['kode'] = 'Kode menu wajib diisi.';
        if ($nama === '') $errors['nama_menu'] = 'Nama menu wajib diisi.';
        if ($makananPokok === '') $errors['makanan_pokok'] = 'Makanan pokok wajib diisi.';
        if ($lauk === '') $errors['lauk'] = 'Lauk wajib diisi.';
        if ($sayur === '') $errors['sayur'] = 'Sayur wajib diisi.';
        if ($buah === '') $errors['buah'] = 'Buah wajib diisi.';
        if ($berat === '' || !is_numeric($berat) || (int) $berat <= 0) $errors['berat_g'] = 'Berat porsi harus angka positif.';
        if (!in_array($status, ['aktif', 'nonaktif'], true)) $errors['status'] = 'Status hanya boleh aktif atau nonaktif.';

        if ($kode !== '') {
            $sql = 'SELECT COUNT(*) FROM tbl_menu WHERE kode = ?' . ($id ? ' AND id_menu <> ?' : '');
            $stmt = $this->db->prepare($sql);
            $stmt->execute($id ? [$kode, $id] : [$kode]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors['kode'] = 'Kode menu sudah digunakan.';
            }
        }

        if ($errors) {
            with_form_errors($errors, 'Gagal simpan. Periksa kembali data menu.');
            redirect($redirect);
        }

        return [$kode, $nama, $makananPokok, $lauk, $sayur, $buah, (int) $berat, trim($_POST['keterangan'] ?? ''), $status];
    }
}
