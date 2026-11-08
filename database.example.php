<?php

$host = "localhost";
$user = "root";
$password = "ISI_PASSWORD_DATABASE";
$database = "absensi_guru";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi database gagal");
}
?>