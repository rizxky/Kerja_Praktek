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
// AMBIL ID GURU
// ========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: data_guru.php");
    exit;

}

$guru_id = (int) $_GET["id"];


// ========================================
// AMBIL DATA GURU
// ========================================

$queryGuru = "
    SELECT
        id,
        nama,
        nip,
        username
    FROM guru
    WHERE id = ?
    AND role = 'guru'
";

$stmtGuru = mysqli_prepare($conn, $queryGuru);

mysqli_stmt_bind_param(
    $stmtGuru,
    "i",
    $guru_id
);

mysqli_stmt_execute($stmtGuru);

$resultGuru = mysqli_stmt_get_result($stmtGuru);

$guru = mysqli_fetch_assoc($resultGuru);


// ========================================
// CEK GURU
// ========================================

if (!$guru) {

    header("Location: data_guru.php");
    exit;

}


// ========================================
// FILTER TANGGAL
// ========================================

$tanggal = "";

if (isset($_GET["tanggal"]) && $_GET["tanggal"] !== "") {

    $tanggal = $_GET["tanggal"];

}


// ========================================
// AMBIL DATA ABSENSI GURU
// ========================================

if ($tanggal !== "") {

    $queryAbsensi = "
        SELECT *
        FROM absensi
        WHERE guru_id = ?
        AND tanggal = ?
        ORDER BY tanggal DESC, jam_masuk DESC
    ";

    $stmtAbsensi = mysqli_prepare(
        $conn,
        $queryAbsensi
    );

    mysqli_stmt_bind_param(
        $stmtAbsensi,
        "is",
        $guru_id,
        $tanggal
    );

} else {

    $queryAbsensi = "
        SELECT *
        FROM absensi
        WHERE guru_id = ?
        ORDER BY tanggal DESC, jam_masuk DESC
    ";

    $stmtAbsensi = mysqli_prepare(
        $conn,
        $queryAbsensi
    );

    mysqli_stmt_bind_param(
        $stmtAbsensi,
        "i",
        $guru_id
    );

}


mysqli_stmt_execute($stmtAbsensi);

$resultAbsensi = mysqli_stmt_get_result(
    $stmtAbsensi
);

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Detail Absensi - <?= htmlspecialchars($guru["nama"]) ?>
    </title>


    <!-- Bootstrap -->

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

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);

        }


        .info-label {

            color: #6c757d;

            font-size: 14px;

            margin-bottom: 3px;

        }


        .info-value {

            font-weight: bold;

            font-size: 16px;

        }


        .table th {

            white-space: nowrap;

        }


        .table td {

            vertical-align: middle;

        }

    </style>

</head>


<body>


<!-- ========================================
     NAVBAR
======================================== -->

<nav class="navbar navbar-expand-lg">

    <div class="container">


        <a
            class="navbar-brand"
            href="dashboard.php"
        >

            🛡️ Admin Sistem Absensi

        </a>


        <div class="d-flex align-items-center">

            <span class="admin-name me-3">

                👤
                <?= htmlspecialchars($_SESSION["nama"]) ?>

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



<!-- ========================================
     CONTENT
======================================== -->

<div class="container py-4">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold">

                📋 Detail Absensi Guru

            </h3>

            <p class="text-muted mb-0">

                Riwayat absensi guru

            </p>

        </div>


        <a
            href="data_guru.php"
            class="btn btn-secondary"
        >

            ← Kembali

        </a>

    </div>



    <!-- ========================================
         INFORMASI GURU
    ======================================== -->

    <div class="card mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-4">

                👨‍🏫 Informasi Guru

            </h5>


            <div class="row g-4">


                <div class="col-md-4">

                    <div class="info-label">

                        Nama Guru

                    </div>

                    <div class="info-value">

                        <?= htmlspecialchars($guru["nama"]) ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="info-label">

                        NIP

                    </div>

                    <div class="info-value">

                        <?= !empty($guru["nip"])
                            ? htmlspecialchars($guru["nip"])
                            : "-"
                        ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="info-label">

                        Username

                    </div>

                    <div class="info-value">

                        <?= htmlspecialchars($guru["username"]) ?>

                    </div>

                </div>

<div class="col-12">

    <hr>

    <a
        href="reset_password.php?id=<?= $guru_id ?>"
        class="btn btn-warning"
    >

        🔐 Reset Password Guru

    </a>

</div>
            </div>

        </div>

    </div>



    <!-- ========================================
         FILTER TANGGAL
    ======================================== -->

    <div class="card mb-4">

        <div class="card-body p-4">

            <form
                method="GET"
                class="row g-3 align-items-end"
            >

                <!-- ID GURU -->

                <input
                    type="hidden"
                    name="id"
                    value="<?= $guru_id ?>"
                >


                <div class="col-md-5">

                    <label
                        for="tanggal"
                        class="form-label fw-bold"
                    >

                        Filter Tanggal

                    </label>

                    <input
                        type="date"
                        name="tanggal"
                        id="tanggal"
                        class="form-control"
                        value="<?= htmlspecialchars($tanggal) ?>"
                    >

                </div>


                <div class="col-md-auto">

                    <button
                        type="submit"
                        class="btn btn-success"
                    >

                        🔍 Tampilkan

                    </button>

                </div>


                <div class="col-md-auto">

                    <a
                        href="detail_guru.php?id=<?= $guru_id ?>"
                        class="btn btn-outline-secondary"
                    >

                        Reset

                    </a>

                </div>

            </form>

        </div>

    </div>



    <!-- ========================================
         RIWAYAT ABSENSI
    ======================================== -->

    <div class="card">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                📅 Riwayat Absensi

            </h5>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-success">

                        <tr>

                            <th>No</th>
<th>Tanggal</th>
<th>Jam Masuk</th>
<th>Foto Masuk</th>
<th>Jam Pulang</th>
<th>Foto Pulang</th>
<th>Status</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    if (
                        mysqli_num_rows($resultAbsensi)
                        > 0
                    ):

                    ?>


                        <?php while (
                            $row =
                            mysqli_fetch_assoc(
                                $resultAbsensi
                            )
                        ): ?>


                            <tr>

    <!-- No -->
    <td>
        <?= $no++ ?>
    </td>

    <!-- Tanggal -->
    <td>
        <?= date(
            "d-m-Y",
            strtotime($row["tanggal"])
        ) ?>
    </td>

    <!-- Jam Masuk -->
    <td>
        <?= $row["jam_masuk"]
            ? htmlspecialchars($row["jam_masuk"])
            : "-"
        ?>
    </td>

    <!-- Foto Masuk -->
    <td>
        <?php if (!empty($row["foto_masuk"])): ?>

            <a
                href="../<?= htmlspecialchars($row["foto_masuk"]) ?>"
                target="_blank"
                class="btn btn-sm btn-primary"
            >
                📷 Lihat Foto
            </a>

        <?php else: ?>

            <span class="text-muted">-</span>

        <?php endif; ?>
    </td>

    <!-- Jam Pulang -->
    <td>
        <?= $row["jam_pulang"]
            ? htmlspecialchars($row["jam_pulang"])
            : "-"
        ?>
    </td>

    <!-- Foto Pulang -->
    <td>
        <?php if (!empty($row["foto_pulang"])): ?>

            <a
                href="../<?= htmlspecialchars($row["foto_pulang"]) ?>"
                target="_blank"
                class="btn btn-sm btn-primary"
            >
                📷 Lihat Foto
            </a>

        <?php else: ?>

            <span class="text-muted">-</span>

        <?php endif; ?>
    </td>

    <!-- Status -->
    <td>

        <?php if (
            $row["jam_masuk"]
            &&
            $row["jam_pulang"]
        ): ?>

            <span class="badge bg-success">
                Lengkap
            </span>

        <?php elseif ($row["jam_masuk"]): ?>

            <span class="badge bg-warning text-dark">
                Belum Pulang
            </span>

        <?php else: ?>

            <span class="badge bg-secondary">
                Belum Absen
            </span>

        <?php endif; ?>

    </td>

</tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-5"
                            >

                                📭 Guru ini belum memiliki
                                data absensi.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>


        </div>

    </div>


</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>