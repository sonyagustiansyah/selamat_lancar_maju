<?php
include 'config.php';
if ($_SESSION['role'] != 'ADMIN') {
    die("Akses ditolak!");
}
echo "<h2>Admin Panel</h2><a href='login.php'>Kembali</a>";