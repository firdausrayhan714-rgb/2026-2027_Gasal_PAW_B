<?php
$judul = 'Data Barang';
$menu  = 'barang';
require_once 'koneksi.php';
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

$barang = mysqli_query($koneksi,
    "SELECT b.id, b.kode_barang, b.nama_barang, b.harga, b.stok, s.nama AS supplier,
            (SELECT COUNT(*) FROM transaksi_detail d WHERE d.barang_id = b.id) AS dipakai
     FROM barang b
     LEFT JOIN supplier s ON s.id = b.supplier_id
     ORDER BY b.kode_barang");
?>
<h1>Data Barang</h1>
<table>
  <thead>
    <tr><th>Kode</th><th>Nama barang</th><th>Supplier</th>
        <th class="angka">Harga</th><th class="angka">Stok</th>
        <th class="angka">Dipakai di</th><th></th></tr>
  </thead>
  <tbody>
  <?php while ($b = mysqli_fetch_assoc($barang)): ?>
    <tr>
      <td><?= e($b['kode_barang']) ?></td>
      <td><?= e($b['nama_barang']) ?></td>
      <td><?= e($b['supplier'] ?? '-') ?></td>
      <td class="angka"><?= rupiah($b['harga']) ?></td>
      <td class="angka"><?= (int)$b['stok'] ?></td>
      <td class="angka"><span class="badge"><?= (int)$b['dipakai'] ?> transaksi</span></td>
      <td>
        <form method="post" action="hapus_barang.php"
              onsubmit="return confirm('Hapus barang <?= e(addslashes($b['nama_barang'])) ?>?')">
          <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
          <button class="btn merah kecil" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
</main>
</body>
</html>
