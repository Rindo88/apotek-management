<?php
function isAuthenticated() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /pages/login.php");
        exit();
    }
    return true;
}

function authorize($allowed_roles = []) {
    isAuthenticated();
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: /403.php");
        exit();
    }
    return true;
}

// Usage examples:
// authorize(['admin']); // Only admin
// authorize(['admin', 'kasir']); // Admin and kasir only
// authorize(['admin', 'kasir', 'gudang']); // All roles
// authorize(); // Just check authentication
