<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: barang.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

// Ambil nama barang untuk pesan notifikasi
$stmt = mysqli_prepare($koneksi, "SELECT nama_barang FROM barang WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$barang) {
    set_pesan('error', 'Barang tidak ditemukan.');
    header('Location: barang.php');
    exit;
}

// 1) Cek dulu: apakah barang sudah dipakai di transaksi_detail?
$stmt = mysqli_prepare($koneksi,
    "SELECT COUNT(*) AS jml FROM transaksi_detail WHERE barang_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$jml = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jml'];
mysqli_stmt_close($stmt);

if ($jml > 0) {
    // Sudah dipakai -> tolak
    set_pesan('error', "Barang \"{$barang['nama_barang']}\" tidak dapat dihapus karena masih digunakan pada $jml transaksi.");
} else {
    // 2) Belum dipakai -> boleh dihapus
    $stmt = mysqli_prepare($koneksi, "DELETE FROM barang WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        set_pesan('sukses', "Barang \"{$barang['nama_barang']}\" berhasil dihapus.");
    } else {
        set_pesan('error', 'Gagal menghapus barang.');
    }
    mysqli_stmt_close($stmt);
}

header('Location: barang.php');
exit;
