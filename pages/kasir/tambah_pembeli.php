<?php
require('../../config/config.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $data = [
        'nama' => $_POST['nama'],
        'no_hp' => $_POST['no_hp'],
        'alamat' => $_POST['alamat']
    ];

    // Memeriksa apakah ada data yang kosong
    if (in_array('', $data)) {
        $error_message = 'Semua data harus diisi!';
    } else {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_map(function($value) use ($conn) {
            return "'" . pg_escape_string($conn, $value) . "'";
        }, array_values($data)));

        try {
            $query = "INSERT INTO pembeli ($columns) VALUES ($values)";
            $result = pg_query($conn, $query);

            if (!$result) {
                $error_message = "Error in inserting data: " . pg_last_error($conn);
            } else {
                // Redirect setelah data berhasil disimpan
                header('Location: transaksi.php');
                exit();
            }
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
    <title>Tambah Pembeli</title>
</head>
<body>
<div class="container">
    <h2>Tambah Pembeli</h2>
    <?php if ($error_message) { ?>
        <div class="alert alert-danger" role="alert">
            <?= $error_message ?>
        </div>
    <?php } ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" class="form-control" name="nama" required>
        </div>
        <div class="form-group">
            <label for="no_hp">No HP</label>
            <input type="text" class="form-control" name="no_hp" required>
        </div>
        <div class="form-group">
            <label for="alamat">Alamat</label>
            <input type="text" class="form-control" name="alamat" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
</body>
</html>
