
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
// TOKEN KEAMANAN
// ========================================

if (empty($_SESSION["csrf_token"])) {

    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

}

// ========================================
// AMBIL DATA GURU
// ========================================

$query = "
    SELECT
        id,
        nama,
        nip,
        username,
        status_akun
    FROM guru
    WHERE role = 'guru'
    ORDER BY nama ASC
";

$result = mysqli_query($conn, $query);

if (!$result) {

    die("Gagal mengambil data guru: " . mysqli_error($conn));

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

    <title>Data Guru - Admin</title>

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

        <div class="d-flex gap-2 flex-wrap">

            <a
                href="tambah_guru.php"
                class="btn btn-success"
            >

                + Tambah Guru

            </a>

            <a
                href="dashboard.php"
                class="btn btn-secondary"
            >

                ← Kembali

            </a>

        </div>

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

                👨‍🏫 Data Guru

            </h3>

            <p class="text-muted mb-0">

                Daftar seluruh guru yang terdaftar

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
         NOTIFIKASI
    ======================================== -->

    <?php

    $status = $_GET["status"] ?? "";

    $notifikasi = [

        "berhasil" => "Data guru berhasil ditambahkan!",

        "edit_berhasil" => "Data guru berhasil diperbarui!",

        "tidak_ditemukan" => "Data guru tidak ditemukan.",

        "nonaktif_berhasil" => "Akun guru berhasil dinonaktifkan!",

        "aktif_berhasil" => "Akun guru berhasil diaktifkan kembali!",

        "gagal" => "Status akun guru gagal diperbarui."

    ];

    if (isset($notifikasi[$status])):

        $jenisAlert = $status === "tidak_ditemukan"
            || $status === "gagal"
            ? "warning"
            : "success";

    ?>

        <div class="alert alert-<?= $jenisAlert ?> alert-dismissible fade show">

            <?= htmlspecialchars($notifikasi[$status]) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Tutup"
            ></button>

        </div>

    <?php endif; ?>


    <!-- ========================================
         TABEL DATA GURU
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

                            <th>Username</th>

                            <th>Status Akun</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $no = 1;

                    if (mysqli_num_rows($result) > 0):

                    ?>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <!-- NOMOR -->

                                <td>

                                    <?= $no++ ?>

                                </td>


                                <!-- NAMA GURU -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars($row["nama"]) ?>

                                    </strong>

                                </td>


                                <!-- NIP -->

                                <td>

                                    <?= !empty($row["nip"])
                                        ? htmlspecialchars($row["nip"])
                                        : "-"
                                    ?>

                                </td>


                                <!-- USERNAME -->

                                <td>

                                    <?= htmlspecialchars($row["username"]) ?>

                                </td>


                                <!-- STATUS AKUN -->

                                <td>

                                    <?php if ($row["status_akun"] === "aktif"): ?>

                                        <span class="badge bg-success">

                                            Aktif

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">

                                            Nonaktif

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- AKSI -->

                                <td>

                                    <div class="d-flex gap-2 flex-wrap">


                                        <!-- LIHAT ABSENSI -->

                                        <a
                                            href="detail_guru.php?id=<?= (int) $row["id"] ?>"
                                            class="btn btn-success btn-sm"
                                        >

                                            📋 Lihat Absensi

                                        </a>


                                        <!-- EDIT GURU -->

                                        <a
                                            href="edit_guru.php?id=<?= (int) $row["id"] ?>"
                                            class="btn btn-warning btn-sm"
                                        >

                                            ✏️ Edit

                                        </a>


                                        <!-- NONAKTIFKAN / AKTIFKAN -->

                                        <form
                                            method="POST"
                                            action="ubah_status_guru.php"
                                            onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun guru ini?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $row["id"] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
                                            >

                                            <?php if ($row["status_akun"] === "aktif"): ?>

                                                <input
                                                    type="hidden"
                                                    name="status_baru"
                                                    value="nonaktif"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                >

                                                    🚫 Nonaktifkan

                                                </button>

                                            <?php else: ?>

                                                <input
                                                    type="hidden"
                                                    name="status_baru"
                                                    value="aktif"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-success btn-sm"
                                                >

                                                    ♻️ Aktifkan Kembali

                                                </button>

                                            <?php endif; ?>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted py-5"
                            >

                                📭 Belum ada data guru.

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
