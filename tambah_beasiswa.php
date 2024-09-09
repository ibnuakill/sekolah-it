<?php
session_start();
require_once 'koneksi.php';  // Connect to the database

// Check if the admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';  // Initialize the message variable

// Handle adding a new scholarship
if (isset($_POST['add_scholarship'])) {
    $nama_beasiswa = $_POST['nama_beasiswa'];
    $jenis_beasiswa = $_POST['jenis_beasiswa'];

    $stmt = $conn->prepare("INSERT INTO beasiswa (nama_beasiswa, jenis_beasiswa) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama_beasiswa, $jenis_beasiswa);

    if ($stmt->execute()) {
        $message = "Beasiswa baru berhasil ditambahkan.";
    } else {
        $message = "Gagal menambahkan beasiswa baru: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: tambah_beasiswa.php?message=" . urlencode($message));
    exit;
}

// Handle updating a scholarship
if (isset($_POST['update_scholarship'])) {
    $id_beasiswa = $_POST['id_beasiswa'];
    $nama_beasiswa = $_POST['nama_beasiswa'];
    $jenis_beasiswa = $_POST['jenis_beasiswa'];

    $stmt = $conn->prepare("UPDATE beasiswa SET nama_beasiswa = ?, jenis_beasiswa = ? WHERE id_beasiswa = ?");
    $stmt->bind_param("ssi", $nama_beasiswa, $jenis_beasiswa, $id_beasiswa);

    if ($stmt->execute()) {
        $message = "Beasiswa berhasil diperbarui.";
    } else {
        $message = "Gagal memperbarui beasiswa: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: tambah_beasiswa.php?message=" . urlencode($message));
    exit;
}

// Handle deleting a scholarship
if (isset($_POST['delete_scholarship'])) {
    $id_beasiswa = $_POST['id_beasiswa'];

    $stmt = $conn->prepare("DELETE FROM beasiswa WHERE id_beasiswa = ?");
    $stmt->bind_param("i", $id_beasiswa);

    if ($stmt->execute()) {
        $message = "Beasiswa berhasil dihapus.";
    } else {
        $message = "Gagal menghapus beasiswa: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: tambah_beasiswa.php?message=" . urlencode($message));
    exit;
}

// Fetch all scholarships from the database
$stmt = $conn->prepare("SELECT id_beasiswa, nama_beasiswa, jenis_beasiswa FROM beasiswa");
$stmt->execute();
$scholarship_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <h2>Tambah Beasiswa Baru</h2>
    <div class="scholarship-form">
        <form action="" method="POST">
            <input type="text" name="nama_beasiswa" placeholder="Nama Beasiswa" required>
            <select name="jenis_beasiswa" required>
                <option value="akademik">Akademik</option>
                <option value="non-akademik">Non-Akademik</option>
            </select>
            <button type="submit" name="add_scholarship">Tambah Beasiswa</button>
            <button type="reset">Reset</button>
            <a href="admin_dashboard.php" class="back-button-2">Kembali ke Dashboard</a>
        </form>
    </div>

    <h2>Edit Beasiswa</h2>
    <?php
    // Display messages if any
    if (isset($_GET['message'])) {
        echo "<p>" . htmlspecialchars($_GET['message']) . "</p>";
    }
    ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Beasiswa</th>
                <th>Jenis Beasiswa</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $scholarship_result->fetch_assoc()): ?>
                <tr>
                    <form action="" method="POST">
                        <td><?php echo $row['id_beasiswa']; ?></td>
                        <td><input type="text" name="nama_beasiswa" value="<?php echo $row['nama_beasiswa']; ?>" required></td>
                        <td>
                            <select name="jenis_beasiswa" required>
                                <option value="akademik" <?php if ($row['jenis_beasiswa'] == 'akademik') echo 'selected'; ?>>Akademik</option>
                                <option value="non-akademik" <?php if ($row['jenis_beasiswa'] == 'non-akademik') echo 'selected'; ?>>Non-Akademik</option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="id_beasiswa" value="<?php echo $row['id_beasiswa']; ?>">
                            <button type="submit" name="update_scholarship">Update</button>
                            <button type="submit" name="delete_scholarship" onclick="return confirm('Are you sure you want to delete this scholarship?');">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>