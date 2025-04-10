<?php
require('../config/config.php');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $username = pg_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = pg_query($conn, $query);
    
    if (!$result) {
        $error_message = "Error querying database: " . pg_last_error($conn);
    } else {
        $user = pg_fetch_assoc($result);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            
            // Debug
            error_log("Login successful for user: " . $user['username']);
            error_log("Role: " . $user['role']);
            error_log("User ID: " . $user['id']);
            
            header("Location: index.php");
            exit();
        } else {
            $error_message = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <title>Login - Sistem Apotek</title>
    <style>
        body {
            background-color: #f0f0f0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #212529;
            font-weight: 500;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border: 1px solid #ced4da;
        }
        .btn-login {
            width: 100%;
            margin-top: 10px;
        }
        .input-group-text {
            background-color: white;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .input-group-text i {
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Login Apotek</h2>
            <p class="text-muted">Silakan masuk untuk melanjutkan</p>
        </div>
        
        <?php if ($error_message) { ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
            </div>
        <?php } ?>

        <form action="" method="POST">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-user text-primary"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-lock text-primary"></i>
                        </span>
                    </div>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
            </div>
            <button type="submit" name="submit" class="btn btn-primary btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
