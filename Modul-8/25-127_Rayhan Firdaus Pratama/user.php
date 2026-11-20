<?php
require 'includes/auth.php';
require 'includes/koneksi.php';
wajib_level([1]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['do'] ?? '') === 'hapus') {
            $id = (int)$_POST['id'];
            if ($id === (int)$_SESSION['id_user']) throw new Exception('Akun yang sedang login tidak dapat dihapus.');
            $st = $conn->prepare('DELETE FROM user WHERE id_user = ?');
            $st->bind_param('i', $id);
            $st->execute();
            flash('success', 'Data user berhasil dihapus.');
        } else {
            $id       = (int)($_POST['id_user'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $nama     = trim($_POST['nama'] ?? '');
            $alamat   = trim($_POST['alamat'] ?? '');
            $hp       = trim($_POST['hp'] ?? '');
            $level    = (int)($_POST['level'] ?? 0);
            if ($username === '' || $nama === '') throw new Exception('Username dan nama wajib diisi.');
            if (!in_array($level, [1, 2, 3], true)) throw new Exception('Level tidak valid.');

            if ($id) {
                if ($password !== '') {
                    $hash = md5($password);
                    $st = $conn->prepare('UPDATE user SET username=?, password=?, nama=?, alamat=?, hp=?, level=? WHERE id_user=?');
                    $st->bind_param('sssssii', $username, $hash, $nama, $alamat, $hp, $level, $id);
                } else {
                    $st = $conn->prepare('UPDATE user SET username=?, nama=?, alamat=?, hp=?, level=? WHERE id_user=?');
                    $st->bind_param('ssssii', $username, $nama, $alamat, $hp, $level, $id);
                }
            } else {
                if ($password === '') throw new Exception('Password wajib diisi untuk user baru.');
                $hash = md5($password);
                $st = $conn->prepare('INSERT INTO user (username, password, nama, alamat, hp, level) VALUES (?,?,?,?,?,?)');
                $st->bind_param('sssssi', $username, $hash, $nama, $alamat, $hp, $level);
            }
            $st->execute();
            if ($id && $id === (int)$_SESSION['id_user']) {
                $_SESSION['nama'] = $nama;
                $_SESSION['level'] = $level;
            }
            flash('success', 'Data user berhasil disimpan.');
        }
    } catch (mysqli_sql_exception $ex) {
        flash('danger', $ex->getCode() == 1062 ? 'Username sudah digunakan.' : 'Terjadi kesalahan database.');
    } catch (Exception $ex) {
        flash('danger', $ex->getMessage());
    }
    header('Location: user.php');
    exit;
}

$judul = 'Data User';
require 'includes/header.php';

if (($_GET['aksi'] ?? '') === 'form'):
    $id  = (int)($_GET['id'] ?? 0);
    $row = ['id_user' => 0, 'username' => '', 'nama' => '', 'alamat' => '', 'hp' => '', 'level' => 2];
    if ($id) {
        $st = $conn->prepare('SELECT * FROM user WHERE id_user = ?');
        $st->bind_param('i', $id);
        $st->execute();
        $row = $st->get_result()->fetch_assoc() ?: $row;
    }
?>
<h4 class="mb-3"><?= $id ? 'Ubah' : 'Tambah' ?> User</h4>
<form method="post" style="max-width:520px" autocomplete="off">
  <input type="hidden" name="id_user" value="<?= (int)$row['id_user'] ?>">
  <div class="mb-3"><label class="form-label">Username</label>
    <input name="username" class="form-control" maxlength="30" required value="<?= e($row['username']) ?>"></div>
  <div class="mb-3"><label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?>>
    <?php if ($id): ?><div class="form-text">Kosongkan jika password tidak diubah.</div><?php endif; ?></div>
  <div class="mb-3"><label class="form-label">Nama</label>
    <input name="nama" class="form-control" maxlength="50" required value="<?= e($row['nama']) ?>"></div>
  <div class="mb-3"><label class="form-label">Alamat</label>
    <input name="alamat" class="form-control" maxlength="150" value="<?= e($row['alamat']) ?>"></div>
  <div class="mb-3"><label class="form-label">HP</label>
    <input name="hp" class="form-control" maxlength="20" value="<?= e($row['hp']) ?>"></div>
  <div class="mb-3"><label class="form-label">Level</label>
    <select name="level" class="form-select">
      <?php foreach ([1, 2, 3] as $l): ?>
      <option value="<?= $l ?>" <?= (int)$row['level'] === $l ? 'selected' : '' ?>><?= $l ?> - <?= nama_level($l) ?></option>
      <?php endforeach; ?>
    </select></div>
  <button class="btn btn-primary">Simpan</button>
  <a href="user.php" class="btn btn-secondary">Batal</a>
</form>
<?php else:
    $data = $conn->query('SELECT id_user, username, nama, alamat, hp, level FROM user ORDER BY id_user');
?>
<div class="d-flex justify-content-between mb-3">
  <h4>Data User</h4>
  <a href="user.php?aksi=form" class="btn btn-primary">Tambah User</a>
</div>
<table class="table table-bordered table-striped">
  <thead><tr><th>No</th><th>Username</th><th>Nama</th><th>Alamat</th><th>HP</th><th>Level</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
    <tr>
      <td><?= $no++ ?></td><td><?= e($r['username']) ?></td><td><?= e($r['nama']) ?></td>
      <td><?= e($r['alamat']) ?></td><td><?= e($r['hp']) ?></td><td><?= $r['level'] ?> - <?= nama_level($r['level']) ?></td>
      <td class="text-nowrap">
        <a href="user.php?aksi=form&id=<?= $r['id_user'] ?>" class="btn btn-sm btn-warning">Ubah</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
          <input type="hidden" name="do" value="hapus"><input type="hidden" name="id" value="<?= $r['id_user'] ?>">
          <button class="btn btn-sm btn-danger">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php endif;
require 'includes/footer.php'; ?>
