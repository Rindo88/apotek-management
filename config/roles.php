<?php
define('ROLE_ADMIN', 'admin');
define('ROLE_KASIR', 'kasir');
define('ROLE_GUDANG', 'gudang');

// Define page access
$PAGE_ACCESS = [
    'transaksi.php' => [ROLE_ADMIN, ROLE_KASIR],
    'pembayaran.php' => [ROLE_ADMIN, ROLE_KASIR],
    'detail_transaksi.php' => [ROLE_ADMIN, ROLE_KASIR],
    'obat.php' => [ROLE_ADMIN, ROLE_GUDANG],
    'supplier.php' => [ROLE_ADMIN, ROLE_GUDANG],
    'tambah_user.php' => [ROLE_ADMIN],
];
