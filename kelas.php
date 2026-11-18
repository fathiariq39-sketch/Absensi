<?php
require 'config.php';
login_required(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        if (isset($_POST['hapus'])) {                                   // hapusKelas()
            $pdo->prepare('DELETE FROM kelas WHERE id_kelas = ?')->execute([$_POST['hapus']]);
            flash('Kelas dihapus.');
        } elseif (!empty($_POST['id_kelas'])) {                         // ubahKelas()
            $pdo->prepare('UPDATE kelas SET nama_kelas=?, jurusan=? WHERE id_kelas=?')
                ->execute([trim($_POST['nama_kelas']), trim($_POST['jurusan']), $_POST['id_kelas']]);
            flash('Kelas diperbarui.');
        } else {                                                        // TambahKelas()
            $pdo->prepare('INSERT INTO kelas (nama_kelas, jurusan) VALUES (?,?)')
                ->execute([trim($_POST['nama_kelas']), trim($_POST['jurusan'])]);
            flash('Kelas ditambahkan.');
        }
    } catch (PDOException $ex) {
        flash('Gagal: kelas masih dipakai oleh siswa/absensi.', 'err');
    }
    header('Location: kelas.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM kelas WHERE id_kelas=?'); $s->execute([$_GET['edit']]); $edit = $s->fetch();
}
$rows = $pdo->query('SELECT k.*, (SELECT COUNT(*) FROM siswa s WHERE s.id_kelas=k.id_kelas) jml FROM kelas k ORDER BY nama_kelas')->fetchAll();
$title = 'Data Kelas';
require 'header.php';
?>
<h1>Data Kelas</h1>
<div class="card">
  <h3><?= $edit ? 'Ubah Kelas' : 'Tambah Kelas' ?></h3>
  <form method="post" class="inline-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id_kelas" value="<?= e($edit['id_kelas'] ?? '') ?>">
    <input name="nama_kelas" placeholder="Nama kelas" value="<?= e($edit['nama_kelas'] ?? '') ?>" required>
    <input name="jurusan" placeholder="Jurusan" value="<?= e($edit['jurusan'] ?? '') ?>" required>
    <button class="btn">Simpan</button>
    <?php if ($edit): ?><a class="btn gray" href="kelas.php">Batal</a><?php endif; ?>
  </form>
</div>
<div class="card table-wrap">
<table>
  <tr><th>No</th><th>Nama Kelas</th><th>Jurusan</th><th>Jumlah Siswa</th><th>Aksi</th></tr>
  <?php foreach ($rows as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td><td><?= e($r['nama_kelas']) ?></td><td><?= e($r['jurusan']) ?></td><td><?= $r['jml'] ?></td>
    <td class="aksi">
      <a class="btn sm" href="?edit=<?= $r['id_kelas'] ?>">Ubah</a>
      <form method="post" onsubmit="return confirm('Hapus kelas ini?')">
        <?= csrf_field() ?><button class="btn sm red" name="hapus" value="<?= $r['id_kelas'] ?>">Hapus</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php require 'footer.php'; ?>
