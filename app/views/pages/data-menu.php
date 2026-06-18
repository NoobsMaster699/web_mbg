<?php
$oldInput = old_input_data();
$oldForm = (string) ($oldInput['_form'] ?? '');
$formHasErrors = fn(string $form): bool => $oldForm === $form;
$fieldValue = function (string $field, mixed $default, string $form) use ($oldInput, $oldForm): mixed {
    return $oldForm === $form ? ($oldInput[$field] ?? $default) : $default;
};
$fieldClass = fn(string $field, string $form): string => $formHasErrors($form) ? field_error_class($field) : '';
$fieldAttrs = fn(string $field, string $form): string => $formHasErrors($form) ? field_error_attrs($field) : '';
$fieldHtml = fn(string $field, string $form): string => $formHasErrors($form) ? field_error_html($field) : '';
$storeForm = 'store-menu';
?>

<div class="page-head">
    <div>
        <h1>Data Menu</h1>
        <p class="muted">Kelola menu MBG beserta komposisi dan skor hasil SAW.</p>
    </div>
</div>

<form class="toolbar" method="get" action="<?= url('menu') ?>">
    <input class="input" name="q" value="<?= e($search) ?>" placeholder="Cari menu atau bahan makanan...">
    <select class="select" name="status">
        <option value="">Semua Status</option>
        <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
    </select>
    <button class="btn btn-primary" type="submit">Cari</button>
    <a class="btn btn-outline" href="<?= url('menu') ?>">Reset</a>
</form>

<div class="spk-alert spk-alert-info" style="margin-bottom:20px">Menu aktif akan digunakan sebagai alternatif dalam perhitungan SAW.</div>

<div class="grid-4">
    <div class="card metric"><div class="micon">M</div><div><h2><?= count($menus) ?></h2>Total Menu</div></div>
    <div class="card metric"><div class="micon">A</div><div><h2><?= count(array_filter($menus, fn($m) => $m['status'] === 'aktif')) ?></h2>Menu Aktif</div></div>
    <div class="card metric"><div class="micon">Vi</div><div><h2><?= e(number_format((float) (array_sum(array_column($menus, 'nilai_vi')) / max(count($menus), 1)), 3)) ?></h2>Rata-rata Vi</div></div>
    <div class="card metric"><div class="micon">T</div><div><h2><?= date('d M Y') ?></h2>Terakhir Dibuka</div></div>
</div>

<div class="grid-2" style="grid-template-columns:1.8fr .7fr;margin-top:20px">
    <div class="card panel">
        <h2>Daftar Menu <span class="badge gray"><?= count($menus) ?> Data</span></h2>

        <?php if (!$menus): ?>
            <?php $title='Belum ada data menu.'; $message='Tambahkan menu MBG terlebih dahulu agar dapat dinilai dan diranking.'; require app_path('views/components/empty-state.php'); ?>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table menu-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Menu</th>
                            <th>Makanan Pokok</th>
                            <th>Lauk</th>
                            <th>Sayur</th>
                            <th>Buah</th>
                            <th>Nilai Vi</th>
                            <th>Status</th>
                            <th class="action-highlight">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                            <?php $modalId = 'menu-edit-' . (int) $menu['id_menu']; ?>
                            <tr>
                                <td><strong><?= e($menu['kode']) ?></strong></td>
                                <td><?= e($menu['nama_menu']) ?></td>
                                <td><?= e($menu['makanan_pokok']) ?></td>
                                <td><?= e($menu['lauk']) ?></td>
                                <td><?= e($menu['sayur']) ?></td>
                                <td><?= e($menu['buah']) ?></td>
                                <td><span class="score <?= $menu['nilai_vi'] < 0.7 ? 'orange' : '' ?>"><?= e(number_format((float) $menu['nilai_vi'], 3)) ?></span></td>
                                <td><span class="badge <?= $menu['status'] === 'aktif' ? 'green' : 'gray' ?>"><?= e(ucfirst($menu['status'])) ?></span></td>
                                <td class="action-highlight">
                                    <div class="action-row">
                                        <button class="btn btn-soft btn-sm" type="button" data-modal-open="<?= e($modalId) ?>">Edit</button>
                                        <form class="inline-form" method="post" action="<?= url('menu/delete/' . $menu['id_menu']) ?>" data-confirm="Apakah Anda yakin ingin menghapus atau menonaktifkan menu ini?" data-loading-text="Menonaktifkan...">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline btn-sm" type="submit">Nonaktifkan</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php foreach ($menus as $menu): ?>
                <?php
                $menuId = (int) $menu['id_menu'];
                $modalId = 'menu-edit-' . $menuId;
                $formKey = 'edit-menu-' . $menuId;
                $currentStatus = (string) $fieldValue('status', $menu['status'], $formKey);
                ?>
                <div class="app-modal-backdrop" data-modal-backdrop="<?= e($modalId) ?>" hidden></div>
                <div class="app-modal" data-modal="<?= e($modalId) ?>" role="dialog" aria-modal="true" aria-labelledby="<?= e($modalId) ?>-title" <?= $formHasErrors($formKey) ? 'data-modal-auto-open' : 'hidden' ?>>
                    <div class="app-modal-card menu-edit-modal">
                        <div class="modal-header">
                            <div>
                                <span class="badge green">Edit Menu</span>
                                <h2 id="<?= e($modalId) ?>-title">Ubah <?= e($menu['kode']) ?> - <?= e($menu['nama_menu']) ?></h2>
                                <p class="muted">Perbarui komposisi menu tanpa membuka dropdown di tabel.</p>
                            </div>
                            <button class="modal-close" type="button" aria-label="Tutup modal" data-modal-close="<?= e($modalId) ?>">&times;</button>
                        </div>

                        <form class="form-grid modal-form" method="post" action="<?= url('menu/update/' . $menuId) ?>" data-loading-text="Menyimpan...">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_form" value="<?= e($formKey) ?>">

                            <label>Kode
                                <input class="<?= e($fieldClass('kode', $formKey)) ?>" name="kode" value="<?= e($fieldValue('kode', $menu['kode'], $formKey)) ?>" required<?= $fieldAttrs('kode', $formKey) ?>>
                                <?= $fieldHtml('kode', $formKey) ?>
                            </label>
                            <label>Nama Menu
                                <input class="<?= e($fieldClass('nama_menu', $formKey)) ?>" name="nama_menu" value="<?= e($fieldValue('nama_menu', $menu['nama_menu'], $formKey)) ?>" required<?= $fieldAttrs('nama_menu', $formKey) ?>>
                                <?= $fieldHtml('nama_menu', $formKey) ?>
                            </label>
                            <label>Makanan Pokok
                                <input class="<?= e($fieldClass('makanan_pokok', $formKey)) ?>" name="makanan_pokok" value="<?= e($fieldValue('makanan_pokok', $menu['makanan_pokok'], $formKey)) ?>" required<?= $fieldAttrs('makanan_pokok', $formKey) ?>>
                                <?= $fieldHtml('makanan_pokok', $formKey) ?>
                            </label>
                            <label>Lauk
                                <input class="<?= e($fieldClass('lauk', $formKey)) ?>" name="lauk" value="<?= e($fieldValue('lauk', $menu['lauk'], $formKey)) ?>" required<?= $fieldAttrs('lauk', $formKey) ?>>
                                <?= $fieldHtml('lauk', $formKey) ?>
                            </label>
                            <label>Sayur
                                <input class="<?= e($fieldClass('sayur', $formKey)) ?>" name="sayur" value="<?= e($fieldValue('sayur', $menu['sayur'], $formKey)) ?>" required<?= $fieldAttrs('sayur', $formKey) ?>>
                                <?= $fieldHtml('sayur', $formKey) ?>
                            </label>
                            <label>Buah
                                <input class="<?= e($fieldClass('buah', $formKey)) ?>" name="buah" value="<?= e($fieldValue('buah', $menu['buah'], $formKey)) ?>" required<?= $fieldAttrs('buah', $formKey) ?>>
                                <?= $fieldHtml('buah', $formKey) ?>
                            </label>
                            <label>Berat Porsi (gram)
                                <input class="<?= e($fieldClass('berat_g', $formKey)) ?>" name="berat_g" type="number" min="1" value="<?= e($fieldValue('berat_g', $menu['berat_g'], $formKey)) ?>" required<?= $fieldAttrs('berat_g', $formKey) ?>>
                                <?= $fieldHtml('berat_g', $formKey) ?>
                            </label>
                            <label>Status
                                <select class="<?= e($fieldClass('status', $formKey)) ?>" name="status" required<?= $fieldAttrs('status', $formKey) ?>>
                                    <option value="aktif" <?= $currentStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $currentStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                                <?= $fieldHtml('status', $formKey) ?>
                            </label>
                            <label class="full-span">Keterangan
                                <textarea name="keterangan" placeholder="Tambahkan keterangan"><?= e($fieldValue('keterangan', $menu['keterangan'], $formKey)) ?></textarea>
                            </label>
                            <div class="modal-actions">
                                <button class="btn btn-outline" type="button" data-modal-close="<?= e($modalId) ?>">Batal</button>
                                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php $storeStatus = (string) $fieldValue('status', 'aktif', $storeForm); ?>
    <div class="card drawer">
        <h2>Tambah Menu</h2>
        <form class="form-grid" method="post" action="<?= url('menu/store') ?>" data-loading-text="Menyimpan...">
            <?= csrf_field() ?>
            <input type="hidden" name="_form" value="<?= e($storeForm) ?>">
            <label>Kode
                <input class="<?= e($fieldClass('kode', $storeForm)) ?>" name="kode" value="<?= e($fieldValue('kode', '', $storeForm)) ?>" placeholder="M06" required<?= $fieldAttrs('kode', $storeForm) ?>>
                <?= $fieldHtml('kode', $storeForm) ?>
            </label>
            <label>Nama Menu
                <input class="<?= e($fieldClass('nama_menu', $storeForm)) ?>" name="nama_menu" value="<?= e($fieldValue('nama_menu', '', $storeForm)) ?>" placeholder="Ayam Teriyaki, Nasi, Sayur Bayam" required<?= $fieldAttrs('nama_menu', $storeForm) ?>>
                <?= $fieldHtml('nama_menu', $storeForm) ?>
            </label>
            <label>Makanan Pokok
                <input class="<?= e($fieldClass('makanan_pokok', $storeForm)) ?>" name="makanan_pokok" value="<?= e($fieldValue('makanan_pokok', '', $storeForm)) ?>" placeholder="Nasi Putih" required<?= $fieldAttrs('makanan_pokok', $storeForm) ?>>
                <?= $fieldHtml('makanan_pokok', $storeForm) ?>
            </label>
            <label>Lauk
                <input class="<?= e($fieldClass('lauk', $storeForm)) ?>" name="lauk" value="<?= e($fieldValue('lauk', '', $storeForm)) ?>" placeholder="Ayam Teriyaki" required<?= $fieldAttrs('lauk', $storeForm) ?>>
                <?= $fieldHtml('lauk', $storeForm) ?>
            </label>
            <label>Sayur
                <input class="<?= e($fieldClass('sayur', $storeForm)) ?>" name="sayur" value="<?= e($fieldValue('sayur', '', $storeForm)) ?>" placeholder="Sayur Bayam" required<?= $fieldAttrs('sayur', $storeForm) ?>>
                <?= $fieldHtml('sayur', $storeForm) ?>
            </label>
            <label>Buah
                <input class="<?= e($fieldClass('buah', $storeForm)) ?>" name="buah" value="<?= e($fieldValue('buah', '', $storeForm)) ?>" placeholder="Semangka" required<?= $fieldAttrs('buah', $storeForm) ?>>
                <?= $fieldHtml('buah', $storeForm) ?>
            </label>
            <label>Berat Porsi (gram)
                <input class="<?= e($fieldClass('berat_g', $storeForm)) ?>" name="berat_g" type="number" min="1" value="<?= e($fieldValue('berat_g', '', $storeForm)) ?>" placeholder="350" required<?= $fieldAttrs('berat_g', $storeForm) ?>>
                <?= $fieldHtml('berat_g', $storeForm) ?>
            </label>
            <label>Status
                <select class="<?= e($fieldClass('status', $storeForm)) ?>" name="status" required<?= $fieldAttrs('status', $storeForm) ?>>
                    <option value="aktif" <?= $storeStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $storeStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
                <?= $fieldHtml('status', $storeForm) ?>
            </label>
            <label>Keterangan
                <textarea name="keterangan" placeholder="Tambahkan keterangan"><?= e($fieldValue('keterangan', '', $storeForm)) ?></textarea>
            </label>
            <button class="btn btn-primary" type="submit">Simpan Menu</button>
        </form>
    </div>
</div>
