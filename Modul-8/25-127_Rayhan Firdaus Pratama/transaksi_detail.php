<?php
require 'includes/auth.php';
require 'includes/koneksi.php';
wajib_level([1, 2]);

$id = (int)($_GET['id'] ?? 0);
$st = $conn->prepare("SELECT t.*, p.nama AS pelanggan, pb.metode, pb.waktu_bayar
    FROM transaksi t
    LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
    LEFT JOIN pembayaran pb ON pb.transaksi_id = t.id
    WHERE t.id = ?");
$st->bind_param('i', $id);
$st->execute();
$t = $st->get_result()->fetch_assoc();
if (!$t) { flash('danger', 'Transaksi tidak ditemukan.'); header('Location: transaksi.php'); exit; }

$st = $conn->prepare("SELECT b.kode_barang, b.nama_barang, d.harga, d.qty
    FROM transaksi_detail d JOIN barang b ON b.id = d.barang_id WHERE d.transaksi_id = ?");
$st->bind_param('i', $id);
$st->execute();
$items = $st->get_result();

$judul = 'Detail Transaksi';
require 'includes/header.php';
?>
<h4 class="mb-3">Detail Transaksi #<?= $t['id'] ?></h4>
<dl class="row">
  <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9"><?= e($t['waktu_transaksi']) ?></dd>
  <dt class="col-sm-3">Pelanggan</dt><dd class="col-sm-9"><?= e($t['pelanggan'] ?? '-') ?></dd>
  <dt class="col-sm-3">Keterangan</dt><dd class="col-sm-9"><?= e($t['keterangan']) ?></dd>
  <dt class="col-sm-3">Pembayaran</dt><dd class="col-sm-9"><?= e($t['metode'] ?? '-') ?> <?= $t['waktu_bayar'] ? '(' . e($t['waktu_bayar']) . ')' : '' ?></dd>
</dl>
<table class="table table-bordered">
  <thead><tr><th>Kode</th><th>Barang</th><th class="text-end">Harga</th><th class="text-end">Qty</th><th class="text-end">Subtotal</th></tr></thead>
  <tbody>
  <?php while ($r = $items->fetch_assoc()): ?>
    <tr><td><?= e($r['kode_barang']) ?></td><td><?= e($r['nama_barang']) ?></td>
      <td class="text-end"><?= rupiah($r['harga']) ?></td><td class="text-end"><?= $r['qty'] ?></td>
      <td class="text-end"><?= rupiah($r['harga'] * $r['qty']) ?></td></tr>
  <?php endwhile; ?>
  </tbody>
  <tfoot><tr><th colspan="4" class="text-end">Total</th><th class="text-end"><?= rupiah($t['total']) ?></th></tr></tfoot>
</table>
<a href="transaksi.php" class="btn btn-secondary">Kembali</a>
<?php require 'includes/footer.php'; ?>
