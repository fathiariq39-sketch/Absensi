<?php
require 'config.php';
login_required();
$u = $_SESSION['user']; $role = $u['hak_akses'];
$title = 'Dashboard';
require 'header.php';
$n = fn($q, $p = []) => (function() use ($pdo, $q, $p) { $s = $pdo->prepare($q); $s->execute($p); return $s->fetchColumn(); })();
?>
<h1>Halo, <?= e($u['nama']) ?> 👋</h1>
<p class="muted">Anda masuk sebagai <b><?= e(ucfirst($role)) ?></b>.</p>

<?php if ($role === 'admin'): ?>
<div class="grid">
  <a class="stat" href="siswa.php"><b><?= $n('SELECT COUNT(*) FROM siswa') ?></b>Siswa</a>
  <a class="stat" href="guru.php"><b><?= $n('SELECT COUNT(*) FROM guru') ?></b>Guru</a>
  <a class="stat" href="kelas.php"><b><?= $n('SELECT COUNT(*) FROM kelas') ?></b>Kelas</a>
  <a class="stat" href="laporan.php"><b><?= $n('SELECT COUNT(*) FROM absensi WHERE tanggal_absensi = CURDATE()') ?></b>Absensi hari ini</a>
</div>
<?php elseif ($role === 'guru'): ?>
<div class="grid">
  <a class="stat" href="absensi.php"><b>✔</b>Lakukan / ubah absensi</a>
  <a class="stat" href="laporan.php"><b>📄</b>Lihat laporan absensi</a>
</div>
<?php else: ?>
<div class="grid">
  <a class="stat" href="riwayat.php"><b><?= $n('SELECT COUNT(*) FROM absensi WHERE id_siswa = ?', [$u['id_siswa']]) ?></b>Catatan absensi saya</a>
</div>
<?php endif; ?>
<?php require 'footer.php'; ?>
