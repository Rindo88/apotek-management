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

// Mengambil data transaksi
$query_transaksi = "SELECT * FROM transaksi WHERE id = '$id_transaksi'";
$result_transaksi = pg_query($conn, $query_transaksi);
$transaksi = pg_fetch_assoc($result_transaksi);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $metode_pembayaran = pg_escape_string($conn, $_POST['metode_pembayaran']);
    $jumlah_bayar = pg_escape_string($conn, $_POST['jumlah_bayar']);
    $id_pembayaran = pg_escape_string($conn, $_POST['id_pembayaran']);

    if ($metode_pembayaran == 'tunai' && $jumlah_bayar < $transaksi['total']) {
        echo "<script>alert('Uang kurang!');</script>";
    } else {
        try {
            $query = "INSERT INTO pembayaran (id_transaksi, metode_pembayaran, jumlah_bayar, id_pembayaran) VALUES ('$id_transaksi', '$metode_pembayaran', '$jumlah_bayar', '$id_pembayaran')";
            pg_query($conn, $query);

            // Mengurangi stok obat
            $query_detail = "SELECT * FROM detail_transaksi WHERE id_transaksi = '$id_transaksi'";
            $result_detail = pg_query($conn, $query_detail);
            while ($detail = pg_fetch_assoc($result_detail)) {
                $id_obat = $detail['id_obat'];
                $jumlah = $detail['jumlah'];
                $query_obat = "UPDATE obat SET stok = stok - $jumlah WHERE id = '$id_obat'";
                pg_query($conn, $query_obat);
            }

            // Redirect ke halaman transaksi selesai
            header("Location: transaksi_selesai.php?id_transaksi=$id_transaksi&jumlah_bayar=$jumlah_bayar");
            exit();
        } catch (Throwable $error) {
            echo "Error: " . $error->getMessage();
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
</head>
<body>
<div class="container">
    <h2>Pembayaran</h2>
    <p>ID Transaksi: <?=$transaksi['id']?></p>
    <p>Total: <?=number_format($transaksi['total'], 0, ',', '.')?></p>

    <form action="" method="POST">
        <div class="form-group">
            <label for="metode_pembayaran">Metode Pembayaran</label>
            <select class="form-control" name="metode_pembayaran" id="metode_pembayaran" required>
                <option value="tunai">Tunai</option>
                <option value="non_tunai">Non Tunai</option>
            </select>
        </div>
        <div class="form-group" id="jumlah_bayar_group">
            <label for="jumlah_bayar">Jumlah Bayar</label>
            <input type="number" class="form-control" name="jumlah_bayar" id="jumlah_bayar" required>
        </div>
        <div class="form-group" id="id_pembayaran_group" style="display: none;">
            <label for="id_pembayaran">ID Pembayaran</label>
            <input type="text" class="form-control" name="id_pembayaran" id="id_pembayaran">
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Bayar</button>
    </form>
</div>

<script>
document.getElementById('metode_pembayaran').addEventListener('change', function() {
    var metode = this.value;
    if (metode === 'tunai') {
        document.getElementById('jumlah_bayar_group').style.display = 'block';
        document.getElementById('id_pembayaran_group').style.display = 'none';
    } else {
        document.getElementById('jumlah_bayar_group').style.display = 'none';
        document.getElementById('id_pembayaran_group').style.display = 'block';
    }
});
</script>
</body>
</html>
