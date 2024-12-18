<?php
// Include database connection and PHPMailer
include('config.php');
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form is submitted for password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password match
    if ($new_password === $confirm_password) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Assuming the user's ID is passed to this page via GET or session
        if (isset($_GET['id'])) {
            $user_id = $_GET['id'];

            // Update password in the database
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $hashed_password, $user_id);

            if ($stmt->execute()) {
                // Fetch the user's email to send a notification
                $email_query = "SELECT email FROM users WHERE id = ?";
                $email_stmt = $conn->prepare($email_query);
                $email_stmt->bind_param("i", $user_id);
                $email_stmt->execute();
                $email_result = $email_stmt->get_result();

                if ($email_result->num_rows > 0) {
                    $user = $email_result->fetch_assoc();
                    $user_email = $user['email'];

                    // Send an email notification to the user
                    $mail = new PHPMailer(true);

                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'lms.sorsu@gmail.com';  // Replace with your email
                        $mail->Password = 'ouqo pbob gquk opta';  // Replace with your app password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Recipient settings
                        $mail->setFrom('lms.sorsu@gmail.com', 'Admin');
                        $mail->addAddress($user_email);

                        // Email content
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Password Has Been Changed';
$mail->Body = 'Dear user, <br><br>Your password has been successfully changed by the admin. Your new password is: <strong>' . $new_password . '</strong>.<br><br>Please log in and change your password for added security.<br><br>To edit your user details, you can visit the <a href="http://yourdomain.com/admin_edit_users.php?id=' . $user_id . '">Admin Edit Users Page</a>.<br><br>Best regards,<br>Admin';


                        $mail->send();
                        $success_message = "Password updated successfully, and the user has been notified via email.";
                    } catch (Exception $e) {
                        $error_message = "Password updated successfully, but the email could not be sent. Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $error_message = "Password updated, but the user's email could not be retrieved.";
                }
            } else {
                $error_message = "Failed to update the password. Please try again.";
            }
        } else {
            $error_message = "User ID not found.";
        }
    } else {
        $error_message = "Passwords do not match.";
    }
}

// Check if the form is submitted for user details update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['role'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];

        // Update user details in the database
        $query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $user_id);

        if ($stmt->execute()) {
            $success_message = "User details updated successfully.";
            // Redirect to the admin_manage_users.php page after successful update
            header("Location: admin_manage_users.php");
            exit();
        } else {
            $error_message = "Failed to update user details. Please try again.";
        }
    } else {
        $error_message = "User ID not found.";
    }
}

// Fetch user details to pre-fill the form
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user details from the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $error_message = "User not found.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
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
    <h1><i class="fas fa-user-edit"></i>Edit User</h1>
    <p class="lead">Manage user details and permissions</p>
</div>

<div class="container mt-5">

    <!-- Success or Error Message -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Edit User Form -->
    <div class="card">
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
                    <option value="instructor" <?php echo isset($user['role']) && $user['role'] == 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                    <option value="student" <?php echo isset($user['role']) && $user['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-maroon">Save Changes</button>
            <a href="admin_manage_users.php" class="btn btn-cancel">Cancel</a>
        </form>
    </div>

    <!-- Change Password Section (Old Password Removed) -->
    <div class="card">
        <h4 class="mb-3"><i class="fas fa-lock me-2"></i>Change Password</h4>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            </div>
            <button type="submit" class="btn btn-maroon">Change Password</button>
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
