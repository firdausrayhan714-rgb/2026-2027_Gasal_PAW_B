<?php
require 'inc.php';
wajib_level([1, 2]);

$valid = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : '';
$dari   = $valid($_GET['dari'] ?? '');
$sampai = $valid($_GET['sampai'] ?? '');

$sql = "SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, t.pelanggan_id, p.nama AS pelanggan, pb.metode
    FROM transaksi t
    LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
    LEFT JOIN pembayaran pb ON pb.transaksi_id = t.id
    WHERE (? = '' OR t.waktu_transaksi >= ?) AND (? = '' OR t.waktu_transaksi <= ?)
    ORDER BY t.waktu_transaksi, t.id";
$st = $conn->prepare($sql);
$st->bind_param('ssss', $dari, $dari, $sampai, $sampai);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$grand = 0; $harian = []; $plg = [];
foreach ($rows as $r) {
    $grand += $r['total'];
    $harian[$r['waktu_transaksi']] = ($harian[$r['waktu_transaksi']] ?? 0) + $r['total'];
    if ($r['pelanggan_id'] !== null) $plg[$r['pelanggan_id']] = true;
}
$jmlPelanggan = count($plg);

if (($_GET['export'] ?? '') === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan_penjualan.xls');
    echo '<meta charset="utf-8">';
    echo '<h3>Laporan Penjualan</h3>';
    echo '<p>Periode: ' . e($dari ?: 'awal') . ' s/d ' . e($sampai ?: 'akhir') . '</p>';
    echo '<table border="1"><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Metode</th><th>Total</th></tr>';
    foreach ($rows as $r) {
        echo '<tr><td>' . $r['id'] . '</td><td>' . e($r['waktu_transaksi']) . '</td><td>' . e($r['pelanggan'] ?? '-')
           . '</td><td>' . e($r['keterangan']) . '</td><td>' . e($r['metode'] ?? '-') . '</td><td>' . (int)$r['total'] . '</td></tr>';
    }
    echo '<tr><th colspan="5">Total Penjualan</th><th>' . $grand . '</th></tr>';
    echo '<tr><th colspan="5">Jumlah Pelanggan</th><th>' . $jmlPelanggan . '</th></tr></table>';
    exit;
}

$judul = 'Laporan';
tampil_header($judul);
?>
<h4 class="mb-3">Laporan Penjualan</h4>
<form method="get" class="row g-2 mb-3 d-print-none">
  <div class="col-auto"><input type="date" name="dari" class="form-control" value="<?= e($dari) ?>"></div>
  <div class="col-auto align-self-center">s/d</div>
  <div class="col-auto"><input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>"></div>
  <div class="col-auto"><button class="btn btn-primary">Tampilkan</button>
    <a href="laporan.php" class="btn btn-secondary">Reset</a></div>
</form>

<div class="mb-3 d-print-none">
  <button type="button" class="btn btn-danger" onclick="window.print()">Export PDF</button>
  <a href="laporan.php?export=excel&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>" class="btn btn-success">Export Excel</a>
  <span class="text-muted small ms-2">Pada Export PDF, pilih printer "Save as PDF".</span>
</div>

<?php if ($rows): ?>
<h5>Grafik Penjualan per Tanggal</h5>
<div class="mb-4" style="max-width:800px; height:320px">
  <canvas id="grafik"></canvas>
</div>
<?php endif; ?>

<table class="table table-bordered table-striped">
  <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Metode</th><th class="text-end">Total</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= $r['id'] ?></td><td><?= e($r['waktu_transaksi']) ?></td><td><?= e($r['pelanggan'] ?? '-') ?></td>
      <td><?= e($r['keterangan']) ?></td><td><?= e($r['metode'] ?? '-') ?></td>
      <td class="text-end"><?= rupiah($r['total']) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr><th colspan="5" class="text-end">Total Penjualan</th><th class="text-end"><?= rupiah($grand) ?></th></tr>
    <tr><th colspan="5" class="text-end">Jumlah Pelanggan</th><th class="text-end"><?= $jmlPelanggan ?> orang</th></tr>
  </tfoot>
</table>

<?php if ($rows): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('grafik'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_keys($harian)) ?>,
    datasets: [{ label: 'Total Penjualan', data: <?= json_encode(array_values($harian)) ?>, backgroundColor: '#0d6efd' }]
  },
  options: {
    maintainAspectRatio: false,
    animation: false,
    scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } },
    plugins: { tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } } }
  }
});
</script>
<?php endif; ?>
<?php tampil_footer(); ?>
