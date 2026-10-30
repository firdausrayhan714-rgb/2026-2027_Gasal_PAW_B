<?php
$servername = "localhost";
$username = "root";
$password = "";

$conn = mysqli_connect($servername, $username, $password);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql_db = "CREATE DATABASE IF NOT EXISTS db_supplier";
mysqli_query($conn, $sql_db);

mysqli_select_db($conn, "db_supplier");

$sql_table = "CREATE TABLE IF NOT EXISTS supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL
)";
mysqli_query($conn, $sql_table);

$check_data = mysqli_query($conn, "SELECT * FROM supplier");
if (mysqli_num_rows($check_data) == 0) {
    $sql_insert = "INSERT INTO supplier (nama, telp, alamat) VALUES 
        ('PT. Maju Bersama', '08123456', 'Surabaya'),
        ('PT. Selang Sekali', '081515563', 'Bangkalan'),
        ('PT. Segar Segar', '0845454663', 'Surabaya')";
    mysqli_query($conn, $sql_insert);
}
?>