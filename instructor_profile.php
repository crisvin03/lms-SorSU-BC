<?php
session_start();
include 'config.php';

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';
$user_id = $_SESSION['user_id'];

// Fetch instructor profile details
$query = $conn->prepare("SELECT first_name, last_name, email, department, profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$instructor = $result->fetch_assoc();

$instructor_name = $instructor['first_name'] . ' ' . $instructor['last_name'];
$email = $instructor['email'];
$department = $instructor['department'];
$profile_picture = $instructor['profile_picture'] ?: 'default.png';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    // Ensure the uploads directory exists
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_info = pathinfo($_FILES['profile_picture']['name']);
    $file_extension = strtolower($file_info['extension']);

    if (in_array($file_extension, $allowed_extensions)) {
        $new_filename = uniqid('profile_', true) . '.' . $file_extension;
        $target_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
            // Update the profile picture in the database
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $new_filename, $user_id);
            if ($stmt->execute()) {
                $profile_picture = $new_filename; // Update the profile picture displayed
                $success_message = "Profile picture updated successfully!";
            } else {
                $error_message = "Failed to update profile picture. Please try again.";
            }
            $stmt->close();
        } else {
            $error_message = "Failed to upload the file. Please try again.";
        }
    } else {
        $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if new password matches the confirm password
    if ($new_password === $confirm_password) {
        // Fetch current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $current_password_hash = $user['password'];

        // Verify old password
        if (password_verify($old_password, $current_password_hash)) {
            // Update new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_password_hash, $user_id);
            if ($update_stmt->execute()) {
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Failed to update password. Please try again.";
            }
            $update_stmt->close();
        } else {
            $error_message = "Incorrect old password.";
        }
        $stmt->close();
    } else {
        $error_message = "New password and confirm password do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-card {
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        .upload-section, .password-section {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-maroon">Profile</h2>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card profile-card mb-4">
        <div class="card-body d-flex align-items-center">
            <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic me-4">
            <div>
                <h5><?php echo htmlspecialchars($instructor_name); ?></h5>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
                <p>Department: <?php echo htmlspecialchars($department); ?></p>
            </div>
        </div>
    </div>

    <!-- Upload Profile Picture Form -->
    <div class="upload-section">
        <h4>Upload Profile Picture</h4>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Choose a new profile picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload Picture</button>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="password-section">
        <h4>Change Password</h4>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="old_password" class="form-label">Old Password</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
