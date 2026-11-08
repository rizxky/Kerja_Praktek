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
// FILTER TANGGAL
// ========================================

$tanggal = "";

if (isset($_GET["tanggal"]) && $_GET["tanggal"] !== "") {

    $tanggal = $_GET["tanggal"];

}


// ========================================
// DATA ABSENSI
// ========================================

if ($tanggal !== "") {

    $query = "
        SELECT
            absensi.*,
            guru.nama,
            guru.nip
        FROM absensi
        INNER JOIN guru
            ON absensi.guru_id = guru.id
        WHERE absensi.tanggal = ?
        ORDER BY absensi.tanggal DESC, absensi.jam_masuk ASC
    ";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $tanggal
    );

} else {

    $query = "
        SELECT
            absensi.*,
            guru.nama,
            guru.nip
        FROM absensi
        INNER JOIN guru
            ON absensi.guru_id = guru.id
        ORDER BY absensi.tanggal DESC, absensi.jam_masuk ASC
    ";

    $stmt = mysqli_prepare($conn, $query);

}


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Absensi - Admin</title>


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

                📋 Data Absensi Guru

            </h3>

            <p class="text-muted mb-0">

                Riwayat absensi seluruh guru

            </p>

        </div>


        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >

            ← Kembali

        </a>

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

                <div class="col-md-5">

                    <label
                        for="tanggal"
                        class="form-label fw-bold"
                    >

                        Filter Berdasarkan Tanggal

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
                        href="data_absensi.php"
                        class="btn btn-outline-secondary"
                    >

                        Reset

                    </a>

                </div>

            </form>

        </div>

    </div>



    <!-- ========================================
         TABEL DATA
    ======================================== -->

    <div class="card">

        <div class="card-body p-4">


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-success">

                        <tr>

                            <th>No</th>

                            <th>Nama Guru</th>

                            <th>NIP</th>

                            <th>Tanggal</th>

                            <th>Jam Masuk</th>

                            <th>Jam Pulang</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    if (mysqli_num_rows($result) > 0):

                    ?>


                        <?php while ($row = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <td>

                                    <?= $no++ ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars($row["nama"]) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars($row["nip"]) ?>

                                </td>


                                <td>

                                    <?= date(
                                        "d-m-Y",
                                        strtotime($row["tanggal"])
                                    ) ?>

                                </td>


                                <td>

                                    <?=
                                        $row["jam_masuk"]
                                        ? htmlspecialchars($row["jam_masuk"])
                                        : "-"
                                    ?>

                                </td>


                                <td>

                                    <?=
                                        $row["jam_pulang"]
                                        ? htmlspecialchars($row["jam_pulang"])
                                        : "-"
                                    ?>

                                </td>


                                <td>


                                    <?php if (
                                        $row["jam_masuk"]
                                        && $row["jam_pulang"]
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

                                📭 Belum ada data absensi.

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
```
