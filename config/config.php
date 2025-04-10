<?php
session_start();
// cocokan dengan database mu
$host = "localhost";
$port = "5432";
$dbname = "database_name"; 
$user = "database_username";
$password = "database_password";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Koneksi gagal: " . pg_last_error());
}
?>
