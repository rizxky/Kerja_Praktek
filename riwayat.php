<?php

session_start();
require_once "config/database.php";

if (!isset($_SESSION["guru_id"])) {
    header("Location: login.php");
    exit;
}

$guru_id = $_SESSION["guru_id"];
$nama = $_SESSION["nama"];


// Ambil semua riwayat absensi
$query = "SELECT *
          FROM absensi
          WHERE guru_id = ?
          ORDER BY tanggal DESC, jam_masuk DESC";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $guru_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Riwayat Absensi - Sistem Absensi</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <style>

        body {

            background-color: #f0fdf4;

            font-family: Arial, sans-serif;

        }


        /* Navbar */

        .navbar-custom {

            background-color: #198754;

        }


        /* Card */

        .history-card {

            border: none;

            border-radius: 20px;

            box-shadow:
                0 8px 25px
                rgba(0, 0, 0, 0.08);

        }


        /* Foto */

        .foto-absen {

            width: 80px;

            height: 80px;

            object-fit: cover;

            border-radius: 10px;

            cursor: pointer;

            transition: 0.2s;

            border: 2px solid #dee2e6;

        }


        .foto-absen:hover {

            transform: scale(1.05);

            border-color: #198754;

        }


        /* Status */

        .status-hadir {

            display: inline-block;

            padding: 6px 12px;

            background-color: #d1e7dd;

            color: #0f5132;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

        }


        /* Table */

        .table {

            vertical-align: middle;

        }


        .table thead th {

            white-space: nowrap;

        }


        /* Empty */

        .empty-icon {

            font-size: 60px;

        }


    </style>

</head>


<body>


<!-- ================================= -->
<!-- NAVBAR -->
<!-- ================================= -->

<nav class="navbar navbar-dark navbar-custom">

    <div class="container">

        <span class="navbar-brand fw-bold">

            📋 Riwayat Absensi

        </span>


        <a
            href="dashboard.php"
            class="btn btn-light btn-sm">

            Dashboard

        </a>

    </div>

</nav>


<!-- ================================= -->
<!-- CONTENT -->
<!-- ================================= -->

<div class="container py-4">


    <!-- HEADER -->

    <div class="mb-4">

        <h2 class="fw-bold">

            Riwayat Absensi

        </h2>

        <p class="text-muted mb-0">

            Daftar seluruh absensi Anda.

        </p>

    </div>


    <!-- INFO GURU -->

    <div class="card history-card mb-4">

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <small class="text-muted">

                        Nama Guru

                    </small>

                    <h5 class="fw-bold mb-0">

                        <?= htmlspecialchars($nama) ?>

                    </h5>

                </div>


                <div class="col-md-6 mt-3 mt-md-0">

                    <small class="text-muted">

                        Total Absensi

                    </small>

                    <h5 class="fw-bold mb-0">

                        <?= mysqli_num_rows($result) ?>

                        Hari

                    </h5>

                </div>

            </div>

        </div>

    </div>


    <!-- ================================= -->
    <!-- TABLE -->
    <!-- ================================= -->

    <div class="card history-card">

        <div class="card-body p-0">


            <?php if (mysqli_num_rows($result) > 0): ?>


                <div class="table-responsive">

                    <table
                        class="table table-hover mb-0">


                        <thead
                            class="table-success">


                            <tr>

                                <th
                                    class="text-center px-3">

                                    No

                                </th>


                                <th>

                                    Tanggal

                                </th>


                                <th
                                    class="text-center">

                                    Foto Masuk

                                </th>


                                <th>

                                    Jam Masuk

                                </th>


                                <th
                                    class="text-center">

                                    Foto Pulang

                                </th>


                                <th>

                                    Jam Pulang

                                </th>


                                <th
                                    class="text-center">

                                    Status

                                </th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php

                        $no = 1;

                        while (
                            $data =
                            mysqli_fetch_assoc($result)
                        ):

                        ?>


                            <tr>


                                <!-- NO -->

                                <td
                                    class="text-center">

                                    <?= $no++ ?>

                                </td>


                                <!-- TANGGAL -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $data["tanggal"]
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- FOTO MASUK -->

                                <td
                                    class="text-center">


                                    <?php if (
                                        !empty(
                                            $data["foto_masuk"]
                                        )
                                    ): ?>


                                        <img
                                            src="<?= htmlspecialchars(
                                                $data["foto_masuk"]
                                            ) ?>"
                                            class="foto-absen"
                                            alt="Foto masuk"
                                            onclick="lihatFoto(this.src)">


                                    <?php else: ?>

                                        <span
                                            class="text-muted">

                                            -

                                        </span>

                                    <?php endif; ?>


                                </td>


                                <!-- JAM MASUK -->

                                <td>

                                    <?=
                                    !empty(
                                        $data["jam_masuk"]
                                    )
                                    ? htmlspecialchars(
                                        $data["jam_masuk"]
                                    )
                                    : "-"
                                    ?>

                                </td>


                                <!-- FOTO PULANG -->

                                <td
                                    class="text-center">


                                    <?php if (
                                        !empty(
                                            $data["foto_pulang"]
                                        )
                                    ): ?>


                                        <img
                                            src="<?= htmlspecialchars(
                                                $data["foto_pulang"]
                                            ) ?>"
                                            class="foto-absen"
                                            alt="Foto pulang"
                                            onclick="lihatFoto(this.src)">


                                    <?php else: ?>

                                        <span
                                            class="text-muted">

                                            -

                                        </span>

                                    <?php endif; ?>


                                </td>


                                <!-- JAM PULANG -->

                                <td>

                                    <?=
                                    !empty(
                                        $data["jam_pulang"]
                                    )
                                    ? htmlspecialchars(
                                        $data["jam_pulang"]
                                    )
                                    : "Belum pulang"
                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td
                                    class="text-center">


                                    <span
                                        class="status-hadir">

                                        <?= htmlspecialchars(
                                            $data["status"]
                                        ) ?>

                                    </span>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>

                </div>


            <?php else: ?>


                <!-- TIDAK ADA DATA -->

                <div class="text-center p-5">

                    <div class="empty-icon">

                        📋

                    </div>

                    <h4 class="fw-bold mt-3">

                        Belum Ada Riwayat

                    </h4>

                    <p class="text-muted">

                        Anda belum memiliki data
                        absensi.

                    </p>


                    <a
                        href="absensi.php"
                        class="btn btn-success">

                        Mulai Absensi

                    </a>

                </div>


            <?php endif; ?>


        </div>

    </div>


    <!-- ================================= -->
    <!-- BUTTON -->
    <!-- ================================= -->

    <div class="mt-4">

        <a
            href="dashboard.php"
            class="btn btn-outline-success">

            ← Kembali ke Dashboard

        </a>

    </div>


</div>


<!-- ================================= -->
<!-- MODAL FOTO -->
<!-- ================================= -->

<div
    class="modal fade"
    id="fotoModal"
    tabindex="-1">


    <div
        class="modal-dialog modal-dialog-centered">


        <div class="modal-content">


            <div class="modal-header">

                <h5 class="modal-title">

                    Foto Absensi

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body text-center">

                <img
                    id="fotoModalImage"
                    src=""
                    class="img-fluid rounded"
                    alt="Foto Absensi">

            </div>


        </div>

    </div>

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<!-- JavaScript Foto -->

<script>

function lihatFoto(src) {

    document.getElementById(
        "fotoModalImage"
    ).src = src;


    const modal =
        new bootstrap.Modal(
            document.getElementById(
                "fotoModal"
            )
        );


    modal.show();

}

</script>


</body>

</html>