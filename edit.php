<?php
include('utils/main.php');
include('koneksi.php');

$id = $_GET['id'];
$obat = find_data($conn, 'obat', 'id ='.$id);
if(!$obat){
  echo 'data tidak di temukan ';
  die();
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
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
    $response = update_data($conn, 'obat', $data, 'id ='.$id);
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
    <input type="text" name="nama" value="<?=$obat['nama']?>">
    <label for="deskripsi">deskripsi</label>
    <input type="text" name="deskripsi" value="<?=$obat['deskripsi']?>">
    <label for="jenis_obat">jenis_obat</label>
    <input type="text" name="jenis_obat" value="<?=$obat['jenis_obat']?>">
    <label for="harga">tahun terbit</label>
    <input type="number" name="harga" value="<?=$obat['harga']?>">
    <label for="stok">stok</label>
    <input type="number" name="stok" value="<?=$obat['stok']?>">
    <button type="submit">submit</button>
  </form>

  
</body>
</html>