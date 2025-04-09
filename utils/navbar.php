<?php
require_once(__DIR__ . '/../config/roles.php');

if (!isset($_SESSION['role'])) {
    return;
}
?>
<!-- CSS Dependencies -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/test-apotek-progtur/pages/index.php">Apotek App</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <?php if (in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_GUDANG])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="gudangDropdown" role="button" data-toggle="dropdown">
                            Gudang
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="/pages/gudang/obat.php">Kelola Obat</a>
                            <a class="dropdown-item" href="/pages/gudang/supplier.php">Kelola Supplier</a>
                        </div>
                    </li>
                <?php } ?>
                
                <?php if (in_array($_SESSION['role'], [ROLE_ADMIN, ROLE_KASIR])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="kasirDropdown" role="button" data-toggle="dropdown">
                            Kasir
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="/pages/kasir/transaksi.php">Transaksi</a>
                            <a class="dropdown-item" href="/pages/kasir/tambah_pembeli.php">Transaksi Cepat</a>
                            <a class="dropdown-item" href="/pages/kasir/riwayat_transaksi.php">Riwayat Transaksi</a>
                        </div>
                    </li>
                <?php } ?>
                
                <?php if ($_SESSION['role'] === ROLE_ADMIN) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/admin/kelola_user.php">Kelola User</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pages/kasir/riwayat_transaksi.php">Riwayat Transaksi</a>
                    </li>
                <?php } ?>
            </ul>
            <span class="navbar-text mr-3">
                <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)
            </span>
            <a href="/utils/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>
<div style="margin-top: 70px;"></div>

<!-- Required JavaScript dependencies in correct order -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Fix dropdown initialization -->
<script>
$(document).ready(function() {
    $('.dropdown-toggle').dropdown();
});
</script>
