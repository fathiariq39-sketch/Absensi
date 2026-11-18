<?php
require 'config.php';
if (!empty($_SESSION['user'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = $pdo->prepare('SELECT * FROM pengguna WHERE email = ?');
    $st->execute([trim($_POST['email'] ?? '')]);
    $p = $st->fetch();
    if ($p && password_verify($_POST['kata_sandi'] ?? '', $p['kata_sandi'])) {   // login valid?
        $role = $p['hak_akses'];
        $nama = $p['nama_pengguna']; $ref = null;
        if ($role !== 'admin') {
            $st = $pdo->prepare("SELECT * FROM $role WHERE id_pengguna = ?");
            $st->execute([$p['id_pengguna']]);
            $ref = $st->fetch();
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $p['id_pengguna'], 'nama' => $nama, 'hak_akses' => $role,
            'id_guru'  => $role === 'guru'  ? $ref['id_guru']  : null,
            'id_siswa' => $role === 'siswa' ? $ref['id_siswa'] : null,
        ];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Email atau kata sandi salah.';
}
$title = 'Login';
require 'header.php';
?>
<div class="login-box card">
  <h2>Login</h2>
  <p class="muted">Masuk sebagai Admin, Guru, atau Siswa</p>
  <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Email</label>
    <input type="email" name="email" required autofocus>
    <label>Kata Sandi</label>
    <input type="password" name="kata_sandi" required>
    <button class="btn" type="submit">Masuk</button>
  </form>
</div>
<?php require 'footer.php'; ?>
