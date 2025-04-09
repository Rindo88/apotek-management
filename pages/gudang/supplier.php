<?php
require('../../config/config.php'); 
require('../../config/roles.php');
require('../../middleware/auth.php');

authorize([ROLE_ADMIN, ROLE_GUDANG]); // Only admin and gudang can access

if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// Tambah Supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_supplier'])) {
    $data = [
        'nama' => $_POST['nama'],
        'kontak' => $_POST['kontak'],
        'alamat' => $_POST['alamat']
    ];

    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_map(function($value) use ($conn) {
        return "'" . pg_escape_string($conn, $value) . "'";
    }, array_values($data)));

    try {
        $query = "INSERT INTO supplier ($columns) VALUES ($values)";
        $result = pg_query($conn, $query);

        if (!$result) {
            echo "Error in inserting data: " . pg_last_error($conn);
        } else {
            header('Location: supplier.php');
            exit();
        }
    } catch (Throwable $error) {
        echo "Error: " . $error->getMessage();
    }
}

// Edit Supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_supplier'])) {
    $id = pg_escape_string($conn, $_POST['id']);
    $data = [
        'nama' => $_POST['nama'],
        'kontak' => $_POST['kontak'],
        'alamat' => $_POST['alamat']
    ];

    $setClause = implode(", ", array_map(function($key, $value) use ($conn) {
        return "$key = '" . pg_escape_string($conn, $value) . "'";
    }, array_keys($data), $data));

    try {
        $query = "UPDATE supplier SET $setClause WHERE id = $id";
        $response = pg_query($conn, $query);
        if (!$response) {
            echo 'Failed to update data';
        } else {
            header('Location: supplier.php');
            exit();
        }
    } catch (Throwable $error) {
        echo $error;
    }
}

// Delete Supplier
if (isset($_POST['delete_supplier'])) {
    $id = pg_escape_string($conn, $_POST['delete_supplier']);
    $query = "DELETE FROM supplier WHERE id = $id";
    pg_query($conn, $query);
    header('Location: supplier.php');
    exit();
}

// Mengambil data supplier
$query_supplier = "SELECT * FROM supplier";
if (isset($_GET['search'])) {
    $search = pg_escape_string($conn, $_GET['search']);
    $query_supplier .= " WHERE nama ILIKE '%$search%'";
}
$result_supplier = pg_query($conn, $query_supplier);
if (!$result_supplier) {
    die("Error fetching supplier: " . pg_last_error($conn));
}
$supplier = pg_fetch_all($result_supplier);

// Mengambil data supplier untuk edit
$edit_supplier = null;
if (isset($_GET['edit'])) {
    $id = pg_escape_string($conn, $_GET['edit']);
    $query = "SELECT * FROM supplier WHERE id = $id";
    $result = pg_query($conn, $query);
    $edit_supplier = pg_fetch_assoc($result);
}

$show_form = isset($_GET['edit']) || isset($_GET['tambah']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Kelola Supplier</title>
</head>
<body>
<?php include('../../utils/navbar.php'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Kelola Supplier</h2>
        <a href="../index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>

    <form action="" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Cari Supplier" value="<?=isset($_GET['search']) ? $_GET['search'] : ''?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <?php if ($show_form) { ?>
        <?php if ($edit_supplier) { ?>
            <h3>Edit Supplier</h3>
            <form action="" method="POST">
                <input type="hidden" name="id" value="<?=$edit_supplier['id']?>">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" class="form-control" name="nama" value="<?=$edit_supplier['nama']?>" required>
                </div>
                <div class="form-group">
                    <label for="kontak">Kontak</label>
                    <input type="text" class="form-control" name="kontak" value="<?=$edit_supplier['kontak']?>" required>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea class="form-control" name="alamat" required><?=$edit_supplier['alamat']?></textarea>
                </div>
                <button type="submit" name="edit_supplier" class="btn btn-primary">Update</button>
                <a href="supplier.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php } else { ?>
            <h3>Tambah Supplier</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" class="form-control" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="kontak">Kontak</label>
                    <input type="text" class="form-control" name="kontak" required>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea class="form-control" name="alamat" required></textarea>
                </div>
                <button type="submit" name="tambah_supplier" class="btn btn-primary">Tambah</button>
                <a href="supplier.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php } ?>
    <?php } else { ?>
        <a href="supplier.php?tambah=true" class="btn btn-success mb-3">Tambah Supplier</a>
    <?php } ?>

    <h3 class="mt-4">Daftar Supplier</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kontak</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($supplier as $s) { ?>
                <tr>
                    <td><?=$i++?></td>
                    <td><?=$s['nama']?></td>
                    <td><?=$s['kontak']?></td>
                    <td><?=$s['alamat']?></td>
                    <td>
                        <a href="supplier.php?edit=<?=$s['id']?>" class="btn btn-warning">Edit</a>
                        <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus supplier ini?');">
                            <button type="submit" name="delete_supplier" value="<?=$s['id']?>" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
