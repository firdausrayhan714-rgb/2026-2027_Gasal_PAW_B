<?php
$koneksi = mysqli_connect('localhost', 'root', '', 'penjualan');

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}
mysqli_set_charset($koneksi, 'utf8mb4');
mysqli_report(MYSQLI_REPORT_OFF);

session_start();

function set_pesan($tipe, $teks) {
    $_SESSION['pesan'] = ['tipe' => $tipe, 'teks' => $teks];
}

function tampil_pesan() {
    if (!empty($_SESSION['pesan'])) {
        $p = $_SESSION['pesan'];
        unset($_SESSION['pesan']);
        echo '<div class="alert ' . htmlspecialchars($p['tipe']) . '">'
           . htmlspecialchars($p['teks']) . '</div>';
    }
}

function rupiah($angka) {
    return 'Rp ' . number_format((int)$angka, 0, ',', '.');
}

function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function hitung_ulang_total($koneksi, $transaksi_id) {
    $sql = "UPDATE transaksi
            SET total = (SELECT COALESCE(SUM(harga * qty), 0)
                         FROM transaksi_detail WHERE transaksi_id = ?)
            WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $transaksi_id, $transaksi_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
