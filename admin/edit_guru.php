```php
<?php

session_start();

require_once "../config/database.php";

// ========================================
// CEK LOGIN
// ========================================

if (!isset($_SESSION["guru_id"])) {
    header("Location: ../login.php");
    exit;
}

// ========================================
// CEK ROLE ADMIN
// ========================================

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

// ========================================
// CEK ID GURU
// ========================================

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: data_guru.php");
    exit;
}

// ========================================
// AMBIL DATA GURU
// ========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, nama, nip, username
     FROM guru
     WHERE id = ? AND role = 'guru'
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$guru) {
    header("Location: data_guru.php?status=tidak_ditemukan");
    exit;
}

// ========================================
// INISIALISASI FORM
// ========================================

$nama = $guru["nama"];
$nip = $guru["nip"] ?? "";
$username = $guru["username"];

$pesan = "";
$tipePesan = "";

// ========================================
// PROSES UPDATE DATA
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $nip = trim($_POST["nip"] ?? "");
    $username = trim($_POST["username"] ?? "");

    if ($nama === "" || $username === "") {

        $pesan = "Nama dan username wajib diisi.";
        $tipePesan = "danger";

    } elseif (strlen($nama) > 100) {

        $pesan = "Nama maksimal 100 karakter.";
        $tipePesan = "danger";

    } elseif (strlen($nip) > 30) {

        $pesan = "NIP maksimal 30 karakter.";
        $tipePesan = "danger";

    } elseif (strlen($username) > 50) {

        $pesan = "Username maksimal 50 karakter.";
        $tipePesan = "danger";

    } else {

        // ========================================
        // CEK USERNAME DUPLIKAT
        // ========================================

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id
             FROM guru
             WHERE username = ? AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $username,
            $id
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $usernameAda = mysqli_stmt_num_rows($stmt) > 0;

        mysqli_stmt_close($stmt);

        if ($usernameAda) {

            $pesan = "Username sudah digunakan oleh akun lain.";
            $tipePesan = "danger";

        } else {

            // ========================================
            // CEK NIP DUPLIKAT
            // ========================================

            $nipAda = false;

            if ($nip !== "") {

                $stmt = mysqli_prepare(
                    $conn,
                    "SELECT id
                     FROM guru
                     WHERE nip = ? AND id != ?
                     LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "si",
                    $nip,
                    $id
                );

                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                $nipAda = mysqli_stmt_num_rows($stmt) > 0;

                mysqli_stmt_close($stmt);

            }

            if ($nipAda) {

                $pesan = "NIP sudah digunakan oleh guru lain.";
                $tipePesan = "danger";

            } else {

                // ========================================
                // SIMPAN PERUBAHAN
                // ========================================

                $nipDatabase = ($nip === "") ? null : $nip;

                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE guru
                     SET nama = ?,
                         nip = ?,
                         username = ?
                     WHERE id = ? AND role = 'guru'"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssi",
                    $nama,
                    $nipDatabase,
                    $username,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: data_guru.php?status=edit_berhasil");
                    exit;

                } else {

                    $pesan = "Gagal memperbarui data guru.";
                    $tipePesan = "danger";

                    mysqli_stmt_close($stmt);

                }

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Data Guru - Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background-color: #f5f7f9;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #198754;
        }

        .navbar-brand {
            color: white;
            font-weight: bold;
        }

        .navbar-brand:hover {
            color: white;
        }

        .admin-name {
            color: white;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .form-control {
            padding: 11px;
            border-radius: 8px;
        }

        .btn {
            border-radius: 8px;
        }

    </style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg">

    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            🛡️ Admin Sistem Absensi
        </a>

        <div class="d-flex align-items-center">

            <span class="admin-name me-3">
                👤 <?= htmlspecialchars($_SESSION["nama"]) ?>
            </span>

            <a
                href="../logout.php"
                class="btn btn-light btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>

<!-- KONTEN -->

<div class="container py-4">

    <div class="mb-4">

        <h3 class="fw-bold">✏️ Edit Data Guru</h3>

        <p class="text-muted">
            Perbarui informasi akun guru yang terdaftar.
        </p>

    </div>

    <div class="card">

        <div class="card-body p-4">

            <?php if ($pesan !== ""): ?>

                <div class="alert alert-<?= $tipePesan ?>">
                    <?= htmlspecialchars($pesan) ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <!-- NAMA -->

                <div class="mb-3">

                    <label for="nama" class="form-label">
                        Nama Lengkap
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        class="form-control"
                        maxlength="100"
                        value="<?= htmlspecialchars($nama) ?>"
                        required
                    >

                </div>

                <!-- NIP -->

                <div class="mb-3">

                    <label for="nip" class="form-label">
                        NIP
                    </label>

                    <input
                        type="text"
                        id="nip"
                        name="nip"
                        class="form-control"
                        maxlength="30"
                        value="<?= htmlspecialchars($nip ?? "") ?>"
                        placeholder="Masukkan NIP jika tersedia"
                    >

                    <small class="text-muted">
                        NIP boleh dikosongkan jika tidak tersedia.
                    </small>

                </div>

                <!-- USERNAME -->

                <div class="mb-4">

                    <label for="username" class="form-label">
                        Username
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        maxlength="50"
                        value="<?= htmlspecialchars($username) ?>"
                        autocomplete="off"
                        required
                    >

                </div>

                <!-- TOMBOL -->

                <div class="d-flex gap-2 flex-wrap">

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        💾 Simpan Perubahan
                    </button>

                    <a
                        href="data_guru.php"
                        class="btn btn-secondary"
                    >
                        ← Kembali
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>

</html>
```