<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Access denied! Admins only.");
}

// Feedback message variables
$message = '';
$message_type = '';

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT); // Validate user ID
    if ($user_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "User deleted successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting user.";
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Invalid user ID.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: admin_manage_users.php");
    exit();
}

// Handle feedback message display
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Pagination setup
$limit = 10; // Items per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page
$offset = ($page - 1) * $limit;

// Fetch users with pagination
$stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$users = $stmt->get_result();

// Fetch total number of users for pagination
$total_result = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-light: #a52a2a;
            --maroon-dark: #5c0000;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: linear-gradient(to right, var(--maroon), var(--maroon-light));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            text-align: center;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--maroon);
            color: white;
        }

        .btn-maroon {
            background-color: var(--maroon);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-maroon:hover {
            background-color: var(--maroon-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        .pagination .page-link {
            color: var(--maroon);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--maroon);
            border-color: var(--maroon);
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <h1 class="display-5"><i class="fas fa-users me-2"></i>Manage Users</h1>
        <p class="lead">Manage and Organize Your Users</p>
    </div>

    <div class="container">
        <!-- Feedback Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add User Button -->
        <div class="mb-3 text-start">
            <a href="admin_add_user.php" class="btn btn-maroon">
                <i class="fas fa-plus me-2"></i>Add New User
            </a>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
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
                            <?php if ($users->num_rows > 0): ?>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                        <td>
                                            <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="admin_manage_users.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No users found</h4>
                                        <p>Add your first user to manage them here.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="admin_manage_users.php?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="text-center py-3 bg-light text-muted">
        &copy; <?php echo date('Y'); ?> Admin Dashboard. All Rights Reserved.
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
