-- Database Aplikasi Absensi Siswa (berdasarkan Class Diagram)
CREATE DATABASE IF NOT EXISTS db_absensi CHARACTER SET utf8mb4;
USE db_absensi;

CREATE TABLE pengguna (
  id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
  nama_pengguna VARCHAR(100) NOT NULL,
  kata_sandi VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  hak_akses ENUM('admin','guru','siswa') NOT NULL
);

CREATE TABLE kelas (
  id_kelas INT AUTO_INCREMENT PRIMARY KEY,
  nama_kelas VARCHAR(50) NOT NULL,
  jurusan VARCHAR(50) NOT NULL
);

CREATE TABLE admin (
  id_admin INT AUTO_INCREMENT PRIMARY KEY,
  nama_admin VARCHAR(100) NOT NULL,
  id_pengguna INT NOT NULL,
  FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
);

CREATE TABLE guru (
  id_guru INT AUTO_INCREMENT PRIMARY KEY,
  nama_guru VARCHAR(100) NOT NULL,
  mata_pelajaran VARCHAR(100) NOT NULL,
  id_pengguna INT NOT NULL,
  FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
);

CREATE TABLE siswa (
  id_siswa INT AUTO_INCREMENT PRIMARY KEY,
  nomor_induk_siswa VARCHAR(30) NOT NULL UNIQUE,
  nama_siswa VARCHAR(100) NOT NULL,
  id_kelas INT NOT NULL,
  id_pengguna INT NOT NULL,
  FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas),
  FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
);

CREATE TABLE absensi (
  id_absensi INT AUTO_INCREMENT PRIMARY KEY,
  tanggal_absensi DATE NOT NULL,
  status_kehadiran ENUM('Hadir','Izin','Sakit','Alfa') NOT NULL,
  keterangan VARCHAR(255) DEFAULT '',
  id_siswa INT NOT NULL,
  id_guru INT NOT NULL,
  id_kelas INT NOT NULL,
  UNIQUE KEY uq_siswa_tanggal (id_siswa, tanggal_absensi),
  FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE,
  FOREIGN KEY (id_guru)  REFERENCES guru(id_guru)   ON DELETE CASCADE,
  FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas)
);

INSERT INTO kelas (nama_kelas, jurusan) VALUES
 ('X RPL 1','Rekayasa Perangkat Lunak'),
 ('XI RPL 1','Rekayasa Perangkat Lunak'),
 ('XII TKJ 1','Teknik Komputer & Jaringan');
-- Laporan absensi dibuat otomatis dari tabel absensi (lihat laporan.php)
