<?php $u = $_SESSION['user'] ?? null; $role = $u['hak_akses'] ?? ''; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Absensi') ?> - Absensi Siswa</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php if ($u): ?>
<nav class="nav">
  <div class="brand">📋 Absensi Siswa</div>
  <div class="links">
    <a href="dashboard.php">Dashboard</a>
    <?php if ($role === 'admin'): ?>
      <a href="siswa.php">Data Siswa</a>
      <a href="guru.php">Data Guru</a>
      <a href="kelas.php">Data Kelas</a>
      <a href="laporan.php">Laporan</a>
    <?php elseif ($role === 'guru'): ?>
      <a href="absensi.php">Absensi</a>
      <a href="laporan.php">Laporan</a>
    <?php else: ?>
      <a href="riwayat.php">Riwayat Absensi</a>
    <?php endif; ?>
    <a href="logout.php" class="logout">Keluar (<?= e($u['nama']) ?>)</a>
  </div>
</nav>
<?php endif; ?>
<main class="container">
<?php if ($f = flash()): ?><div class="alert <?= $f[1] ?>"><?= e($f[0]) ?></div><?php endif; ?>
