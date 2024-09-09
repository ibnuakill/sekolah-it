<?php
session_start();
require_once 'koneksi.php';  // Koneksi ke database

// Cek apakah user adalah admin, jika tidak redirect ke login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Inisialisasi pencarian
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Proses pengubahan status ajuan
if (isset($_POST['status_action'])) {
    $id = $_POST['id'];
    $new_status = ($_POST['status_action'] == 'setuju') ? 'Disetujui' : 'Tidak Disetujui';

    // Gunakan prepared statement untuk mengupdate status
    $stmt = $conn->prepare("UPDATE pendaftaran SET status_ajuan = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    $stmt->close();
}

// Proses penghapusan data pendaftaran dan dokumen
if (isset($_POST['delete_action'])) {
    $id = $_POST['id'];

    // Cek apakah dokumen ada di database
    $stmt = $conn->prepare("SELECT dokumen FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($dokumen);
    $stmt->fetch();
    $stmt->close();

    // Jika dokumen ada, hapus file dari direktori
    if ($dokumen) {
        $file_path = "uploads/" . $dokumen;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Hapus data dari database
    $stmt = $conn->prepare("DELETE FROM pendaftaran WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Tampilkan daftar pendaftar berdasarkan pencarian (jika ada)
$query = "SELECT * FROM pendaftaran";
if (!empty($search_query)) {
    $query .= " WHERE LOWER(nama) LIKE LOWER(?)";
}

$stmt = $conn->prepare($query);
if (!empty($search_query)) {
    $search_param = "%$search_query%";
    $stmt->bind_param("s", $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="header">
        <h1>Admin Dashboard - Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <a href="tambah_user.php">Tambah User</a>
        <a href="tambah_beasiswa.php">Tambah Beasiswa</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>Daftar Pendaftar Beasiswa</h2>

        <!-- Search bar -->
        <div class="search-bar">
            <form action="admin_dashboard.php" method="GET">
                <label for="search">Cari berdasarkan nama:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Masukkan nama...">
                <input type="submit" value="Cari">
            </form>
        </div>

        <table>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Semester</th>
                <th>IPK</th>
                <th>Beasiswa</th>
                <th>Dokumen</th>
                <th>Status Ajuan</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                    <td><?php echo htmlspecialchars($row['semester']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($row['ipk'], 2, '.', ',')); ?></td>
                    <td><?php echo htmlspecialchars($row['beasiswa']); ?></td>
                    <td>
                        <?php if (!empty($row['dokumen'])) : ?>
                            <a href="uploads/<?php echo htmlspecialchars($row['dokumen']); ?>" target="_blank">Download</a>
                        <?php else : ?>
                            Tidak ada berkas
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['status_ajuan']); ?></td>
                    <td>
                        <?php if ($row['status_ajuan'] !== 'Disetujui') : ?>
                            <form action="admin_dashboard.php" method="POST" class="status-form">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="status_action" value="setuju" class="status-button approve">Setuju</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($row['status_ajuan'] !== 'Tidak Disetujui') : ?>
                            <form action="admin_dashboard.php" method="POST" class="status-form">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="status_action" value="tidak_setuju" class="status-button reject">Tidak Setuju</button>
                            </form>
                        <?php endif; ?>

                        <form action="admin_dashboard.php" method="POST" class="status-form" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_action" value="hapus" class="status-button delete">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>

</html>