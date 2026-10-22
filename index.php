
<?php
session_start();

if (isset($_SESSION['karyawan_id'])) {
    header('Location: absensi/index.php');
    exit;
}

header('Location: auth/login.php');
exit;
