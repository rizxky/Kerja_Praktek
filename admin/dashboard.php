<?php

session_start();

require_once "../config/database.php";

// Cek login
if (!isset($_SESSION["guru_id"])) {
    header("Location: ../login.php");
    exit;
}

// Cek role admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit;
}


// ========================================
// DATA DASHBOARD
// ========================================

// Total guru
$queryGuru = "SELECT COUNT(*) AS total FROM guru WHERE role = 'guru'";
$resultGuru = mysqli_query($conn, $queryGuru);
$totalGuru = mysqli_fetch_assoc($resultGuru)["total"];


// Absensi hari ini
$tanggalHariIni = date("Y-m-d");

$queryHadir = "
    SELECT COUNT(*) AS total
    FROM absensi
    INNER JOIN guru
        ON absensi.guru_id = guru.id
    WHERE absensi.tanggal = ?
    AND absensi.jam_masuk IS NOT NULL
    AND guru.role = 'guru'
";

$stmtHadir = mysqli_prepare($conn, $queryHadir);

mysqli_stmt_bind_param(
    $stmtHadir,
    "s",
    $tanggalHariIni
);

mysqli_stmt_execute($stmtHadir);

$resultHadir = mysqli_stmt_get_result($stmtHadir);

$totalHadir = mysqli_fetch_assoc($resultHadir)["total"];


// Guru yang belum absen
$totalBelumAbsen = $totalGuru - $totalHadir;


// ========================================
// DATA ABSENSI HARI INI
// ========================================

$queryAbsensi = "
    SELECT
        absensi.*,
        guru.nama,
        guru.nip
    FROM absensi
    INNER JOIN guru
        ON absensi.guru_id = guru.id
    WHERE absensi.tanggal = ?
    AND guru.role = 'guru'
    ORDER BY absensi.jam_masuk ASC
";

$stmtAbsensi = mysqli_prepare($conn, $queryAbsensi);

mysqli_stmt_bind_param(
    $stmtAbsensi,
    "s",
    $tanggalHariIni
);

mysqli_stmt_execute($stmtAbsensi);

$resultAbsensi = mysqli_stmt_get_result($stmtAbsensi);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Sistem Absensi</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


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


        .card-stat {

            border: none;

            border-radius: 15px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);

        }


        .stat-number {

            font-size: 32px;

            font-weight: bold;

        }


        .attendance-card {

            border: none;

            border-radius: 15px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.08);

        }


        .table th {

            white-space: nowrap;

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
            href="dashboard.php">

            🛡️ Admin Sistem Absensi

        </a>


        <div class="d-flex align-items-center">

            <span class="admin-name me-3">

                👤
                <?= htmlspecialchars($_SESSION["nama"]) ?>

            </span>


            <a
                href="../logout.php"
                class="btn btn-light btn-sm">

                Logout

            </a>

        </div>

    </div>

</nav>



<!-- ========================================
     CONTENT
======================================== -->

<div class="container py-4">


    <!-- Judul -->

    <div class="mb-4">

        <h3 class="fw-bold">

            Dashboard Admin

        </h3>

        <p class="text-muted">

            Monitoring absensi guru hari ini

        </p>

    </div>



    <!-- ========================================
         STATISTIK
    ======================================== -->

    <div class="row g-4 mb-4">


        <!-- Total Guru -->

        <div class="col-md-4">

            <div class="card card-stat">

                <div class="card-body p-4">

                    <div class="text-muted mb-2">

                        👨‍🏫 Total Guru

                    </div>

                    <div class="stat-number">

                        <?= $totalGuru ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Sudah Absen -->

        <div class="col-md-4">

            <div class="card card-stat">

                <div class="card-body p-4">

                    <div class="text-muted mb-2">

                        ✅ Sudah Absen

                    </div>

                    <div class="stat-number text-success">

                        <?= $totalHadir ?>

                    </div>

                </div>

            </div>

        </div>



        <!-- Belum Absen -->

        <div class="col-md-4">

            <div class="card card-stat">

                <div class="card-body p-4">

                    <div class="text-muted mb-2">

                        ⏳ Belum Absen

                    </div>

                    <div class="stat-number text-danger">

                        <?= $totalBelumAbsen ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ========================================
         TABEL ABSENSI
    ======================================== -->

    <div class="card attendance-card">


        <div class="card-body p-4">


            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="fw-bold mb-1">

                        📋 Absensi Hari Ini

                    </h5>

                    <small class="text-muted">

                        <?= date("d-m-Y") ?>

                    </small>

                </div>


               <div class="d-flex gap-2">

    <a
        href="data_guru.php"
        class="btn btn-primary">

        👨‍🏫 Data Guru

    </a>

    <a
        href="data_absensi.php"
        class="btn btn-success">

        📋 Lihat Semua Absensi

    </a>

</div>

            </div>



            <div class="table-responsive">


                <table class="table table-hover align-middle">


                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Nama Guru</th>

                            <th>NIP</th>

                            <th>Jam Masuk</th>

                            <th>Jam Pulang</th>

                            <th>Status</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    if (mysqli_num_rows($resultAbsensi) > 0):

                    ?>


                        <?php while ($row = mysqli_fetch_assoc($resultAbsensi)): ?>


                            <tr>

                                <td>

                                    <?= $no++ ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars($row["nama"]) ?>

                                </td>


                                <td>
<?= !empty($row["nip"])
    ? htmlspecialchars($row["nip"])
    : "-"
?>
                                </td>


                                <td>

                                    <?= $row["jam_masuk"]
                                        ? htmlspecialchars($row["jam_masuk"])
                                        : "-"
                                    ?>

                                </td>


                                <td>

                                    <?= $row["jam_pulang"]
                                        ? htmlspecialchars($row["jam_pulang"])
                                        : "-"
                                    ?>

                                </td>


                                <td>


                                    <?php if ($row["jam_masuk"] && $row["jam_pulang"]): ?>

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
                                colspan="6"
                                class="text-center text-muted py-4">

                                Belum ada data absensi hari ini.

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
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>