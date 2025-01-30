<?php
include('utils/main.php');
include('koneksi.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])){
  $data = [
    'nama' => $_POST['nama'],
    'deskripsi' => $_POST['deskripsi'],
    'jenis_obat' => $_POST['jenis_obat'],
    'harga' => $_POST['harga'],
    'stok' => $_POST['stok']
  ];

  if(!$data){ 
    echo 'missing data'; 
    die();
  }

  try {
    $response = create_data($conn, 'obat', $data);
    if(!$response){
      echo 'failled to fecth';
    }

    echo '<script>alert("data berhasil di update")</script>';
    header('url = index.php; refresh:0;');
  } catch (Throwable $error) {
    echo $error;
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <form action="" method="POST">
    <label for="nama">nama</label>
    <input type="text" name="nama">
    <label for="jenis_obat">jenis obat</label>
    <input type="text" name="jenis_obat">
    <label for="deskripsi">deskripsi</label>
    <input type="text" name="deskripsi">
    <label for="harga">harga</label>
    <input type="number" name="harga">
    <label for="stok">stok</label>
    <input type="number" name="stok">
    <button type="submit" name="submit">submit</button>
  </form>


</body>
</html>
