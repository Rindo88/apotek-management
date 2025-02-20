<?php
require('../config/config.php'); 

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// Tambah Obat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_obat'])) {
    $data = [
        'nama' => $_POST['nama'],
        'deskripsi' => $_POST['deskripsi'],
        'jenis_obat' => $_POST['jenis_obat'],
        'harga' => $_POST['harga'],
        'stok' => $_POST['stok']
    ];

    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_map(function($value) use ($conn) {
        return "'" . pg_escape_string($conn, $value) . "'";
    }, array_values($data)));

    try {
        $query = "INSERT INTO obat ($columns) VALUES ($values)";
        $result = pg_query($conn, $query);

        if (!$result) {
            echo "Error in inserting data: " . pg_last_error($conn);
        } else {
            header('Location: kelola_obat.php');
            exit();
        }
    } catch (Throwable $error) {
        echo "Error: " . $error->getMessage();
    }
}

// Edit Obat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_obat'])) {
    $id = pg_escape_string($conn, $_POST['id']);
    $data = [
        'nama' => $_POST['nama'],
        'deskripsi' => $_POST['deskripsi'],
        'jenis_obat' => $_POST['jenis_obat'],
        'harga' => $_POST['harga'],
        'stok' => $_POST['stok']
    ];

    $setClause = implode(", ", array_map(function($key, $value) use ($conn) {
        return "$key = '" . pg_escape_string($conn, $value) . "'";
    }, array_keys($data), $data));

    try {
        $query = "UPDATE obat SET $setClause WHERE id = $id";
        $response = pg_query($conn, $query);
        if (!$response) {
            echo 'Failed to update data';
        } else {
            header('Location: kelola_obat.php');
            exit();
        }
    } catch (Throwable $error) {
        echo $error;
    }
}

// Delete Obat
if (isset($_POST['delete_obat'])) {
    $id = pg_escape_string($conn, $_POST['delete_obat']);
    $query = "DELETE FROM obat WHERE id = $id";
    pg_query($conn, $query);
    header('Location: kelola_obat.php');
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

// Mengambil data obat untuk edit
$edit_obat = null;
if (isset($_GET['edit'])) {
    $id = pg_escape_string($conn, $_GET['edit']);
    $query = "SELECT * FROM obat WHERE id = $id";
    $result = pg_query($conn, $query);
    $edit_obat = pg_fetch_assoc($result);
}

$show_form = isset($_GET['edit']) || isset($_GET['tambah']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Kelola Obat</title>
</head>
<body>
<div class="container">
    <h2>Kelola Obat</h2>
    <form action="" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Cari Obat" value="<?=isset($_GET['search']) ? $_GET['search'] : ''?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <?php if ($show_form) { ?>
        <?php if ($edit_obat) { ?>
            <h3>Edit Obat</h3>
            <form action="" method="POST">
                <input type="hidden" name="id" value="<?=$edit_obat['id']?>">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" class="form-control" name="nama" value="<?=$edit_obat['nama']?>" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <input type="text" class="form-control" name="deskripsi" value="<?=$edit_obat['deskripsi']?>" required>
                </div>
                <div class="form-group">
                    <label for="jenis_obat">Jenis Obat</label>
                    <select class="form-control" name="jenis_obat" id="jenis_obat" required>
                        <option value="<?=$edit_obat['jenis_obat']?>" hidden><?=$edit_obat['jenis_obat']?></option>
                        <option value="bebas">Bebas</option>
                        <option value="terbatas">Terbatas</option>
                        <option value="keras">Keras</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" class="form-control" name="harga" value="<?=$edit_obat['harga']?>" required>
                </div>
                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" class="form-control" name="stok" value="<?=$edit_obat['stok']?>" required>
                </div>
                <button type="submit" name="edit_obat" class="btn btn-primary">Update</button>
            </form>
        <?php } else { ?>
            <h3>Tambah Obat</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" class="form-control" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <input type="text" class="form-control" name="deskripsi" required>
                </div>
                <div class="form-group">
                    <label for="jenis_obat">Jenis Obat</label>
                    <select class="form-control" name="jenis_obat" id="jenis_obat" required>
                        <option value="bebas">Bebas</option>
                        <option value="terbatas">Terbatas</option>
                        <option value="keras">Keras</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" class="form-control" name="harga" required>
                </div>
                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" class="form-control" name="stok" required>
                </div>
                <button type="submit" name="tambah_obat" class="btn btn-primary">Tambah</button>
            </form>
        <?php } ?>
    <?php } else { ?>
        <a href="kelola_obat.php?tambah=true" class="btn btn-success mb-3">Tambah Obat</a>
    <?php } ?>

    <h3 class="mt-4">Daftar Obat</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Jenis Obat</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($obat as $o) { ?>
                <tr>
                    <td><?=$i++?></td>
                    <td><?=$o['nama']?></td>
                    <td><?=$o['deskripsi']?></td>
                    <td><?=$o['jenis_obat']?></td>
                    <td><?=number_format($o['harga'], 0, ',', '.')?></td>
                    <td><?=$o['stok']?></td>
                    <td>
                        <a href="kelola_obat.php?edit=<?=$o['id']?>" class="btn btn-warning">Edit</a>
                        <form action="" method="POST" style="display:inline;">
                            <button type="submit" name="delete_obat" value="<?=$o['id']?>" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
