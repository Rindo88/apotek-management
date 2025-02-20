<?php
require('../config/config.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

if (!isset($_GET['id_transaksi'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id_transaksi = pg_escape_string($conn, $_GET['id_transaksi']);
$jumlah_bayar = isset($_GET['jumlah_bayar']) ? pg_escape_string($conn, $_GET['jumlah_bayar']) : 0;

// Mengambil data transaksi
$query_transaksi = "SELECT * FROM transaksi WHERE id = '$id_transaksi'";
$result_transaksi = pg_query($conn, $query_transaksi);
$transaksi = pg_fetch_assoc($result_transaksi);

// Mengambil data detail transaksi
$query_detail = "SELECT dt.*, o.nama FROM detail_transaksi dt JOIN obat o ON dt.id_obat = o.id WHERE dt.id_transaksi = '$id_transaksi'";
$result_detail = pg_query($conn, $query_detail);
$detail_transaksi = pg_fetch_all($result_detail);

$kembalian = $jumlah_bayar - $transaksi['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Transaksi Selesai</title>
</head>
<body>
<div class="container">
    <h2>Transaksi Selesai</h2>
    <p>ID Transaksi: <?=$transaksi['id']?></p>
    <p>Tanggal: <?=$transaksi['tanggal']?></p>
    <p>Total: <?=number_format($transaksi['total'], 0, ',', '.')?></p>
    <p>Jumlah Bayar: <?=number_format($jumlah_bayar, 0, ',', '.')?></p>
    <p>Kembalian: <?=number_format($kembalian, 0, ',', '.')?></p>

    <h3 class="mt-4">Detail Transaksi</h3>
    <table class="table table-bordered mt-3">
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
                    <td><?=number_format($dt['subtotal'], 0, ',', '.')?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="index.php" class="btn btn-primary mt-3">Kembali ke Home</a>
</div>
</body>
</html>
