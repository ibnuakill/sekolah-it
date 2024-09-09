<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $no_hp = preg_match('/^[0-9]+$/', $_POST['no_hp']) ? trim($_POST['no_hp']) : null;
    $semester = intval($_POST['semester']);
    $ipk = floatval($_POST['ipk']);  // Convert to float
    $beasiswa = trim($_POST['beasiswa']);
    $berkas = $_FILES['berkas']['name'];
    $berkas_tmp = $_FILES['berkas']['tmp_name'];
    $berkas_size = $_FILES['berkas']['size'];
    $berkas_ext = strtolower(pathinfo($berkas, PATHINFO_EXTENSION));

    // Validate IPK
    if ($ipk < 0 || $ipk > 4.0) {
        die("IPK harus antara 0 dan 4.0");
    }

    // Validate berkas
    $allowed_ext = ['pdf', 'doc', 'docx']; // Contoh ekstensi yang diizinkan
    if (!in_array($berkas_ext, $allowed_ext)) {
        die("Jenis file yang diizinkan hanya PDF, DOC, atau DOCX");
    }

    // Batasi ukuran file maksimal 2MB
    if ($berkas_size > 2 * 1024 * 1024) {
        die("Ukuran file tidak boleh lebih dari 2MB");
    }

    // Folder untuk menyimpan berkas
    $upload_dir = "uploads/";
    $upload_file = $upload_dir . basename($berkas);

    // Pindahkan file ke direktori tujuan
    if (move_uploaded_file($berkas_tmp, $upload_file)) {
        // Simpan data ke database
        $sql = "INSERT INTO pendaftaran (nama, email, no_hp, semester, ipk, beasiswa, berkas, status_ajuan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiiss", $nama, $email, $no_hp, $semester, $ipk, $beasiswa, $berkas);

        if ($stmt->execute()) {
            echo "Pendaftaran berhasil!";
            header("Location: index.php");
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error uploading file.";
    }
}
