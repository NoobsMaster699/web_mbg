USE db_mbg;

INSERT INTO tbl_users (username, password, role, created_at) VALUES
('admin', '$2y$10$35o03HvIFWZJM7gNbmvFke5jAZDQF95PtgqVH0LNL12qGALDUze9a', 'admin', NOW());

INSERT INTO tbl_kriteria (kode, nama, jenis, bobot, deskripsi, status) VALUES
('C1', 'Gizi', 'benefit', 0.350000, 'Menilai kecukupan energi, protein, vitamin, dan mineral sesuai standar gizi seimbang.', 'aktif'),
('C2', 'Biaya', 'cost', 0.250000, 'Menilai efisiensi biaya bahan makanan per porsi yang diperlukan.', 'aktif'),
('C3', 'Ketersediaan Bahan', 'benefit', 0.200000, 'Menilai ketersediaan bahan di pasar lokal dan kemudahan memperoleh bahan.', 'aktif'),
('C4', 'Penerimaan Siswa', 'benefit', 0.100000, 'Menilai tingkat kesukaan, penerimaan, dan potensi penolakan siswa.', 'aktif'),
('C5', 'Variasi Menu', 'benefit', 0.100000, 'Menilai keberagaman bahan, warna, rasa, dan variasi menu.', 'aktif');

INSERT INTO tbl_menu (kode, nama_menu, makanan_pokok, lauk, sayur, buah, berat_g, keterangan, status, created_at, updated_at) VALUES
('M01', 'Ayam Teriyaki, Nasi, Sayur Bayam, Buah Semangka', 'Nasi', 'Ayam Teriyaki', 'Sayur Bayam', 'Semangka', 350, 'Menu contoh MBG.', 'aktif', NOW(), NOW()),
('M02', 'Ikan Goreng, Nasi, Tempe, Sayur Bening, Buah Pisang', 'Nasi', 'Ikan Goreng dan Tempe', 'Sayur Bening', 'Pisang', 350, 'Menu contoh MBG.', 'aktif', NOW(), NOW()),
('M03', 'Telur Dadar, Nasi, Sayur, Buah Pepaya', 'Nasi', 'Telur Dadar', 'Sayur', 'Pepaya', 330, 'Menu contoh MBG.', 'aktif', NOW(), NOW()),
('M04', 'Ayam Katsu, Nasi, Sayur, Buah Apel', 'Nasi', 'Ayam Katsu', 'Sayur', 'Apel', 360, 'Menu contoh MBG.', 'aktif', NOW(), NOW()),
('M05', 'Daging Sapi, Nasi, Sayur Sop, Buah Jeruk', 'Nasi', 'Daging Sapi', 'Sayur Sop', 'Jeruk', 370, 'Menu contoh MBG.', 'aktif', NOW(), NOW());

INSERT INTO tbl_penilaian (id_menu, id_kriteria, nilai_xij, created_at, updated_at) VALUES
(1,1,0.942,NOW(),NOW()),(1,2,0.880,NOW(),NOW()),(1,3,0.905,NOW(),NOW()),(1,4,0.910,NOW(),NOW()),(1,5,0.870,NOW(),NOW()),
(2,1,0.872,NOW(),NOW()),(2,2,0.812,NOW(),NOW()),(2,3,0.860,NOW(),NOW()),(2,4,0.840,NOW(),NOW()),(2,5,0.790,NOW(),NOW()),
(3,1,0.814,NOW(),NOW()),(3,2,0.920,NOW(),NOW()),(3,3,0.760,NOW(),NOW()),(3,4,0.810,NOW(),NOW()),(3,5,0.740,NOW(),NOW()),
(4,1,0.723,NOW(),NOW()),(4,2,0.780,NOW(),NOW()),(4,3,0.700,NOW(),NOW()),(4,4,0.760,NOW(),NOW()),(4,5,0.690,NOW(),NOW()),
(5,1,0.689,NOW(),NOW()),(5,2,0.750,NOW(),NOW()),(5,3,0.680,NOW(),NOW()),(5,4,0.720,NOW(),NOW()),(5,5,0.660,NOW(),NOW());

INSERT INTO tbl_perbandingan (id_kriteria_i, id_kriteria_j, nilai_saaty) VALUES
(1,1,1),(1,2,3),(1,3,5),(1,4,4),(1,5,3),
(2,1,0.333333),(2,2,1),(2,3,2),(2,4,0.500000),(2,5,2),
(3,1,0.200000),(3,2,0.500000),(3,3,1),(3,4,0.333333),(3,5,1),
(4,1,0.250000),(4,2,2),(4,3,3),(4,4,1),(4,5,2),
(5,1,0.333333),(5,2,0.500000),(5,3,1),(5,4,0.500000),(5,5,1);

INSERT INTO tbl_hasil (id_menu, nilai_vi, ranking, cr_value, tanggal_hitung) VALUES
(1, 0.963068, 1, 0.055072, NOW()),
(2, 0.928070, 2, 0.055072, NOW()),
(3, 0.848270, 3, 0.055072, NOW()),
(4, 0.826538, 4, 0.055072, NOW()),
(5, 0.811257, 5, 0.055072, NOW());

INSERT INTO tbl_riwayat_laporan (tanggal, periode_dari, periode_sampai, menu_terbaik, nilai_vi, admin, file_path) VALUES
(NOW(), CURDATE(), CURDATE(), 'Ayam Teriyaki, Nasi, Sayur Bayam, Buah Semangka', 0.963068, 'admin', 'contoh-laporan.pdf');

INSERT INTO tbl_aktivitas (user_id, modul, aksi, deskripsi, status, created_at) VALUES
(1, 'Setup', 'Seeder', 'Data awal SPK MBG berhasil dimuat', 'Berhasil', NOW()),
(1, 'Perbandingan AHP', 'Hitung AHP', 'CR = 0.0551 konsisten', 'Berhasil', NOW()),
(1, 'Hasil Ranking SAW', 'Hitung SAW', 'Ranking awal menu berhasil dibuat', 'Berhasil', NOW());
