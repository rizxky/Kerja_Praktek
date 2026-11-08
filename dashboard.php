<?php

session_start();

if (!isset($_SESSION["guru_id"])) {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION["nama"];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Dashboard Guru - Sistem Absensi</title>

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

        /* Header */

        .welcome-card {

            background: linear-gradient(
                135deg,
                #198754,
                #20c997
            );

            color: white;

            border: none;

            border-radius: 20px;

            box-shadow:
                0 8px 20px
                rgba(0, 0, 0, 0.1);

        }

        /* Menu Card */

        .menu-card {

            border: none;

            border-radius: 18px;

            transition: 0.3s;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.08);

        }

        .menu-card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 10px 25px
                rgba(0, 0, 0, 0.12);

        }

        .menu-icon {

            width: 70px;

            height: 70px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 32px;

            margin: 0 auto 15px;

        }

        .icon-masuk {

            background-color: #adeaca;

        }

        .icon-pulang {

            background-color: #fee2e2;

        }

        .icon-riwayat {

            background-color: #cffafe;

        }

        .section-title {

            font-weight: bold;

        }

    </style>

</head>

<body>


<!-- ============================= -->
<!-- NAVBAR -->
<!-- ============================= -->

<nav class="navbar navbar-dark navbar-custom">

    <div class="container">

        <span class="navbar-brand fw-bold">

            📋 Sistem Absensi Guru

        </span>

        <a
            href="logout.php"
            class="btn btn-light btn-sm">

            Logout

        </a>

    </div>

</nav>


<!-- ============================= -->
<!-- CONTENT -->
<!-- ============================= -->

<div class="container py-5">


    <!-- WELCOME -->

    <div class="card welcome-card mb-5">

        <div class="card-body p-4 p-md-5">

            <p class="mb-2">
                Selamat datang 👋
            </p>

            <h2 class="fw-bold mb-2">

                <?= htmlspecialchars($nama) ?>

            </h2>

            <p class="mb-0">

                Silakan lakukan absensi sesuai jadwal
                Anda.

            </p>

        </div>

    </div>


    <!-- MENU -->

    <h4 class="section-title mb-4">

        Menu Utama

    </h4>


    <div class="row g-4">


        <!-- ABSEN MASUK -->

<div class="col-md-6">

    <div class="card menu-card h-100">

        <div class="card-body text-center p-4">

            <div
                class="menu-icon icon-masuk">

                🟢

            </div>

            <h4 class="fw-bold">

                Absen Masuk

            </h4>

            <p class="text-muted">

                Lakukan absensi masuk
                menggunakan selfie dan
                lokasi GPS.

            </p>

            <a
                href="absensi.php?jenis=masuk"
                class="btn btn-success w-100">

                Absen Masuk

            </a>

        </div>

    </div>

</div>


<!-- ABSEN PULANG -->

<div class="col-md-6">

    <div class="card menu-card h-100">

        <div class="card-body text-center p-4">

            <div
                class="menu-icon icon-pulang">

                🔴

            </div>

            <h4 class="fw-bold">

                Absen Pulang

            </h4>

            <p class="text-muted">

                Lakukan absensi pulang
                menggunakan selfie dan
                lokasi GPS.

            </p>

            <a
                href="absensi.php?jenis=pulang"
                class="btn btn-danger w-100">

                Absen Pulang

            </a>

        </div>

    </div>

</div>


        <!-- RIWAYAT -->

        <div class="col-md-6">

            <div class="card menu-card h-100">

                <div class="card-body text-center p-4">

                    <div
                        class="menu-icon icon-riwayat">

                        📋

                    </div>

                    <h4 class="fw-bold">

                        Riwayat Absensi

                    </h4>

                    <p class="text-muted">

                        Lihat seluruh riwayat
                        absensi yang telah
                        dilakukan.

                    </p>

                    <a
                        href="riwayat.php"
                        class="btn btn-info text-white w-100">

                        Lihat Riwayat

                    </a>

                </div>

            </div>

        </div>


    </div>


    <!-- FOOTER -->

    <div class="text-center mt-5">

        <p class="text-muted mb-0">

            Sistem Absensi Guru © 2026

        </p>

    </div>


</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>