<?php
require('../../config/config.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

if (!isset($_GET['id_transaksi'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id_transaksi = pg_escape_string($conn, $_GET['id_transaksi']);
$total_pembayaran = isset($_GET['total_pembayaran']) ? (float)pg_escape_string($conn, $_GET['total_pembayaran']) : 0.0;

// Mengambil data transaksi
$query_transaksi = "SELECT t.*, p.nama AS pembeli_nama, u.nama AS kasir_nama, pm.metode_pembayaran, pm.pembayaran_merchant 
                    FROM transaksi t 
                    JOIN pembeli p ON t.pembeli_id = p.id 
                    JOIN users u ON t.kasir_id = u.id 
                    LEFT JOIN pembayaran pm ON t.id = pm.transaksi_id 
                    WHERE t.id = '$id_transaksi'";
$result_transaksi = pg_query($conn, $query_transaksi);
$transaksi = pg_fetch_assoc($result_transaksi);

// Mengambil data detail transaksi
$query_detail = "SELECT td.*, o.nama FROM transaksi_detail td JOIN obat o ON td.obat_id = o.id WHERE td.transaksi_id = '$id_transaksi'";
$result_detail = pg_query($conn, $query_detail);
$detail_transaksi = pg_fetch_all($result_detail);

$kembalian = $total_pembayaran - (float)$transaksi['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Transaksi Selesai</title>
    <style>
        .container {
            margin-top: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">Transaksi Selesai</h2>
        </div>
        <div class="card-body">
            <p><strong>ID Transaksi:</strong> <?=$transaksi['id']?></p>
            <p><strong>Pembeli:</strong> <?=$transaksi['pembeli_nama']?></p>
            <p><strong>Kasir:</strong> <?=$transaksi['kasir_nama']?></p>
            <p><strong>Tanggal:</strong> <?=$transaksi['created_at']?></p>
            <p><strong>Total:</strong> Rp.<?=number_format($transaksi['total'], 0, ',', '.')?></p>
            <p><strong>Jumlah Bayar:</strong> Rp.<?=number_format($total_pembayaran, 0, ',', '.')?></p>
            <p><strong>Kembalian:</strong> Rp.<?=number_format($kembalian, 0, ',', '.')?></p>
            <p><strong>Metode Pembayaran:</strong> <?=$transaksi['metode_pembayaran']?></p>
            <?php if ($transaksi['metode_pembayaran'] !== 'Cash') { ?>
                <p><strong>Merchant:</strong> <?=$transaksi['pembayaran_merchant']?></p>
            <?php } ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="mb-0">Detail Transaksi</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Obat</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($detail_transaksi as $dt) { ?>
                        <tr>
                            <td><?=$i++?></td>
                            <td><?=$dt['nama']?></td>
                            <td><?=$dt['jumlah']?></td>
                            <td>Rp.<?=number_format($dt['subtotal'], 0, ',', '.')?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <a href="../index.php" class="btn btn-primary mt-3">Kembali ke Home</a>
    <a href="../../utils/generate_receipt.php?id_transaksi=<?=$id_transaksi?>" class="btn btn-success mt-3 ml-2" target="_blank">
        <i class="fas fa-print"></i> Cetak Struk
    </a>
</div>
</body>
</html>
