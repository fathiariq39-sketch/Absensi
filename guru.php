<?php
require 'config.php';
login_required(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        if (isset($_POST['hapus'])) {
            $s = $pdo->prepare('SELECT id_pengguna FROM guru WHERE id_guru=?'); $s->execute([$_POST['hapus']]);
            $pdo->prepare('DELETE FROM pengguna WHERE id_pengguna=?')->execute([$s->fetchColumn()]);
            flash('Data guru dihapus.');
        } else {
            $pdo->beginTransaction();
            $nama = trim($_POST['nama_guru']); $email = trim($_POST['email']); $pass = $_POST['kata_sandi'] ?? '';
            if (!empty($_POST['id_guru'])) {
                $s = $pdo->prepare('SELECT id_pengguna FROM guru WHERE id_guru=?'); $s->execute([$_POST['id_guru']]);
                $idp = $s->fetchColumn();
                $pdo->prepare('UPDATE pengguna SET nama_pengguna=?, email=? WHERE id_pengguna=?')->execute([$nama, $email, $idp]);
                if ($pass !== '') $pdo->prepare('UPDATE pengguna SET kata_sandi=? WHERE id_pengguna=?')->execute([password_hash($pass, PASSWORD_DEFAULT), $idp]);
                $pdo->prepare('UPDATE guru SET nama_guru=?, mata_pelajaran=? WHERE id_guru=?')
                    ->execute([$nama, trim($_POST['mata_pelajaran']), $_POST['id_guru']]);
                flash('Data guru diperbarui.');
            } else {
                if ($pass === '') throw new Exception('Kata sandi wajib diisi untuk guru baru.');
                $pdo->prepare("INSERT INTO pengguna (nama_pengguna,kata_sandi,email,hak_akses) VALUES (?,?,?,'guru')")
                    ->execute([$nama, password_hash($pass, PASSWORD_DEFAULT), $email]);
                $pdo->prepare('INSERT INTO guru (nama_guru,mata_pelajaran,id_pengguna) VALUES (?,?,?)')
                    ->execute([$nama, trim($_POST['mata_pelajaran']), $pdo->lastInsertId()]);
                flash('Guru ditambahkan.');
            }
            $pdo->commit();
        }
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash($ex instanceof PDOException ? 'Gagal: email sudah dipakai.' : $ex->getMessage(), 'err');
    }
    header('Location: guru.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT g.*, p.email FROM guru g JOIN pengguna p USING(id_pengguna) WHERE id_guru=?');
    $s->execute([$_GET['edit']]); $edit = $s->fetch();
}
$rows = $pdo->query('SELECT g.*, p.email FROM guru g JOIN pengguna p USING(id_pengguna) ORDER BY nama_guru')->fetchAll();
$title = 'Data Guru';
require 'header.php';
?>
<h1>Data Guru</h1>
<div class="card">
  <h3><?= $edit ? 'Ubah Guru' : 'Tambah Guru' ?></h3>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="id_guru" value="<?= e($edit['id_guru'] ?? '') ?>">
    <div><label>Nama Guru</label><input name="nama_guru" value="<?= e($edit['nama_guru'] ?? '') ?>" required></div>
    <div><label>Mata Pelajaran</label><input name="mata_pelajaran" value="<?= e($edit['mata_pelajaran'] ?? '') ?>" required></div>
    <div><label>Email (untuk login)</label><input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>" required></div>
    <div><label>Kata Sandi <?= $edit ? '(kosongkan jika tidak diubah)' : '' ?></label><input type="password" name="kata_sandi" <?= $edit ? '' : 'required' ?>></div>
    <div class="end"><button class="btn">Simpan</button> <?php if ($edit): ?><a class="btn gray" href="guru.php">Batal</a><?php endif; ?></div>
  </form>
</div>
<div class="card table-wrap">
<table>
  <tr><th>No</th><th>Nama</th><th>Mata Pelajaran</th><th>Email</th><th>Aksi</th></tr>
  <?php foreach ($rows as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td><td><?= e($r['nama_guru']) ?></td><td><?= e($r['mata_pelajaran']) ?></td><td><?= e($r['email']) ?></td>
    <td class="aksi">
      <a class="btn sm" href="?edit=<?= $r['id_guru'] ?>">Ubah</a>
      <form method="post" onsubmit="return confirm('Hapus guru ini?')">
        <?= csrf_field() ?><button class="btn sm red" name="hapus" value="<?= $r['id_guru'] ?>">Hapus</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php require 'footer.php'; ?>
