<?php
require('../config/config.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: /pages/gudang/obat.php");
    exit();
}

$id = pg_escape_string($conn, $_GET['id']);
$id_transaksi = isset($_GET['id_transaksi']) ? pg_escape_string($conn, $_GET['id_transaksi']) : null;
$query = "SELECT obat.*, supplier.nama AS supplier_nama FROM obat LEFT JOIN supplier ON obat.supplier_id = supplier.id WHERE obat.id = $id";
$result = pg_query($conn, $query);
if (!$result) {
    die("Error fetching obat: " . pg_last_error($conn));
}
$obat = pg_fetch_assoc($result);

if (!$obat) {
    header("Location: obat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Detail Obat</title>
    <style>
        .detail-container {
            display: flex;
            flex-wrap: wrap;
        }
        .detail-image {
            flex: 1;
            max-width: 50%;
            padding: 10px;
        }
        .detail-info {
            flex: 1;
            max-width: 50%;
            padding: 10px;
        }
        .detail-info h5 {
            margin-bottom: 20px;
        }
        .detail-info p {
            margin-bottom: 10px;
        }
        .detail-description {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Detail Obat</h2>
    <div class="card mb-3">
        <div class="detail-container">
            <div class="detail-image">
                <img src="<?=$obat['gambar']?>" class="card-img-top" alt="<?=$obat['nama']?>">
            </div>
            <div class="detail-info">
                <h5 class="card-title"><?=$obat['nama']?></h5>
                <p class="card-text"><strong>Barcode:</strong> <?=$obat['barcode']?></p>
                <p class="card-text"><strong>Jenis:</strong> <?=$obat['jenis']?></p>
                <p class="card-text"><strong>Kategori:</strong> <?=$obat['kategori']?></p>
                <p class="card-text"><strong>Komposisi:</strong> <?=$obat['komposisi']?></p>
                <p class="card-text"><strong>Dosis:</strong> <?=$obat['dosis']?></p>
                <p class="card-text"><strong>Satuan:</strong> <?=$obat['satuan']?></p>
                <p class="card-text"><strong>Stok:</strong> <?=$obat['stok']?></p>
                <p class="card-text"><strong>Supplier:</strong> <?=$obat['supplier_nama']?></p>
                <p class="card-text"><strong>Harga:</strong> <?=number_format($obat['harga'], 0, ',', '.')?></p>
                <p class="card-text"><strong>Deskripsi:</strong> <?=$obat['deskripsi']?></p>
                <?php if ($id_transaksi) { ?>
                    <a href="/pages/kasir/detail_transaksi.php?id_transaksi=<?=$id_transaksi?>" class="btn btn-primary">Kembali ke Transaksi</a>
                <?php } else { ?>
                    <a href="/pages/gudang/obat.php" class="btn btn-primary">Kembali</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
