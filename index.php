<?php
session_start();
require_once 'koneksi.php';  // Pastikan file ini terhubung ke database

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

// Function to get beasiswa options from the database
function getBeasiswaOptions($conn)
{
    $options = '';
    $sql = "SELECT nama_beasiswa FROM beasiswa"; // Query to get scholarship names
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='" . htmlspecialchars($row['nama_beasiswa']) . "'>" . htmlspecialchars($row['nama_beasiswa']) . "</option>";
        }
    } else {
        $options .= "<option value=''>No Beasiswa Available</option>";
    }

    return $options;
}

$error_message = $success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $nama = !empty($_POST['nama']) ? $conn->real_escape_string(trim($_POST['nama'])) : null;
    $email = !empty($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : null;
    $no_hp = !empty($_POST['no_hp']) ? $conn->real_escape_string(trim($_POST['no_hp'])) : null;
    $semester = !empty($_POST['semester']) ? intval($_POST['semester']) : null;
    $ipk = !empty($_POST['ipk']) ? floatval($_POST['ipk']) : null;
    $beasiswa = !empty($_POST['beasiswa']) ? $conn->real_escape_string(trim($_POST['beasiswa'])) : null;

    // Validate required fields
    if (!$nama || !$email || !$no_hp || !$semester || !$ipk || !$beasiswa) {
        $error_message = "All fields are required.";
    } else {
        // Validate IPK
        if ($ipk < 3.0 || $ipk > 4.0) {
            $error_message = "IPK must be between 3.0 and 4.0.";
        } else {
            // Handle file upload
            $dokumen = '';
            if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["dokumen"]["name"]);
                $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = array("pdf", "jpg", "jpeg", "png");

                if (in_array($file_type, $allowed_types)) {
                    if (move_uploaded_file($_FILES["dokumen"]["tmp_name"], $target_file)) {
                        $dokumen = basename($_FILES["dokumen"]["name"]);
                    } else {
                        $error_message = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $error_message = "Only PDF, JPG, JPEG, and PNG files are allowed.";
                }
            }

            // If no errors, proceed with database insertion
            if (empty($error_message)) {
                $sql = "INSERT INTO pendaftaran (nama, email, no_hp, semester, ipk, beasiswa, dokumen, status_ajuan) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssiiss", $nama, $email, $no_hp, $semester, $ipk, $beasiswa, $dokumen);

                if ($stmt->execute()) {
                    $success_message = "Registration submitted successfully!";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }

                $stmt->close();
            }
        }
    }
}

// Fungsi untuk menampilkan pesan
function displayMessage($message, $type)
{
    if (!empty($message)) {
        echo "<div class='message $type'>$message</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Pendaftaran Beasiswa</title>
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <div class="header">
        <h1>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <a href="view_results.php">View Hasil Beasiswa</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">

        <?php
        // Tampilkan pesan hanya jika form telah disubmit
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            displayMessage($success_message, 'success');
            displayMessage($error_message, 'error');
        }
        ?>

        <div class="section">
            <h2>Formulir Pendaftaran Beasiswa</h2>
            <form id="scholarshipForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                <label for="nama">Nama:</label>
                <input type="text" name="nama" id="nama" required>

                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>

                <label for="no_hp">Nomor HP:</label>
                <input type="tel" name="no_hp" id="no_hp" pattern="[0-9]+" required>

                <label for="semester">Semester:</label>
                <select name="semester" id="semester" required>
                    <option value="">Pilih Semester</option>
                    <?php for ($i = 1; $i <= 8; $i++) {
                        echo "<option value='$i'>$i</option>";
                    } ?>
                </select>

                <label for="ipk">IPK:</label>
                <input type="number" step="0.01" min="3.0" max="4.0" name="ipk" id="ipk" required>
                <span id="ipkError" style="color: red; display: none;">Minimal IPK adalah 3.0</span>

                <label for="dokumen">Upload Dokumen Pendukung:</label>
                <input type="file" name="dokumen" id="dokumen" required accept=".pdf,.jpg,.jpeg,.png">

                <label for="beasiswa">Pilih Beasiswa:</label>
                <select name="beasiswa" id="beasiswa" required>
                    <option value="">Pilih Beasiswa</option>
                    <?php echo getBeasiswaOptions($conn); ?>
                </select>

                <input type="submit" value="Daftar">
            </form>
        </div>
    </div>

    <script>
        document.getElementById('scholarshipForm').addEventListener('submit', function(event) {
            const ipkInput = document.getElementById('ipk');
            const ipkError = document.getElementById('ipkError');

            // Reset pesan kesalahan
            ipkError.style.display = 'none';

            // Cek nilai IPK
            if (parseFloat(ipkInput.value) < 3.0) {
                ipkError.style.display = 'block';
                event.preventDefault(); // Mencegah pengiriman formulir
            }
        });
    </script>
</body>

</html>