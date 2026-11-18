<?php
// Jalankan SEKALI untuk membuat akun contoh, lalu HAPUS file ini.
require 'config.php';
if ($pdo->query('SELECT COUNT(*) FROM pengguna')->fetchColumn() > 0) exit('Setup sudah pernah dijalankan. Hapus file setup.php.');

function buat($pdo, $nama, $email, $pass, $role) {
    $pdo->prepare('INSERT INTO pengguna (nama_pengguna,kata_sandi,email,hak_akses) VALUES (?,?,?,?)')
        ->execute([$nama, password_hash($pass, PASSWORD_DEFAULT), $email, $role]);
    return $pdo->lastInsertId();
}
$idp = buat($pdo, 'Administrator', 'admin@sekolah.test', 'admin123', 'admin');
$pdo->prepare('INSERT INTO admin (nama_admin,id_pengguna) VALUES (?,?)')->execute(['Administrator', $idp]);

$idp = buat($pdo, 'Bu Guru Contoh', 'guru@sekolah.test', 'guru123', 'guru');
$pdo->prepare('INSERT INTO guru (nama_guru,mata_pelajaran,id_pengguna) VALUES (?,?,?)')->execute(['Bu Guru Contoh', 'Pemrograman Web', $idp]);

foreach ([['Budi Santoso', '1001', 'siswa@sekolah.test'], ['Siti Aminah', '1002', 'siti@sekolah.test']] as [$n, $nis, $em]) {
    $idp = buat($pdo, $n, $em, 'siswa123', 'siswa');
    $pdo->prepare('INSERT INTO siswa (nomor_induk_siswa,nama_siswa,id_kelas,id_pengguna) VALUES (?,?,1,?)')->execute([$nis, $n, $idp]);
}
echo "Berhasil! Login:<br>admin@sekolah.test / admin123<br>guru@sekolah.test / guru123<br>siswa@sekolah.test / siswa123<br><b>Hapus setup.php sekarang.</b> <a href='index.php'>Ke login</a>";
