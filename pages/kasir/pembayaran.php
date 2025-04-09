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

// Mengambil data transaksi
$query_transaksi = "SELECT * FROM transaksi WHERE id = '$id_transaksi'";
$result_transaksi = pg_query($conn, $query_transaksi);
$transaksi = pg_fetch_assoc($result_transaksi);

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $metode_pembayaran = pg_escape_string($conn, $_POST['metode_pembayaran']);
    $total_pembayaran = pg_escape_string($conn, $_POST['total_pembayaran']);
    $pembayaran_merchant = pg_escape_string($conn, $_POST['pembayaran_merchant']);
    $pembayaran_merchant_id = pg_escape_string($conn, $_POST['pembayaran_merchant_id']);
    $status = 'Completed';

    if ($metode_pembayaran == 'Cash' && $total_pembayaran < $transaksi['total']) {
        $error_message = 'Uang kurang!';
    } else {
        // Pastikan total_pembayaran memiliki nilai yang valid
        if ($metode_pembayaran != 'Cash') {
            $total_pembayaran = $transaksi['total'];
        }

        try {
            $query = "INSERT INTO pembayaran (transaksi_id, metode_pembayaran, total_pembayaran, pembayaran_merchant, pembayaran_merchant_id, status) VALUES ('$id_transaksi', '$metode_pembayaran', '$total_pembayaran', '$pembayaran_merchant', '$pembayaran_merchant_id', '$status')";
            $result = pg_query($conn, $query);

            if (!$result) {
                throw new Exception("Error in inserting data: " . pg_last_error($conn));
            }

            // Mengurangi stok obat
            $query_detail = "SELECT * FROM transaksi_detail WHERE transaksi_id = '$id_transaksi'";
            $result_detail = pg_query($conn, $query_detail);
            while ($detail = pg_fetch_assoc($result_detail)) {
                $id_obat = $detail['obat_id'];
                $jumlah = $detail['jumlah'];
                $query_obat = "UPDATE obat SET stok = stok - $jumlah WHERE id = '$id_obat'";
                pg_query($conn, $query_obat);
            }

            // Redirect ke halaman transaksi selesai
            header("Location: transaksi_selesai.php?id_transaksi=$id_transaksi&total_pembayaran=$total_pembayaran");
            exit();
        } catch (Throwable $error) {
            $error_message = "Error: " . $error->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Pembayaran</title>
    <style>
        .container {
            margin-top: 20px;
        }
        .form-container {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group label {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2>Pembayaran</h2>
        <?php if ($error_message) { ?>
            <div class="alert alert-danger" role="alert">
                <?= $error_message ?>
            </div>
        <?php } ?>
        <p>ID Transaksi: <?=$transaksi['id']?></p>
        <p>Total: Rp.<?=number_format($transaksi['total'], 0, ',', '.')?></p>

        <form action="" method="POST">
            <div class="form-group">
                <label for="metode_pembayaran">Metode Pembayaran</label>
                <select class="form-control" name="metode_pembayaran" id="metode_pembayaran" required>
                    <option value="Cash">Cash</option>
                    <option value="Debit">Debit</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>
            </div>
            <div class="form-group" id="total_pembayaran_group">
                <label for="total_pembayaran">Jumlah Bayar</label>
                <input type="number" class="form-control" name="total_pembayaran" id="total_pembayaran" required>
            </div>
            <div class="form-group" id="pembayaran_merchant_group" style="display: none;">
                <label for="pembayaran_merchant">Nama Merchant</label>
                <input type="text" class="form-control" name="pembayaran_merchant" id="pembayaran_merchant">
            </div>
            <div class="form-group" id="pembayaran_merchant_id_group" style="display: none;">
                <label for="pembayaran_merchant_id">ID Merchant</label>
                <input type="text" class="form-control" name="pembayaran_merchant_id" id="pembayaran_merchant_id">
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Bayar</button>
        </form>
    </div>
</div>

<script>
document.getElementById('metode_pembayaran').addEventListener('change', function() {
    var metode = this.value;
    if (metode === 'Cash') {
        document.getElementById('total_pembayaran_group').style.display = 'block';
        document.getElementById('pembayaran_merchant_group').style.display = 'none';
        document.getElementById('pembayaran_merchant_id_group').style.display = 'none';
        document.getElementById('total_pembayaran').required = true;
        document.getElementById('pembayaran_merchant').required = false;
        document.getElementById('pembayaran_merchant_id').required = false;
    } else {
        document.getElementById('total_pembayaran_group').style.display = 'none';
        document.getElementById('pembayaran_merchant_group').style.display = 'block';
        document.getElementById('pembayaran_merchant_id_group').style.display = 'block';
        document.getElementById('total_pembayaran').required = false;
        document.getElementById('pembayaran_merchant').required = true;
        document.getElementById('pembayaran_merchant_id').required = true;
    }
});
</script>
</body>
</html>
