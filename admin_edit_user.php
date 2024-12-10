<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Access denied! Admins only.");
}

// Fetch user details if an ID is passed
$user_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);

    if ($first_name && $last_name && $email && $role && $user_id) {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $user_id);

        if ($stmt->execute()) {
            header("Location: admin_manage_users.php?message=User updated successfully.&message_type=success");
            exit();
        } else {
            $error_message = "Error updating user.";
        }

        $stmt->close();
    } else {
        $error_message = "Invalid input.";
    }
}

// Fetch user details to pre-fill the form
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .btn-maroon {
            background-color: #800000; /* Dark maroon color */
            border-color: #800000;
            color: white;
        }
        .btn-maroon:hover {
            background-color: #A52A2A; /* Light maroon color on hover */
            border-color: #A52A2A;
        }
        .btn-blue {
            background-color: #007BFF; /* Blue color */
            border-color: #007BFF;
            color: white;
        }
        .btn-blue:hover {
            background-color: #0056b3; /* Darker blue on hover */
            border-color: #0056b3;
        }
        .btn-cancel {
            background-color: #800000; /* Dark maroon color */
            border-color: #800000;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #A52A2A; /* Light maroon color on hover */
            border-color: #A52A2A;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">Edit User</h1>
    
    <!-- Display error message if any -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-control" id="role" name="role" required>
                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
            </select>
        </div>
        <button type="submit" name="submit" class="btn btn-maroon">Save Changes</button>
        <a href="admin_manage_users.php" class="btn btn-cancel">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
