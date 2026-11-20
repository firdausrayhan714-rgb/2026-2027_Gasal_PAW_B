<?php
require 'includes/auth.php';
require 'includes/koneksi.php';
wajib_level([1, 2]);
$judul = 'Transaksi';
require 'includes/header.php';

$data = $conn->query("SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS pelanggan, pb.metode
    FROM transaksi t
    LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
    LEFT JOIN pembayaran pb ON pb.transaksi_id = t.id
    ORDER BY t.waktu_transaksi DESC, t.id DESC");
?>
<div class="d-flex justify-content-between mb-3">
  <h4>Transaksi</h4>
  <a href="transaksi_tambah.php" class="btn btn-primary">Tambah Transaksi</a>
</div>
<table class="table table-bordered table-striped">
  <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Metode</th><th class="text-end">Total</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php while ($r = $data->fetch_assoc()): ?>
    <tr>
      <td><?= $r['id'] ?></td><td><?= e($r['waktu_transaksi']) ?></td><td><?= e($r['pelanggan'] ?? '-') ?></td>
      <td><?= e($r['keterangan']) ?></td><td><?= e($r['metode'] ?? '-') ?></td>
      <td class="text-end"><?= rupiah($r['total']) ?></td>
      <td><a href="transaksi_detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">Detail</a></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php require 'includes/footer.php'; ?>
