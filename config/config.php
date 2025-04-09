<?php
session_start();

$host = "localhost";
$port = "5432";
$dbname = "testing_apotek"; 
$user = "postgres";
$password = ".....";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Koneksi gagal: " . pg_last_error());
}
?>