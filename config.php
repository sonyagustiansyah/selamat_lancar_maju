<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "selamat_lancar_maju";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
session_start();
?>