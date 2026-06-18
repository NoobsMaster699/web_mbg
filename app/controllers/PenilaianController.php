<?php

class PenilaianController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $menus = $this->db->query("SELECT * FROM tbl_menu WHERE status = 'aktif' ORDER BY id_menu")->fetchAll();
        $criteria = $this->db->query("SELECT * FROM tbl_kriteria WHERE status = 'aktif' ORDER BY id_kriteria")->fetchAll();
        $rows = $this->db->query("SELECT p.* FROM tbl_penilaian p JOIN tbl_menu m ON m.id_menu = p.id_menu AND m.status = 'aktif' JOIN tbl_kriteria k ON k.id_kriteria = p.id_kriteria AND k.status = 'aktif'")->fetchAll();
        $scores = [];
        foreach ($rows as $row) {
            $scores[$row['id_menu']][$row['id_kriteria']] = $row['nilai_xij'];
        }
        $this->view('pages/penilaian', compact('menus', 'criteria', 'scores') + ['title' => 'Penilaian']);
    }

    public function save(): void
    {
        $this->requireAuth();
        verify_csrf();
        $menus = $this->db->query("SELECT id_menu FROM tbl_menu WHERE status = 'aktif'")->fetchAll();
        $criteriaList = $this->db->query("SELECT id_kriteria FROM tbl_kriteria WHERE status = 'aktif'")->fetchAll();
        if (!$menus || !$criteriaList) {
            flash_message('warning', 'Data Belum Lengkap', 'Data menu atau kriteria belum tersedia.');
            redirect('penilaian');
        }

        $scores = $_POST['nilai'] ?? [];
        $errors = [];
        $validRows = [];
        foreach ($menus as $menu) {
            $menuId = (int) $menu['id_menu'];
            $rowHasPositiveValue = false;
            $rowHasBlockingError = false;

            foreach ($criteriaList as $criterion) {
                $criterionId = (int) $criterion['id_kriteria'];
                $key = 'nilai.' . $menuId . '.' . $criterionId;
                $value = $scores[$menuId][$criterionId] ?? null;
                $valueText = trim((string) $value);

                if ($value === null || $valueText === '') {
                    $errors[$key] = 'Nilai wajib diisi.';
                    $rowHasBlockingError = true;
                    continue;
                }

                if (!is_numeric($valueText) || (float) $valueText < 0 || (float) $valueText > 1) {
                    $errors[$key] = 'Nilai harus berada pada rentang 0.000 sampai 1.000.';
                    $rowHasBlockingError = true;
                    continue;
                }

                if (!preg_match('/^\d+(?:\.\d{1,3})?$/', $valueText)) {
                    $errors[$key] = 'Nilai maksimal 3 angka di belakang koma.';
                    $rowHasBlockingError = true;
                    continue;
                }

                if ((float) $valueText > 0) {
                    $rowHasPositiveValue = true;
                }

                $validRows[$menuId][$criterionId] = number_format((float) $valueText, 3, '.', '');
            }

            if (!$rowHasBlockingError && !$rowHasPositiveValue) {
                $errors['row.' . $menuId] = 'Minimal satu nilai pada baris menu harus lebih dari 0.';
            }
        }

        if ($errors) {
            with_form_errors($errors, 'Penilaian gagal disimpan. Periksa kembali data input.', 'error', 'Gagal');
            redirect('penilaian');
        }

        $stmt = $this->db->prepare('INSERT INTO tbl_penilaian (id_menu, id_kriteria, nilai_xij, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE nilai_xij=VALUES(nilai_xij), updated_at=NOW()');
        foreach ($validRows as $menuId => $criteria) {
            foreach ($criteria as $criterionId => $value) {
                $stmt->execute([(int) $menuId, (int) $criterionId, $value]);
            }
        }
        $this->logActivity('Penilaian', 'Simpan Penilaian', 'Menyimpan matriks penilaian Xij');
        flash_message('success', 'Berhasil', 'Penilaian berhasil disimpan.');
        redirect('penilaian');
    }
}
