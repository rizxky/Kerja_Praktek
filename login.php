<?php

session_start();

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT * FROM guru WHERE username = ?";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $username
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $guru = mysqli_fetch_assoc($result);


   
if ($guru && password_verify($password, $guru["password"])) {

    // Jika Admin
    if ($guru["role"] === "admin") {

        $_SESSION["guru_id"] = $guru["id"];
        $_SESSION["nama"] = $guru["nama"];
        $_SESSION["role"] = $guru["role"];

        header("Location: admin/dashboard.php");
        exit;
    }

    // Jika Guru
    if ($guru["role"] === "guru") {

        // Cek status akun
        if ($guru["status_akun"] !== "aktif") {

            $error = "Akun Anda telah dinonaktifkan. Silakan hubungi Admin.";

        } else {

            $_SESSION["guru_id"] = $guru["id"];
            $_SESSION["nama"] = $guru["nama"];
            $_SESSION["role"] = $guru["role"];

            header("Location: dashboard.php");
            exit;
        }

    } else {

        $error = "Username atau password salah!";

    }

} else {

    $error = "Username atau password salah!";

}

}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Login Guru - Sistem Absensi</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            background: linear-gradient(
                135deg,
                #d1fae5,
                #ecfdf5
            );

            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: Arial, sans-serif;
        }

        .login-card {

            width: 100%;
            max-width: 420px;

            border: none;

            border-radius: 20px;

            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, 0.12);

        }

        .login-icon {

            width: 80px;
            height: 80px;

            background-color: #198754;

            color: white;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 36px;

            margin: 0 auto 20px;

        }

        .btn-login {

            border-radius: 10px;

            padding: 12px;

            font-weight: bold;

        }

        .form-control {

            border-radius: 10px;

            padding: 12px;

        }

    </style>

</head>

<body>

<div class="container">

    <div class="row justify-content-center">

        <div class="col-12">

            <div class="card login-card">

                <div class="card-body p-4 p-md-5">

                    <!-- Icon -->

                    <div class="login-icon">

                        👤

                    </div>


                    <!-- Judul -->

                    <div class="text-center mb-4">

                        <h3 class="fw-bold">
                            Sistem Absensi Guru
                        </h3>

                        <p class="text-muted mb-0">
                            Silakan login untuk melanjutkan
                        </p>

                    </div>


                    <!-- Error -->

                    <?php if ($error): ?>

                        <div
                            class="alert alert-danger"
                            role="alert">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <!-- Form -->

                    <form method="POST">

                        <!-- Username -->

                        <div class="mb-3">

                            <label
                                for="username"
                                class="form-label">

                                Username

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                placeholder="Masukkan username"
                                required>

                        </div>


                        <!-- Password -->

                        <div class="mb-4">

                            <label
                                for="password"
                                class="form-label">

                                Password

                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                required>

                        </div>


                        <!-- Tombol -->

                        <button
                            type="submit"
                            class="btn btn-success w-100 btn-login">

                            Login

                        </button>

                    </form>

                </div>

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