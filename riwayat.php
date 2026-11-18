<?php
require 'config.php';
login_required(['siswa']);
$id = $_SESSION['user']['id_siswa'];

$st = $pdo->prepare('SELECT s.nama_siswa, s.nomor_induk_siswa, k.nama_kelas FROM siswa s JOIN kelas k USING(id_kelas) WHERE id_siswa=?');
$st->execute([$id]); $me = $st->fetch();

$st = $pdo->prepare('SELECT a.*, g.nama_guru FROM absensi a JOIN guru g USING(id_guru) WHERE a.id_siswa=? ORDER BY tanggal_absensi DESC');
$st->execute([$id]); $rows = $st->fetchAll();
$rekap = array_count_values(array_column($rows, 'status_kehadiran'));
$title = 'Riwayat Absensi';
require 'header.php';
?>
<h1>Riwayat Absensi</h1>
<p class="muted"><?= e($me['nama_siswa']) ?> · NIS <?= e($me['nomor_induk_siswa']) ?> · <?= e($me['nama_kelas']) ?></p>
<div class="grid">
  <?php foreach (['Hadir', 'Izin', 'Sakit', 'Alfa'] as $s): ?>
    <div class="stat"><b><?= $rekap[$s] ?? 0 ?></b><?= $s ?></div>
  <?php endforeach; ?>
</div>
<div class="card table-wrap">
<table>
  <tr><th>No</th><th>Tanggal</th><th>Status</th><th>Keterangan</th><th>Dicatat oleh</th></tr>
  <?php foreach ($rows as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td><td><?= tgl($r['tanggal_absensi']) ?></td>
    <td><span class="badge <?= strtolower($r['status_kehadiran']) ?>"><?= $r['status_kehadiran'] ?></span></td>
    <td><?= e($r['keterangan']) ?></td><td><?= e($r['nama_guru']) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="5" class="c">Belum ada data absensi.</td></tr><?php endif; ?>
</table>
</div>
<?php require 'footer.php'; ?>
