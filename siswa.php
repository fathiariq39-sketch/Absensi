<?php
require 'config.php';
login_required(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        if (isset($_POST['hapus'])) {
            $s = $pdo->prepare('SELECT id_pengguna FROM siswa WHERE id_siswa=?'); $s->execute([$_POST['hapus']]);
            $pdo->prepare('DELETE FROM pengguna WHERE id_pengguna=?')->execute([$s->fetchColumn()]); // cascade ke siswa & absensi
            flash('Data siswa dihapus.');
        } else {
            $pdo->beginTransaction();
            $nama = trim($_POST['nama_siswa']); $email = trim($_POST['email']); $pass = $_POST['kata_sandi'] ?? '';
            if (!empty($_POST['id_siswa'])) {
                $s = $pdo->prepare('SELECT id_pengguna FROM siswa WHERE id_siswa=?'); $s->execute([$_POST['id_siswa']]);
                $idp = $s->fetchColumn();
                $pdo->prepare('UPDATE pengguna SET nama_pengguna=?, email=? WHERE id_pengguna=?')->execute([$nama, $email, $idp]);
                if ($pass !== '') $pdo->prepare('UPDATE pengguna SET kata_sandi=? WHERE id_pengguna=?')->execute([password_hash($pass, PASSWORD_DEFAULT), $idp]);
                $pdo->prepare('UPDATE siswa SET nomor_induk_siswa=?, nama_siswa=?, id_kelas=? WHERE id_siswa=?')
                    ->execute([trim($_POST['nomor_induk_siswa']), $nama, $_POST['id_kelas'], $_POST['id_siswa']]);
                flash('Data siswa diperbarui.');
            } else {
                if ($pass === '') throw new Exception('Kata sandi wajib diisi untuk siswa baru.');
                $pdo->prepare("INSERT INTO pengguna (nama_pengguna,kata_sandi,email,hak_akses) VALUES (?,?,?,'siswa')")
                    ->execute([$nama, password_hash($pass, PASSWORD_DEFAULT), $email]);
                $pdo->prepare('INSERT INTO siswa (nomor_induk_siswa,nama_siswa,id_kelas,id_pengguna) VALUES (?,?,?,?)')
                    ->execute([trim($_POST['nomor_induk_siswa']), $nama, $_POST['id_kelas'], $pdo->lastInsertId()]);
                flash('Siswa ditambahkan.');
            }
            $pdo->commit();
        }
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash($ex instanceof PDOException ? 'Gagal: NIS atau email sudah dipakai.' : $ex->getMessage(), 'err');
    }
    header('Location: siswa.php'); exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare('SELECT s.*, p.email FROM siswa s JOIN pengguna p USING(id_pengguna) WHERE id_siswa=?');
    $s->execute([$_GET['edit']]); $edit = $s->fetch();
}
$kelas = $pdo->query('SELECT * FROM kelas ORDER BY nama_kelas')->fetchAll();
$rows = $pdo->query('SELECT s.*, k.nama_kelas, p.email FROM siswa s JOIN kelas k USING(id_kelas) JOIN pengguna p USING(id_pengguna) ORDER BY k.nama_kelas, s.nama_siswa')->fetchAll();
$title = 'Data Siswa';
require 'header.php';
?>
<h1>Data Siswa</h1>
<div class="card">
  <h3><?= $edit ? 'Ubah Siswa' : 'Tambah Siswa' ?></h3>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="id_siswa" value="<?= e($edit['id_siswa'] ?? '') ?>">
    <div><label>NIS</label><input name="nomor_induk_siswa" value="<?= e($edit['nomor_induk_siswa'] ?? '') ?>" required></div>
    <div><label>Nama Siswa</label><input name="nama_siswa" value="<?= e($edit['nama_siswa'] ?? '') ?>" required></div>
    <div><label>Kelas</label>
      <select name="id_kelas" required>
        <?php foreach ($kelas as $k): ?><option value="<?= $k['id_kelas'] ?>" <?= ($edit['id_kelas'] ?? '') == $k['id_kelas'] ? 'selected' : '' ?>><?= e($k['nama_kelas']) ?></option><?php endforeach; ?>
      </select></div>
    <div><label>Email (untuk login)</label><input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>" required></div>
    <div><label>Kata Sandi <?= $edit ? '(kosongkan jika tidak diubah)' : '' ?></label><input type="password" name="kata_sandi" <?= $edit ? '' : 'required' ?>></div>
    <div class="end"><button class="btn">Simpan</button> <?php if ($edit): ?><a class="btn gray" href="siswa.php">Batal</a><?php endif; ?></div>
  </form>
</div>
<div class="card table-wrap">
<table>
  <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Email</th><th>Aksi</th></tr>
  <?php foreach ($rows as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td><td><?= e($r['nomor_induk_siswa']) ?></td><td><?= e($r['nama_siswa']) ?></td>
    <td><?= e($r['nama_kelas']) ?></td><td><?= e($r['email']) ?></td>
    <td class="aksi">
      <a class="btn sm" href="?edit=<?= $r['id_siswa'] ?>">Ubah</a>
      <form method="post" onsubmit="return confirm('Hapus siswa ini beserta data absensinya?')">
        <?= csrf_field() ?><button class="btn sm red" name="hapus" value="<?= $r['id_siswa'] ?>">Hapus</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php require 'footer.php'; ?>
