<?php
session_start();
include 'config.php';

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';
$user_id = $_SESSION['user_id'];

// Fetch student profile details
$query = $conn->prepare("SELECT first_name, last_name, email, department, profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$student = $result->fetch_assoc();

$student_name = $student['first_name'] . ' ' . $student['last_name'];
$email = $student['email'];
$department = $student['department'];
$profile_picture = $student['profile_picture'] ?: 'default.png';

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
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

        /* Header Section */
        .header {
            background: linear-gradient(to right, var(--maroon), var(--maroon-light));
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 2rem;
            font-weight: bold;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background-color: white;
            margin-bottom: 30px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--maroon);
        }

        .btn-maroon {
            background-color: var(--maroon);
            color: white;
            border: none;
        }

        .btn-maroon:hover {
            background-color: var(--maroon-dark);
            color: white;
        }

        label {
            font-weight: 500;
        }

        footer {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h2><i class="fas fa-user-circle me-2"></i>Profile Management</h2>
    <p class="lead">Manage your profile details and settings</p>
</div>

<div class="container">

    <!-- Success or Error Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="card p-4">
        <div class="d-flex align-items-center">
            <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-pic me-4">
            <div>
                <h4 class="fw-bold"><?php echo htmlspecialchars($student_name); ?></h4>
                <p><i class="fas fa-envelope me-2"></i>Email: <?php echo htmlspecialchars($email); ?></p>
                <p><i class="fas fa-building me-2"></i>Department: <?php echo htmlspecialchars($department); ?></p>
            </div>
        </div>
    </div>

    <!-- Upload Profile Picture -->
    <div class="card p-4">
        <h4 class="mb-3"><i class="fas fa-upload me-2"></i>Upload Profile Picture</h4>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Choose a new profile picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-maroon">Upload Picture</button>
        </form>
    </div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> Profile Management System. All Rights Reserved.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
