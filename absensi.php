<?php
require 'config.php';
login_required(['guru']);
$idGuru = $_SESSION['user']['id_guru'];
$statusList = ['Hadir', 'Izin', 'Sakit', 'Alfa'];

$idKelas = (int)($_GET['id_kelas'] ?? $_POST['id_kelas'] ?? 0);
$tanggal = $_GET['tanggal'] ?? $_POST['tanggal'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['hapus_semua'])) {                                  // hapus()
        $pdo->prepare('DELETE FROM absensi WHERE id_kelas=? AND tanggal_absensi=?')->execute([$idKelas, $tanggal]);
        flash('Absensi tanggal ' . tgl($tanggal) . ' dihapus.');
    } else {                                                             // inputAbsensi() / ubah()
        $sql = 'INSERT INTO absensi (tanggal_absensi,status_kehadiran,keterangan,id_siswa,id_guru,id_kelas)
                VALUES (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE status_kehadiran=VALUES(status_kehadiran), keterangan=VALUES(keterangan), id_guru=VALUES(id_guru)';
        $st = $pdo->prepare($sql);
        foreach ($_POST['status'] ?? [] as $idSiswa => $status) {
            if (!in_array($status, $statusList)) continue;
            $st->execute([$tanggal, $status, trim($_POST['ket'][$idSiswa] ?? ''), (int)$idSiswa, $idGuru, $idKelas]);
        }
        flash('Absensi tanggal ' . tgl($tanggal) . ' berhasil disimpan.');
    }
    header("Location: absensi.php?id_kelas=$idKelas&tanggal=$tanggal"); exit;
}

$kelas = $pdo->query('SELECT * FROM kelas ORDER BY nama_kelas')->fetchAll();
$siswa = [];
if ($idKelas) {
    $st = $pdo->prepare('SELECT s.*, a.status_kehadiran, a.keterangan FROM siswa s
        LEFT JOIN absensi a ON a.id_siswa=s.id_siswa AND a.tanggal_absensi=?
        WHERE s.id_kelas=? ORDER BY s.nama_siswa');
    $st->execute([$tanggal, $idKelas]);
    $siswa = $st->fetchAll();
}
$sudah = $siswa && $siswa[0]['status_kehadiran'] !== null;
$title = 'Absensi';
require 'header.php';
?>
<h1>Absensi Siswa</h1>
<div class="card">
  <form method="get" class="inline-form">
    <select name="id_kelas" required>
      <option value="">-- Pilih kelas --</option>
      <?php foreach ($kelas as $k): ?><option value="<?= $k['id_kelas'] ?>" <?= $idKelas == $k['id_kelas'] ? 'selected' : '' ?>><?= e($k['nama_kelas']) ?></option><?php endforeach; ?>
    </select>
    <input type="date" name="tanggal" value="<?= e($tanggal) ?>" max="<?= date('Y-m-d') ?>" required>
    <button class="btn">Tampilkan</button>
  </form>
</div>

<?php if ($idKelas && !$siswa): ?>
  <div class="alert err">Belum ada siswa di kelas ini.</div>
<?php elseif ($siswa): ?>
<form method="post" class="card table-wrap">
  <?= csrf_field() ?>
  <input type="hidden" name="id_kelas" value="<?= $idKelas ?>">
  <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
  <h3><?= $sudah ? 'Ubah' : 'Isi' ?> absensi — <?= tgl($tanggal) ?></h3>
  <table>
    <tr><th>No</th><th>NIS</th><th>Nama</th><?php foreach ($statusList as $s): ?><th><?= $s ?></th><?php endforeach; ?><th>Keterangan</th></tr>
    <?php foreach ($siswa as $i => $s): $cur = $s['status_kehadiran'] ?? 'Hadir'; ?>
    <tr>
      <td><?= $i + 1 ?></td><td><?= e($s['nomor_induk_siswa']) ?></td><td><?= e($s['nama_siswa']) ?></td>
      <?php foreach ($statusList as $st): ?>
        <td class="c"><input type="radio" name="status[<?= $s['id_siswa'] ?>]" value="<?= $st ?>" <?= $cur === $st ? 'checked' : '' ?>></td>
      <?php endforeach; ?>
      <td><input name="ket[<?= $s['id_siswa'] ?>]" value="<?= e($s['keterangan'] ?? '') ?>" placeholder="opsional"></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div class="actions">
    <button class="btn">💾 Simpan Absensi</button>
    <?php if ($sudah): ?>
      <button class="btn red" name="hapus_semua" value="1" onclick="return confirm('Hapus seluruh absensi kelas ini pada tanggal tersebut?')">Hapus</button>
    <?php endif; ?>
  </div>
</form>
<?php endif; ?>
<?php require 'footer.php'; ?>
