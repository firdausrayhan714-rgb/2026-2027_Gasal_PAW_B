<?php
$judul = 'Daftar Transaksi';
$menu  = 'transaksi';
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal    = $_POST['waktu_transaksi'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');
    $pelanggan  = $_POST['pelanggan_id'] ?? '';

    if ($tanggal === '' || $pelanggan === '') {
        set_pesan('error', 'Tanggal dan pelanggan wajib diisi.');
    } else {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id) VALUES (?, ?, 0, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $tanggal, $keterangan, $pelanggan);
        if (mysqli_stmt_execute($stmt)) {
            $id_baru = mysqli_insert_id($koneksi);
            set_pesan('sukses', 'Transaksi dibuat. Silakan tambahkan barang.');
            header("Location: transaksi_detail.php?id=$id_baru");
            exit;
        }
        set_pesan('error', 'Gagal menyimpan transaksi: ' . mysqli_error($koneksi));
    }
    header('Location: index.php');
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

$pelanggan = mysqli_query($koneksi, "SELECT id, nama FROM pelanggan ORDER BY nama");
$transaksi = mysqli_query($koneksi,
    "SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS pelanggan,
            (SELECT COUNT(*) FROM transaksi_detail d WHERE d.transaksi_id = t.id) AS jml_item
     FROM transaksi t
     LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
     ORDER BY t.id DESC");
?>
<h1>Transaksi</h1>

<div class="kartu">
  <h2>Transaksi baru</h2>
  <form method="post">
    <div class="baris">
      <div>
        <label for="waktu_transaksi">Tanggal</label>
        <input type="date" id="waktu_transaksi" name="waktu_transaksi" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div>
        <label for="pelanggan_id">Pelanggan</label>
        <select id="pelanggan_id" name="pelanggan_id" required>
          <option value="">-- pilih pelanggan --</option>
          <?php while ($p = mysqli_fetch_assoc($pelanggan)): ?>
            <option value="<?= e($p['id']) ?>"><?= e($p['nama']) ?> (<?= e($p['id']) ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label for="keterangan">Keterangan</label>
        <input type="text" id="keterangan" name="keterangan">
      </div>
      <div style="flex:0 0 auto">
        <button class="btn" type="submit">Buat transaksi</button>
      </div>
    </div>
  </form>
</div>

<table>
  <thead>
    <tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th>
        <th class="angka">Item</th><th class="angka">Total</th><th></th></tr>
  </thead>
  <tbody>
  <?php while ($t = mysqli_fetch_assoc($transaksi)): ?>
    <tr>
      <td><?= (int)$t['id'] ?></td>
      <td><?= e($t['waktu_transaksi']) ?></td>
      <td><?= e($t['pelanggan'] ?? '-') ?></td>
      <td><?= e($t['keterangan']) ?></td>
      <td class="angka"><?= (int)$t['jml_item'] ?></td>
      <td class="angka"><?= rupiah($t['total']) ?></td>
      <td><a class="btn kecil" href="transaksi_detail.php?id=<?= (int)$t['id'] ?>">Lihat detail</a></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</main>
</body>
</html>
