<?php 
require('../config/config.php'); 

if (!isset($_SESSION['username'])) { 
    header("Location: login/login.php"); 
    exit(); 
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Home</title>
    <style>
        body{
            background-color: blue;
        }
        a{
            margin-top: 10px;
            margin-left: 50px;
    
        }


    </style>
</head>
<body>
    <div class="container">
        <center><h2>Selamat Datang, <?php echo $_SESSION['username']; ?>!</h2></center>
        <p>Role Anda: <?php echo $_SESSION['role']; ?></p>
        <a href="login/logout.php" class="btn btn-danger">Logout</a>
        <a href="obat.php" class="btn btn-primary">Kelola Obat</a>
        <a href="tambah_pelanggan.php" class="btn btn-info">Tambah Pelanggan</a>
        <a href="transaksi.php" class="btn btn-secondary">Transaksi</a>
        <?php if ($_SESSION['role'] == 'admin') { ?>
            <a href="admin/tambah_user.php" class="btn btn-warning text-white">Tambah User</a>
        <?php } ?>
    </div>
</body>
</html>