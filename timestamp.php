<?php
include 'config.php';

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role     = $_SESSION['role'];
$success = $error = "";

// --- Simpan data timestamp ---
if (isset($_POST['submit'])) {
    $tanggal    = $_POST['tanggal'];
    $nama_toko  = strtoupper($_POST['nama_toko']);
    $nama_pic   = strtoupper($_POST['nama_pic']);
    $alamat     = strtoupper($_POST['alamat']);
    $area       = strtoupper($_POST['area']);
    $kode       = strtoupper($_POST['kode']);
    $tujuan     = strtoupper($_POST['tujuan']);
    $result     = strtoupper($_POST['result']);
    $brand      = strtoupper($_POST['brand']);
    $qty        = (int) $_POST['qty'];
    $keterangan = strtoupper($_POST['keterangan']);
    $inputer    = $username;

    // --- Upload Foto ---
    $foto = "";
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if(in_array($ext, $allowed)){
            $foto = "uploads/".time()."_".basename($_FILES['foto']['name']);
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        } else {
            $error = "HANYA BOLEH UPLOAD FILE GAMBAR (JPG, JPEG, PNG, GIF, WEBP).";
        }
    }

    if(empty($error) && $tanggal && $nama_toko){
      $stmt = $conn->prepare("INSERT INTO timestamp 
          (tanggal, nama_toko, nama_pic, alamat, area, kode, tujuan, result, brand, qty, keterangan, foto, inputer)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt->bind_param("sssssssssssss", 
          $tanggal, $nama_toko, $nama_pic, $alamat, $area, $kode, $tujuan, $result, $brand, $qty, $keterangan, $foto, $inputer
      );
      if($stmt->execute()){
          $success = "DATA BERHASIL DISIMPAN.";
      } else {
          $error = "GAGAL SIMPAN DATA: ".$stmt->error;
      }
      $stmt->close();
  } elseif(empty($error)) {
      $error = "TANGGAL DAN NAMA TOKO WAJIB DIISI.";
  }
}

// --- Pagination & Pencarian ---
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where  = $search ? "WHERE tanggal LIKE '%$search%' OR nama_toko LIKE '%$search%' OR nama_pic LIKE '%$search%' OR alamat LIKE '%$search%' OR area LIKE '%$search%' OR kode LIKE '%$search%' OR tujuan LIKE '%$search%' OR result LIKE '%$search%' OR brand LIKE '%$search%' OR inputer LIKE '%$search%'" : '';

$sql = "SELECT * FROM timestamp $where ORDER BY tanggal DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$total_sql = "SELECT COUNT(*) as total FROM timestamp $where";
$total_result = $conn->query($total_sql);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// --- Export Excel Timestamp
if(isset($_GET['export']) && $_GET['export'] == 'excel'){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=daftar_timestamp.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";
    echo "<tr>
            <th>NO</th>
            <th>TANGGAL</th>
            <th>NAMA TOKO</th>
            <th>NAMA PIC</th>
            <th>ALAMAT</th>
            <th>AREA</th>
            <th>KODE</th>
            <th>TUJUAN</th>
            <th>RESULT</th>
            <th>PRODUK</th>
            <th>QTY</th>
            <th>KETERANGAN</th>
            <th>FOTO</th>
            <th>INPUTER</th>
          </tr>";

    $export_sql = "SELECT * FROM timestamp $where ORDER BY id DESC";
    $export_result = $conn->query($export_sql);
    $no = 1;
    while($row = $export_result->fetch_assoc()){
        echo "<tr>
                <td>".$no++."</td>
                <td>".$row['tanggal']."</td>
                <td>".$row['nama_toko']."</td>
                <td>".$row['nama_pic']."</td>
                <td>".$row['alamat']."</td>
                <td>".$row['area']."</td>
                <td>".$row['kode']."</td>
                <td>".$row['tujuan']."</td>
                <td>".$row['result']."</td>
                <td>".$row['brand']."</td>
                <td>".$row['qty']."</td>
                <td>".$row['keterangan']."</td>
                <td>".$row['foto']."</td>
                <td>".$row['inputer']."</td>
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
<title>TIMESTAMP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
#tokoList { max-height:200px; overflow-y:auto; position:absolute; z-index:1000; width:100%; }
</style>
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
<li class="nav-item"><a class="nav-link active" href="timestamp.php">TIMESTAMP</a></li>
<li class="nav-item"><a class="nav-link" href="purchase_order.php">PURCHASE ORDER</a></li>
<li class="nav-item"><a class="nav-link" href="stock_card.php">DAFTAR STOCK CARD</a></li>
<li class="nav-item"><a class="nav-link text-white btn btn-sm btn-danger px-3" href="logout.php">LOGOUT</a></li>
</ul>
</div>
</div>
</nav>

<div class="container-fluid mt-4">
<h4>TIMESTAMP</h4>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<!-- Form Input -->
<form method="POST" enctype="multipart/form-data">
<div class="row mb-3">
  <div class="col-md-2">
    <label class="form-label">TANGGAL</label>
    <input type="date" name="tanggal" class="form-control" required>
  </div>
  <div class="col-md-4 position-relative">
    <label class="form-label">NAMA TOKO</label>
    <input type="text" id="nama_toko" name="nama_toko" class="form-control" autocomplete="off" style="text-transform: uppercase;" required>
    <div id="tokoList" class="list-group"></div>
  </div>
  <div class="col-md-2">
    <label class="form-label">NAMA PIC</label>
    <input type="text" id="nama_pic" name="nama_pic" class="form-control" style="text-transform: uppercase;" readonly>
  </div>
  <div class="col-md-4">
    <label class="form-label">ALAMAT</label>
    <input type="text" id="alamat" name="alamat" class="form-control" style="text-transform: uppercase;" readonly>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-2">
    <label class="form-label">AREA</label>
    <input type="text" id="area" name="area" class="form-control" style="text-transform: uppercase;" readonly>
  </div>
  <div class="col-md-2">
    <label class="form-label">KODE</label>
    <select name="kode" class="form-select" required>
      <option value="">PILIH</option>
      <option value="DK1">DK1</option>
      <option value="DK2">DK2</option>
      <option value="LK1">LK1</option>
      <option value="LK2">LK2</option>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">TUJUAN</label>
    <select name="tujuan" class="form-select" required>
      <option value="">PILIH</option>
      <option value="Visit Wajib">VISIT WAJIB</option>
      <option value="Revisit">REVISIT</option>
      <option value="Reschedule">RESCHEDULE</option>
      <option value="NOV">NOV</option>
      <option value="Tagihan">TAGIHAN</option>
      <option value="NOO">NOO</option>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">RESULT</label>
    <select name="result" class="form-select" required>
      <option value="">PILIH</option>
      <option value="Tervisit">TERVISIT</option>
      <option value="Tidak Tervisit">TIDAK TERVISIT</option>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">PRODUK</label>
    <input type="text" name="brand" class="form-control" style="text-transform: uppercase;" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">QTY</label>
    <input type="number" name="qty" class="form-control" min="0" required>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8">
    <label class="form-label">KETERANGAN</label>
    <input type="text" name="keterangan" class="form-control" style="text-transform: uppercase;">
  </div>
  <div class="col-md-4">
    <label class="form-label">UPLOAD FOTO</label>
    <input type="file" name="foto" class="form-control" required>
  </div>
</div>

<button type="submit" name="submit" class="btn btn-primary">SIMPAN</button>
</form>

<!-- Tabel Daftar Timestamp -->
<h4 class="mt-4">DAFTAR TIMESTAMP</h4>
<form method="GET" class="d-flex mb-3">
<input type="text" name="search" class="form-control me-2" style="text-transform: uppercase;" value="<?= htmlspecialchars($search) ?>">
<button type="submit" class="btn btn-primary">CARI</button>
<a href="timestamp.php" class="btn btn-warning ms-2">RESET</a>
</form>

<a href="timestamp.php?export=excel<?= ($search ? "&search=" . urlencode($search) : "") ?>" class="btn btn-success mb-3">EXPORT EXCEL</a>

<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
  <th>NO</th>
  <th>TANGGAL</th>
  <th>NAMA TOKO</th>
  <th>NAMA PIC</th>
  <th>ALAMAT</th>
  <th>AREA</th>
  <th>KODE</th>
  <th>TUJUAN</th>
  <th>RESULT</th>
  <th>PRODUK</th>
  <th>QTY</th>
  <th>KETERANGAN</th>
  <th>FOTO</th>
  <th>INPUTER</th>
</tr>
</thead>
<tbody>
<?php
if ($result->num_rows > 0) {
    $no = $offset + 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".htmlspecialchars($no)."</td>
                <td>".htmlspecialchars($row['tanggal'])."</td>
                <td>".htmlspecialchars($row['nama_toko'])."</td>
                <td>".htmlspecialchars($row['nama_pic'])."</td>
                <td>".htmlspecialchars($row['alamat'])."</td>
                <td>".htmlspecialchars($row['area'])."</td>
                <td>".htmlspecialchars($row['kode'])."</td>
                <td>".htmlspecialchars($row['tujuan'])."</td>
                <td>".htmlspecialchars($row['result'])."</td>
                <td>".htmlspecialchars($row['brand'])."</td>
                <td>".htmlspecialchars($row['qty'])."</td>
                <td>".htmlspecialchars($row['keterangan'])."</td>
                <td>".($row['foto'] ? "<a href='".htmlspecialchars($row['foto'], ENT_QUOTES)."' target='_blank'>LIHAT</a>" : "-")."</td>
                <td>".htmlspecialchars(strtoupper($row['inputer']))."</td>
              </tr>";
        $no++;
    }
} else {
    echo "<tr><td colspan='14' class='text-center'>TIDAK ADA DATA.</td></tr>";
}
?>
</tbody>
</table>
</div>

<!-- Pagination -->
<nav aria-label="Page navigation">
<ul class="pagination pagination-sm justify-content-center">
<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
<a class="page-link" href="?page=1<?= ($search ? "&search=".urlencode($search) : "") ?>">AWAL</a>
</li>
<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
<a class="page-link" href="?page=<?= $page-1 ?><?= ($search ? "&search=".urlencode($search) : "") ?>">SEBELUMNYA</a>
</li>

<?php
$range = 2;
$start = max(1, $page - $range);
$end   = min($total_pages, $page + $range);

if($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

for($i=$start; $i<=$end; $i++): ?>
<li class="page-item <?= ($i==$page) ? 'active' : '' ?>">
<a class="page-link" href="?page=<?= $i ?><?= ($search ? "&search=".urlencode($search) : "") ?>"><?= $i ?></a>
</li>
<?php endfor;

if($end < $total_pages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
?>

<li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
<a class="page-link" href="?page=<?= $page+1 ?><?= ($search ? "&search=".urlencode($search) : "") ?>">SELANJUTNYA</a>
</li>
<li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
<a class="page-link" href="?page=<?= $total_pages ?><?= ($search ? "&search=".urlencode($search) : "") ?>">AKHIR</a>
</li>
</ul>
</nav>
</div>

<script>
$(document).ready(function(){
    // Live search nama toko
    $("#nama_toko").keyup(function(){
        var query = $(this).val();
        if(query != ""){
            $.ajax({
                url: "search_customer.php",
                method: "POST",
                data: {query:query},
                success: function(data){
                    $("#tokoList").fadeIn().html(data);
                }
            });
        } else {
            $("#tokoList").fadeOut();
        }
    });

    $(document).on("click", ".toko-item", function(){
        $("#nama_toko").val($(this).data("nama"));
        $("#nama_pic").val($(this).data("pic"));
        $("#alamat").val($(this).data("alamat"));
        $("#area").val($(this).data("area"));
        $("#tokoList").fadeOut();
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>