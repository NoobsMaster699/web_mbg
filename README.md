# SPK MBG - Sistem Pendukung Keputusan Menu Makan Bergizi Gratis

Implementasi PHP MVC sederhana dari bundle slicing frontend `spk-mbg-slicing-2026` untuk proyek SPK MBG Kecamatan Kapetakan. Aplikasi memakai metode AHP untuk bobot kriteria dan SAW untuk ranking menu.

## Struktur

- `public/index.php` - front controller dan route aplikasi.
- `public/assets/` - CSS, JS, logo, dan gambar referensi dari bundle slicing.
- `app/controllers/` - controller halaman dan aksi POST.
- `app/services/AhpService.php` - perhitungan bobot AHP, CI, RI, CR.
- `app/services/SawService.php` - normalisasi SAW, nilai Vi, dan ranking.
- `app/views/layouts/` - partial layout admin dan guest.
- `app/views/pages/` - hasil konversi halaman slicing ke PHP.
- `database/schema.sql` - struktur database MySQL `db_mbg`.
- `database/seeder.sql` - data awal admin, kriteria, menu, penilaian, dan matriks AHP.
- `storage/reports/` - target file laporan bila export PDF/Excel sudah diintegrasikan.

## Cara menjalankan di Laragon/XAMPP

1. Salin folder proyek ke `www` Laragon atau `htdocs` XAMPP.
2. Jalankan Apache dan MySQL.
3. Buka phpMyAdmin, lalu import berurutan:
   - `database/schema.sql`
   - `database/seeder.sql`
4. Pastikan konfigurasi database di `app/config/database.php` sesuai:
   - database: `db_mbg`
   - username: `root`
   - password: kosong, kecuali instalasi lokal Anda berbeda.
5. Akses aplikasi dari browser:
   - Laragon: `http://spk-mbg-slicing-2026.test` atau virtual host yang dibuat Laragon.
   - XAMPP: `http://localhost/spk-mbg-slicing-2026/public`.

## Akun admin

- Username: `admin`
- Password: `admin123`

## Fitur aktif

- Homepage dan login sesuai slicing.
- Session login/logout admin dengan `password_verify`.
- CSRF token untuk semua form POST.
- Dashboard memakai data database.
- CRUD menu sederhana dengan nonaktif sebagai aksi hapus aman.
- CRUD kriteria sederhana.
- Simpan matriks penilaian Xij.
- Hitung AHP, validasi CR, dan simpan bobot jika konsisten.
- Hitung SAW, simpan ranking, dan tampilkan rekomendasi menu terbaik.
- Preview laporan dan pencatatan riwayat export.
- Riwayat aktivitas dengan filter modul.

## Placeholder

- Export PDF/Excel sudah disiapkan route dan controller-nya, tetapi belum memakai mPDF/TCPDF atau PhpSpreadsheet.
- Import Excel pada halaman penilaian masih placeholder UI.

