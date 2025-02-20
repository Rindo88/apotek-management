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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $id_obat = pg_escape_string($conn, $_POST['id_obat']);
    $jumlah = pg_escape_string($conn, $_POST['jumlah']);

    // Mengambil harga obat dari database
    $query_harga = "SELECT harga FROM obat WHERE id = '$id_obat'";
    $result_harga = pg_query($conn, $query_harga);
    if (!$result_harga) {
        die("Error fetching harga: " . pg_last_error($conn));
    }
    $harga = pg_fetch_result($result_harga, 0, 'harga');
    $subtotal = $harga * $jumlah;

    try {
        $query = "INSERT INTO detail_transaksi (id_transaksi, id_obat, jumlah, subtotal) VALUES ('$id_transaksi', '$id_obat', '$jumlah', '$subtotal')";
        pg_query($conn, $query);

        // Update total transaksi
        $query = "UPDATE transaksi SET total = total + '$subtotal' WHERE id = '$id_transaksi'";
        pg_query($conn, $query);

        // Redirect ke halaman detail transaksi
        header("Location: detail_transaksi.php?id_transaksi=$id_transaksi");
        exit();
    } catch (Throwable $error) {
        echo "Error: " . $error->getMessage();
    }
}

if (isset($_POST['delete'])) {
    $id_detail = pg_escape_string($conn, $_POST['delete']);
    $query = "DELETE FROM detail_transaksi WHERE id = '$id_detail'";
    pg_query($conn, $query);
    header("Location: detail_transaksi.php?id_transaksi=$id_transaksi");
    exit();
}

// Mengambil data obat
$query_obat = "SELECT * FROM obat";
if (isset($_GET['search'])) {
    $search = pg_escape_string($conn, $_GET['search']);
    $query_obat .= " WHERE nama ILIKE '%$search%'";
}
$result_obat = pg_query($conn, $query_obat);
if (!$result_obat) {
    die("Error fetching obat: " . pg_last_error($conn));
}
$obat = pg_fetch_all($result_obat);

// Mengambil data detail transaksi
$query_detail = "SELECT dt.*, o.nama FROM detail_transaksi dt JOIN obat o ON dt.id_obat = o.id WHERE dt.id_transaksi = '$id_transaksi'";
$result_detail = pg_query($conn, $query_detail);
if (!$result_detail) {
    die("Error fetching detail transaksi: " . pg_last_error($conn));
}
$detail_transaksi = pg_fetch_all($result_detail);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Detail Transaksi</title>
</head>
<body>
<div class="container">
    <h2>Detail Transaksi</h2>
    <label for="id_obat">Obat</label>
    <form action="" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Cari Obat" value="<?=isset($_GET['search']) ? $_GET['search'] : ''?>">
            <input type="hidden" name="id_transaksi" value="<?=$id_transaksi?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
    </form>
    <form action="" method="POST">
        <div class="form-group">
            <select class="form-control" name="id_obat" id="id_obat" required>
                <option value="" hidden>pilih obat</option>
                <?php foreach ($obat as $o) { ?>
                    <option value="<?=$o['id']?>" data-harga="<?=$o['harga']?>"><?=$o['nama']?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="jumlah">Jumlah</label>
            <input type="number" class="form-control" name="jumlah" id="jumlah" required>
        </div>
        <div class="form-group">
            <label for="harga">Harga</label>
            <input type="number" class="form-control" name="harga" id="harga" readonly>
        </div>
        <div class="form-group">
            <label for="subtotal">Subtotal</label>
            <input type="number" class="form-control" name="subtotal" id="subtotal" readonly>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Tambah Obat</button>
    </form>

    <h3 class="mt-4">Detail Transaksi</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Obat</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
                <th>Aksi</th>
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
                    <td><?=$dt['subtotal']?></td>
                    <td>
                        <form action="" method="POST" style="display:inline;">
                            <button type="submit" name="delete" value="<?=$dt['id']?>" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="transaksi_selesai.php?id_transaksi=<?=$id_transaksi?>" class="btn btn-success mt-3">Selesaikan Transaksi</a>
    <a href="pembayaran.php?id_transaksi=<?=$id_transaksi?>" class="btn btn-success mt-3">Lanjut ke Pembayaran</a>
</div>

<script>
document.getElementById('id_obat').addEventListener('change', function() {
    var harga = this.selectedOptions[0].getAttribute('data-harga');
    document.getElementById('harga').value = harga;
    calculateSubtotal();
});

document.getElementById('jumlah').addEventListener('input', calculateSubtotal);

function calculateSubtotal() {
    var harga = document.getElementById('harga').value;
    var jumlah = document.getElementById('jumlah').value;
    var subtotal = harga * jumlah;
    document.getElementById('subtotal').value = subtotal;
}
</script>
</body>
</html>
