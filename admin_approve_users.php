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
    $user_id = intval($_GET['approve']);
    
    // Fetch user details before approval
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Update user status
    $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Set success message
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => "User {$user['first_name']} {$user['last_name']} has been approved successfully."
        ];
    } else {
        // Set error message
        $_SESSION['message'] = [
            'type' => 'danger',
            'text' => "Failed to approve user. Please try again."
        ];
    }
    
    header("Location: admin_approve_users.php");
    exit();
}

// Handle rejection
if (isset($_GET['reject'])) {
    $user_id = intval($_GET['reject']);
    
    // Fetch user details before rejection
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Set success message
        $_SESSION['message'] = [
            'type' => 'warning',
            'text' => "User {$user['first_name']} {$user['last_name']} has been rejected and removed."
        ];
    } else {
        // Set error message
        $_SESSION['message'] = [
            'type' => 'danger',
            'text' => "Failed to reject user. Please try again."
        ];
    }
    
    header("Location: admin_approve_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Approval Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-light: #a52a2a;
            --maroon-dark: #5c0000;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(128,0,0,0.05);
            transition: background-color 0.3s ease;
        }
        .badge-pending {
            background-color: var(--maroon);
            color: white;
        }
        .admin-header {
            background: linear-gradient(to right, var(--maroon), var(--maroon-light));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .card-header {
            background-color: var(--maroon) !important;
        }
        .btn-approve {
            background-color: var(--maroon);
            border-color: var(--maroon-dark);
        }
        .btn-approve:hover {
            background-color: var(--maroon-dark);
        }
        .table-light {
            background-color: rgba(128,0,0,0.1);
        }
        .alert-dismissible .btn-close {
            padding: 0.5rem 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header text-center">
        <div class="container">
            <h1 class="display-5">
                <i class="fas fa-user-shield me-2"></i>User Approval Dashboard
            </h1>
            <p class="lead">Manage Pending User Registrations</p>
        </div>
    </div>

    <div class="container">
        <!-- Display message if exists -->
        <?php 
        if (isset($_SESSION['message'])) {
            $messageType = $_SESSION['message']['type'];
            $messageText = $_SESSION['message']['text'];
            echo "
            <div class='alert alert-{$messageType} alert-dismissible fade show' role='alert'>
                {$messageText}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
            
            // Clear the message after displaying
            unset($_SESSION['message']);
        }
        ?>

        <div class="card">
            <div class="card-header text-white">
                <h3 class="mb-0">
                    <i class="fas fa-users me-2"></i>Pending Users 
                    <span class="badge bg-light text-dark ms-2"><?php echo count($pending_users); ?></span>
                </h3>
            </div>
            <div class="card-body">
                <?php if (empty($pending_users)): ?>
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        No pending user registrations at the moment.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge badge-pending">
                                                <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="admin_approve_users.php?approve=<?php echo $user['id']; ?>" 
                                                   class="btn btn-approve btn-sm text-white" 
                                                   onclick="return confirm('Are you sure you want to approve this user?');">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </a>
                                                <a href="admin_approve_users.php?reject=<?php echo $user['id']; ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Are you sure you want to reject this user?');">
                                                    <i class="fas fa-times me-1"></i>Reject
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-muted mt-4 py-3" style="color: var(--maroon) !important;">
        <div class="container">
            <p class="mb-0">
                <small>© <?php echo date('Y'); ?> Admin Dashboard. All Rights Reserved.</small>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>