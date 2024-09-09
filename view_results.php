<?php
require_once 'koneksi.php';  // Pastikan koneksi ke database tersedia

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pendaftaran Beasiswa</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="container">
        <h1>Hasil Pendaftaran Beasiswa</h1>

        <!-- Search bar untuk mencari berdasarkan nama -->
        <div class="search-bar">
            <form action="view_results.php" method="GET">
                <label for="search">Cari berdasarkan nama:</label>
                <input type="text" id="search" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Masukkan nama...">
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
                <th>Status Ajuan</th>
            </tr>
            <?php
            // Jika ada parameter pencarian
            $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

            // Query database dengan prepared statement
            if ($search_query !== '') {
                $sql = "SELECT nama, email, no_hp, semester, ipk, beasiswa, status_ajuan FROM pendaftaran WHERE nama LIKE ?";
                $stmt = $conn->prepare($sql);
                $like_query = "%" . $search_query . "%";
                $stmt->bind_param("s", $like_query);
            } else {
                // Query tanpa pencarian
                $sql = "SELECT nama, email, no_hp, semester, ipk, beasiswa, status_ajuan FROM pendaftaran";
                $stmt = $conn->prepare($sql);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            // Cek apakah ada data yang ditemukan
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['nama']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['no_hp']) . "</td>
                        <td>" . htmlspecialchars($row['semester']) . "</td>
                        <td>" . number_format($row['ipk'], 2, '.', ',') . "</td>
                        <td>" . htmlspecialchars($row['beasiswa']) . "</td>
                        <td>" . htmlspecialchars($row['status_ajuan']) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Tidak ada hasil yang ditemukan.</td></tr>";
            }

            // Menutup statement dan koneksi database
            $stmt->close();
            $conn->close();
            ?>
        </table>

        <!-- Tombol Kembali ke Beranda -->
        <a href="index.php" class="back-button">Kembali ke Beranda</a>
    </div>

</body>

</html>