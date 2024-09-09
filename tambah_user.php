<?php
session_start();
require_once 'koneksi.php';  // Connect to the database

// Check if the admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';  // Initialize the message variable

// Handle adding a new user
if (isset($_POST['add_user'])) {
    $new_username = $_POST['new_username'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $new_role = $_POST['new_role'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $new_username, $new_password, $new_role);

    if ($stmt->execute()) {
        $message = "User baru berhasil ditambahkan.";
    } else {
        $message = "Gagal menambahkan user baru: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: tambah_user.php?message=" . urlencode($message));
    exit;
}

// Handle updating a user
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $hashed_password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $id);
    }

    if ($stmt->execute()) {
        $message = "User berhasil diperbarui.";
    } else {
        $message = "Gagal memperbarui user: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: tambah_user.php?message=" . urlencode($message));
    exit;
}

// Handle deleting a user
if (isset($_POST['delete_user'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "User berhasil dihapus.";
    } else {
        $message = "Gagal menghapus user: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to avoid form resubmission
    header("Location: tambah_user.php?message=" . urlencode($message));
    exit;
}

// Fetch all users from the database
$stmt = $conn->prepare("SELECT id, username, role FROM users");
$stmt->execute();
$result = $stmt->get_result();
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
    <h2>Tambah User Baru</h2>
    <div class="user-form">
        <form action="" method="POST">
            <input type="text" name="new_username" placeholder="Username" required>
            <input type="password" name="new_password" placeholder="Password" required>
            <select name="new_role" required>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
            <button type="submit" name="add_user">Tambah User</button>
            <button type="reset">Reset</button>
            <a href="admin_dashboard.php" class="back-button-2">Kembali ke Dashboard</a>
        </form>
    </div>

    <h2>Edit Users</h2>
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
                <th>Username</th>
                <th>Password</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <form action="" method="POST">
                        <td><?php echo $row['id']; ?></td>
                        <td><input type="text" name="username" value="<?php echo $row['username']; ?>" required></td>
                        <td><input type="password" name="password" placeholder="New Password"></td>
                        <td>
                            <select name="role" required>
                                <option value="admin" <?php if ($row['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                <option value="user" <?php if ($row['role'] == 'user') echo 'selected'; ?>>User</option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="update_user">Update</button>
                            <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>