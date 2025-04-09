<?php
require('../../config/config.php'); 
require('../../config/roles.php');
require('../../middleware/auth.php');

authorize([ROLE_ADMIN, ROLE_GUDANG]); // Only admin and gudang can access

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// Mengambil data supplier untuk select option
$query_supplier = "SELECT id, nama FROM supplier";
$result_supplier = pg_query($conn, $query_supplier);
if (!$result_supplier) {
    die("Error fetching supplier: " . pg_last_error($conn));
}
$suppliers = pg_fetch_all($result_supplier);

// Tambah Obat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_obat'])) {
    $data = [
        'nama' => $_POST['nama'],
        'barcode' => $_POST['barcode'],
        'jenis' => $_POST['jenis'],
        'kategori' => $_POST['kategori'],
        'komposisi' => $_POST['komposisi'],
        'dosis' => $_POST['dosis'],
        'satuan' => $_POST['satuan'],
        'gambar' => $_POST['gambar'],
        'stok' => $_POST['stok'],
        'supplier_id' => $_POST['supplier_id'],
        'harga' => $_POST['harga'],
        'deskripsi' => $_POST['deskripsi']
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
            header('Location: obat.php');
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
        'barcode' => $_POST['barcode'],
        'jenis' => $_POST['jenis'],
        'kategori' => $_POST['kategori'],
        'komposisi' => $_POST['komposisi'],
        'dosis' => $_POST['dosis'],
        'satuan' => $_POST['satuan'],
        'gambar' => $_POST['gambar'],
        'stok' => $_POST['stok'],
        'supplier_id' => $_POST['supplier_id'],
        'harga' => $_POST['harga'],
        'deskripsi' => $_POST['deskripsi']
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
            header('Location: obat.php');
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
    header('Location: obat.php');
    exit();
}

// Mengambil data obat
$query_obat = "SELECT id, nama, barcode, jenis, satuan, dosis, stok, kategori, harga FROM obat";
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
$jenis_options = ['Tablet', 'Kapsul', 'Sirup', 'Salep', 'Injeksi'];
$kategori_options = ['Bebas', 'Bebas Terbatas', 'Resep Dokter'];
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
<?php include('../../utils/navbar.php'); ?>
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
                    <label for="barcode">Barcode</label>
                    <input type="text" class="form-control" name="barcode" value="<?=$edit_obat['barcode']?>" required>
                </div>
                <div class="form-group">
                    <label for="jenis">Jenis</label>
                    <select class="form-control" name="jenis" required>
                        <option value="" hidden>Pilih Jenis</option>
                        <?php foreach ($jenis_options as $jenis) { ?>
                            <option value="<?=$jenis?>" <?=($jenis == $edit_obat['jenis']) ? 'selected' : ''?>><?=$jenis?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select class="form-control" name="kategori" required>
                        <option value="" hidden>Pilih Kategori</option>
                        <?php foreach ($kategori_options as $kategori) { ?>
                            <option value="<?=$kategori?>" <?=($kategori == $edit_obat['kategori']) ? 'selected' : ''?>><?=$kategori?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="komposisi">Komposisi</label>
                    <textarea class="form-control" name="komposisi" required><?=$edit_obat['komposisi']?></textarea>
                </div>
                <div class="form-group">
                    <label for="dosis">Dosis</label>
                    <input type="text" class="form-control" name="dosis" value="<?=$edit_obat['dosis']?>">
                </div>
                <div class="form-group">
                    <label for="satuan">Satuan</label>
                    <input type="text" class="form-control" name="satuan" value="<?=$edit_obat['satuan']?>" required>
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar</label>
                    <input type="text" class="form-control" name="gambar" value="<?=$edit_obat['gambar']?>">
                </div>
                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" class="form-control" name="stok" value="<?=$edit_obat['stok']?>" required>
                </div>
                <div class="form-group">
                    <label for="supplier_id">Supplier</label>
                    <select class="form-control" name="supplier_id" required>
                        <option value="" hidden>Pilih Supplier</option>
                        <?php foreach ($suppliers as $supplier) { ?>
                            <option value="<?=$supplier['id']?>" <?=($supplier['id'] == $edit_obat['supplier_id']) ? 'selected' : ''?>><?=$supplier['nama']?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" class="form-control" name="harga" value="<?=$edit_obat['harga']?>" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" required><?=$edit_obat['deskripsi']?></textarea>
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
                    <label for="barcode">Barcode</label>
                    <input type="text" class="form-control" name="barcode" required>
                </div>
                <div class="form-group">
                    <label for="jenis">Jenis</label>
                    <select class="form-control" name="jenis" required>
                        <option value="" hidden>Pilih Jenis</option>
                        <?php foreach ($jenis_options as $jenis) { ?>
                            <option value="<?=$jenis?>"><?=$jenis?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select class="form-control" name="kategori" required>
                        <option value="" hidden>Pilih Kategori</option>
                        <?php foreach ($kategori_options as $kategori) { ?>
                            <option value="<?=$kategori?>"><?=$kategori?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="komposisi">Komposisi</label>
                    <textarea class="form-control" name="komposisi" required></textarea>
                </div>
                <div class="form-group">
                    <label for="dosis">Dosis</label>
                    <input type="text" class="form-control" name="dosis">
                </div>
                <div class="form-group">
                    <label for="satuan">Satuan</label>
                    <input type="text" class="form-control" name="satuan" required>
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar</label>
                    <input type="text" class="form-control" name="gambar">
                </div>
                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" class="form-control" name="stok" required>
                </div>
                <div class="form-group">
                    <label for="supplier_id">Supplier</label>
                    <select class="form-control" name="supplier_id" required>
                        <option value="" hidden>Pilih Supplier</option>
                        <?php foreach ($suppliers as $supplier) { ?>
                            <option value="<?=$supplier['id']?>"><?=$supplier['nama']?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" class="form-control" name="harga" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" required></textarea>
                </div>
                <button type="submit" name="tambah_obat" class="btn btn-primary">Tambah</button>
            </form>
        <?php } ?>
    <?php } else { ?>
        <a href="obat.php?tambah=true" class="btn btn-success mb-3">Tambah Obat</a>
    <?php } ?>

    <h3 class="mt-4">Daftar Obat</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Satuan</th>
                <th>Dosis</th>
                <th>Stok</th>
                <th>Kategori</th>
                <th>Harga</th>
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
                    <td><?=$o['jenis']?></td>
                    <td><?=$o['satuan']?></td>
                    <td><?=$o['dosis']?></td>
                    <td><?=$o['stok']?></td>
                    <td><?=$o['kategori']?></td>
                    <td><?=number_format($o['harga'], 0, ',', '.')?></td>
                    <td>
                        <a href="../detail_obat.php?id=<?=$o['id']?>" class="btn btn-info">Detail</a>
                        <a href="obat.php?edit=<?=$o['id']?>" class="btn btn-warning">Edit</a>
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
