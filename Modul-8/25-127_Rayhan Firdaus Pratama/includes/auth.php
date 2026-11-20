<?php
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

function tolak_jika_login() {
    if (sudah_login()) { header('Location: index.php'); exit; }
}

function wajib_login() {
    if (!sudah_login()) { header('Location: login.php'); exit; }
}

function wajib_level(array $level) {
    wajib_login();
    if (!in_array((int)$_SESSION['level'], $level, true)) {
        flash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        header('Location: index.php'); exit;
    }
}
