<?php
require 'inc.php';
wajib_level([1]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['do'] ?? '') === 'hapus') {
            $id = (int)$_POST['id'];
            $st = $conn->prepare('DELETE FROM supplier WHERE id = ?');
            $st->bind_param('i', $id);
            $st->execute();
            flash('success', 'Data supplier berhasil dihapus.');
        } else {
            $id     = (int)($_POST['id'] ?? 0);
            $nama   = trim($_POST['nama'] ?? '');
            $telp   = trim($_POST['telp'] ?? '');
            $alamat = trim($_POST['alamat'] ?? '');
            if ($nama === '') throw new Exception('Nama supplier wajib diisi.');

            if ($id) {
                $st = $conn->prepare('UPDATE supplier SET nama=?, telp=?, alamat=? WHERE id=?');
                $st->bind_param('sssi', $nama, $telp, $alamat, $id);
            } else {
                $st = $conn->prepare('INSERT INTO supplier (nama, telp, alamat) VALUES (?,?,?)');
                $st->bind_param('sss', $nama, $telp, $alamat);
            }
            $st->execute();
            flash('success', 'Data supplier berhasil disimpan.');
        }
    } catch (mysqli_sql_exception $ex) {
        flash('danger', 'Terjadi kesalahan database.');
    } catch (Exception $ex) {
        flash('danger', $ex->getMessage());
    }
    header('Location: supplier.php');
    exit;
}

$judul = 'Data Supplier';
tampil_header($judul);

if (($_GET['aksi'] ?? '') === 'form'):
    $id  = (int)($_GET['id'] ?? 0);
    $row = ['id' => 0, 'nama' => '', 'telp' => '', 'alamat' => ''];
    if ($id) {
        $st = $conn->prepare('SELECT * FROM supplier WHERE id = ?');
        $st->bind_param('i', $id);
        $st->execute();
        $row = $st->get_result()->fetch_assoc() ?: $row;
    }
?>
<h4 class="mb-3"><?= $id ? 'Ubah' : 'Tambah' ?> Supplier</h4>
<form method="post" style="max-width:520px">
  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
  <div class="mb-3"><label class="form-label">Nama</label>
    <input name="nama" class="form-control" maxlength="100" required value="<?= e($row['nama']) ?>"></div>
  <div class="mb-3"><label class="form-label">Telepon</label>
    <input name="telp" class="form-control" maxlength="12" value="<?= e($row['telp']) ?>"></div>
  <div class="mb-3"><label class="form-label">Alamat</label>
    <textarea name="alamat" class="form-control" rows="3"><?= e($row['alamat']) ?></textarea></div>
  <button class="btn btn-primary">Simpan</button>
  <a href="supplier.php" class="btn btn-secondary">Batal</a>
</form>
<?php else:
    $data = $conn->query('SELECT * FROM supplier ORDER BY id');
?>
<div class="d-flex justify-content-between mb-3">
  <h4>Data Supplier</h4>
  <a href="supplier.php?aksi=form" class="btn btn-primary">Tambah Supplier</a>
</div>
<table class="table table-bordered table-striped">
  <thead><tr><th>No</th><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
    <tr>
      <td><?= $no++ ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['telp']) ?></td><td><?= e($r['alamat']) ?></td>
      <td class="text-nowrap">
        <a href="supplier.php?aksi=form&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Ubah</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
          <input type="hidden" name="do" value="hapus"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-danger">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php endif;
tampil_footer(); ?>
