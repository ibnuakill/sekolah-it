<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "pendaftaran_beasiswa";

// Membuat koneksi ke database
$conn = mysqli_connect($hostname, $username, $password, $database);

// Memeriksa koneksi
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
