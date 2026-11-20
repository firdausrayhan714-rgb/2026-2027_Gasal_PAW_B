<?php
// Dipanggil paling awal di setiap halaman (session_start + fungsi proteksi)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }

function flash($tipe, $pesan) { $_SESSION['flash'] = [$tipe, $pesan]; }

function nama_level($l) {
    return [1 => 'Owner', 2 => 'Kasir', 3 => 'Gudang'][(int)$l] ?? '-';
}

function sudah_login() { return isset($_SESSION['id_user']); }

// Halaman login: user yang sudah login tidak boleh masuk lagi
function tolak_jika_login() {
    if (sudah_login()) { header('Location: index.php'); exit; }
}

// Semua halaman lain: wajib login
function wajib_login() {
    if (!sudah_login()) { header('Location: login.php'); exit; }
}

// Halaman dengan batasan level (mis. Data Master hanya level 1)
function wajib_level(array $level) {
    wajib_login();
    if (!in_array((int)$_SESSION['level'], $level, true)) {
        flash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        header('Location: index.php'); exit;
    }
}

// Koneksi database (sesuaikan host/user/password dengan XAMPP/Laragon Anda)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli('127.0.0.1', 'root', '', 'penjualan');
$conn->set_charset('utf8mb4');

function tampil_header($judul = 'Sistem Penjualan') {
    $level = (int)$_SESSION['level'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> - Sistem Penjualan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary d-print-none">
  <div class="container">
    <a class="navbar-brand" href="index.php">Sistem Penjualan</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <?php if ($level === 1): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Data Master</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="barang.php">Data Barang</a></li>
            <li><a class="dropdown-item" href="supplier.php">Data Supplier</a></li>
            <li><a class="dropdown-item" href="pelanggan.php">Data Pelanggan</a></li>
            <li><a class="dropdown-item" href="user.php">Data User</a></li>
          </ul>
        </li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="transaksi.php">Transaksi</a></li>
        <li class="nav-item"><a class="nav-link" href="laporan.php">Laporan</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><?= e($_SESSION['nama']) ?></a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
<?php if (!empty($_SESSION['flash'])): [$tp, $ps] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert alert-<?= e($tp) ?>"><?= e($ps) ?></div>
<?php endif; ?>
<?php
}

function tampil_footer() {
?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
