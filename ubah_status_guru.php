
<?php

session_start();

require_once "../config/database.php";

// CEK LOGIN
if (!isset($_SESSION["guru_id"])) {
    header("Location: ../login.php");
    exit;
}

// CEK ROLE ADMIN
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

// CEK REQUEST POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: data_guru.php");
    exit;
}

// CEK TOKEN KEAMANAN
if (
    !isset($_POST["csrf_token"], $_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    die("Permintaan tidak valid. Silakan kembali dan coba lagi.");
}

// AMBIL ID GURU
if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
    header("Location: data_guru.php");
    exit;
}

$guru_id = (int) $_POST["id"];

// AMBIL STATUS BARU
$status_baru = $_POST["status_baru"] ?? "";

if (!in_array($status_baru, ["aktif", "nonaktif"], true)) {
    header("Location: data_guru.php");
    exit;
}

// PASTIKAN YANG DIUBAH ADALAH AKUN GURU
$query = "
    UPDATE guru
    SET status_akun = ?
    WHERE id = ?
    AND role = 'guru'
";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $status_baru,
    $guru_id
);

if (mysqli_stmt_execute($stmt)) {

    header("Location: data_guru.php?status=" .
        ($status_baru === "nonaktif"
            ? "nonaktif_berhasil"
            : "aktif_berhasil"));

    exit;

} else {

    header("Location: data_guru.php?status=gagal");
    exit;

}
