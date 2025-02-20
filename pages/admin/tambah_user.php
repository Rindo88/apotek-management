<?php 
require('../../config/config.php'); 

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $username = pg_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = pg_escape_string($conn, $_POST['role']);

    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if (pg_query($conn, $sql)) {
        echo "Registrasi berhasil!";
        header('Location: ../index.php');
    } else {
        echo "Error: " . pg_last_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Tambah User</title>
</head>
<body>
<div class="container">
    <h2>Tambah User</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" name="username" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" name="role" required>
                <option value="admin">Admin</option>
                <option value="kasir">Kasir</option>
                <option value="gudang">Gudang</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Daftar</button>
    </form>
</div>
</body>
</html>
