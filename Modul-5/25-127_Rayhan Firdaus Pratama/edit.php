<?php
include 'koneksi.php';

$id = $_GET['id'];
$sql = "SELECT * FROM supplier WHERE id = '$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $telp   = mysqli_real_escape_string($conn, $_POST['telp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $sql_update = "UPDATE supplier SET nama='$nama', telp='$telp', alamat='$alamat' WHERE id='$id'";

    if (mysqli_query($conn, $sql_update)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Data Master Supplier</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; width: 80px; font-weight: bold; }
        input[type="text"] { width: 300px; padding: 6px; }
        .btn-update { background-color: #5cb85c; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .btn-batal { background-color: #d9534f; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>

    <h2>Edit Data Master Supplier</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($row['nama']); ?>" required>
        </div>
        <div class="form-group">
            <label>Telp</label>
            <input type="text" name="telp" value="<?php echo htmlspecialchars($row['telp']); ?>" required>
        </div>
        <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat" value="<?php echo htmlspecialchars($row['alamat']); ?>" required>
        </div>
        <button type="submit" name="update" class="btn-update">Update</button>
        <a href="index.php" class="btn-batal">Batal</a>
    </form>

</body>
</html>