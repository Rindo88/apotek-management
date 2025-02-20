<?php 
require('../../config/config.php'); 
// session_start(); 

if (isset($_SESSION['username'])) { 
    header("Location: ../index.php"); 
    exit(); 
} 

if (isset($_POST['submit'])) { 
    $username = pg_escape_string($conn, $_POST['username']); 
    $password = $_POST['password']; 

    $sql = "SELECT * FROM users WHERE username='$username'"; 
    $result = pg_query($conn, $sql); 
    if (pg_num_rows($result) > 0) { 
        $row = pg_fetch_assoc($result); 
        if (password_verify($password, $row['password'])) { 
            $_SESSION['username'] = $row['username']; 
            $_SESSION['role'] = $row['role']; // Simpan role di session
            header("Location: ../index.php"); 
            exit(); 
        } else { 
            echo "Password salah!"; 
        } 
    } else { 
        echo "Username tidak terdaftar!"; 
    } 
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>