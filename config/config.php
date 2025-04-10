<?php
session_start();
// cocokan dengan database mu
$host = "localhost";
$port = "5432";
$dbname = "testing_apotek"; 
$user = "postgres";
$password = "ahmad";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Koneksi gagal: " . pg_last_error());
}
?>
