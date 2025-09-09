<?php
include 'config.php';
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// ===== Export Excel =====
if (isset($_GET['export']) && $_GET['export'] == "excel") {
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $where = "";
    if ($search) {
        $where = "WHERE DATE_FORMAT(tanggal, '%Y/%m/%d') LIKE '%$search%'
                  OR nama_sales LIKE '%$search%'
                  OR nama_toko LIKE '%$search%'
                  OR nama_pic LIKE '%$search%'
                  OR alamat LIKE '%$search%'
                  OR area LIKE '%$search%'
                  OR kode LIKE '%$search%'
                  OR DATE_FORMAT(tanggal_kirim, '%Y/%m/%d') LIKE '%$search%'
                  OR DATE_FORMAT(ar_deadline, '%Y/%m/%d') LIKE '%$search%'";
    }

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=purchase_orders.xls");

    echo "<table border='1'>
          <tr>
            <th>NO</th><th>TANGGAL</th><th>NAMA SALES</th><th>NAMA TOKO</th>
            <th>NAMA PIC</th><th>ALAMAT</th><th>AREA</th><th>KODE</th>
            <th>DISKON (%)</th><th>TOP (DAY)</th><th>QTY</th>
            <th>TANGGAL KIRIM</th><th>AR DEADLINE</th>
            <th>KETERANGAN</th><th>INPUTER</th>
          </tr>";

    $result = $conn->query("SELECT * FROM purchase_orders $where ORDER BY id ASC");
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$no++."</td>
                <td>".htmlspecialchars(date('Y/m/d', strtotime($row['tanggal'])))."</td>
                <td>".htmlspecialchars($row['nama_sales'])."</td>
                <td>".htmlspecialchars($row['nama_toko'])."</td>
                <td>".htmlspecialchars($row['nama_pic'])."</td>
                <td>".htmlspecialchars($row['alamat'])."</td>
                <td>".htmlspecialchars($row['area'])."</td>
                <td>".htmlspecialchars($row['kode'])."</td>
                <td>".htmlspecialchars($row['diskon'])."</td>
                <td>".htmlspecialchars($row['top_day'])."</td>
                <td>".htmlspecialchars($row['qty'])."</td>
                <td>".htmlspecialchars(date('Y/m/d', strtotime($row['tanggal_kirim'])))."</td>
                <td>".htmlspecialchars(date('Y/m/d', strtotime($row['ar_deadline'])))."</td>
                <td>".htmlspecialchars($row['keterangan'])."</td>
                <td>".htmlspecialchars($row['inputer'])."</td>
              </tr>";
    }
    echo "</table>";
    exit;
}

// ===== Proses simpan data =====
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    $tanggal       = date('Y-m-d', strtotime($_POST['tanggal']));
    $nama_sales    = strtoupper($_POST['nama_sales']);
    $nama_toko     = strtoupper($_POST['nama_toko']);
    $nama_pic      = $_POST['nama_pic'];
    $alamat        = $_POST['alamat'];
    $area          = $_POST['area'];
    $kode          = $_POST['kode'];
    $diskon        = $_POST['diskon'] !== '' ? (float)$_POST['diskon'] : 0;
    $top_day       = $_POST['top_day'] !== '' ? (int)$_POST['top_day'] : 0;
    $qty           = $_POST['qty'] !== '' ? (int)$_POST['qty'] : 0;

    // Tanggal Kirim wajib diisi
    if (!empty($_POST['tanggal_kirim'])) {
        $tanggal_kirim = date('Y-m-d', strtotime($_POST['tanggal_kirim']));
    } else {
        die("<script>alert('Tanggal Kirim wajib diisi!');window.history.back();</script>");
    }

    $keterangan    = strtoupper($_POST['keterangan']);
    $inputer       = $_SESSION['username'];

    // Hitung AR Deadline (server-side)
    $ar_deadline = $tanggal;
    if ($tanggal && $top_day > 0) {
        $ar_deadline = date('Y-m-d', strtotime($tanggal . " +$top_day days"));
    }

    $stmt = $conn->prepare("INSERT INTO purchase_orders 
        (tanggal, nama_sales, nama_toko, nama_pic, alamat, area, kode, diskon, top_day, qty, tanggal_kirim, ar_deadline, keterangan, inputer) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssiiissss", 
        $tanggal, $nama_sales, $nama_toko, $nama_pic, 
        $alamat, $area, $kode, $diskon, $top_day, $qty, 
        $tanggal_kirim, $ar_deadline, $keterangan, $inputer
    );
    $stmt->execute();
}

// ===== Ambil data untuk tabel + Pencarian =====
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where  = "";
if ($search) {
    $where = "WHERE DATE_FORMAT(tanggal, '%Y/%m/%d') LIKE '%$search%'
                OR nama_sales LIKE '%$search%'
                OR nama_toko LIKE '%$search%'
                OR nama_pic LIKE '%$search%'
                OR alamat LIKE '%$search%'
                OR area LIKE '%$search%'
                OR kode LIKE '%$search%'
                OR DATE_FORMAT(tanggal_kirim, '%Y/%m/%d') LIKE '%$search%'
                OR DATE_FORMAT(ar_deadline, '%Y/%m/%d') LIKE '%$search%'";
}

$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$result = $conn->query("SELECT COUNT(*) AS total FROM purchase_orders $where");
$total  = $result->fetch_assoc()['total'];
$pages  = ceil($total / $limit);

$data = $conn->query("SELECT * FROM purchase_orders $where ORDER BY id ASC LIMIT $start,$limit");
?>

<!DOCTYPE html>
<html>
<head>
    <title>PURCHASE ORDER</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <li class="nav-item"><a class="nav-link active" href="purchase_order.php">PURCHASE ORDER</a></li>
    <li class="nav-item"><a class="nav-link" href="stock_card.php">DAFTAR STOCK CARD</a></li>
    <li class="nav-item"><a class="nav-link text-white btn btn-sm btn-danger px-3" href="logout.php">LOGOUT</a></li>
    </ul>
    </div>
    </div>
    </nav>

<div class="container-fluid mt-4">
    <h4>PURCHASE ORDER</h4>
    <form method="POST">
        <div class="row mb-2">
            <div class="col-md-3">
                <label class="form-label">TANGGAL</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">NAMA SALES</label>
                <input type="text" name="nama_sales" class="form-control text-uppercase" required>
            </div>
            <div class="col-md-6 position-relative">
                <label class="form-label">NAMA TOKO</label>
                <input type="text" name="nama_toko" id="nama_toko" class="form-control text-uppercase" autocomplete="off" required>
                <div id="tokoList" class="list-group position-absolute w-100"></div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3">
                <label class="form-label">NAMA PIC</label>
                <input type="text" name="nama_pic" id="nama_pic" class="form-control" readonly>
            </div>
            <div class="col-md-5">
                <label class="form-label">ALAMAT</label>
                <input type="text" name="alamat" id="alamat" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">AREA</label>
                <input type="text" name="area" id="area" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label class="form-label">KODE</label>
                <select name="kode" class="form-select">
                    <option value="">PILIH</option>
                    <option value="DK1">DK1</option>
                    <option value="DK2">DK2</option>
                    <option value="LK1">LK1</option>
                    <option value="LK2">LK2</option>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-2">
                <label class="form-label">DISKON (%)</label>
                <input type="number" step="0.01" name="diskon" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">TOP (DAY)</label>
                <input type="number" name="top_day" id="top_day" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">QTY</label>
                <input type="number" name="qty" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">TANGGAL KIRIM</label>
                <input type="date" name="tanggal_kirim" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">AR DEADLINE</label>
                <input type="date" name="ar_deadline" id="ar_deadline" class="form-control" readonly>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label">KETERANGAN</label>
            <textarea name="keterangan" class="form-control text-uppercase"></textarea>
        </div>

        <button type="submit" name="simpan" class="btn btn-primary mt-2">SIMPAN</button>
    </form>

    <div class="mt-4 mb-3">
        <h4>DAFTAR PURCHASE ORDER</h4>
    </div>

    <form method="GET" class="row mb-3">
        <div class="d-flex">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" style="text-transform: uppercase;">
            <button type="submit" class="btn btn-primary me-2">CARI</button>
            <a href="purchase_order.php" class="btn btn-warning">RESET</a>
        </div>
    </form>
    <div class="mb-3">
        <a href="purchase_order.php?export=excel<?= ($search ? '&search='.urlencode($search) : '') ?>" class="btn btn-success">EXPORT EXCEL</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-dark text-center">
                <tr>
                    <th>NO</th>
                    <th>TANGGAL</th>
                    <th>NAMA SALES</th>
                    <th>NAMA TOKO</th>
                    <th>NAMA PIC</th>
                    <th>ALAMAT</th>
                    <th>AREA</th>
                    <th>KODE</th>
                    <th>DISKON (%)</th>
                    <th>TOP (DAY)</th>
                    <th>QTY</th>
                    <th>TANGGAL KIRIM</th>
                    <th>AR DEADLINE</th>
                    <th>KETERANGAN</th>
                    <th>INPUTER</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = $start + 1;
                if ($data->num_rows > 0):
                    while ($row = $data->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= date('Y/m/d', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_sales']) ?></td>
                            <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                            <td><?= htmlspecialchars($row['nama_pic']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= htmlspecialchars($row['area']) ?></td>
                            <td><?= htmlspecialchars($row['kode']) ?></td>
                            <td class="text-end"><?= htmlspecialchars($row['diskon']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['top_day']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($row['qty']) ?></td>
                            <td><?= date('Y/m/d', strtotime($row['tanggal_kirim'])) ?></td>
                            <td><?= date('Y/m/d', strtotime($row['ar_deadline'])) ?></td>
                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td><?= htmlspecialchars(strtoupper($row['inputer'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="15" class="text-center">TIDAK ADA DATA.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <nav>
        <ul class="pagination pagination-sm justify-content-center">
            <li class="page-item <?= ($page <= 1)?'disabled':'' ?>">
                <a class="page-link" href="?page=1&search=<?= $search ?>">AWAL</a>
            </li>
            <li class="page-item <?= ($page <= 1)?'disabled':'' ?>">
                <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= $search ?>">SEBELUMNYA</a>
            </li>

            <?php for($i=1; $i<=$pages; $i++): ?>
                <li class="page-item <?= ($page==$i)?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= ($page >= $pages)?'disabled':'' ?>">
                <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= $search ?>">SELANJUTNYA</a>
            </li>
            <li class="page-item <?= ($page >= $pages)?'disabled':'' ?>">
                <a class="page-link" href="?page=<?= $pages ?>&search=<?= $search ?>">AKHIR</a>
            </li>
        </ul>
    </nav>
</div>

<script>
$(document).ready(function(){
    // Live search toko
    $("#nama_toko").keyup(function(){
        let query = $(this).val();
        if (query.length > 1){
            $.post("search_customer.php", {query:query}, function(data){
                $("#tokoList").html(data).fadeIn();
            });
        } else {
            $("#tokoList").fadeOut();
        }
    });

    $(document).on('click', '.toko-item', function(e){
        e.preventDefault();
        $("#nama_toko").val($(this).data('nama'));
        $("#nama_pic").val($(this).data('pic'));
        $("#alamat").val($(this).data('alamat'));
        $("#area").val($(this).data('area'));
        $("#tokoList").fadeOut();
    });

    // Hitung AR Deadline otomatis
    function hitungAR() {
        let tgl = $("#tanggal").val();
        let top = parseInt($("#top_day").val());
        if(tgl && !isNaN(top)){
            let parts = tgl.split("-");
            let base = new Date(parts[0], parts[1]-1, parts[2]);
            base.setDate(base.getDate() + top);
            let dd = String(base.getDate()).padStart(2,'0');
            let mm = String(base.getMonth()+1).padStart(2,'0');
            let yyyy = base.getFullYear();
            $("#ar_deadline").val(yyyy+"-"+mm+"-"+dd);
        }
    }
    $("#tanggal, #top_day").on("change keyup", hitungAR);
});
</script>
</body>
</html>