
<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$error = '';
$info = $_SESSION['register_info'] ?? '';
unset($_SESSION['register_info']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, nama, email, password
                 FROM karyawan
                 WHERE email = :email
                 LIMIT 1'
            );

            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $user &&
                isset($user['password']) &&
                password_verify($password, $user['password'])
            ) {
                session_regenerate_id(true);

                $_SESSION['karyawan_id'] = (int) $user['id'];
                $_SESSION['karyawan_nama'] = $user['nama'];

                header('Location: ../absensi/index.php');
                exit;
            }

            $error = 'Email atau password salah. Periksa kembali data login kamu.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Terjadi kesalahan saat memeriksa akun. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Karyawan</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff1f5;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 15px;
        }

        .card {
            width: 100%;
            max-width: 420px;
            padding: 30px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 25px #00000012;
        }

        h1 {
            text-align: center;
            color: #c04476;
            margin-top: 0;
        }

        .subtitle {
            text-align: center;
            color: #777;
            font-size: 14px;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            color: #444;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #f3b4ce;
            border-color: #d94f87;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #d94f87;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #bd3d72;
        }

        .error, .info {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .error {
            background: #ffe3e3;
            color: #a30000;
        }

        .info {
            background: #e5f8e8;
            color: #176b32;
        }

        .link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        a {
            color: #c04476;
        }
    </style>
</head>

<body>
<div class="card">
    <h1>Login Karyawan</h1>
    <p class="subtitle">Silakan masuk ke akun kamu</p>

    <?php if ($info): ?>
        <div class="info">
            <?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            required
            autocomplete="username"
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
        >

        <button type="submit">LOGIN</button>
    </form>

    <div class="link">
        Belum punya akun?
        <a href="register.php">Register sekarang</a>
    </div>
</div>
</body>
</html>
