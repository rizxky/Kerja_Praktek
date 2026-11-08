<?php

date_default_timezone_set("Asia/Jakarta");

session_start();
require_once "config/database.php";


// ========================================
// CEK LOGIN
// ========================================

if (!isset($_SESSION["guru_id"])) {
    header("Location: login.php");
    exit;
}

$guru_id = $_SESSION["guru_id"];
$nama = $_SESSION["nama"];

// ========================================
// PEMBATASAN PERANGKAT: ABSENSI HANYA DARI HP
// ========================================
// Deteksi sederhana berdasarkan identitas browser (User-Agent).
// Ini bukan perlindungan mutlak karena User-Agent dapat dipalsukan.
$userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "";
$isMobile = preg_match(
    '/Android|iPhone|iPad|iPod|Mobile|Windows Phone|IEMobile|Opera Mini/i',
    $userAgent
);

if (!$isMobile) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Absensi Ditolak</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f0fdf4;
                font-family: Arial, sans-serif;
            }
            .notice {
                max-width: 520px;
                margin: 20px;
                padding: 32px;
                border-radius: 18px;
                background: white;
                box-shadow: 0 8px 25px rgba(0,0,0,.08);
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="notice">
            <div class="display-4 mb-3">📱</div>
            <h2 class="text-danger fw-bold">Akses Absensi Ditolak</h2>
            <p class="text-muted mt-3">
                Fitur absensi masuk dan pulang hanya dapat diakses melalui HP.
                Silakan buka sistem absensi menggunakan browser di HP Anda.
            </p>
            <a href="dashboard.php" class="btn btn-success mt-2">Kembali ke Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}


// ========================================
// LOKASI SEKOLAH
// ========================================

$schoolLat = -6.308845091633093;
$schoolLng = 106.68282722053694;

// Radius maksimal absensi
$radiusMaksimal = 100; // meter


// ========================================
// VARIABEL
// ========================================

$pesan = "";
$tipePesan = "";

$jarakDariSekolah = null;

$tanggal = date("Y-m-d");


// ========================================
// FUNGSI HITUNG JARAK GPS
// ========================================

function hitungJarak(
    $lat1,
    $lon1,
    $lat2,
    $lon2
) {

    // Radius bumi dalam meter
    $earthRadius = 6371000;

    // Konversi derajat ke radian
    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);

    $deltaLat = deg2rad($lat2 - $lat1);
    $deltaLon = deg2rad($lon2 - $lon1);

    // Rumus Haversine
    $a =
        sin($deltaLat / 2) *
        sin($deltaLat / 2)
        +
        cos($lat1) *
        cos($lat2) *
        sin($deltaLon / 2) *
        sin($deltaLon / 2);

    $c =
        2 *
        atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

    return $earthRadius * $c;
}


// ========================================
// AMBIL ABSENSI HARI INI
// ========================================

$queryHariIni = "
    SELECT *
    FROM absensi
    WHERE guru_id = ?
    AND tanggal = ?
";

$stmtHariIni = mysqli_prepare(
    $conn,
    $queryHariIni
);

mysqli_stmt_bind_param(
    $stmtHariIni,
    "is",
    $guru_id,
    $tanggal
);

mysqli_stmt_execute(
    $stmtHariIni
);

$resultHariIni =
    mysqli_stmt_get_result(
        $stmtHariIni
    );

$absensiHariIni =
    mysqli_fetch_assoc(
        $resultHariIni
    );


// ========================================
// PROSES ABSENSI
// ========================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $foto =
        $_POST["foto"] ?? "";

    $jenis_absen =
        $_POST["jenis_absen"] ?? "";

    $latitude =
        $_POST["latitude"] ?? "";

    $longitude =
        $_POST["longitude"] ?? "";


    // ========================================
    // VALIDASI FOTO
    // ========================================

    if (
        empty($foto) ||
        !str_starts_with(
            $foto,
            "data:image/jpeg;base64,"
        )
    ) {

        $pesan =
            "Silakan ambil foto terlebih dahulu.";

        $tipePesan =
            "danger";
    }


    // ========================================
    // VALIDASI GPS
    // ========================================

    elseif (
        !is_numeric($latitude) ||
        !is_numeric($longitude) ||
        abs((float)$latitude) > 90 ||
        abs((float)$longitude) > 180
    ) {

        $pesan =
            "Lokasi GPS tidak valid. Silakan aktifkan lokasi.";

        $tipePesan =
            "danger";
    }


    else {

    // ========================================
    // KOORDINAT LOKASI PENGUJIAN
    // ========================================

    $latitudeSekolah = -6.308845091633093;
    $longitudeSekolah = 106.68282722053694;


    // ========================================
    // HITUNG JARAK DARI SEKOLAH
    // ========================================

    $jarakDariSekolah =
        hitungJarak(
            (float)$latitude,
            (float)$longitude,
            $latitudeSekolah,
            $longitudeSekolah
        );


    // ========================================
    // CEK RADIUS
    // ========================================

    if (
        $jarakDariSekolah >
        $radiusMaksimal
    ) {

            $pesan =
                "Anda berada di luar area absensi. " .
                "Jarak dari sekolah: " .
                round($jarakDariSekolah) .
                " meter. " .
                "Maksimal radius: " .
                $radiusMaksimal .
                " meter.";

            $tipePesan =
                "danger";
        }


        else {

            // ========================================
            // DECODE FOTO
            // ========================================

            $fotoBase64 =
                substr(
                    $foto,
                    strlen(
                        "data:image/jpeg;base64,"
                    )
                );


            $fotoData =
                base64_decode(
                    $fotoBase64,
                    true
                );


            // ========================================
            // VALIDASI FOTO
            // ========================================

            if (
                $fotoData === false ||
                strlen($fotoData) >
                5 * 1024 * 1024
            ) {

                $pesan =
                    "Foto tidak valid atau ukurannya terlalu besar.";

                $tipePesan =
                    "danger";
            }


            else {

                // ========================================
                // AMBIL DATA ABSENSI TERBARU
                // ========================================

                $stmtHariIni =
                    mysqli_prepare(
                        $conn,
                        $queryHariIni
                    );

                mysqli_stmt_bind_param(
                    $stmtHariIni,
                    "is",
                    $guru_id,
                    $tanggal
                );

                mysqli_stmt_execute(
                    $stmtHariIni
                );

                $resultHariIni =
                    mysqli_stmt_get_result(
                        $stmtHariIni
                    );

                $absensiHariIni =
                    mysqli_fetch_assoc(
                        $resultHariIni
                    );


                // ========================================
                // ABSEN MASUK
                // ========================================

                if (
                    $jenis_absen === "masuk"
                ) {

                    // Cek apakah sudah absen
                    if ($absensiHariIni) {

                        $pesan =
                            "Anda sudah melakukan absen masuk hari ini.";

                        $tipePesan =
                            "danger";
                    }


                    else {

                        $jam_masuk =
                            date("H:i:s");

                        $status =
                            "Hadir";


                        // Nama file
                        $namaFile =
                            "masuk_" .
                            $guru_id .
                            "_" .
                            time() .
                            ".jpg";


                        $lokasi =
                            "uploads/selfie/" .
                            $namaFile;


                        // Simpan foto
                        if (
                            file_put_contents(
                                $lokasi,
                                $fotoData
                            ) !== false
                        ) {

                            // ========================================
                            // INSERT ABSENSI
                            // ========================================

                            $query =
                                "
                                INSERT INTO absensi
                                (
                                    guru_id,
                                    tanggal,
                                    jam_masuk,
                                    foto_masuk,
                                    latitude,
                                    longitude,
                                    status
                                )
                                VALUES
                                (?, ?, ?, ?, ?, ?, ?)
                                ";


                            $stmt =
                                mysqli_prepare(
                                    $conn,
                                    $query
                                );


                            mysqli_stmt_bind_param(
                                $stmt,
                                "issssss",
                                $guru_id,
                                $tanggal,
                                $jam_masuk,
                                $lokasi,
                                $latitude,
                                $longitude,
                                $status
                            );


                            if (
                                mysqli_stmt_execute(
                                    $stmt
                                )
                            ) {

                                $pesan =
                                    "Absen masuk berhasil disimpan! " .
                                    "Jarak Anda dari sekolah: " .
                                    round($jarakDariSekolah) .
                                    " meter.";

                                $tipePesan =
                                    "success";
                            }


                            else {

                                // Hapus foto jika database gagal
                                unlink($lokasi);

                                $pesan =
                                    "Gagal menyimpan data absen.";

                                $tipePesan =
                                    "danger";
                            }

                        }


                        else {

                            $pesan =
                                "Gagal menyimpan foto.";

                            $tipePesan =
                                "danger";
                        }

                    }

                }


                // ========================================
                // ABSEN PULANG
                // ========================================

                elseif (
                    $jenis_absen === "pulang"
                ) {


                    // Belum absen masuk
                    if (!$absensiHariIni) {

                        $pesan =
                            "Anda belum melakukan absen masuk.";

                        $tipePesan =
                            "danger";
                    }


                    // Sudah absen pulang
                    elseif (
                        !empty(
                            $absensiHariIni["jam_pulang"]
                        )
                    ) {

                        $pesan =
                            "Anda sudah melakukan absen pulang hari ini.";

                        $tipePesan =
                            "danger";
                    }


                    else {

                        $jam_pulang =
                            date("H:i:s");


                        $namaFile =
                            "pulang_" .
                            $guru_id .
                            "_" .
                            time() .
                            ".jpg";


                        $lokasi =
                            "uploads/selfie/" .
                            $namaFile;


                        // Simpan foto
                        if (
                            file_put_contents(
                                $lokasi,
                                $fotoData
                            ) !== false
                        ) {


                            // ========================================
                            // UPDATE ABSEN PULANG
                            // ========================================

                            $query =
                                "
                                UPDATE absensi
                                SET
                                    jam_pulang = ?,
                                    foto_pulang = ?,
                                    latitude = ?,
                                    longitude = ?
                                WHERE
                                    guru_id = ?
                                    AND tanggal = ?
                                    AND jam_pulang IS NULL
                                ";


                            $stmt =
                                mysqli_prepare(
                                    $conn,
                                    $query
                                );


                            mysqli_stmt_bind_param(
                                $stmt,
                                "ssssis",
                                $jam_pulang,
                                $lokasi,
                                $latitude,
                                $longitude,
                                $guru_id,
                                $tanggal
                            );


                            if (
                                mysqli_stmt_execute(
                                    $stmt
                                ) &&
                                mysqli_stmt_affected_rows(
                                    $stmt
                                ) > 0
                            ) {

                                $pesan =
                                    "Absen pulang berhasil disimpan! " .
                                    "Jarak Anda dari sekolah: " .
                                    round($jarakDariSekolah) .
                                    " meter.";

                                $tipePesan =
                                    "success";
                            }


                            else {

                                unlink($lokasi);

                                $pesan =
                                    "Gagal menyimpan absen pulang.";

                                $tipePesan =
                                    "danger";
                            }

                        }


                        else {

                            $pesan =
                                "Gagal menyimpan foto.";

                            $tipePesan =
                                "danger";
                        }

                    }

                }


                else {

                    $pesan =
                        "Jenis absensi tidak valid.";

                    $tipePesan =
                        "danger";
                }

            }

        }

    }


    // ========================================
    // AMBIL ULANG DATA ABSENSI
    // ========================================

    $stmtHariIni =
        mysqli_prepare(
            $conn,
            $queryHariIni
        );

    mysqli_stmt_bind_param(
        $stmtHariIni,
        "is",
        $guru_id,
        $tanggal
    );

    mysqli_stmt_execute(
        $stmtHariIni
    );

    $resultHariIni =
        mysqli_stmt_get_result(
            $stmtHariIni
        );

    $absensiHariIni =
        mysqli_fetch_assoc(
            $resultHariIni
        );
}


// Tentukan jenis absensi berdasarkan pilihan dari dashboard
$jenisRequest = $_GET["jenis"] ?? "";

// Jika tidak ada pilihan dari dashboard,
// gunakan sistem otomatis seperti sebelumnya
if ($jenisRequest === "") {

    if (!$absensiHariIni) {

        $jenisAbsen = "masuk";

        $judulAbsen = "Absen Masuk";

        $deskripsiAbsen =
            "Lakukan absensi masuk dengan selfie dan lokasi GPS.";

    } elseif (!$absensiHariIni["jam_pulang"]) {

        $jenisAbsen = "pulang";

        $judulAbsen = "Absen Pulang";

        $deskripsiAbsen =
            "Lakukan absensi pulang dengan selfie dan lokasi GPS.";

    } else {

        $jenisAbsen = "";

        $judulAbsen = "Absensi Hari Ini Lengkap";

        $deskripsiAbsen =
            "Anda sudah melakukan absen masuk dan pulang hari ini.";

    }

} elseif ($jenisRequest === "masuk") {

    // Pilihan Absen Masuk

    if ($absensiHariIni) {

        $jenisAbsen = "";

        $judulAbsen = "Absen Masuk Sudah Dilakukan";

        $deskripsiAbsen =
            "Anda sudah melakukan absen masuk hari ini.";

    } else {

        $jenisAbsen = "masuk";

        $judulAbsen = "Absen Masuk";

        $deskripsiAbsen =
            "Lakukan absensi masuk dengan selfie dan lokasi GPS.";

    }

} elseif ($jenisRequest === "pulang") {

    // Pilihan Absen Pulang

    if (!$absensiHariIni) {

        $jenisAbsen = "";

        $judulAbsen = "Belum Absen Masuk";

        $deskripsiAbsen =
            "Anda harus melakukan absen masuk terlebih dahulu.";

    } elseif ($absensiHariIni["jam_pulang"]) {

        $jenisAbsen = "";

        $judulAbsen = "Absen Pulang Sudah Dilakukan";

        $deskripsiAbsen =
            "Anda sudah melakukan absen pulang hari ini.";

    } else {

        $jenisAbsen = "pulang";

        $judulAbsen = "Absen Pulang";

        $deskripsiAbsen =
            "Lakukan absensi pulang dengan selfie dan lokasi GPS.";

    }

} else {

    // Jika parameter tidak valid

    $jenisAbsen = "";

    $judulAbsen = "Pilihan Tidak Valid";

    $deskripsiAbsen =
        "Jenis absensi tidak ditemukan.";

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

    <title>Absensi Guru</title>


    <!-- ========================================
         BOOTSTRAP
    ======================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {

            background-color: #f0fdf4;

            font-family: Arial, sans-serif;

        }


        .navbar-custom {

            background-color: #198754;

        }


        .attendance-card {

            border: none;

            border-radius: 20px;

            box-shadow:
                0 8px 25px
                rgba(0, 0, 0, 0.08);

        }


        .status-card {

            border-radius: 15px;

            border: none;

            background-color: white;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.06);

        }


        .camera-box {

            background-color: #111;

            border-radius: 15px;

            padding: 10px;

            overflow: hidden;

        }


        #camera {

            width: 100%;

            max-width: 500px;

            display: block;

            margin: auto;

            border-radius: 10px;

        }


        #hasilFoto {

            width: 100%;

            max-width: 500px;

            height: auto;

            object-fit: contain;

            border-radius: 10px;

            display: none;

            margin: 15px auto 0;

        }


        .btn-main {

            border-radius: 10px;

            padding: 12px 20px;

            font-weight: bold;

        }


        .time-box {

            background-color: #d1fae5;

            border-radius: 12px;

            padding: 15px;

        }


        .location-box {

            background-color: #ecfdf5;

            border: 1px solid #a7f3d0;

            border-radius: 12px;

            padding: 15px;

        }

    </style>

</head>


<body>


<!-- ========================================
     NAVBAR
======================================== -->

<nav class="navbar navbar-dark navbar-custom">

    <div class="container">

        <span class="navbar-brand fw-bold">

            📸 Absensi Guru

        </span>


        <a
            href="dashboard.php"
            class="btn btn-light btn-sm"
        >

            Dashboard

        </a>

    </div>

</nav>


<!-- ========================================
     CONTENT
======================================== -->

<div class="container py-4">


    <!-- HEADER -->

    <div class="text-center mb-4">

        <h2 class="fw-bold">

            <?= htmlspecialchars($judulAbsen) ?>

        </h2>


        <p class="text-muted">

            <?= htmlspecialchars($deskripsiAbsen) ?>

        </p>

    </div>


    <!-- ========================================
         PESAN
    ======================================== -->

    <?php if (!empty($pesan)): ?>

        <div
            class="alert alert-<?= htmlspecialchars($tipePesan) ?> text-center"
            role="alert"
        >

            <strong>

                <?= htmlspecialchars($pesan) ?>

            </strong>

        </div>

    <?php endif; ?>


    <!-- ========================================
         INFORMASI ABSENSI
    ======================================== -->

    <div class="card status-card mb-4">

        <div class="card-body">

            <div class="row text-center">


                <!-- NAMA -->

                <div class="col-md-4 mb-3 mb-md-0">

                    <small class="text-muted">

                        Nama Guru

                    </small>


                    <h5 class="fw-bold mt-1">

                        <?= htmlspecialchars($nama) ?>

                    </h5>

                </div>


                <!-- TANGGAL -->

                <div class="col-md-4 mb-3 mb-md-0">

                    <small class="text-muted">

                        Tanggal

                    </small>


                    <h5 class="fw-bold mt-1">

                        <?= date("d-m-Y") ?>

                    </h5>

                </div>


                <!-- STATUS -->

                <div class="col-md-4">

                    <small class="text-muted">

                        Status

                    </small>


                    <h5 class="fw-bold text-success mt-1">

                        <?php if ($absensiHariIni): ?>

                            Hadir

                        <?php else: ?>

                            Belum Absen

                        <?php endif; ?>

                    </h5>

                </div>


            </div>

        </div>

    </div>


    <!-- ========================================
         JAM MASUK / PULANG
    ======================================== -->

    <?php if ($absensiHariIni && $jenisAbsen === "masuk"): ?>

    <!-- Hanya JAM MASUK -->

<?php elseif ($absensiHariIni && $jenisAbsen === "pulang"): ?>

    <!-- Hanya JAM PULANG -->

<?php endif; ?>


    <!-- ========================================
         GPS + KAMERA
    ======================================== -->

    <?php if ($jenisAbsen !== ""): ?>


        <!-- ========================================
             GPS STATUS
        ======================================== -->

        <div
            class="alert alert-warning text-center"
            id="gpsStatus"
        >

            📍 Sedang mengambil lokasi...

        </div>


        <!-- ========================================
             INFORMASI LOKASI
        ======================================== -->

        <div
            class="location-box text-center mb-4"
            id="locationInfo"
        >

            <strong>

                📍 Lokasi Absensi

            </strong>

            <div class="mt-2">

                <small>

                    Jarak dari sekolah akan ditampilkan
                    setelah GPS berhasil didapatkan.

                </small>

            </div>

        </div>


        <!-- ========================================
             CAMERA CARD
        ======================================== -->

        <div class="card attendance-card">

            <div class="card-body p-3 p-md-4">


                <h5 class="fw-bold text-center mb-3">

                    📷 Ambil Foto Selfie

                </h5>


                <!-- CAMERA -->

                <div class="camera-box">

                    <video
                        id="camera"
                        autoplay
                        playsinline
                    ></video>


                    <canvas
                        id="hasilFoto"
                        width="500"
                        height="375"
                    ></canvas>

                </div>


                <!-- AMBIL FOTO -->

                <div class="text-center mt-3">

                    <button
                        type="button"
                        onclick="ambilFoto()"
                        class="btn btn-success btn-main"
                    >

                        📸 Ambil Foto

                    </button>

                </div>


                <!-- FORM -->

                <form
                    method="POST"
                    class="text-center mt-3"
                    onsubmit="return validasiAbsensi()"
                >


                    <!-- FOTO -->

                    <input
                        type="hidden"
                        name="foto"
                        id="foto"
                    >


                    <!-- JENIS ABSEN -->

                    <input
                        type="hidden"
                        name="jenis_absen"
                        value="<?= htmlspecialchars($jenisAbsen) ?>"
                    >


                    <!-- LATITUDE -->

                    <input
                        type="hidden"
                        name="latitude"
                        id="latitude"
                    >


                    <!-- LONGITUDE -->

                    <input
                        type="hidden"
                        name="longitude"
                        id="longitude"
                    >


                    <!-- SIMPAN -->

                    <button
                        type="submit"
                        class="btn btn-primary btn-main"
                        id="btnSimpan"
                        disabled
                    >

                        ✓ Simpan
                        <?= ucfirst($jenisAbsen) ?>

                    </button>


                </form>


                <p
                    class="text-muted text-center mt-3 mb-0"
                >

                    Pastikan wajah terlihat jelas dan
                    lokasi GPS berada dalam radius
                    <?= $radiusMaksimal ?> meter dari sekolah.

                </p>


            </div>

        </div>


    <?php else: ?>

    <!-- ========================================
         STATUS ABSENSI
    ======================================== -->

    <div class="card attendance-card">

        <div class="card-body text-center p-5">

            <?php if (!$absensiHariIni): ?>

                <!-- BELUM ABSEN MASUK -->

                <div class="display-1 mb-3">
                    ⚠️
                </div>

                <h4 class="fw-bold text-danger">
                    Belum Absen Masuk
                </h4>

                <p class="text-muted">
                    Anda harus melakukan absen masuk terlebih dahulu.
                </p>

                <a
                    href="absensi.php?jenis=masuk"
                    class="btn btn-success btn-main"
                >
                    Absen Masuk
                </a>


            <?php elseif ($jenisRequest === "masuk" && $absensiHariIni): ?>

                <!-- SUDAH ABSEN MASUK -->

                <div class="display-1 mb-3">
                    ✅
                </div>

                <h4 class="fw-bold text-success">
                    Absen Masuk Sudah Dilakukan
                </h4>

                <p class="text-muted">
                    Anda sudah melakukan absen masuk hari ini.
                </p>

                <a
                    href="absensi.php?jenis=pulang"
                    class="btn btn-success btn-main"
                >
                    Absen Pulang
                </a>


            <?php elseif (
                $jenisRequest === "pulang" &&
                !empty($absensiHariIni["jam_pulang"])
            ): ?>

                <!-- SUDAH ABSEN PULANG -->

                <div class="display-1 mb-3">
                    ✅
                </div>

                <h4 class="fw-bold text-success">
                    Absen Pulang Sudah Dilakukan
                </h4>

                <p class="text-muted">
                    Anda sudah melakukan absen pulang hari ini.
                </p>

                <a
                    href="riwayat.php"
                    class="btn btn-success btn-main"
                >
                    Lihat Riwayat
                </a>


            <?php else: ?>

                <!-- ABSENSI LENGKAP -->

                <div class="display-1 mb-3">
                    ✅
                </div>

                <h4 class="fw-bold text-success">
                    Absensi Hari Ini Lengkap
                </h4>

                <p class="text-muted">
                    Anda sudah melakukan absen masuk dan pulang hari ini.
                </p>

                <a
                    href="riwayat.php"
                    class="btn btn-success btn-main"
                >
                    Lihat Riwayat
                </a>

            <?php endif; ?>

        </div>

    </div>

<?php endif; ?>

    <!-- ========================================
         NAVIGASI
    ======================================== -->

    <div class="text-center mt-4">

        <a
            href="dashboard.php"
            class="btn btn-outline-secondary me-2"
        >

            ← Dashboard

        </a>


        <a
            href="riwayat.php"
            class="btn btn-outline-success"
        >

            Riwayat Absensi

        </a>

    </div>


</div>


<!-- ========================================
     JAVASCRIPT
======================================== -->

<script>

<?php if ($jenisAbsen !== ""): ?>


const video =
    document.getElementById("camera");


const canvas =
    document.getElementById("hasilFoto");


const context =
    canvas.getContext("2d");


const inputFoto =
    document.getElementById("foto");


const latitudeInput =
    document.getElementById("latitude");


const longitudeInput =
    document.getElementById("longitude");


const btnSimpan =
    document.getElementById("btnSimpan");


const gpsStatus =
    document.getElementById("gpsStatus");


const locationInfo =
    document.getElementById("locationInfo");


let streamKamera = null;

let gpsSiap = false;

let fotoSiap = false;


// ========================================
// TOMBOL SIMPAN
// ========================================

function perbaruiTombol() {

    btnSimpan.disabled =
        !(gpsSiap && fotoSiap);

}


// ========================================
// GPS
// ========================================

console.log(
    "GPS script berjalan..."
);


if (
    navigator.geolocation
) {

    gpsStatus.className =
        "alert alert-warning text-center";

    gpsStatus.textContent =
        "📍 Sedang meminta lokasi...";


    navigator.geolocation.getCurrentPosition(

        function(position) {

            console.log(
                "GPS berhasil"
            );


            const latitude =
                position.coords.latitude;


            const longitude =
                position.coords.longitude;


            console.log(
                "Latitude:",
                latitude
            );


            console.log(
                "Longitude:",
                longitude
            );


            // Simpan koordinat
            latitudeInput.value =
                latitude;


            longitudeInput.value =
                longitude;


            gpsSiap =
                true;


            // ========================================
            // TAMPILKAN LOKASI
            // ========================================

            gpsStatus.className =
                "alert alert-success text-center";

            gpsStatus.textContent =
                "📍 Lokasi berhasil didapatkan.";


            // ========================================
// HITUNG JARAK DARI SEKOLAH
// ========================================

const latitudeSekolah =
    -6.308845091633093;

const longitudeSekolah =
    106.68282722053694;

const radiusBumi =
    6371000;


// Konversi ke radian

const latSekolah =
    latitudeSekolah * Math.PI / 180;

const latGuru =
    latitude * Math.PI / 180;

const deltaLat =
    (latitude - latitudeSekolah)
    * Math.PI / 180;

const deltaLon =
    (longitude - longitudeSekolah)
    * Math.PI / 180;


// Rumus Haversine

const a =
    Math.sin(deltaLat / 2) *
    Math.sin(deltaLat / 2)
    +
    Math.cos(latSekolah) *
    Math.cos(latGuru) *
    Math.sin(deltaLon / 2) *
    Math.sin(deltaLon / 2);


const c =
    2 *
    Math.atan2(
        Math.sqrt(a),
        Math.sqrt(1 - a)
    );


const jarak =
    radiusBumi * c;


// ========================================
// TENTUKAN STATUS
// ========================================

let statusJarak = "";

if (jarak <= 100) {

    statusJarak = `
        <div class="text-success fw-bold mt-2">
            ✅ Anda berada dalam radius absensi.
        </div>
    `;

} else {

    statusJarak = `
        <div class="text-danger fw-bold mt-2">
            ❌ Anda berada di luar radius absensi.
        </div>
    `;

}


// ========================================
// TAMPILKAN INFORMASI
// ========================================

locationInfo.innerHTML = `

    <strong>
        📍 Lokasi Absensi
    </strong>

    <div class="mt-2">

        <div>
            Latitude:
            ${latitude.toFixed(6)}
        </div>

        <div>
            Longitude:
            ${longitude.toFixed(6)}
        </div>

        <div class="mt-2">
            Jarak dari sekolah:
            <strong>
                ${Math.round(jarak)} meter
            </strong>
        </div>

        <div>
            Radius maksimal:
            <strong>
                100 meter
            </strong>
        </div>

        ${statusJarak}

    </div>

`;


            perbaruiTombol();

        },


        function(error) {

            console.log(
                "GPS error:",
                error.code,
                error.message
            );


            gpsSiap =
                false;


            gpsStatus.className =
                "alert alert-danger text-center";


            if (
                error.code === 1
            ) {

                gpsStatus.textContent =
                    "❌ Izin lokasi ditolak. Izinkan akses lokasi pada browser.";

            }

            else if (
                error.code === 2
            ) {

                gpsStatus.textContent =
                    "❌ Lokasi tidak tersedia.";

            }

            else if (
                error.code === 3
            ) {

                gpsStatus.textContent =
                    "❌ Permintaan lokasi timeout.";

            }

            else {

                gpsStatus.textContent =
                    "❌ Gagal mendapatkan lokasi.";

            }


            locationInfo.innerHTML = `

                <strong>
                    📍 Lokasi Absensi
                </strong>

                <div class="mt-2 text-danger">

                    Lokasi GPS belum tersedia.

                </div>

            `;


            perbaruiTombol();

        },


        {

            enableHighAccuracy: true,

            timeout: 15000,

            maximumAge: 0

        }

    );

}


else {

    gpsStatus.className =
        "alert alert-danger text-center";

    gpsStatus.textContent =
        "❌ Browser tidak mendukung GPS.";

}


// ========================================
// KAMERA
// ========================================

if (
    navigator.mediaDevices &&
    navigator.mediaDevices.getUserMedia
) {


    navigator.mediaDevices.getUserMedia({

        video: {

            facingMode: "user"

        },

        audio: false

    })


    .then(
        function(stream) {

            streamKamera =
                stream;


            video.srcObject =
                stream;

        }
    )


    .catch(
        function(error) {

            console.log(
                "Camera error:",
                error
            );


            alert(
                "Kamera tidak dapat diakses: "
                + error.message
            );

        }
    );

}


else {

    alert(
        "Browser tidak mendukung akses kamera."
    );

}


// ========================================
// AMBIL FOTO
// ========================================

function ambilFoto() {


    if (
        !video.videoWidth
    ) {

        alert(
            "Kamera belum siap. Silakan tunggu."
        );

        return;

    }


    const lebar =
        video.videoWidth;


    const tinggi =
        video.videoHeight;


    canvas.width =
        lebar;


    canvas.height =
        tinggi;


    context.drawImage(

        video,

        0,

        0,

        lebar,

        tinggi

    );


    const foto =
        canvas.toDataURL(
            "image/jpeg",
            0.85
        );


    inputFoto.value =
        foto;


    canvas.style.display =
        "block";


    fotoSiap =
        true;


    perbaruiTombol();

}


// ========================================
// VALIDASI ABSENSI
// ========================================

function validasiAbsensi() {


    // Cek foto

    if (
        !fotoSiap ||
        !inputFoto.value
    ) {

        alert(
            "Silakan ambil foto terlebih dahulu."
        );

        return false;

    }


    // Cek GPS

    if (
        !gpsSiap ||
        !latitudeInput.value ||
        !longitudeInput.value
    ) {

        alert(
            "Lokasi GPS belum tersedia."
        );

        return false;

    }


    return true;

}


// ========================================
// MATIKAN KAMERA
// ========================================

window.addEventListener(
    "pagehide",
    function() {

        if (streamKamera) {

            streamKamera
                .getTracks()
                .forEach(
                    function(track) {

                        track.stop();

                    }
                );

        }

    }
);


<?php endif; ?>

</script>


</body>

</html>