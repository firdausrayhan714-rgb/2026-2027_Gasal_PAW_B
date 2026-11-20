<?php
require 'includes/auth.php';
require 'includes/koneksi.php';
wajib_level([1, 2]);

$valid = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : '';
$dari   = $valid($_GET['dari'] ?? '');
$sampai = $valid($_GET['sampai'] ?? '');

$sql = "SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS pelanggan, pb.metode
    FROM transaksi t
    LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
    LEFT JOIN pembayaran pb ON pb.transaksi_id = t.id
    WHERE (? = '' OR t.waktu_transaksi >= ?) AND (? = '' OR t.waktu_transaksi <= ?)
    ORDER BY t.waktu_transaksi, t.id";
$st = $conn->prepare($sql);
$st->bind_param('ssss', $dari, $dari, $sampai, $sampai);
$st->execute();
$data = $st->get_result();

$judul = 'Laporan';
require 'includes/header.php';
$grand = 0;
?>
<h4 class="mb-3">Laporan Penjualan</h4>
<form method="get" class="row g-2 mb-3">
  <div class="col-auto"><input type="date" name="dari" class="form-control" value="<?= e($dari) ?>"></div>
  <div class="col-auto align-self-center">s/d</div>
  <div class="col-auto"><input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>"></div>
  <div class="col-auto"><button class="btn btn-primary">Tampilkan</button>
    <a href="laporan.php" class="btn btn-secondary">Reset</a></div>
</form>
<table class="table table-bordered table-striped">
  <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Metode</th><th class="text-end">Total</th></tr></thead>
  <tbody>
  <?php while ($r = $data->fetch_assoc()): $grand += $r['total']; ?>
    <tr><td><?= $r['id'] ?></td><td><?= e($r['waktu_transaksi']) ?></td><td><?= e($r['pelanggan'] ?? '-') ?></td>
      <td><?= e($r['keterangan']) ?></td><td><?= e($r['metode'] ?? '-') ?></td>
      <td class="text-end"><?= rupiah($r['total']) ?></td></tr>
  <?php endwhile; ?>
  </tbody>
  <tfoot><tr><th colspan="5" class="text-end">Total Penjualan</th><th class="text-end"><?= rupiah($grand) ?></th></tr></tfoot>
</table>
<?php require 'includes/footer.php'; ?>
