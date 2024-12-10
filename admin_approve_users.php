<?php
session_start();
require 'config.php';

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Access denied! Admins only.");
}

// Fetch pending users
$stmt = $conn->query("SELECT id, first_name, last_name, email, role, status FROM users WHERE status = 'pending'");
$pending_users = $stmt->fetch_all(MYSQLI_ASSOC);

// Handle approval
if (isset($_GET['approve'])) {
    $user_id = $_GET['approve'];
    $conn->query("UPDATE users SET status = 'approved' WHERE id = $user_id");
    header("Location: admin_approve_users.php");
    exit();
}

// Handle rejection
if (isset($_GET['reject'])) {
    $user_id = $_GET['reject'];
    $conn->query("DELETE FROM users WHERE id = $user_id");
    header("Location: admin_approve_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">Approve Pending Users</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                    <td>
                        <a href="admin_approve_users.php?approve=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                        <a href="admin_approve_users.php?reject=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
