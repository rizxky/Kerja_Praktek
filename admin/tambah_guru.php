```php
<?php

session_start();

require_once "../config/database.php";

// ========================================
// CEK LOGIN ADMIN
// ========================================

if (!isset($_SESSION["guru_id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}

// ========================================
// INISIALISASI
// ========================================

$pesan = "";
$tipePesan = "";

$nama = "";
$nip = "";
$username = "";

// ========================================
// PROSES TAMBAH GURU
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $nip = trim($_POST["nip"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $konfirmasiPassword = $_POST["konfirmasi_password"] ?? "";

    if (
        $nama === "" ||
        $username === "" ||
        $password === "" ||
        $konfirmasiPassword === ""
    ) {

        $pesan = "Nama, username, dan password wajib diisi.";
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

    } elseif (strlen($password) < 8) {

        $pesan = "Password minimal 8 karakter.";
        $tipePesan = "danger";

    } elseif ($password !== $konfirmasiPassword) {

        $pesan = "Konfirmasi password tidak cocok.";
        $tipePesan = "danger";

    } else {

        // Cek username yang sudah digunakan

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM guru WHERE username = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        $usernameAda = mysqli_stmt_num_rows($stmt) > 0;

        mysqli_stmt_close($stmt);

        if ($usernameAda) {

            $pesan = "Username sudah digunakan. Silakan gunakan username lain.";
            $tipePesan = "danger";

        } else {

            // Cek NIP jika diisi

            $nipAda = false;

            if ($nip !== "") {

                $stmt = mysqli_prepare(
                    $conn,
                    "SELECT id FROM guru WHERE nip = ?"
                );

                mysqli_stmt_bind_param($stmt, "s", $nip);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                $nipAda = mysqli_stmt_num_rows($stmt) > 0;

                mysqli_stmt_close($stmt);

            }

            if ($nipAda) {

                $pesan = "NIP sudah terdaftar.";
                $tipePesan = "danger";

            } else {

                // Hash password

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $role = "guru";

                // Simpan akun guru

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO guru
                    (nip, nama, username, password, role)
                    VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssss",
                    $nip,
                    $nama,
                    $username,
                    $passwordHash,
                    $role
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    header("Location: data_guru.php?status=berhasil");
                    exit;

                } else {

                    $pesan = "Gagal menambahkan guru. Silakan coba kembali.";
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Guru - Admin</title>

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

<nav class="navbar navbar-expand-lg">

    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            🛡️ Admin Sistem Absensi
        </a>

        <div class="d-flex align-items-center">

            <span class="admin-name me-3">
                👤 <?= htmlspecialchars($_SESSION["nama"]) ?>
            </span>

            <a href="../logout.php" class="btn btn-light btn-sm">
                Logout
            </a>

        </div>

    </div>

</nav>

<div class="container py-4">

    <div class="mb-4">

        <h3 class="fw-bold">➕ Tambah Guru</h3>

        <p class="text-muted">
            Tambahkan data dan akun login guru baru.
        </p>

    </div>

    <div class="card">

        <div class="card-body p-4">

            <?php if ($pesan !== ""): ?>

                <div class="alert alert-<?= $tipePesan ?>">
                    <?= htmlspecialchars($pesan) ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">

                    <label for="nama" class="form-label">
                        Nama Lengkap <span class="text-danger">*</span>
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
                        value="<?= htmlspecialchars($nip) ?>"
                        placeholder="Masukkan NIP jika tersedia"
                    >

                    <small class="text-muted">
                        NIP boleh dikosongkan jika guru tidak memilikinya.
                    </small>

                </div>

                <div class="mb-3">

                    <label for="username" class="form-label">
                        Username <span class="text-danger">*</span>
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

                <div class="mb-3">

                    <label for="password" class="form-label">
                        Password <span class="text-danger">*</span>
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <small class="text-muted">
                        Minimal 8 karakter.
                    </small>

                </div>

                <div class="mb-4">

                    <label for="konfirmasi_password" class="form-label">
                        Konfirmasi Password
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="password"
                        id="konfirmasi_password"
                        name="konfirmasi_password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                </div>

                <div class="d-flex gap-2 flex-wrap">

                    <button type="submit" class="btn btn-success">
                        💾 Simpan Guru
                    </button>

                    <a href="data_guru.php" class="btn btn-secondary">
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