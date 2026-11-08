
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
// CEK ID GURU
// ========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: data_guru.php");
    exit;
}

$guru_id = (int) $_GET["id"];

// ========================================
// AMBIL DATA GURU
// ========================================

$query = "SELECT id, nama, username FROM guru
          WHERE id = ? AND role = 'guru'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($result);

if (!$guru) {
    header("Location: data_guru.php");
    exit;
}

// ========================================
// PROSES RESET PASSWORD
// ========================================

$pesan = "";
$tipePesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password = $_POST["password"] ?? "";
    $konfirmasi = $_POST["konfirmasi"] ?? "";

    if (strlen($password) < 8) {

        $pesan = "Password minimal 8 karakter.";
        $tipePesan = "danger";

    } elseif ($password !== $konfirmasi) {

        $pesan = "Konfirmasi password tidak cocok.";
        $tipePesan = "danger";

    } else {

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $queryUpdate = "
            UPDATE guru
            SET password = ?
            WHERE id = ?
            AND role = 'guru'
        ";

        $stmtUpdate = mysqli_prepare(
            $conn,
            $queryUpdate
        );

        mysqli_stmt_bind_param(
            $stmtUpdate,
            "si",
            $passwordHash,
            $guru_id
        );

        if (mysqli_stmt_execute($stmtUpdate)
            && mysqli_stmt_affected_rows($stmtUpdate) === 1) {

            header(
                "Location: detail_guru.php?id=" .
                $guru_id . "&reset=berhasil"
            );
            exit;

        } else {

            $pesan = "Password gagal diperbarui.";
            $tipePesan = "danger";

        }

    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reset Password Guru</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f7f9;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: #198754;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

    </style>

</head>

<body>

<nav class="navbar navbar-dark">

    <div class="container">

        <a class="navbar-brand fw-bold"
           href="dashboard.php">

            🛡️ Admin Sistem Absensi

        </a>

        <a href="../logout.php"
           class="btn btn-light btn-sm">

            Logout

        </a>

    </div>

</nav>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-3">

                        🔐 Reset Password Guru

                    </h4>

                    <p class="text-muted">

                        Guru:
                        <strong>
                            <?= htmlspecialchars($guru["nama"]) ?>
                        </strong>

                        <br>

                        Username:
                        <strong>
                            <?= htmlspecialchars($guru["username"]) ?>
                        </strong>

                    </p>

                    <hr>

                    <?php if ($pesan !== ""): ?>

                        <div class="alert alert-<?= $tipePesan ?>">

                            <?= htmlspecialchars($pesan) ?>

                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label fw-bold">

                                Password Baru

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                Konfirmasi Password Baru

                            </label>

                            <input
                                type="password"
                                name="konfirmasi"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success w-100">

                            Simpan Password Baru

                        </button>

                        <a
                            href="detail_guru.php?id=<?= $guru_id ?>"
                            class="btn btn-secondary w-100 mt-2">

                            Kembali

                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
