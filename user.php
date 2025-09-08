<?php
include 'config.php';
if ($_SESSION['role'] != 'USER') {
    die("Akses ditolak!");
}
echo "<h2>User Panel</h2><a href='login.php'>Kembali</a>";