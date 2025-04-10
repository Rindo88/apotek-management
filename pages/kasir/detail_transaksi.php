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

// Revisi delete item transaksi
if (isset($_POST['delete'])) {
    $id_detail = pg_escape_string($conn, $_POST['delete']);
    
    // Ambil data jumlah dan obat_id sebelum delete
    $query_get = "SELECT jumlah, obat_id FROM transaksi_detail WHERE id = '$id_detail'";
    $result_get = pg_query($conn, $query_get);
    $detail = pg_fetch_assoc($result_get);
    
    // Kembalikan stok
    $query_stok = "UPDATE obat SET stok = stok + {$detail['jumlah']} WHERE id = {$detail['obat_id']}";
    pg_query($conn, $query_stok);
    
    // Hapus detail transaksi
    $query = "DELETE FROM transaksi_detail WHERE id = '$id_detail'";
    pg_query($conn, $query);
    
    header("Location: detail_transaksi.php?id_transaksi=$id_transaksi");
    exit();
}

// Tambah item ke transaksi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $id_obat = pg_escape_string($conn, $_POST['id_obat']);
    $jumlah = pg_escape_string($conn, $_POST['jumlah']);

    // Mengambil harga dan stok obat dari database
    $query_obat = "SELECT harga, stok FROM obat WHERE id = '$id_obat'";
    $result_obat = pg_query($conn, $query_obat);
    if (!$result_obat) {
        die("Error fetching obat: " . pg_last_error($conn));
    }
    $obat = pg_fetch_assoc($result_obat);
    $harga = $obat['harga'];
    $stok = $obat['stok'];
    $subtotal = $harga * $jumlah;

    if ($stok < $jumlah) {
        $error_message = "Stok obat kurang!";
    } else {
        try {
            // Begin transaction
            pg_query($conn, "BEGIN");

            // Insert detail transaksi
            $query = "INSERT INTO transaksi_detail (transaksi_id, obat_id, jumlah, harga_satuan, subtotal) 
                     VALUES ('$id_transaksi', '$id_obat', '$jumlah', '$harga', '$subtotal')";
            $result = pg_query($conn, $query);

            if ($result) {
                // Update total transaksi
                $query = "UPDATE transaksi SET total = total + '$subtotal' WHERE id = '$id_transaksi'";
                pg_query($conn, $query);

                // Update stok obat
                $query = "UPDATE obat SET stok = stok - '$jumlah' WHERE id = '$id_obat'";
                pg_query($conn, $query);

                // Commit transaction
                pg_query($conn, "COMMIT");
                
                header("Location: detail_transaksi.php?id_transaksi=$id_transaksi");
                exit();
            } else {
                pg_query($conn, "ROLLBACK");
                $error_message = "Error inserting detail: " . pg_last_error($conn);
            }
        } catch (Throwable $error) {
            pg_query($conn, "ROLLBACK");
            $error_message = "Error: " . $error->getMessage();
        }
    }
}

if (isset($_POST['increment'])) {
    $id_detail = pg_escape_string($conn, $_POST['increment']);
    $query = "UPDATE transaksi_detail SET jumlah = jumlah + 1, subtotal = subtotal + harga_satuan WHERE id = '$id_detail'";
    pg_query($conn, $query);

    // Update total transaksi
    $query = "UPDATE transaksi SET total = total + (SELECT harga_satuan FROM transaksi_detail WHERE id = '$id_detail') WHERE id = '$id_transaksi'";
    pg_query($conn, $query);

    // Update stok obat
    $query = "UPDATE obat SET stok = stok - 1 WHERE id = (SELECT obat_id FROM transaksi_detail WHERE id = '$id_detail')";
    pg_query($conn, $query);

    header("Location: detail_transaksi.php?id_transaksi=$id_transaksi");
    exit();
}

if (isset($_POST['decrement'])) {
    $id_detail = pg_escape_string($conn, $_POST['decrement']);
    $query = "UPDATE transaksi_detail SET jumlah = jumlah - 1, subtotal = subtotal - harga_satuan WHERE id = '$id_detail' AND jumlah > 1";
    pg_query($conn, $query);

    // Update total transaksi
    $query = "UPDATE transaksi SET total = total - (SELECT harga_satuan FROM transaksi_detail WHERE id = '$id_detail') WHERE id = '$id_transaksi'";
    pg_query($conn, $query);

    // Update stok obat
    $query = "UPDATE obat SET stok = stok + 1 WHERE id = (SELECT obat_id FROM transaksi_detail WHERE id = '$id_detail')";
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
$query_detail = "SELECT td.*, o.nama FROM transaksi_detail td JOIN obat o ON td.obat_id = o.id WHERE td.transaksi_id = '$id_transaksi'";
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
    <style>
        .stock-info {
            font-weight: bold;
        }
        .stock-info.available {
            color: green;
        }
        .stock-info.limited {
            color: red;
        }
        .stock-info.unavailable {
            color: #666; /* abu-abu */
        }
        .container {
            margin-top: 20px;
        }
        .form-container, .table-container {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 form-container">
            <h2>Tambah Obat</h2>
            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error_message ?>
                </div>
            <?php } ?>
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
                    <label for="id_obat">Obat</label>
                    <select class="form-control" name="id_obat" id="id_obat" required>
                        <option value="" hidden>Pilih Obat</option>
                        <?php foreach ($obat as $o) { ?>
                            <option value="<?=$o['id']?>" data-harga="<?=$o['harga']?>" data-stok="<?=$o['stok']?>"><?=$o['nama']?></option>
                        <?php } ?>
                    </select>
                    <p id="stock-info" class="stock-info"></p>
                </div>
                <div class="form-group">
                    <label for="jumlah">Jumlah</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-outline-secondary" id="decrease-quantity">-</button>
                        </div>
                        <input type="number" class="form-control" name="jumlah" id="jumlah" value="1" min="1" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="increase-quantity">+</button>
                        </div>
                    </div>
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
        </div>
        <div class="col-md-8 table-container">
            <h2>Detail Transaksi</h2>
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
                    $total_items = 0;
                    $total_price = 0;
                    foreach ($detail_transaksi as $dt) { 
                        $total_items += $dt['jumlah'];
                        $total_price += $dt['subtotal'];
                    ?>
                        <tr>
                            <td><?=$i++?></td>
                            <td><?=$dt['nama']?></td>
                            <td>
                                <form action="" method="POST" style="display:inline;">
                                    <button type="submit" name="decrement" value="<?=$dt['id']?>" class="btn btn-outline-secondary" <?=($dt['jumlah'] <= 1) ? 'disabled' : ''?>>-</button>
                                    <?=$dt['jumlah']?>
                                    <button type="submit" name="increment" value="<?=$dt['id']?>" class="btn btn-outline-secondary">+</button>
                                </form>
                            </td>
                            <td><?=number_format($dt['subtotal'], 0, ',', '.')?></td>
                            <td>
                                <a href="../detail_obat.php?id=<?=$dt['obat_id']?>&id_transaksi=<?=$id_transaksi?>" class="btn btn-info">Detail</a>
                                <form action="" method="POST" style="display:inline;">
                                    <button type="submit" name="delete" value="<?=$dt['id']?>" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total</th>
                        <th class="text-center"><?=$total_items?></th>
                        <th colspan="2"><?=number_format($total_price, 0, ',', '.')?></th>
                    </tr>
                </tfoot>
            </table>
            <a href="pembayaran.php?id_transaksi=<?=$id_transaksi?>" class="btn btn-success">Lanjut ke Pembayaran</a>
        </div>
    </div>
</div>

<script>
document.getElementById('id_obat').addEventListener('change', function() {
    var harga = this.selectedOptions[0].getAttribute('data-harga');
    var stok = this.selectedOptions[0].getAttribute('data-stok');
    document.getElementById('harga').value = harga;
    calculateSubtotal();
    updateStockInfo(stok);
});

document.getElementById('jumlah').addEventListener('input', calculateSubtotal);

document.getElementById('decrease-quantity').addEventListener('click', function() {
    var jumlah = document.getElementById('jumlah');
    if (jumlah.value > 1) {
        jumlah.value--;
        calculateSubtotal();
    }
});

document.getElementById('increase-quantity').addEventListener('click', function() {
    var jumlah = document.getElementById('jumlah');
    jumlah.value++;
    calculateSubtotal();
});

function calculateSubtotal() {
    var harga = document.getElementById('harga').value;
    var jumlah = document.getElementById('jumlah').value;
    var subtotal = harga * jumlah;
    document.getElementById('subtotal').value = subtotal;
}

function updateStockInfo(stok) {
    var stockInfo = document.getElementById('stock-info');
    if (stok == 0) {
        stockInfo.textContent = 'Stok habis';
        stockInfo.classList.remove('available', 'limited');
        stockInfo.classList.add('unavailable');
    } else if (stok <= 5) {
        stockInfo.textContent = 'Stok terbatas: ' + stok;
        stockInfo.classList.remove('available', 'unavailable');
        stockInfo.classList.add('limited');
    } else {
        stockInfo.textContent = 'Stok tersedia: ' + stok;
        stockInfo.classList.remove('limited', 'unavailable');
        stockInfo.classList.add('available');
    }
}
</script>
</body>
</html>
