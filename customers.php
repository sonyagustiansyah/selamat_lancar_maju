<?php
include 'config.php';
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// --- Pagination setup
$limit = 10; // data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = "";
$params = [];
$types  = "";

if ($search != '') {
    $where = "WHERE nama_toko LIKE ? 
              OR nama_pic LIKE ?
              OR alamat LIKE ?
              OR region LIKE ?
              OR area LIKE ?
              OR kota_kabupaten LIKE ?
              OR class LIKE ?";
    // siapkan wildcard untuk LIKE
    $searchParam = "%{$search}%";
    // isi parameter array
    for ($i = 0; $i < 7; $i++) {
        $params[] = $searchParam;
        $types   .= "s";
    }
}

// --- Hitung total data (gunakan prepared statement juga)
if ($where != "") {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM customers $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_result = $stmt->get_result();
} else {
    $total_result = $conn->query("SELECT COUNT(*) as total FROM customers");
}
$total_data  = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// --- Ambil data sesuai halaman
if ($where != "") {
    $sql  = "SELECT * FROM customers $where ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);

    // gabungkan param pencarian + limit + offset
    $params[] = $limit;
    $params[] = $offset;
    $types   .= "ii";

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM customers ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// --- Export Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data_customers.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>NO</th>
            <th>NAMA TOKO</th>
            <th>NAMA PIC</th>
            <th>ALAMAT</th>
            <th>NO TELP</th>
            <th>REGION</th>
            <th>AREA</th>
            <th>KOTA/KABUPATEN</th>
            <th>CLASS</th>
          </tr>";

    if ($where != "") {
        // bikin ulang param khusus untuk export (tanpa limit & offset)
        $export_params = array_fill(0, 7, "%{$search}%");
        $export_types  = str_repeat("s", 7);

        $export_sql = "SELECT * FROM customers $where ORDER BY id DESC";
        $stmt = $conn->prepare($export_sql);
        $stmt->bind_param($export_types, ...$export_params);
        $stmt->execute();
        $export_result = $stmt->get_result();
    } else {
        $export_result = $conn->query("SELECT * FROM customers ORDER BY id DESC");
    }

    $no = 1;
    while ($row = $export_result->fetch_assoc()) {
        echo "<tr>
                <td>".$no++."</td>
                <td>".htmlspecialchars($row['nama_toko'])."</td>
                <td>".htmlspecialchars($row['nama_pic'])."</td>
                <td>".htmlspecialchars($row['alamat'])."</td>
                <td>".htmlspecialchars($row['no_telp'])."</td>
                <td>".htmlspecialchars($row['region'])."</td>
                <td>".htmlspecialchars($row['area'])."</td>
                <td>".htmlspecialchars($row['kota_kabupaten'])."</td>
                <td>CLASS ".htmlspecialchars($row['class'])."</td>
              </tr>";
    }
    echo "</table>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>DATA CUSTOMER</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="customers.php">PT. SELAMAT LANCAR MAJU</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="customers.php">DATA CUSTOMER</a></li>
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

  <!-- Content -->
  <div class="container mt-4">
    <h4 id="customers">DATA CUSTOMERS</h4>

    <!-- Form Pencarian -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-10">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" style="text-transform: uppercase;">
        </div>
        <div class="col">
            <button type="submit" class="btn btn-primary w-100">CARI</button>
        </div>
        <div class="col">
            <a href="customers.php" class="btn btn-warning w-100">RESET</a>
        </div>
      </form>

      <div class="col">
          <a href="customers.php?export=excel&search=<?= urlencode($search) ?>" class="btn btn-success">EXPORT EXCEL</a>
      </div>

    <!-- Tabel -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
          <tr>
            <th>NO</th>
            <th>NAMA TOKO</th>
            <th>NAMA PIC</th>
            <th>ALAMAT</th>
            <th>NO. TELP</th>
            <th>REGION</th>
            <th>AREA</th>
            <th>KOTA/KABUPATEN</th>
            <th>CLASS</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if ($result->num_rows > 0): 
              $no = $offset + 1;
              while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                <td><?= htmlspecialchars($row['nama_pic']) ?></td>
                <td><?= htmlspecialchars($row['alamat']) ?></td>
                <td><?= htmlspecialchars($row['no_telp']) ?></td>
                <td><?= htmlspecialchars($row['region']) ?></td>
                <td><?= htmlspecialchars($row['area']) ?></td>
                <td><?= htmlspecialchars($row['kota_kabupaten']) ?></td>
                <td>CLASS <?= htmlspecialchars($row['class']) ?></td>
              </tr>
          <?php 
              endwhile;
          else: ?>
            <tr>
              <td colspan="9" class="text-center">TIDAK ADA DATA.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination pagination-sm justify-content-center flex-wrap">
        <!-- Tombol First -->
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=1<?= ($search ? "&search=" . urlencode($search) : "") ?>">AWAL</a>
        </li>

        <!-- Tombol Previous -->
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page - 1 ?><?= ($search ? "&search=" . urlencode($search) : "") ?>">SEBELUMNYA</a>
        </li>

        <!-- Nomor halaman dinamis -->
        <?php
        $range = 2; // tampilkan 2 nomor sebelum & sesudah halaman aktif
        $start = max(1, $page - $range);
        $end   = min($total_pages, $page + $range);

        if ($start > 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for ($i = $start; $i <= $end; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?><?= ($search ? "&search=" . urlencode($search) : "") ?>"><?= $i ?></a>
          </li>
        <?php endfor;

        if ($end < $total_pages) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        ?>

        <!-- Tombol Next -->
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page + 1 ?><?= ($search ? "&search=" . urlencode($search) : "") ?>">SELANJUTNYA</a>
        </li>

        <!-- Tombol Last -->
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $total_pages ?><?= ($search ? "&search=" . urlencode($search) : "") ?>">AKHIR</a>
        </li>
      </ul>
    </nav>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>