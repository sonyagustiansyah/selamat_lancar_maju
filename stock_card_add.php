<?php
include 'config.php';

$success = $error = "";

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $kode       = strtoupper($_POST['kode']);
    $nomor_oem  = strtoupper($_POST['nomor_oem']);
    $brand      = strtoupper($_POST['brand']);
    $nama_mobil = strtoupper($_POST['nama_mobil']);
    $posisi     = strtoupper($_POST['posisi']);
    $produk     = strtoupper($_POST['produk']);
    $qty        = (int) $_POST['qty'];
    $keterangan = strtoupper($_POST['keterangan']);
    $inputer    = $_SESSION['username'];

    if($kode && $produk){
        $stmt = $conn->prepare("INSERT INTO stock_card (kode, nomor_oem, brand, nama_mobil, posisi, produk, qty, keterangan, inputer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiss", $kode, $nomor_oem, $brand, $nama_mobil, $posisi, $produk, $qty, $keterangan, $inputer);
        if($stmt->execute()){
            $success = "DATA BERHASIL DISIMPAN.";
        } else {
            $error = "GAGAL SIMPAN DATA: ".$stmt->error;
        }
        $stmt->close();
    } else {
        $error = "KODE DAN PRODUK WAJIB DIISI.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>TAMBAH STOCK CARD</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
    <a class="navbar-brand" href="timestamp.php">PT. SELAMAT LANCAR MAJU</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">
    <li class="nav-item"><a class="nav-link" href="customers.php">DATA CUSTOMER</a></li>
    <li class="nav-item"><a class="nav-link" href="daily_visit.php">DAILY VISIT</a></li>
    <li class="nav-item"><a class="nav-link" href="timestamp.php">TIMESTAMP</a></li>
    <li class="nav-item"><a class="nav-link" href="purchase_order.php">PURCHASE ORDER</a></li>
    <li class="nav-item"><a class="nav-link" href="stock_card.php">DAFTAR STOCK CARD</a></li>
    <li class="nav-item"><a class="nav-link text-white btn btn-sm btn-danger px-3" href="logout.php">LOGOUT</a></li>
    </ul>
    </div>
    </div>
    </nav>

<div class="container mt-4">
<h4>TAMBAH STOCK CARD</h4>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<form method="POST">
<div class="row mb-2">
    <div class="col-md-3">
        <label class="form-label">KODE</label>
        <input type="text" name="kode" class="form-control text-uppercase" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">NOMOR OEM</label>
        <input type="text" name="nomor_oem" class="form-control text-uppercase" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">BRAND MOBIL</label>
        <input type="text" name="brand" class="form-control text-uppercase" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">NAMA MOBIL/MODEL MOBIL</label>
        <input type="text" name="nama_mobil" class="form-control text-uppercase" required>
    </div>
</div>

<div class="row mb-2">
    <div class="col-md-3">
        <label class="form-label">POSISI</label>
        <input type="text" name="posisi" class="form-control text-uppercase" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">PRODUK</label>
        <input type="text" name="produk" class="form-control text-uppercase" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">QTY</label>
        <input type="number" name="qty" class="form-control" min="0" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">KETERANGAN</label>
        <input type="text" name="keterangan" class="form-control text-uppercase">
    </div>
</div>

<button type="submit" name="submit" class="btn btn-primary mt-2">SIMPAN</button>
<a href="stock_card.php" class="btn btn-danger mt-2">KEMBALI</a>
</form>
</div>
</body>
</html>