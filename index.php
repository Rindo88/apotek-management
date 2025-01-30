<?php
include('koneksi.php');
include('utils/main.php'); // Pastikan jalur ini benar

// Mengambil data obat
$row = get_all_data($conn, 'obat'); // Simpan hasil ke dalam variabel $row

if ($row === false) {
    die("Error fetching data: " . pg_last_error($conn));
}

if(isset($_POST['delete'])){
  try {
    delete_data($conn, 'obat', 'where id='.$_POST['delete']);
  } catch (\Throwable $th) {
    die();
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

<table border="1px">
  <thead>
    <tr>
      <th>no</th>
      <th>nama</th>
      <th>jenis obat</th>
      <th>deskripsi</th>
      <th>harga</th>
      <th>stok</th>
      <th>aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $i = 1;
    foreach($row as $obat) { ?>
       <tr>
        <td><?=$i++?></td>
        <td><?=$obat['nama']?></td>
        <td><?=$obat['jenis_obat']?></td>
        <td><?=$obat['deskripsi']?></td>
        <td><?=$obat['harga']?></td>
        <td><?=$obat['stok']?></td>
        <td>
          <a href="edit.php?id=<?=$obat['id']?>">edit</a>
          <form action="" method="post" style="display:inline;">
            <button type="submit" name="delete" value="<?=$obat['id']?>">delete</button>
          </form>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
  
</body>
</html>