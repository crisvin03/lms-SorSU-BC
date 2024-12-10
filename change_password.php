<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'config.php';

// Process the password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the current, new, and confirm passwords
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = "New password and confirm password do not match.";
        $_SESSION['message_type'] = "error";
        header("Location: student_dashboard.php?section=profile"); // Redirect to the profile page
        exit();
    }

    // Fetch the current password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    // Verify the current password
    if (!password_verify($current_password, $stored_password)) {
        $_SESSION['message'] = "Incorrect current password.";
        $_SESSION['message_type'] = "error";
        header("Location: student_dashboard.php?section=profile");
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Password updated successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: student_dashboard.php?section=profile"); // Redirect to the profile page
    } else {
        $_SESSION['message'] = "Error updating password.";
        $_SESSION['message_type'] = "error";
        header("Location: student_dashboard.php?section=profile");
    }
    $stmt->close();
}
?>
