<?php
require('../config/config.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $id_pelanggan = $_POST['id_pelanggan'];

    try {
        $query = "INSERT INTO transaksi (id_pelanggan, total) VALUES ('$id_pelanggan', 0)";
        $result = pg_query($conn, $query);

        if (!$result) {
            echo "Error in inserting data: " . pg_last_error($conn);
        } else {
            $id_transaksi = pg_fetch_result(pg_query($conn, "SELECT LASTVAL()"), 0, 0);
            // Redirect ke halaman detail transaksi
            header("Location: detail_transaksi.php?id_transaksi=$id_transaksi");
            exit();
        }
    } catch (Throwable $error) {
        echo "Error: " . $error->getMessage();
    }
}

// Mengambil data pelanggan
$query_pelanggan = "SELECT * FROM pelanggan";
$result_pelanggan = pg_query($conn, $query_pelanggan);
$pelanggan = pg_fetch_all($result_pelanggan);
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
    <form action="" method="POST">
        <div class="form-group">
            <label for="id_pelanggan">Pelanggan</label>
            <select class="form-control" name="id_pelanggan" id="id_pelanggan" required>
                <?php foreach ($pelanggan as $p) { ?>
                    <option value="<?=$p['id']?>"><?=$p['nama']?></option>
                <?php } ?>
            </select>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Buat Transaksi</button>
    </form>
</div>
</body>
</html>
