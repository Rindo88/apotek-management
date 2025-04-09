<?php
require('../../config/config.php');
require('../../config/roles.php');
require('../../middleware/auth.php');

authorize([ROLE_ADMIN, ROLE_KASIR]);

if (!isset($_GET['id'])) {
    header('Location: riwayat_transaksi.php');
    exit();
}

$id_transaksi = pg_escape_string($conn, $_GET['id']);

// Mengambil detail transaksi
$query = "SELECT 
    t.*,
    p.nama as pembeli_nama,
    p.no_hp as pembeli_hp,
    p.alamat as pembeli_alamat,
    u.nama as kasir_nama,
    pm.metode_pembayaran,
    pm.status,
    pm.total_pembayaran
FROM transaksi t
LEFT JOIN pembeli p ON t.pembeli_id = p.id
LEFT JOIN users u ON t.kasir_id = u.id
LEFT JOIN pembayaran pm ON t.id = pm.transaksi_id
WHERE t.id = $id_transaksi";

$result = pg_query($conn, $query);
$transaksi = pg_fetch_assoc($result);

// Mengambil detail obat yang dibeli
$query_detail = "SELECT 
    td.*,
    o.nama as obat_nama,
    o.satuan
FROM transaksi_detail td
JOIN obat o ON td.obat_id = o.id
WHERE td.transaksi_id = $id_transaksi";

$result_detail = pg_query($conn, $query_detail);
$details = pg_fetch_all($result_detail);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Detail Riwayat Transaksi</title>
</head>
<body>
<?php include('../../utils/navbar.php'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Detail Transaksi #<?= $id_transaksi ?></h2>
        <a href="riwayat_transaksi.php" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Transaksi</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($transaksi['created_at'])) ?></p>
                    <p><strong>Kasir:</strong> <?= $transaksi['kasir_nama'] ?></p>
                    <p><strong>Total:</strong> Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></p>
                    <p><strong>Metode Pembayaran:</strong> <?= $transaksi['metode_pembayaran'] ?? 'Belum dibayar' ?></p>
                    <p><strong>Status:</strong> <?= $transaksi['status'] ?? 'Pending' ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Pembeli</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nama:</strong> <?= $transaksi['pembeli_nama'] ?></p>
                    <p><strong>No HP:</strong> <?= $transaksi['pembeli_hp'] ?></p>
                    <p><strong>Alamat:</strong> <?= $transaksi['pembeli_alamat'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Detail Obat</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Obat</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        foreach ($details as $d): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $d['obat_nama'] ?></td>
                            <td><?= $d['jumlah'] ?></td>
                            <td><?= $d['satuan'] ?></td>
                            <td>Rp <?= number_format($d['harga_satuan'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
