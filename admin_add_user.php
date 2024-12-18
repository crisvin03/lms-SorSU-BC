<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Access denied! Admins only.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);

    if ($stmt->execute()) {
        header("Location: admin_manage_users.php");
        exit();
    } else {
        $error = "Failed to add user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-light: #A52A2A;
            --maroon-dark: #5c0000;
            --white: #ffffff;
            --light-gray: #f4f6f9;
            --dark-gray: #6c757d;
        }

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header Section */
        .header {
            background: linear-gradient(to right, var(--maroon), var(--maroon-light));
            color: var(--white);
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: bold;
        }

        .header i {
            font-size: 2.5rem;
            margin-right: 10px;
        }

        .btn-maroon {
            background-color: var(--maroon);
            color: var(--white);
            border: none;
        }

        .btn-maroon:hover {
            background-color: var(--maroon-dark);
            color: var(--white);
        }

        .btn-cancel {
            background-color: var(--maroon);
            color: var(--white);
            border: none;
        }

        .btn-cancel:hover {
            background-color: var(--maroon-light);
            color: var(--white);
        }

        .card {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            padding: 20px;
        }

        label {
            font-weight: 500;
        }

        footer {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            padding: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1><i class="fas fa-user-plus"></i>Add New User</h1>
    <p class="lead">Add a new user to the system</p>
</div>

<div class="container mt-5">

    <!-- Error Message -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="card">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="instructor">Instructor</option>
                    <option value="student">Student</option>
                </select>
            </div>
            <button type="submit" class="btn btn-maroon">Add User</button>
        </form>
    </div>

</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> Profile Management System. All Rights Reserved.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>