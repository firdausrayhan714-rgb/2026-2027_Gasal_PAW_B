<?php
require 'includes/auth.php';
require 'includes/koneksi.php';
tolak_jika_login();

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $st = $conn->prepare('SELECT id_user, username, password, nama, level FROM user WHERE username = ?');
        $st->bind_param('s', $username);
        $st->execute();
        $u = $st->get_result()->fetch_assoc();

        // password di tabel user disimpan dalam bentuk MD5
        if (!$u || !hash_equals($u['password'], md5($password))) {
            $error = 'Username atau password salah.';
        } elseif (!in_array((int)$u['level'], [1, 2], true)) {
            $error = 'Akun Anda tidak memiliki hak akses untuk login.';
        } else {
            session_regenerate_id(true);
            $_SESSION['id_user']  = $u['id_user'];
            $_SESSION['username'] = $u['username'];
            $_SESSION['nama']     = $u['nama'];
            $_SESSION['level']    = (int)$u['level'];
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Sistem Penjualan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:400px; margin-top:12vh">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h4 class="mb-4 text-center">Sistem Penjualan</h4>
      <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= e($username) ?>" maxlength="30" autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control">
        </div>
        <button class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
