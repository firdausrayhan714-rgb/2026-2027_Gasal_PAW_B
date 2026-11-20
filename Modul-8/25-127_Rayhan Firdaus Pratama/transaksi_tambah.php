<?php
require 'includes/auth.php';
require 'includes/koneksi.php';
wajib_level([1, 2]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tgl    = $_POST['waktu_transaksi'] ?? '';
        $ket    = trim($_POST['keterangan'] ?? '');
        $plg    = ($_POST['pelanggan_id'] ?? '') !== '' ? $_POST['pelanggan_id'] : null;
        $metode = $_POST['metode'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) throw new Exception('Tanggal tidak valid.');
        if (!in_array($metode, ['TUNAI', 'TRANSFER', 'EDC'], true)) throw new Exception('Metode pembayaran tidak valid.');

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
        header('Location: transaksi_detail.php?id=' . $tid);
        exit;
    } catch (mysqli_sql_exception $ex) {
        flash('danger', 'Terjadi kesalahan database.');
    } catch (Exception $ex) {
        flash('danger', $ex->getMessage());
    }
    header('Location: transaksi_tambah.php');
    exit;
}

$judul = 'Tambah Transaksi';
require 'includes/header.php';
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
<?php require 'includes/footer.php'; ?>
