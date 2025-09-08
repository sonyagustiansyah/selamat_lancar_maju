<?php
include 'config.php';
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_toko      = strtoupper(trim($_POST['nama_toko']));
    $nama_pic       = strtoupper(trim($_POST['nama_pic']));
    $alamat         = strtoupper(trim($_POST['alamat']));
    $no_telp        = trim($_POST['no_telp']);
    $region         = strtoupper(trim($_POST['region']));
    $area           = strtoupper(trim($_POST['area']));
    $kota_kabupaten = strtoupper(trim($_POST['kota_kabupaten']));
    $class          = $_POST['class'];

    $sql = "INSERT INTO customers 
            (nama_toko, nama_pic, alamat, no_telp, region, area, kota_kabupaten, class) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", 
        $nama_toko, $nama_pic, $alamat, $no_telp, 
        $region, $area, $kota_kabupaten, $class
    );

    if ($stmt->execute()) {
        header("Location: customers.php");
        exit();
    } else {
        $message = "Gagal menambahkan data: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>TAMBAH CUSTOMER</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="customers.php">PT. SLM</a>
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
            <li class="nav-item">
                <a class="nav-link text-white btn btn-sm btn-danger px-3" href="logout.php">LOGOUT</a>
            </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <h3>TAMBAH CUSTOMER</h3>
    <?php if ($message): ?>
      <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">NAMA TOKO</label>
        <input type="text" name="nama_toko" class="form-control" style="text-transform: uppercase;" required>
      </div>
      <div class="mb-3">
        <label class="form-label">NAMA PIC</label>
        <input type="text" name="nama_pic" class="form-control" style="text-transform: uppercase;">
      </div>
      <div class="mb-3">
        <label class="form-label">ALAMAT</label>
        <textarea name="alamat" class="form-control" rows="3" style="text-transform: uppercase;" required></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">NO. TELP</label>
        <input type="text" name="no_telp" class="form-control" style="text-transform: uppercase;">
      </div>
      <div class="mb-3">
        <label class="form-label">REGION</label>
        <input type="text" name="region" class="form-control" style="text-transform: uppercase;" required>
      </div>
      <div class="mb-3">
        <label class="form-label">AREA</label>
        <input type="text" name="area" class="form-control" style="text-transform: uppercase;" required>
      </div>
      <div class="mb-3">
        <label class="form-label">KOTA/KABUPATEN</label>
        <input type="text" name="kota_kabupaten" class="form-control" style="text-transform: uppercase;" required>
      </div>
      <div class="mb-3">
        <label class="form-label">CLASS</label>
        <select name="class" class="form-select" required>
          <option value="">-- PILIH CLASS --</option>
          <option value="1">CLASS 1</option>
          <option value="2">CLASS 2</option>
          <option value="3">CLASS 3</option>
          <option value="4">CLASS 4</option>
          <option value="5">CLASS 5</option>
          <option value="6">CLASS 6</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">SIMPAN</button>
      <a href="customers.php" class="btn btn-danger">BATAL</a>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>