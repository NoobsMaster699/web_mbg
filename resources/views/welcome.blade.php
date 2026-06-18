<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SPK Menu MBG AHP-SAW</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="#dashboard" aria-label="SPK Menu MBG">
                <span class="brand-mark">M</span>
                <span>
                    <strong>SPK MBG</strong>
                    <small>AHP + SAW</small>
                </span>
            </a>

            <nav class="nav-list" aria-label="Navigasi utama">
                <a href="#dashboard" class="nav-link active" data-section-link="dashboard">Dashboard</a>
                <a href="#menu" class="nav-link" data-section-link="menu">Data Menu</a>
                <a href="#penilaian" class="nav-link" data-section-link="penilaian">Penilaian</a>
                <a href="#perbandingan" class="nav-link" data-section-link="perbandingan">Perbandingan</a>
                <a href="#hasil" class="nav-link" data-section-link="hasil">Hasil</a>
                <a href="#laporan" class="nav-link" data-section-link="laporan">Laporan</a>
            </nav>

            <div class="sidebar-note">
                <span>Objek penelitian</span>
                <strong>SD Kecamatan Kapetakan</strong>
                <small>Penentuan menu Makan Bergizi Gratis terbaik.</small>
            </div>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Sistem Pendukung Keputusan</p>
                    <h1>Penentuan Menu MBG Terbaik</h1>
                </div>
                <div class="user-chip">
                    <span>{{ session('mbg_admin.role', 'Admin') }}</span>
                    <strong>{{ session('mbg_admin.name', 'Risa Hayatun Nupus') }}</strong>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </div>
            </header>

            <section class="hero-panel">
                <div>
                    <p class="eyebrow">Implementasi metode AHP dan SAW</p>
                    <h2>Analisis keputusan menu makan bergizi gratis untuk sekolah dasar.</h2>
                    <p>
                        Kriteria AHP menentukan bobot prioritas gizi, lalu SAW melakukan normalisasi
                        dan perangkingan alternatif menu berdasarkan nilai energi, protein, lemak,
                        karbohidrat, dan serat.
                    </p>
                </div>
                <div class="nutrition-visual" aria-hidden="true">
                    <span class="plate"></span>
                    <span class="food food-rice"></span>
                    <span class="food food-protein"></span>
                    <span class="food food-veg"></span>
                    <span class="food food-fruit"></span>
                </div>
            </section>

            <section id="dashboard" class="page-section active" data-section="dashboard">
                <div class="stats-grid">
                    <article class="stat-card">
                        <span>Kriteria Aktif</span>
                        <strong>5</strong>
                        <small>Energi, protein, lemak, karbohidrat, serat</small>
                    </article>
                    <article class="stat-card">
                        <span>Alternatif Menu</span>
                        <strong data-menu-count>5</strong>
                        <small>Data contoh dari laporan skripsi</small>
                    </article>
                    <article class="stat-card">
                        <span>Consistency Ratio</span>
                        <strong>0.037</strong>
                        <small>Matriks AHP konsisten</small>
                    </article>
                    <article class="stat-card highlight">
                        <span>Menu Terbaik</span>
                        <strong data-best-menu>Nasi + Telur Dadar</strong>
                        <small data-best-score>Skor 0.8990</small>
                    </article>
                </div>

                <div class="content-grid">
                    <article class="panel wide">
                        <div class="panel-header">
                            <div>
                                <p class="eyebrow">Ringkasan sekolah</p>
                                <h2>Rata-rata Gizi dan Skor SAW</h2>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sekolah</th>
                                        <th>Jenjang</th>
                                        <th>Menu</th>
                                        <th>Energi</th>
                                        <th>Protein</th>
                                        <th>Skor SAW</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>MI Tarbiatuttolabah</td>
                                        <td>SD</td>
                                        <td>10</td>
                                        <td>594.0</td>
                                        <td>22.8</td>
                                        <td><span class="badge good">0.8607</span></td>
                                    </tr>
                                    <tr>
                                        <td>RA Tarbiatuttolabah</td>
                                        <td>TK</td>
                                        <td>10</td>
                                        <td>502.0</td>
                                        <td>16.8</td>
                                        <td><span class="badge muted">0.7471</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="panel">
                        <p class="eyebrow">Bobot AHP</p>
                        <h2>Prioritas Kriteria</h2>
                        <div class="weights-list">
                            <div><span>Energi</span><meter value="0.4636" max="1"></meter><strong>46.36%</strong></div>
                            <div><span>Protein</span><meter value="0.2344" max="1"></meter><strong>23.44%</strong></div>
                            <div><span>Karbohidrat</span><meter value="0.1735" max="1"></meter><strong>17.35%</strong></div>
                            <div><span>Lemak</span><meter value="0.0804" max="1"></meter><strong>8.04%</strong></div>
                            <div><span>Serat</span><meter value="0.0484" max="1"></meter><strong>4.84%</strong></div>
                        </div>
                    </article>
                </div>
            </section>

            <section id="menu" class="page-section" data-section="menu">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <p class="eyebrow">Alternatif</p>
                            <h2>Data Menu MBG</h2>
                        </div>
                        <input class="search-input" type="search" placeholder="Cari menu" data-search-menu>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Menu</th>
                                    <th>Komponen</th>
                                    <th>Kategori</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody data-menu-table></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="penilaian" class="page-section" data-section="penilaian">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <p class="eyebrow">Input nilai</p>
                            <h2>Penilaian Kandungan Gizi Menu</h2>
                        </div>
                        <div class="action-row">
                            <button class="btn secondary" type="button" data-reset-scores>Reset</button>
                            <button class="btn primary" type="button" data-save-scores>Simpan</button>
                        </div>
                    </div>
                    <p class="notice">Masukkan nilai gizi per menu. Lemak dihitung sebagai kriteria cost, nilai lebih rendah lebih baik.</p>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Energi</th>
                                    <th>Protein</th>
                                    <th>Lemak</th>
                                    <th>Karbohidrat</th>
                                    <th>Serat</th>
                                </tr>
                            </thead>
                            <tbody data-score-table></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="perbandingan" class="page-section" data-section="perbandingan">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <p class="eyebrow">Comparison</p>
                            <h2>Perbandingan Menu per Kriteria</h2>
                        </div>
                    </div>
                    <div class="menu-selector" data-menu-selector></div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Energi</th>
                                    <th>Protein</th>
                                    <th>Lemak</th>
                                    <th>Karbohidrat</th>
                                    <th>Serat</th>
                                </tr>
                            </thead>
                            <tbody data-comparison-table></tbody>
                        </table>
                    </div>
                    <div class="chart-list" data-criteria-chart></div>
                </div>
            </section>

            <section id="hasil" class="page-section" data-section="hasil">
                <div class="recommendation">
                    <span>Rekomendasi terbaik</span>
                    <strong data-result-title>Nasi + Telur Dadar</strong>
                    <small data-result-detail>Nilai preferensi tertinggi berdasarkan bobot AHP dan normalisasi SAW.</small>
                </div>
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <p class="eyebrow">Ranking akhir</p>
                            <h2>Hasil Perhitungan AHP-SAW</h2>
                        </div>
                        <button class="btn primary" type="button" data-print-report>Cetak</button>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Alternatif</th>
                                    <th>Nilai Preferensi</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody data-ranking-table></tbody>
                        </table>
                    </div>
                    <div class="chart-list" data-ranking-chart></div>
                </div>
            </section>

            <section id="laporan" class="page-section" data-section="laporan">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <p class="eyebrow">Export laporan</p>
                            <h2>Preview Laporan Rekomendasi</h2>
                        </div>
                        <div class="action-row">
                            <select class="search-input" data-report-month>
                                <option>Januari 2026</option>
                                <option>Februari 2026</option>
                                <option>Maret 2026</option>
                                <option>April 2026</option>
                            </select>
                            <button class="btn primary" type="button" data-print-report>Unduh</button>
                        </div>
                    </div>
                    <article class="report-preview">
                        <h3>Laporan Sistem Pendukung Keputusan Menu MBG</h3>
                        <p>Metode: Analytical Hierarchy Process dan Simple Additive Weighting.</p>
                        <p data-report-summary>
                            Menu terbaik adalah Nasi + Telur Dadar dengan nilai preferensi tertinggi.
                        </p>
                        <div class="signature-row">
                            <span>Admin Program MBG</span>
                            <span>Kecamatan Kapetakan</span>
                        </div>
                    </article>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
