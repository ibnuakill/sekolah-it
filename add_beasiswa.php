<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_beasiswa = trim($_POST['nama_beasiswa']);
    $min_ipk = floatval($_POST['min_ipk']); // Mengonversi ke float untuk memastikan IPK berbentuk angka

    // Validasi nama beasiswa tidak kosong
    if (empty($nama_beasiswa)) {
        die("Nama beasiswa tidak boleh kosong");
    }

    // Validasi min_ipk harus berada di antara 0 dan 4.0
    if ($min_ipk < 0 || $min_ipk > 4.0) {
        die("IPK minimal harus berada antara 0 dan 4.0");
    }

    // Simpan data beasiswa ke database
    $sql = "INSERT INTO beasiswa (nama_beasiswa, min_ipk) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $nama_beasiswa, $min_ipk); // "sd" = string dan double untuk bind_param

    if ($stmt->execute()) {
        echo "Beasiswa berhasil ditambahkan!";
        header("Location: index.php");
        exit(); // Pastikan untuk exit setelah header
    } else {
        echo "Error: " . $stmt->error;
    }

    // Tutup statement
    $stmt->close();
}

// Tutup koneksi
$conn->close();
