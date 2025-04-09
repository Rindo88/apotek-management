<?php
require('../../config/config.php'); 
require('../../config/roles.php');
require('../../middleware/auth.php');

authorize([ROLE_ADMIN, ROLE_KASIR]); // Only admin and kasir can access

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    if (!isset($_SESSION['user_id'])) {
        $error_message = "Sesi kasir tidak ditemukan. Silakan login ulang.";
    } else {
        $pembeli_id = (int)$_POST['pembeli_id'];
        $kasir_id = (int)$_SESSION['user_id']; 

        try {
            $query = "INSERT INTO transaksi (pembeli_id, kasir_id, total) VALUES ($pembeli_id, $kasir_id, 0)";
            $result = pg_query($conn, $query);

            if (!$result) {
                $error_message = "Error in inserting data: " . pg_last_error($conn);
            } else {
                $id_transaksi = pg_fetch_result(pg_query($conn, "SELECT LASTVAL()"), 0, 0);
                // Redirect ke halaman detail transaksi
                header("Location: detail_transaksi.php?id_transaksi=$id_transaksi");
                exit();
            }
        } catch (Throwable $error) {
            $error_message = "Error: " . $error->getMessage();
        }
    }
}

// Mengambil data pembeli
$query_pembeli = "SELECT * FROM pembeli";
if (isset($_GET['search'])) {
    $search = pg_escape_string($conn, $_GET['search']);
    $query_pembeli .= " WHERE nama ILIKE '%$search%'";
}
$result_pembeli = pg_query($conn, $query_pembeli);
$pembeli = pg_fetch_all($result_pembeli);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Transaksi</title>
</head>
<body>
<div class="container">
    <h2>Transaksi</h2>
    <?php if ($error_message) { ?>
        <div class="alert alert-danger" role="alert">
            <?= $error_message ?>
        </div>
    <?php } ?>
    <form action="" method="GET">
        <div class="form-group">
            <label for="search">Cari Pembeli</label>
            <div class="input-group"> <!-- Menggunakan input-group untuk menempelkan input dan tombol -->
                <input type="text" class="form-control" id="search" name="search" placeholder="Masukkan nama pembeli">
                <div class="input-group-append"> <!-- Menambahkan input-group-append untuk tombol -->
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </div>
        </div>
    </form>
    <form action="" method="POST">
        <div class="form-group">
            <label for="pembeli_id">Pembeli</label>
            <select class="form-control" name="pembeli_id" id="pembeli_id" required>
                <?php foreach ($pembeli as $p) { ?>
                    <option value="<?=$p['id']?>"><?=$p['nama']?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Buat Transaksi</button>
    </form>
</div>
</body>
</html>
