<?php
session_start();
require 'config.php';

// Initialize $success and $error variables
$success = '';
$error = '';

// Check if token is present in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Fetch token and expiration time from the database
    $stmt = $conn->prepare("SELECT id, email, reset_token, token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    // Check if token exists
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $email, $reset_token, $token_expiry);
        $stmt->fetch();

        // Check if the token has expired (1-hour expiration)
        $current_time = time();
        if ($current_time > strtotime($token_expiry)) {
            // Token has expired
            $error = "The password reset token has expired. Please request a new one.";
        } else {
            // Token is valid, show password reset form
            if (isset($_POST['reset_password'])) {
                // Process new password
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                // Check if passwords match
                if ($new_password === $confirm_password) {
                    // Hash new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the password in the database and reset token and expiry
                    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
                    $stmt->bind_param("ss", $hashed_password, $token);
                    $stmt->execute();

                    $success = "Your password has been reset successfully.";
                } else {
                    $error = "Passwords do not match.";
                }
            }
        }
    } else {
        $error = "Invalid or expired token.";
    }
} else {
    $error = "No reset token found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .image-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
        }

        .login-form-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        .logo-container img {
            max-width: 150px;
            margin-bottom: 20px;
        }

        .portal-text {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .error-message, .success-message {
            color: #f44336;
            background-color: #ffe6e6;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #f44336;
        }

        .success-message {
            color: #4caf50;
            background-color: #e8f5e9;
            border: 1px solid #4caf50;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }

        .btn-maroon {
            background-color: #800000;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .btn-maroon:hover {
            background-color: #660000;
        }

        p {
            margin-top: 20px;
        }

        a {
            color: #800000;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .reset-password-form {
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="image-container">
        <img src="assets/images/sorsu.jpg" alt="Education">
    </div>

    <div class="login-form-container">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
        </div>
        <h1 class="portal-text">Reset Password</h1>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <form method="POST" action="reset_password.php?token=<?php echo $token; ?>" class="reset-password-form">
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password:</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password:</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="reset_password" class="btn-maroon">Reset Password</button>
            </form>
        <?php endif; ?>

        <p><a href="login.php">Back to Login</a></p>
    </div>
</div>

</body>
</html>
