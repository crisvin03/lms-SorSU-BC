<?php
require 'config.php';

if (isset($_GET['token'])) {
    $token = htmlspecialchars(trim($_GET['token']));

    // Validate token
    $stmt = $conn->prepare("SELECT id, status FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $status);
        $stmt->fetch();

        if ($status === 'approved') {
            echo "Your email is already verified. <a href='login.php'>Login here</a>";
        } else {
            // Approve the user and remove the token
            $update_stmt = $conn->prepare("UPDATE users SET status = 'approved', verification_token = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $id);
            if ($update_stmt->execute()) {
                echo "Your email has been successfully verified. You can now <a href='login.php'>log in</a>.";
            } else {
                // Log the error and display a user-friendly message
                error_log("Error verifying user with ID $id: " . $update_stmt->error);
                echo "Verification failed. Please try again.";
            }
            $update_stmt->close();
        }
    } else {
        echo "Invalid or expired token. Please request a new verification email.";
    }
    $stmt->close();
} else {
    echo "No token provided. Please check your verification link.";
}
?>
