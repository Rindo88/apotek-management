<?php
session_start();
// cocokan dengan database mu
$host = "localhost";
$port = "5432";
$dbname = "nama_database"; 
$user = "username_database";
$password = "password_database";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Koneksi gagal: " . pg_last_error());
}
?>
