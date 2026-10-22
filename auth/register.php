
<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../api/fonnte.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik = trim($_POST['nik'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (
        $nik === '' || $nama === '' || $jabatan === '' ||
        $no_hp === '' || $email === '' || $alamat === '' ||
        $password === '' || $konfirmasi === ''
    ) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!preg_match('/^[0-9]{5,30}$/', $nik)) {
        $error = 'NIK harus berupa 5–30 digit angka.';
    } elseif (
        !preg_match('/^\+?[0-9][0-9\s()-]{7,19}$/', $no_hp)
    ) {
        $error = 'Format nomor WhatsApp tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Password dan konfirmasi tidak sama.';
    } else {
        $cek = $pdo->prepare(
            'SELECT id FROM karyawan
             WHERE nik = ? OR email = ? LIMIT 1'
        );
        $cek->execute([$nik, $email]);

        if ($cek->fetch()) {
            $error = 'NIK atau email sudah terdaftar.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $simpan = $pdo->prepare(
                    'INSERT INTO karyawan
                    (nik, nama, jabatan, no_hp, email, password, alamat)
                    VALUES (?, ?, ?, ?, ?, ?, ?)'
                );

                $simpan->execute([
                    $nik, $nama, $jabatan,
                    $no_hp, $email, $hash, $alamat
                ]);

                $pesanKaryawan =
                    "REGISTRASI BERHASIL\n\n" .
                    "Halo {$nama},\n" .
                    "Data Anda berhasil terdaftar sebagai karyawan.\n\n" .
                    "NIK: {$nik}\n" .
                    "Jabatan: {$jabatan}\n\n" .
                    "Silakan login menggunakan akun Anda.\nTerima kasih.";

                $pesanAdmin =
                    "NOTIFIKASI KARYAWAN BARU\n\n" .
                    "Nama: {$nama}\n" .
                    "NIK: {$nik}\n" .
                    "Jabatan: {$jabatan}\n" .
                    "Nomor WhatsApp: {$no_hp}\n" .
                    "Silakan periksa sistem.";

                $waKaryawan = kirimWhatsApp($no_hp, $pesanKaryawan);
                $waAdmin = kirimWhatsApp(ADMIN_WA, $pesanAdmin);

                if (!$waKaryawan['success'] || !$waAdmin['success']) {
                    error_log(
                        'Notifikasi register: karyawan=' .
                        $waKaryawan['message'] .
                        '; admin=' . $waAdmin['message']
                    );
                }

                $_SESSION['register_info'] =
                    'Registrasi berhasil. ' .
                    'Silakan login menggunakan akun yang dibuat.';

                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Registrasi gagal. Silakan coba kembali.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Karyawan</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px 15px;
            font-family: Arial, sans-serif;
            background: #fff1f5;
            color: #333;
        }
        .container {
            max-width: 540px;
            margin: auto;
            padding: 28px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 8px 25px #00000012;
        }
        h1 { text-align: center; color: #c04476; }
        p { text-align: center; color: #777; }
        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 9px;
            font: inherit;
        }
        button {
            width: 100%;
            padding: 13px;
            margin-top: 22px;
            border: 0;
            border-radius: 9px;
            background: #d94f87;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .error {
            padding: 12px;
            background: #ffe3e3;
            color: #a30000;
            border-radius: 8px;
        }
        .link { text-align: center; margin-top: 18px; }
        a { color: #c04476; }
    </style>
</head>
<body>
<div class="container">
    <h1>Register Karyawan</h1>
    <p>Buat akun untuk mulai menggunakan sistem absensi.</p>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="nik">NIK</label>
        <input id="nik" name="nik" required maxlength="30"
            pattern="[0-9]{5,30}"
            value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>">

        <label for="nama">Nama Lengkap</label>
        <input id="nama" name="nama" required maxlength="100"
            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">

        <label for="jabatan">Jabatan</label>
        <input id="jabatan" name="jabatan" required maxlength="100"
            value="<?= htmlspecialchars($_POST['jabatan'] ?? '') ?>">

        <label for="no_hp">Nomor WhatsApp</label>
        <input id="no_hp" name="no_hp" type="tel" required
            maxlength="20"
            placeholder="Contoh: 081234567890"
            value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required
            maxlength="100"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" required
            maxlength="255"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>

        <label for="password">Password</label>
        <input id="password" name="password" type="password"
            required minlength="8" autocomplete="new-password">

        <label for="konfirmasi">Konfirmasi Password</label>
        <input id="konfirmasi" name="konfirmasi" type="password"
            required minlength="8" autocomplete="new-password">

        <button type="submit">REGISTER</button>
    </form>

    <div class="link">
        Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </div>
</div>
</body>
</html>
