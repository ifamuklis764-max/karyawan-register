<?php
session_start();

if (!isset($_SESSION['karyawan_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$nama = htmlspecialchars(
    $_SESSION['karyawan_nama'] ?? 'Karyawan',
    ENT_QUOTES,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Absensi Karyawan</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff1f5;
            color: #333;
            font-family: Arial, sans-serif;
        }
        main {
            width: 100%;
            max-width: 520px;
            padding: 30px;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 25px #00000012;
            text-align: center;
        }
        h1 { color: #c04476; }
        p { color: #666; line-height: 1.6; }
        a { color: #c04476; }
    </style>
</head>
<body>
<main>
    <h1>Selamat datang, <?= $nama ?>!</h1>
    <p>Login berhasil. Kamu sudah masuk ke halaman karyawan.</p>
    <p>Fitur absensi belum tersedia.</p>
    <a href="../auth/logout.php">Keluar</a>
</main>
</body>
</html>
