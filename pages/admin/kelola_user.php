<?php
require('../../config/config.php'); 
require('../../config/roles.php');
require('../../middleware/auth.php');

authorize([ROLE_ADMIN]);

$error_message = '';

// Tambah User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_user'])) {
    $nama = pg_escape_string($conn, $_POST['nama']);
    $username = pg_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = pg_escape_string($conn, $_POST['role']);

    // Pastikan role sesuai dengan constraint
    $allowed_roles = ['admin', 'kasir', 'gudang'];
    if (!in_array($role, $allowed_roles)) {
        $error_message = "Role tidak valid!";
    } else {
        $sql = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')";
        $result = @pg_query($conn, $sql); // Menekan warning otomatis
        if ($result) {
            echo "Registrasi berhasil!";
            header('Location: ../index.php');
            exit();
        } else {
            $error = pg_last_error($conn);
            if (strpos($error, 'duplicate key value violates unique constraint "users_username_key"') !== false) {
                $error_message = "Username sudah terdaftar!";
            } elseif (strpos($error, 'new row for relation "users" violates check constraint "users_role_check"') !== false) {
                $error_message = "Role tidak valid!";
            } else {
                $error_message = "Error: " . $error;
            }
        }
    }
}

// Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = pg_escape_string($conn, $_POST['id']);
    $data = [
        'nama' => $_POST['nama'],
        'username' => $_POST['username'],
        'role' => $_POST['role']
    ];

    // Jika password diisi, update password
    if (!empty($_POST['password'])) {
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $setClause = implode(", ", array_map(function($key, $value) use ($conn) {
        return "$key = '" . pg_escape_string($conn, $value) . "'";
    }, array_keys($data), $data));

    try {
        $query = "UPDATE users SET $setClause WHERE id = $id";
        $result = pg_query($conn, $query);
        if (!$result) {
            $error_message = "Error updating user: " . pg_last_error($conn);
        } else {
            header('Location: kelola_user.php');
            exit();
        }
    } catch (Throwable $error) {
        $error_message = "Error: " . $error->getMessage();
    }
}

// Delete User
if (isset($_POST['delete_user'])) {
    $id = pg_escape_string($conn, $_POST['delete_user']);
    $query = "DELETE FROM users WHERE id = $id";
    pg_query($conn, $query);
    header('Location: kelola_user.php');
    exit();
}

// Fetch Users
$query_users = "SELECT id, nama, username, role FROM users";
if (isset($_GET['search'])) {
    $search = pg_escape_string($conn, $_GET['search']);
    $query_users .= " WHERE nama ILIKE '%$search%' OR username ILIKE '%$search%'";
}
$result_users = pg_query($conn, $query_users);
$users = pg_fetch_all($result_users);

// Get User for Edit
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = pg_escape_string($conn, $_GET['edit']);
    $query = "SELECT id, nama, username, role FROM users WHERE id = $id";
    $result = pg_query($conn, $query);
    $edit_user = pg_fetch_assoc($result);
}

$show_form = isset($_GET['edit']) || isset($_GET['tambah']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Kelola User</title>
</head>
<body>
<?php include('../../utils/navbar.php'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Kelola User</h2>
        <a href="../index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>

    <form action="" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Cari User..." value="<?=isset($_GET['search']) ? $_GET['search'] : ''?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </div>
    </form>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <?php if ($edit_user): ?>
            <h3>Edit User</h3>
            <form action="" method="POST">
                <input type="hidden" name="id" value="<?=$edit_user['id']?>">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" class="form-control" name="nama" value="<?=$edit_user['nama']?>" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" name="username" value="<?=$edit_user['username']?>" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select class="form-control" name="role" required>
                        <option value="admin" <?=$edit_user['role']=='admin'?'selected':''?>>Admin</option>
                        <option value="kasir" <?=$edit_user['role']=='kasir'?'selected':''?>>Kasir</option>
                        <option value="gudang" <?=$edit_user['role']=='gudang'?'selected':''?>>Gudang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" class="form-control" name="password">
                </div>
                <button type="submit" name="edit_user" class="btn btn-primary">Update</button>
            </form>
        <?php else: ?>
            <h3>Tambah User</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" class="form-control" name="nama" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select class="form-control" name="role" required>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                        <option value="gudang">Gudang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" name="tambah_user" class="btn btn-primary">Tambah</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <a href="?tambah=true" class="btn btn-success mb-3">Tambah User</a>
    <?php endif; ?>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Role</th>
                <th>Password</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            foreach ($users as $user): ?>
            <tr>
                <td><?=$i++?></td>
                <td><?=$user['nama']?></td>
                <td><?=$user['username']?></td>
                <td><?=$user['role']?></td>
                <td><em>hidden</em></td>
                <td>
                    <a href="?edit=<?=$user['id']?>" class="btn btn-warning">Edit</a>
                    <form action="" method="POST" style="display:inline;" 
                          onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                        <button type="submit" name="delete_user" value="<?=$user['id']?>" 
                                class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
