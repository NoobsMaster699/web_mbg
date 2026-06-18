CREATE DATABASE IF NOT EXISTS db_mbg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_mbg;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tbl_aktivitas;
DROP TABLE IF EXISTS tbl_riwayat_laporan;
DROP TABLE IF EXISTS tbl_hasil;
DROP TABLE IF EXISTS tbl_perbandingan;
DROP TABLE IF EXISTS tbl_penilaian;
DROP TABLE IF EXISTS tbl_menu;
DROP TABLE IF EXISTS tbl_kriteria;
DROP TABLE IF EXISTS tbl_users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE tbl_users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'admin',
    created_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE tbl_kriteria (
    id_kriteria INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jenis ENUM('benefit','cost') NOT NULL,
    bobot DECIMAL(10,6) NOT NULL DEFAULT 0,
    deskripsi TEXT,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB;

CREATE TABLE tbl_menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama_menu VARCHAR(255) NOT NULL,
    makanan_pokok VARCHAR(100),
    lauk VARCHAR(100),
    sayur VARCHAR(100),
    buah VARCHAR(100),
    berat_g INT DEFAULT 0,
    keterangan TEXT,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE tbl_penilaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_menu INT NOT NULL,
    id_kriteria INT NOT NULL,
    nilai_xij DECIMAL(10,6) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_penilaian (id_menu, id_kriteria),
    CONSTRAINT fk_penilaian_menu FOREIGN KEY (id_menu) REFERENCES tbl_menu(id_menu) ON DELETE CASCADE,
    CONSTRAINT fk_penilaian_kriteria FOREIGN KEY (id_kriteria) REFERENCES tbl_kriteria(id_kriteria) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tbl_perbandingan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kriteria_i INT NOT NULL,
    id_kriteria_j INT NOT NULL,
    nilai_saaty DECIMAL(10,6) NOT NULL,
    UNIQUE KEY uq_perbandingan (id_kriteria_i, id_kriteria_j),
    CONSTRAINT fk_perbandingan_i FOREIGN KEY (id_kriteria_i) REFERENCES tbl_kriteria(id_kriteria) ON DELETE CASCADE,
    CONSTRAINT fk_perbandingan_j FOREIGN KEY (id_kriteria_j) REFERENCES tbl_kriteria(id_kriteria) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tbl_hasil (
    id_hasil INT AUTO_INCREMENT PRIMARY KEY,
    id_menu INT NOT NULL,
    nilai_vi DECIMAL(10,6) NOT NULL,
    ranking INT NOT NULL,
    cr_value DECIMAL(10,6),
    tanggal_hitung DATETIME NOT NULL,
    CONSTRAINT fk_hasil_menu FOREIGN KEY (id_menu) REFERENCES tbl_menu(id_menu) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tbl_riwayat_laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATETIME NOT NULL,
    periode_dari DATE,
    periode_sampai DATE,
    menu_terbaik VARCHAR(255),
    nilai_vi DECIMAL(10,6),
    admin VARCHAR(100),
    file_path VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE tbl_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    modul VARCHAR(100) NOT NULL,
    aksi VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    status VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_aktivitas_user FOREIGN KEY (user_id) REFERENCES tbl_users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB;

