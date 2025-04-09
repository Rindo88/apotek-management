<?php 
require('../config/config.php'); 
require('../config/roles.php');
require('../middleware/auth.php');

isAuthenticated();

if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
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
        body {
            background-color: #f0f0f0;
            padding-top: 60px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .nav-link {
            color: rgba(255,255,255,.75) !important;
        }
        .nav-link:hover {
            color: rgba(255,255,255,1) !important;
        }
        .navbar-text {
            color: rgba(255,255,255,.75) !important;
        }
    </style>
</head>
<body>
<?php include('../utils/navbar.php'); ?>

    <div class="container">
        <div class="jumbotron">
            <h1 class="display-4">Selamat Datang, <?php echo $_SESSION['username']; ?>!</h1>
            <p class="lead">Sistem Informasi Apotek</p>
        </div>

        <!-- Old menu (commented out) -->
        <?php /* 
        <?php if (in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_GUDANG])) { ?>
            <a href="./gudang/obat.php" class="btn btn-primary">Kelola Obat</a>
            <a href="./gudang/supplier.php" class="btn btn-info">Kelola Supplier</a>
        <?php } ?>
        
        <?php if (in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_KASIR])) { ?>
            <a href="./kasir/transaksi.php" class="btn btn-secondary">Transaksi</a>
            <a href="./kasir/tambah_pembeli.php" class="btn btn-info">Transaksi Cepat</a>
        <?php } ?>
        
        <?php if ($_SESSION['role'] === ROLE_ADMIN) { ?>
            <a href="./admin/kelola_user.php" class="btn btn-warning text-white">Tambah User</a>
        <?php } ?>
        */ ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>