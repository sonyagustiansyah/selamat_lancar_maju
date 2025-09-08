<?php
include 'config.php';
if(isset($_POST['query'])){
    $query = $conn->real_escape_string($_POST['query']);
    $sql = "SELECT * FROM customers 
            WHERE nama_toko LIKE '%$query%' 
            ORDER BY nama_toko ASC LIMIT 10";
    $result = $conn->query($sql);
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            echo "<a href='#' class='list-group-item list-group-item-action toko-item'
                    data-nama='".htmlspecialchars($row['nama_toko'], ENT_QUOTES)."'
                    data-pic='".htmlspecialchars($row['nama_pic'], ENT_QUOTES)."'
                    data-alamat='".htmlspecialchars($row['alamat'], ENT_QUOTES)."'
                    data-area='".htmlspecialchars($row['area'], ENT_QUOTES)."'>
                    ".htmlspecialchars($row['nama_toko'])."
                </a>";
        }
    } else {
        echo "<span class='list-group-item'>Tidak ditemukan</span>";
    }
}
?>