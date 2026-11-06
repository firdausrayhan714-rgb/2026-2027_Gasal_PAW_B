<?php
$judul = 'Detail Transaksi';
$menu  = 'transaksi';
require_once 'koneksi.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi,
    "SELECT t.*, p.nama AS pelanggan
     FROM transaksi t LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
     WHERE t.id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$master = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$master) {
    set_pesan('error', 'Transaksi tidak ditemukan.');
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $barang_id = (int)($_POST['barang_id'] ?? 0);
    $qty       = (int)($_POST['qty'] ?? 0);

    if ($barang_id <= 0 || $qty <= 0) {
        set_pesan('error', 'Pilih barang dan isi qty minimal 1.');
    } else {
        // Harga selalu diambil dari tabel barang (bukan dari input pengguna)
        $stmt = mysqli_prepare($koneksi, "SELECT harga FROM barang WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $barang_id);
        mysqli_stmt_execute($stmt);
        $b = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$b) {
            set_pesan('error', 'Barang tidak ditemukan.');
        } else {
            mysqli_begin_transaction($koneksi);
            $stmt = mysqli_prepare($koneksi,
                "INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iiii', $id, $barang_id, $b['harga'], $qty);
            $ok = mysqli_stmt_execute($stmt);
            $errno = mysqli_errno($koneksi);
            mysqli_stmt_close($stmt);

            if ($ok && hitung_ulang_total($koneksi, $id)) {
                mysqli_commit($koneksi);
                set_pesan('sukses', 'Barang ditambahkan dan total transaksi diperbarui.');
            } else {
                mysqli_rollback($koneksi);
                if ($errno == 1062) {
                    set_pesan('error', 'Barang tersebut sudah ada di transaksi ini.');
                } else {
                    set_pesan('error', 'Gagal menyimpan detail.');
                }
            }
        }
    }
    header("Location: transaksi_detail.php?id=$id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $barang_id = (int)($_POST['barang_id'] ?? 0);
    mysqli_begin_transaction($koneksi);
    $stmt = mysqli_prepare($koneksi,
        "DELETE FROM transaksi_detail WHERE transaksi_id = ? AND barang_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $barang_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok && hitung_ulang_total($koneksi, $id)) {
        mysqli_commit($koneksi);
        set_pesan('sukses', 'Barang dihapus dari transaksi dan total diperbarui.');
    } else {
        mysqli_rollback($koneksi);
        set_pesan('error', 'Gagal menghapus detail.');
    }
    header("Location: transaksi_detail.php?id=$id");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'Penjualan') ?></title>
<style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        margin: 0;
        background-color: #f3f5f8;
        color: #1d2430;
    }

    nav {
        background-color: #1f4e8c;
        padding: 0 24px;
    }

    nav a {
        display: inline-block;
        color: #ffffff;
        text-decoration: none;
        padding: 14px 16px;
    }

    nav a:hover {
        background-color: rgba(255, 255, 255, 0.18);
    }

    nav a.aktif {
        background-color: rgba(255, 255, 255, 0.18);
        font-weight: bold;
    }

    main {
        max-width: 1000px;
        margin: 24px auto;
        padding: 0 16px;
    }

    h1 {
        font-size: 24px;
        margin: 0 0 16px;
    }

    h2 {
        font-size: 18px;
        margin: 0 0 12px;
    }

    .kartu {
        background-color: #ffffff;
        border: 1px solid #d5dbe3;
        border-radius: 6px;
        padding: 18px;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #ffffff;
    }

    th, td {
        border-bottom: 1px solid #d5dbe3;
        padding: 9px 10px;
        text-align: left;
    }

    th {
        background-color: #e8edf4;
    }

    td.angka, th.angka {
        text-align: right;
    }

    tfoot td {
        font-weight: bold;
        background-color: #f7f9fc;
    }

    label {
        display: block;
        margin: 10px 0 4px;
        font-weight: bold;
    }

    input, select, textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 8px;
        border: 1px solid #d5dbe3;
        border-radius: 4px;
        font: inherit;
    }

    .baris {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .baris > div {
        flex: 1;
        min-width: 160px;
    }

    .btn {
        display: inline-block;
        padding: 8px 14px;
        background-color: #1f4e8c;
        color: #ffffff;
        border: 0;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        font: inherit;
    }

    .btn.merah {
        background-color: #b3261e;
    }

    .btn.abu {
        background-color: #6b7686;
    }

    .btn.kecil {
        padding: 4px 10px;
        font-size: 14px;
    }

    .alert {
        padding: 10px 14px;
        margin-bottom: 16px;
        border: 1px solid;
        border-radius: 4px;
    }

    .alert.sukses {
        background-color: #e6f4ea;
        border-color: #a6d4b1;
        color: #1b5e2e;
    }

    .alert.error {
        background-color: #fdeceb;
        border-color: #f0b3ae;
        color: #8c1d18;
    }

    .info {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 4px 12px;
        margin: 0;
    }

    .info dt {
        font-weight: bold;
    }

    .info dd {
        margin: 0;
    }

    .kosong {
        color: #6b7686;
        padding: 14px 0;
    }

    .badge {
        font-size: 14px;
        color: #6b7686;
    }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="<?= ($menu ?? '') === 'transaksi' ? 'aktif' : '' ?>">Transaksi</a>
  <a href="barang.php" class="<?= ($menu ?? '') === 'barang' ? 'aktif' : '' ?>">Barang</a>
</nav>
<main>
<?php tampil_pesan(); ?>
<?php

$stmt = mysqli_prepare($koneksi,
    "SELECT d.barang_id, b.kode_barang, b.nama_barang, d.harga, d.qty, (d.harga * d.qty) AS subtotal
     FROM transaksi_detail d
     INNER JOIN barang b ON b.id = d.barang_id
     WHERE d.transaksi_id = ?
     ORDER BY b.nama_barang");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$detail = mysqli_stmt_get_result($stmt);

$stmt2 = mysqli_prepare($koneksi,
    "SELECT b.id, b.kode_barang, b.nama_barang, b.harga
     FROM barang b
     WHERE b.id NOT IN (SELECT barang_id FROM transaksi_detail WHERE transaksi_id = ?)
     ORDER BY b.nama_barang");
mysqli_stmt_bind_param($stmt2, 'i', $id);
mysqli_stmt_execute($stmt2);
$opsi_barang = mysqli_stmt_get_result($stmt2);
$ada_opsi = mysqli_num_rows($opsi_barang) > 0;
?>
<h1>Transaksi #<?= (int)$master['id'] ?></h1>

<div class="kartu">
  <h2>Data transaksi</h2>
  <dl class="info">
    <dt>No. transaksi</dt><dd><?= (int)$master['id'] ?></dd>
    <dt>Tanggal</dt><dd><?= e($master['waktu_transaksi']) ?></dd>
    <dt>Pelanggan</dt><dd><?= e($master['pelanggan'] ?? '-') ?></dd>
    <dt>Keterangan</dt><dd><?= e($master['keterangan']) ?></dd>
    <dt>Total</dt><dd><strong><?= rupiah($master['total']) ?></strong></dd>
  </dl>
</div>

<div class="kartu">
  <h2>Tambah barang</h2>
  <?php if ($ada_opsi): ?>
  <form method="post">
    <input type="hidden" name="aksi" value="tambah">
    <div class="baris">
      <div style="flex:2">
        <label for="barang_id">Barang</label>
        <select id="barang_id" name="barang_id" required>
          <option value="">-- pilih barang --</option>
          <?php while ($o = mysqli_fetch_assoc($opsi_barang)): ?>
            <option value="<?= (int)$o['id'] ?>">
              <?= e($o['kode_barang']) ?> - <?= e($o['nama_barang']) ?> (<?= rupiah($o['harga']) ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label for="qty">Qty</label>
        <input type="number" id="qty" name="qty" min="1" value="1" required>
      </div>
      <div style="flex:0 0 auto">
        <button class="btn" type="submit">Simpan barang</button>
      </div>
    </div>
  </form>
  <?php else: ?>
    <p class="kosong">Semua barang sudah masuk ke transaksi ini.</p>
  <?php endif; ?>
</div>

<h2>Daftar barang</h2>
<table>
  <thead>
    <tr><th>Kode</th><th>Nama barang</th><th class="angka">Harga</th>
        <th class="angka">Qty</th><th class="angka">Subtotal</th><th></th></tr>
  </thead>
  <tbody>
  <?php if (mysqli_num_rows($detail) === 0): ?>
    <tr><td colspan="6" class="kosong">Belum ada barang. Pilih barang pada form di atas.</td></tr>
  <?php endif; ?>
  <?php while ($d = mysqli_fetch_assoc($detail)): ?>
    <tr>
      <td><?= e($d['kode_barang']) ?></td>
      <td><?= e($d['nama_barang']) ?></td>
      <td class="angka"><?= rupiah($d['harga']) ?></td>
      <td class="angka"><?= (int)$d['qty'] ?></td>
      <td class="angka"><?= rupiah($d['subtotal']) ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Hapus barang ini dari transaksi?')">
          <input type="hidden" name="aksi" value="hapus">
          <input type="hidden" name="barang_id" value="<?= (int)$d['barang_id'] ?>">
          <button class="btn merah kecil" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
  <tfoot>
    <tr><td colspan="4" class="angka">Total</td>
        <td class="angka"><?= rupiah($master['total']) ?></td><td></td></tr>
  </tfoot>
</table>

<p><a class="btn abu" href="index.php">Kembali ke daftar transaksi</a></p>
</main>
</body>
</html>
