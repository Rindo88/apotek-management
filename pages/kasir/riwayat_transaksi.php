<?php
require('../../config/config.php');
require('../../config/roles.php');
require('../../middleware/auth.php');

authorize([ROLE_ADMIN, ROLE_KASIR]);

// Mengambil data transaksi
$query = "SELECT 
    t.id, 
    t.created_at, 
    t.total,
    p.nama as pembeli_nama,
    u.nama as kasir_nama,
    COUNT(td.id) as jumlah_item,
    pm.metode_pembayaran,
    pm.status
FROM transaksi t
LEFT JOIN pembeli p ON t.pembeli_id = p.id
LEFT JOIN users u ON t.kasir_id = u.id
LEFT JOIN transaksi_detail td ON t.id = td.transaksi_id
LEFT JOIN pembayaran pm ON t.id = pm.transaksi_id
GROUP BY t.id, t.created_at, t.total, p.nama, u.nama, pm.metode_pembayaran, pm.status
ORDER BY t.created_at DESC";

$result = pg_query($conn, $query);
$transaksis = pg_fetch_all($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Riwayat Transaksi</title>
</head>
<body>
<?php include('../../utils/navbar.php'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Riwayat Transaksi</h2>
        <a href="../index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Pembeli</th>
                    <th>Kasir</th>
                    <th>Jumlah Item</th>
                    <th>Total</th>
                    <th>Metode Pembayaran</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                foreach ($transaksis as $t): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                    <td><?= $t['pembeli_nama'] ?></td>
                    <td><?= $t['kasir_nama'] ?></td>
                    <td><?= $t['jumlah_item'] ?></td>
                    <td>Rp <?= number_format($t['total'], 0, ',', '.') ?></td>
                    <td><?= $t['metode_pembayaran'] ?? 'Belum dibayar' ?></td>
                    <td>
                        <span class="badge badge-<?= $t['status'] == 'success' ? 'success' : 'warning' ?>">
                            <?= $t['status'] ?? 'Pending' ?>
                        </span>
                    </td>
                    <td>
                        <a href="detail_riwayat.php?id=<?= $t['id'] ?>" class="btn btn-info btn-sm">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
