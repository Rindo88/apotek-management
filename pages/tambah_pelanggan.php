<?php
require('../config/config.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $data = [
        'nama' => $_POST['nama'],
        'alamat' => $_POST['alamat'],
        'telepon' => $_POST['telepon']
    ];

    // Memeriksa apakah ada data yang kosong
    if (in_array('', $data)) {
        echo 'Missing data';
        die();
    }

    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_map(function($value) use ($conn) {
        return "'" . pg_escape_string($conn, $value) . "'";
    }, array_values($data)));

    try {
        $query = "INSERT INTO pelanggan ($columns) VALUES ($values)";
        $result = pg_query($conn, $query);

        if (!$result) {
            echo "Error in inserting data: " . pg_last_error($conn);
        } else {
            // Redirect setelah data berhasil disimpan
            header('Location: transaksi.php');
            exit();
        }
    } catch (Throwable $error) {
        echo "Error: " . $error->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Tambah Pelanggan</title>
</head>
<body>
<div class="container">
    <h2>Tambah Pelanggan</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" class="form-control" name="nama" required>
        </div>
        <div class="form-group">
            <label for="alamat">Alamat</label>
            <input type="text" class="form-control" name="alamat" required>
        </div>
        <div class="form-group">
            <label for="telepon">Telepon</label>
            <input type="text" class="form-control" name="telepon" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</body>
</html>
