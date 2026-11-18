<?php
require 'config.php';
login_required(['admin', 'guru']);

$idKelas = (int)($_GET['id_kelas'] ?? 0);
$dari    = $_GET['dari']   ?? date('Y-m-01');
$sampai  = $_GET['sampai'] ?? date('Y-m-d');

$kelas = $pdo->query('SELECT * FROM kelas ORDER BY nama_kelas')->fetchAll();
$sql = "SELECT s.nomor_induk_siswa, s.nama_siswa, k.nama_kelas,
          COALESCE(SUM(a.status_kehadiran='Hadir'),0) hadir,
          COALESCE(SUM(a.status_kehadiran='Izin'),0)  izin,
          COALESCE(SUM(a.status_kehadiran='Sakit'),0) sakit,
          COALESCE(SUM(a.status_kehadiran='Alfa'),0)  alfa,
          COUNT(a.id_absensi) total
        FROM siswa s JOIN kelas k ON k.id_kelas=s.id_kelas
        LEFT JOIN absensi a ON a.id_siswa=s.id_siswa AND a.tanggal_absensi BETWEEN ? AND ?
        " . ($idKelas ? 'WHERE s.id_kelas=?' : '') . "
        GROUP BY s.id_siswa ORDER BY k.nama_kelas, s.nama_siswa";
$st = $pdo->prepare($sql);
$st->execute($idKelas ? [$dari, $sampai, $idKelas] : [$dari, $sampai]);
$rows = $st->fetchAll();

if (isset($_GET['csv'])) {                                              // file_laporan
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_absensi_' . $dari . '_' . $sampai . '.csv"');
    $o = fopen('php://output', 'w');
    fputcsv($o, ['NIS', 'Nama', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alfa', 'Total']);
    foreach ($rows as $r) fputcsv($o, [$r['nomor_induk_siswa'], $r['nama_siswa'], $r['nama_kelas'], $r['hadir'], $r['izin'], $r['sakit'], $r['alfa'], $r['total']]);
    exit;
}
$title = 'Laporan Absensi';
require 'header.php';
?>
<h1>Laporan Absensi</h1>
<div class="card no-print">
  <form method="get" class="inline-form">
    <select name="id_kelas">
      <option value="0">Semua kelas</option>
      <?php foreach ($kelas as $k): ?><option value="<?= $k['id_kelas'] ?>" <?= $idKelas == $k['id_kelas'] ? 'selected' : '' ?>><?= e($k['nama_kelas']) ?></option><?php endforeach; ?>
    </select>
    <input type="date" name="dari" value="<?= e($dari) ?>">
    <input type="date" name="sampai" value="<?= e($sampai) ?>">
    <button class="btn">Tampilkan</button>
    <button class="btn gray" type="button" onclick="window.print()">🖨 Cetak</button>
    <button class="btn gray" name="csv" value="1">⬇ CSV</button>
  </form>
</div>
<div class="card table-wrap">
  <h3>Periode <?= tgl($dari) ?> s/d <?= tgl($sampai) ?></h3>
  <table>
    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alfa</th><th>% Hadir</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
      <td><?= $i + 1 ?></td><td><?= e($r['nomor_induk_siswa']) ?></td><td><?= e($r['nama_siswa']) ?></td><td><?= e($r['nama_kelas']) ?></td>
      <td><?= $r['hadir'] ?></td><td><?= $r['izin'] ?></td><td><?= $r['sakit'] ?></td><td><?= $r['alfa'] ?></td>
      <td><?= $r['total'] ? round($r['hadir'] / $r['total'] * 100) . '%' : '-' ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="9" class="c">Tidak ada data.</td></tr><?php endif; ?>
  </table>
</div>
<?php require 'footer.php'; ?>
