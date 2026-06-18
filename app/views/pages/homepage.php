<nav class="top-nav">
    <div class="container nav-inner">
        <a class="brand" href="<?= url() ?>"><img src="<?= asset('images/ui/logo-spk-mbg.svg') ?>" alt="SPK MBG"></a>
        <div class="nav-links">
            <a class="active" href="<?= url() ?>">Beranda</a>
            <a href="#fitur">Fitur</a>
            <a href="#proses">Proses AHP-SAW</a>
            <a href="<?= url('laporan') ?>">Laporan</a>
            <a href="#tentang">Tentang</a>
        </div>
        <a class="btn btn-primary" href="<?= auth_check() ? url('dashboard') : url('login') ?>">Login</a>
    </div>
</nav>
<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <span class="badge green">Sistem Cerdas untuk Gizi Anak Indonesia</span>
                <h1>Menentukan Menu Makan Bergizi Gratis Secara <span class="text-green">Objektif dan Terukur</span></h1>
                <p>SPK MBG membantu Sekolah Dasar di Kecamatan Kapetakan menentukan menu terbaik menggunakan metode Analytical Hierarchy Process (AHP) dan Simple Additive Weighting (SAW).</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="<?= auth_check() ? url('dashboard') : url('login') ?>">Mulai Sekarang</a>
                    <a class="btn btn-outline" href="<?= url('dashboard') ?>">Lihat Demo</a>
                </div>
                <div class="trust-row"><span>Objektif & Terukur</span><span>Mudah Digunakan</span><span>Transparan</span><span>Berbasis Data</span></div>
            </div>
            <div class="hero-preview">
                <div class="preview-shell">
                    <div class="preview-side"><div class="mini-logo">SPK MBG</div><div class="mini-nav"><span>Dashboard</span><span>Data Kriteria</span><span>Data Menu</span><span>Penilaian</span><span>Perbandingan AHP</span><span>Hasil Ranking SAW</span><span>Laporan</span></div></div>
                    <div class="preview-main">
                        <div class="kpi-grid"><div class="mini-card"><strong>5</strong>Menu</div><div class="mini-card"><strong>5</strong>Kriteria</div><div class="mini-card"><strong>0.015</strong>CR Valid</div><div class="mini-card"><strong>PDF</strong>Export</div></div>
                        <div class="chart-row"><div class="bars"><div class="bar" style="height:80%"></div><div class="bar" style="height:64%"></div><div class="bar" style="height:60%"></div><div class="bar" style="height:42%"></div></div><div class="mini-card"><div class="donut"></div></div></div>
                        <div class="table-wrap"><table class="data-table"><thead><tr><th>Ranking</th><th>Kode</th><th>Nama Menu</th><th>Nilai Vi</th><th>Status</th></tr></thead><tbody><tr><td><strong>1</strong></td><td>M01</td><td>Ayam Teriyaki, Nasi, Sayur Bayam, Buah</td><td><strong>0.942</strong></td><td><span class="badge green">Rekomendasi Utama</span></td></tr><tr><td><strong>2</strong></td><td>M02</td><td>Ikan Goreng, Nasi, Tempe, Sayur, Buah</td><td><strong>0.872</strong></td><td><span class="badge green">Sangat Baik</span></td></tr></tbody></table></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="features" id="fitur"><div class="container"><h2 class="section-title">Fitur Utama SPK MBG</h2><div class="feature-grid">
        <?php foreach ([['dashboard','Dashboard','Ringkasan data dan statistik sistem.'],['kriteria','Data Kriteria','Atur kriteria dan bobot.'],['menu','Data Menu','Kelola menu MBG dan komposisi.'],['penilaian','Penilaian','Input nilai menu berdasarkan kriteria.'],['ahp','Perbandingan AHP','Hitung bobot prioritas kriteria.'],['hasil','Hasil Ranking SAW','Lihat menu terbaik otomatis.'],['laporan','Laporan PDF/Excel','Export hasil keputusan.'],['riwayat','Riwayat Aktivitas','Pantau log perubahan sistem.']] as $feature): ?>
            <a class="card feature-card" href="<?= url($feature[0]) ?>"><div class="icon">*</div><h3><?= e($feature[1]) ?></h3><p><?= e($feature[2]) ?></p></a>
        <?php endforeach; ?>
    </div></div></section>
    <section class="process" id="proses"><div class="container"><h2 class="section-title">Alur Proses Keputusan (AHP - SAW)</h2><div class="steps">
        <?php foreach (['Input Data','Hitung AHP','Validasi CR','Hitung SAW','Ranking','Export Laporan'] as $step): ?><div class="step"><div class="circle">*</div><h3><?= e($step) ?></h3><p class="muted">Tahapan sistem SPK MBG.</p></div><?php endforeach; ?>
    </div><div class="stats-strip"><div class="stat-item"><span>*</span><div><b>5</b>Menu Contoh</div></div><div class="stat-item"><span>*</span><div><b>5</b>Kriteria</div></div><div class="stat-item"><span>*</span><div><b><= 0.1</b>CR Valid</div></div><div class="stat-item"><span>*</span><div><b>PDF/Excel</b>Export</div></div></div></div></section>
    <section class="method" id="tentang"><div class="container method-grid"><div class="card method-card"><h2>AHP</h2><p>Analytical Hierarchy Process menentukan bobot kriteria melalui perbandingan berpasangan.</p></div><div class="card method-card"><h2>SAW</h2><p>Simple Additive Weighting menghitung nilai akhir setiap alternatif dan menghasilkan ranking menu terbaik.</p></div></div></section>
</main>
<footer class="footer"><div class="container footer-grid"><div><img src="<?= asset('images/ui/logo-spk-mbg.svg') ?>" style="filter:brightness(0) invert(1);width:170px"><p>Mendukung program Makan Bergizi Gratis untuk generasi sehat, cerdas, dan berprestasi.</p></div><div><b>Navigasi</b><p>Beranda</p><p>Fitur</p><p>Proses AHP-SAW</p></div><div><b>Bantuan</b><p>Panduan Pengguna</p><p>FAQ</p><p>Kontak</p></div><div><b>UCIC 2026</b><p>Proyek Tugas Akhir - Program Studi Sistem Informasi</p><p>2026 SPK MBG. All rights reserved.</p></div></div></footer>
