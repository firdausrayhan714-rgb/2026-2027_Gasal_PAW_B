<?php
require 'inc.php';
wajib_level([1]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['do'] ?? '') === 'hapus') {
            $id = $_POST['id'];
            $st = $conn->prepare('DELETE FROM pelanggan WHERE id = ?');
            $st->bind_param('s', $id);
            $st->execute();
            flash('success', 'Data pelanggan berhasil dihapus.');
        } else {
            $mode   = $_POST['mode'] ?? 'tambah';
            $id     = trim($_POST['id'] ?? '');
            $nama   = trim($_POST['nama'] ?? '');
            $jk     = $_POST['jenis_kelamin'] ?? '';
            $telp   = trim($_POST['telp'] ?? '');
            $alamat = trim($_POST['alamat'] ?? '');
            if ($id === '' || $nama === '' || $alamat === '') throw new Exception('ID, nama, dan alamat wajib diisi.');
            if (!in_array($jk, ['L', 'P'], true)) throw new Exception('Jenis kelamin tidak valid.');

            if ($mode === 'edit') {
                $st = $conn->prepare('UPDATE pelanggan SET nama=?, jenis_kelamin=?, telp=?, alamat=? WHERE id=?');
                $st->bind_param('sssss', $nama, $jk, $telp, $alamat, $id);
            } else {
                $st = $conn->prepare('INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES (?,?,?,?,?)');
                $st->bind_param('sssss', $id, $nama, $jk, $telp, $alamat);
            }
            $st->execute();
            flash('success', 'Data pelanggan berhasil disimpan.');
        }
    } catch (mysqli_sql_exception $ex) {
        flash('danger', $ex->getCode() == 1062 ? 'ID pelanggan sudah digunakan.' : 'Terjadi kesalahan database.');
    } catch (Exception $ex) {
        flash('danger', $ex->getMessage());
    }
    header('Location: pelanggan.php');
    exit;
}

$judul = 'Data Pelanggan';
tampil_header($judul);

if (($_GET['aksi'] ?? '') === 'form'):
    $id   = $_GET['id'] ?? '';
    $mode = 'tambah';
    $row  = ['id' => '', 'nama' => '', 'jenis_kelamin' => 'L', 'telp' => '', 'alamat' => ''];
    if ($id !== '') {
        $st = $conn->prepare('SELECT * FROM pelanggan WHERE id = ?');
        $st->bind_param('s', $id);
        $st->execute();
        if ($found = $st->get_result()->fetch_assoc()) { $row = $found; $mode = 'edit'; }
    }
?>
<h4 class="mb-3"><?= $mode === 'edit' ? 'Ubah' : 'Tambah' ?> Pelanggan</h4>
<form method="post" style="max-width:520px">
  <input type="hidden" name="mode" value="<?= $mode ?>">
  <div class="mb-3"><label class="form-label">ID Pelanggan</label>
    <input name="id" class="form-control" maxlength="20" required value="<?= e($row['id']) ?>" <?= $mode === 'edit' ? 'readonly' : '' ?>></div>
  <div class="mb-3"><label class="form-label">Nama</label>
    <input name="nama" class="form-control" maxlength="100" required value="<?= e($row['nama']) ?>"></div>
  <div class="mb-3"><label class="form-label">Jenis Kelamin</label>
    <select name="jenis_kelamin" class="form-select">
      <option value="L" <?= $row['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
      <option value="P" <?= $row['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
    </select></div>
  <div class="mb-3"><label class="form-label">Telepon</label>
    <input name="telp" class="form-control" maxlength="12" value="<?= e($row['telp']) ?>"></div>
  <div class="mb-3"><label class="form-label">Alamat</label>
    <textarea name="alamat" class="form-control" rows="3" required><?= e($row['alamat']) ?></textarea></div>
  <button class="btn btn-primary">Simpan</button>
  <a href="pelanggan.php" class="btn btn-secondary">Batal</a>
</form>
<?php else:
    $data = $conn->query('SELECT * FROM pelanggan ORDER BY id');
?>
<div class="d-flex justify-content-between mb-3">
  <h4>Data Pelanggan</h4>
  <a href="pelanggan.php?aksi=form" class="btn btn-primary">Tambah Pelanggan</a>
</div>
<table class="table table-bordered table-striped">
  <thead><tr><th>ID</th><th>Nama</th><th>JK</th><th>Telepon</th><th>Alamat</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php while ($r = $data->fetch_assoc()): ?>
    <tr>
      <td><?= e($r['id']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['jenis_kelamin']) ?></td>
      <td><?= e($r['telp']) ?></td><td><?= e($r['alamat']) ?></td>
      <td class="text-nowrap">
        <a href="pelanggan.php?aksi=form&id=<?= urlencode($r['id']) ?>" class="btn btn-sm btn-warning">Ubah</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
          <input type="hidden" name="do" value="hapus"><input type="hidden" name="id" value="<?= e($r['id']) ?>">
          <button class="btn btn-sm btn-danger">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php endif;
tampil_footer(); ?>
