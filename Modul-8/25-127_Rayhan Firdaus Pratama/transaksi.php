<?php
require 'inc.php';
wajib_level([1, 2]);
$aksi = $_GET['aksi'] ?? 'list';

if ($aksi === 'tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tgl    = $_POST['waktu_transaksi'] ?? '';
        $ket    = trim($_POST['keterangan'] ?? '');
        $plg    = ($_POST['pelanggan_id'] ?? '') !== '' ? $_POST['pelanggan_id'] : null;
        $metode = $_POST['metode'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) throw new Exception('Tanggal tidak valid.');
        if (!in_array($metode, ['TUNAI', 'TRANSFER', 'EDC'], true)) throw new Exception('Metode pembayaran tidak valid.');

        // gabungkan barang yang dipilih lebih dari sekali
        $item = [];
        foreach (($_POST['barang_id'] ?? []) as $i => $bid) {
            $bid = (int)$bid; $q = (int)($_POST['qty'][$i] ?? 0);
            if ($bid > 0 && $q > 0) $item[$bid] = ($item[$bid] ?? 0) + $q;
        }
        if (!$item) throw new Exception('Pilih minimal satu barang.');

        $conn->begin_transaction();
        try {
            $st = $conn->prepare('INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id) VALUES (?,?,0,?)');
            $st->bind_param('sss', $tgl, $ket, $plg);
            $st->execute();
            $tid = $conn->insert_id;

            $total = 0;
            $cek = $conn->prepare('SELECT nama_barang, harga, stok FROM barang WHERE id = ? FOR UPDATE');
            $det = $conn->prepare('INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?,?,?,?)');
            $upd = $conn->prepare('UPDATE barang SET stok = stok - ? WHERE id = ?');
            foreach ($item as $bid => $q) {
                $cek->bind_param('i', $bid);
                $cek->execute();
                $b = $cek->get_result()->fetch_assoc();
                if (!$b) throw new Exception('Barang tidak ditemukan.');
                if ($b['stok'] < $q) throw new Exception('Stok ' . $b['nama_barang'] . ' tidak cukup (sisa ' . $b['stok'] . ').');
                $det->bind_param('iiii', $tid, $bid, $b['harga'], $q);
                $det->execute();
                $upd->bind_param('ii', $q, $bid);
                $upd->execute();
                $total += $b['harga'] * $q;
            }

            $st = $conn->prepare('UPDATE transaksi SET total = ? WHERE id = ?');
            $st->bind_param('ii', $total, $tid);
            $st->execute();

            $waktu = date('Y-m-d H:i:s');
            $st = $conn->prepare('INSERT INTO pembayaran (waktu_bayar, total, metode, transaksi_id) VALUES (?,?,?,?)');
            $st->bind_param('sisi', $waktu, $total, $metode, $tid);
            $st->execute();

            $conn->commit();
        } catch (Throwable $t) {
            $conn->rollback();
            throw $t;
        }
        flash('success', 'Transaksi berhasil disimpan.');
        header('Location: transaksi.php?aksi=detail&id=' . $tid);
        exit;
    } catch (mysqli_sql_exception $ex) {
        flash('danger', 'Terjadi kesalahan database.');
    } catch (Exception $ex) {
        flash('danger', $ex->getMessage());
    }
    header('Location: transaksi.php?aksi=tambah');
    exit;
}

if ($aksi === 'detail') {
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
}

if ($aksi === 'tambah') {
$judul = 'Tambah Transaksi';
tampil_header($judul);
$pelanggan = $conn->query('SELECT id, nama FROM pelanggan ORDER BY nama');
$barang    = $conn->query('SELECT id, kode_barang, nama_barang, harga, stok FROM barang WHERE stok > 0 ORDER BY nama_barang');
$opsiBarang = '<option value="">- Pilih barang -</option>';
while ($b = $barang->fetch_assoc()) {
    $opsiBarang .= '<option value="' . $b['id'] . '" data-harga="' . $b['harga'] . '">'
        . e($b['kode_barang'] . ' - ' . $b['nama_barang'] . ' (' . rupiah($b['harga']) . ', stok ' . $b['stok'] . ')') . '</option>';
}
?>
<h4 class="mb-3">Tambah Transaksi</h4>
<form method="post">
  <div class="row g-3 mb-3">
    <div class="col-md-4"><label class="form-label">Tanggal</label>
      <input type="date" name="waktu_transaksi" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-md-4"><label class="form-label">Pelanggan</label>
      <select name="pelanggan_id" class="form-select">
        <option value="">- Tanpa pelanggan -</option>
        <?php while ($p = $pelanggan->fetch_assoc()): ?>
        <option value="<?= e($p['id']) ?>"><?= e($p['id'] . ' - ' . $p['nama']) ?></option>
        <?php endwhile; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Metode Pembayaran</label>
      <select name="metode" class="form-select">
        <option>TUNAI</option><option>TRANSFER</option><option>EDC</option>
      </select></div>
    <div class="col-12"><label class="form-label">Keterangan</label>
      <input name="keterangan" class="form-control"></div>
  </div>

  <table class="table table-bordered">
    <thead><tr><th>Barang</th><th style="width:120px">Qty</th><th class="text-end" style="width:160px">Subtotal</th><th style="width:80px"></th></tr></thead>
    <tbody id="body"></tbody>
    <tfoot><tr><th colspan="2" class="text-end">Total</th><th class="text-end" id="total">0</th><th></th></tr></tfoot>
  </table>
  <template id="tpl"><tr>
    <td><select name="barang_id[]" class="form-select" required><?= $opsiBarang ?></select></td>
    <td><input type="number" name="qty[]" class="form-control" min="1" value="1" required></td>
    <td class="text-end sub">0</td>
    <td><button type="button" class="btn btn-sm btn-danger hapus">Hapus</button></td>
  </tr></template>

  <button type="button" class="btn btn-secondary" onclick="tambah()">Tambah Baris</button>
  <button class="btn btn-primary">Simpan Transaksi</button>
  <a href="transaksi.php" class="btn btn-outline-secondary">Batal</a>
</form>
<script>
const body = document.getElementById('body'), tpl = document.getElementById('tpl');
function hitung() {
  let t = 0;
  body.querySelectorAll('tr').forEach(tr => {
    const o = tr.querySelector('select').selectedOptions[0];
    const h = o && o.dataset.harga ? +o.dataset.harga : 0;
    const q = +tr.querySelector('input').value || 0;
    tr.querySelector('.sub').textContent = (h * q).toLocaleString('id-ID');
    t += h * q;
  });
  document.getElementById('total').textContent = t.toLocaleString('id-ID');
}
function tambah() { body.appendChild(tpl.content.cloneNode(true)); hitung(); }
body.addEventListener('input', hitung);
body.addEventListener('change', hitung);
body.addEventListener('click', e => {
  if (e.target.classList.contains('hapus')) { e.target.closest('tr').remove(); hitung(); }
});
tambah();
</script>
<?php
} elseif ($aksi === 'detail') {
$judul = 'Detail Transaksi';
tampil_header($judul);
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
<?php
} else {
$judul = 'Transaksi';
tampil_header($judul);

$data = $conn->query("SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS pelanggan, pb.metode
    FROM transaksi t
    LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
    LEFT JOIN pembayaran pb ON pb.transaksi_id = t.id
    ORDER BY t.waktu_transaksi DESC, t.id DESC");
?>
<div class="d-flex justify-content-between mb-3">
  <h4>Transaksi</h4>
  <a href="transaksi.php?aksi=tambah" class="btn btn-primary">Tambah Transaksi</a>
</div>
<table class="table table-bordered table-striped">
  <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Metode</th><th class="text-end">Total</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php while ($r = $data->fetch_assoc()): ?>
    <tr>
      <td><?= $r['id'] ?></td><td><?= e($r['waktu_transaksi']) ?></td><td><?= e($r['pelanggan'] ?? '-') ?></td>
      <td><?= e($r['keterangan']) ?></td><td><?= e($r['metode'] ?? '-') ?></td>
      <td class="text-end"><?= rupiah($r['total']) ?></td>
      <td><a href="transaksi.php?aksi=detail&id=<?= $r['id'] ?>" class="btn btn-sm btn-info">Detail</a></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php
}
tampil_footer();
