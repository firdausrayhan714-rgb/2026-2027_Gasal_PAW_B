<?php
require 'includes/auth.php';
wajib_login();
$judul = 'Home';
require 'includes/header.php';
?>
<div class="p-4 bg-light border rounded">
  <h4>Selamat datang, <?= e($_SESSION['nama']) ?></h4>
  <p class="mb-0 text-muted">Anda login sebagai <?= e(nama_level($_SESSION['level'])) ?>.</p>
</div>
<?php require 'includes/footer.php'; ?>
