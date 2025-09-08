<?php
include 'config.php';
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// Export Excel
if(isset($_GET['export']) && $_GET['export']=='excel'){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=daftar_stock_card.xls");
    echo "<table border='1'>";
    echo "<tr>
            <th>NO</th><th>KODE</th><th>NOMOR OEM</th><th>BRAND</th><th>NAMA MOBIL</th>
            <th>POSISI</th><th>PRODUK</th><th>QTY</th><th>KETERANGAN</th><th>INPUTER</th>
          </tr>";

    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $where = $search ? "WHERE kode LIKE '%$search%' OR nomor_oem LIKE '%$search%' OR brand LIKE '%$search%' OR nama_mobil LIKE '%$search%' OR posisi LIKE '%$search%' OR produk LIKE '%$search%' OR keterangan LIKE '%$search%' OR inputer LIKE '%$search%'" : '';

    $result = $conn->query("SELECT * FROM stock_card $where ORDER BY id DESC");
    $no = 1;
    while($row = $result->fetch_assoc()){
        echo "<tr>
                <td>".$no++."</td>
                <td>".htmlspecialchars($row['kode'])."</td>
                <td>".htmlspecialchars($row['nomor_oem'])."</td>
                <td>".htmlspecialchars($row['brand'])."</td>
                <td>".htmlspecialchars($row['nama_mobil'])."</td>
                <td>".htmlspecialchars($row['posisi'])."</td>
                <td>".htmlspecialchars($row['produk'])."</td>
                <td>".htmlspecialchars($row['qty'])."</td>
                <td>".htmlspecialchars($row['keterangan'])."</td>
                <td>".htmlspecialchars($row['inputer'])."</td>
              </tr>";
    }
    echo "</table>";
    exit();
}

// Pagination + search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = $search ? "WHERE kode LIKE '%$search%' OR nomor_oem LIKE '%$search%' OR brand LIKE '%$search%' OR nama_mobil LIKE '%$search%' OR posisi LIKE '%$search%' OR produk LIKE '%$search%' OR keterangan LIKE '%$search%' OR inputer LIKE '%$search%'" : '';

$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page-1)*$limit;

$total_res = $conn->query("SELECT COUNT(*) as total FROM stock_card $where");
$total = $total_res->fetch_assoc()['total'];
$pages = ceil($total/$limit);

$data = $conn->query("SELECT * FROM stock_card $where ORDER BY id ASC LIMIT $start,$limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>DAFTAR STOCK CARD</title>
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
    <li class="nav-item"><a class="nav-link active" href="stock_card.php">DAFTAR STOCK CARD</a></li>
    <li class="nav-item"><a class="nav-link text-white btn btn-sm btn-danger px-3" href="logout.php">LOGOUT</a></li>
    </ul>
    </div>
    </div>
    </nav>

<div class="container mt-4">
<h4>DAFTAR STOCK CARD</h4>

<form method="GET" class="d-flex mb-2">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" style="text-transform: uppercase;">
    <button type="submit" class="btn btn-primary me-2">CARI</button>
    <a href="stock_card_list.php" class="btn btn-warning">RESET</a>
</form>

<a href="stock_card.php?export=excel<?= $search ? "&search=".urlencode($search) : '' ?>" class="btn btn-success mb-3">EXPORT EXCEL</a>

<div class="table-responsive">
<table class="table table-bordered table-striped table-sm">
<thead class="table-dark text-center">
<tr>
<th>NO</th><th>KODE</th><th>NOMOR OEM</th><th>BRAND</th><th>NAMA MOBIL</th>
<th>POSISI</th><th>PRODUK</th><th>QTY</th><th>KETERANGAN</th><th>INPUTER</th>
</tr>
</thead>
<tbody>
<?php
$no = $start+1;
if($data->num_rows>0){
    while($row=$data->fetch_assoc()){
        echo "<tr>
                <td class='text-center'>$no</td>
                <td>".htmlspecialchars($row['kode'])."</td>
                <td>".htmlspecialchars($row['nomor_oem'])."</td>
                <td>".htmlspecialchars($row['brand'])."</td>
                <td>".htmlspecialchars($row['nama_mobil'])."</td>
                <td>".htmlspecialchars($row['posisi'])."</td>
                <td>".htmlspecialchars($row['produk'])."</td>
                <td class='text-center'>".htmlspecialchars($row['qty'])."</td>
                <td>".htmlspecialchars($row['keterangan'])."</td>
                <td>".htmlspecialchars(strtoupper($row['inputer']))."</td>
              </tr>";
        $no++;
    }
}else{
    echo "<tr><td colspan='10' class='text-center'>TIDAK ADA DATA.</td></tr>";
}
?>
</tbody>
</table>
</div>

<nav>
<ul class="pagination pagination-sm justify-content-center">
<li class="page-item <?=($page<=1)?'disabled':''?>"><a class="page-link" href="?page=1&search=<?= urlencode($search) ?>">AWAL</a></li>
<li class="page-item <?=($page<=1)?'disabled':''?>"><a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">SEBELUMNYA</a></li>

<?php for($i=1;$i<=$pages;$i++): ?>
<li class="page-item <?=($page==$i)?'active':''?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
<?php endfor; ?>

<li class="page-item <?=($page>=$pages)?'disabled':''?>"><a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">SELANJUTNYA</a></li>
<li class="page-item <?=($page>=$pages)?'disabled':''?>"><a class="page-link" href="?page=<?= $pages ?>&search=<?= urlencode($search) ?>">AKHIR</a></li>
</ul>
</nav>

</div>
</body>
</html>