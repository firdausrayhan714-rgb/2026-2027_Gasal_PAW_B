<?php
require 'inc.php';
wajib_level([1]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['do'] ?? '') === 'hapus') {
            $id = (int)$_POST['id'];
            $st = $conn->prepare('DELETE FROM barang WHERE id = ?');
            $st->bind_param('i', $id);
            $st->execute();
            flash('success', 'Data barang berhasil dihapus.');
        } else {
            $id    = (int)($_POST['id'] ?? 0);
            $kode  = trim($_POST['kode_barang'] ?? '');
            $nama  = trim($_POST['nama_barang'] ?? '');
            $harga = (int)($_POST['harga'] ?? 0);
            $stok  = (int)($_POST['stok'] ?? 0);
            $sup   = ($_POST['supplier_id'] ?? '') !== '' ? (int)$_POST['supplier_id'] : null;
            if ($kode === '' || $nama === '') throw new Exception('Kode dan nama barang wajib diisi.');
            if ($harga < 0 || $stok < 0) throw new Exception('Harga dan stok tidak boleh negatif.');

            if ($id) {
                $st = $conn->prepare('UPDATE barang SET kode_barang=?, nama_barang=?, harga=?, stok=?, supplier_id=? WHERE id=?');
                $st->bind_param('ssiiii', $kode, $nama, $harga, $stok, $sup, $id);
            } else {
                $st = $conn->prepare('INSERT INTO barang (kode_barang, nama_barang, harga, stok, supplier_id) VALUES (?,?,?,?,?)');
                $st->bind_param('ssiii', $kode, $nama, $harga, $stok, $sup);
            }
            $st->execute();
            flash('success', 'Data barang berhasil disimpan.');
        }
    } catch (mysqli_sql_exception $ex) {
        $c = $ex->getCode();
        flash('danger', $c == 1062 ? 'Kode barang sudah digunakan.' : ($c == 1451 ? 'Barang tidak dapat dihapus karena sudah dipakai di transaksi.' : 'Terjadi kesalahan database.'));
    } catch (Exception $ex) {
        flash('danger', $ex->getMessage());
    }
    header('Location: barang.php');
    exit;
}

$judul = 'Data Barang';
tampil_header($judul);

if (($_GET['aksi'] ?? '') === 'form'):
    $id  = (int)($_GET['id'] ?? 0);
    $row = ['id' => 0, 'kode_barang' => '', 'nama_barang' => '', 'harga' => '', 'stok' => 0, 'supplier_id' => null];
    if ($id) {
        $st = $conn->prepare('SELECT * FROM barang WHERE id = ?');
        $st->bind_param('i', $id);
        $st->execute();
        $row = $st->get_result()->fetch_assoc() ?: $row;
    }
    $supplier = $conn->query('SELECT id, nama FROM supplier ORDER BY nama');
?>
<h4 class="mb-3"><?= $id ? 'Ubah' : 'Tambah' ?> Barang</h4>
<form method="post" style="max-width:520px">
  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
  <div class="mb-3"><label class="form-label">Kode Barang</label>
    <input name="kode_barang" class="form-control" maxlength="10" required value="<?= e($row['kode_barang']) ?>"></div>
  <div class="mb-3"><label class="form-label">Nama Barang</label>
    <input name="nama_barang" class="form-control" maxlength="100" required value="<?= e($row['nama_barang']) ?>"></div>
  <div class="mb-3"><label class="form-label">Harga</label>
    <input type="number" name="harga" class="form-control" min="0" required value="<?= e($row['harga']) ?>"></div>
  <div class="mb-3"><label class="form-label">Stok</label>
    <input type="number" name="stok" class="form-control" min="0" required value="<?= e($row['stok']) ?>"></div>
  <div class="mb-3"><label class="form-label">Supplier</label>
    <select name="supplier_id" class="form-select">
      <option value="">- Tanpa supplier -</option>
      <?php while ($s = $supplier->fetch_assoc()): ?>
      <option value="<?= $s['id'] ?>" <?= $s['id'] == $row['supplier_id'] ? 'selected' : '' ?>><?= e($s['nama']) ?></option>
      <?php endwhile; ?>
    </select></div>
  <button class="btn btn-primary">Simpan</button>
  <a href="barang.php" class="btn btn-secondary">Batal</a>
</form>
<?php else:
    $data = $conn->query('SELECT b.*, s.nama AS supplier FROM barang b LEFT JOIN supplier s ON s.id = b.supplier_id ORDER BY b.id');
?>
<div class="d-flex justify-content-between mb-3">
  <h4>Data Barang</h4>
  <a href="barang.php?aksi=form" class="btn btn-primary">Tambah Barang</a>
</div>
<table class="table table-bordered table-striped">
  <thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Harga</th><th>Stok</th><th>Supplier</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
    <tr>
      <td><?= $no++ ?></td><td><?= e($r['kode_barang']) ?></td><td><?= e($r['nama_barang']) ?></td>
      <td><?= rupiah($r['harga']) ?></td><td><?= $r['stok'] ?></td><td><?= e($r['supplier'] ?? '-') ?></td>
      <td class="text-nowrap">
        <a href="barang.php?aksi=form&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Ubah</a>
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
