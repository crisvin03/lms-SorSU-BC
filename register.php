<?php
session_start();
require 'config.php';

error_reporting(0);
ini_set('display_errors', '0');

// Include PHPMailer
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $role = htmlspecialchars($_POST['role']);
    $department = htmlspecialchars($_POST['department']);

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($department)) {
        $message = "All fields are required.";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "danger";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = "danger";
    } else {
        // Check if the email or name is already registered
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR (first_name = ? AND last_name = ?)");
        $stmt->bind_param("sss", $email, $first_name, $last_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "The email address or name is already registered. Please use a different email or name.";
            $message_type = "danger";
        } else {
            $status = 'pending';
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $verification_token = bin2hex(random_bytes(16));

            // Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, status, department, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $hashed_password, $role, $status, $department, $verification_token);

            if ($stmt->execute()) {
                // Get the ID of the newly inserted user
                $user_id = $stmt->insert_id;

                // Insert into the appropriate table based on role
                if ($role == 'student') {
                    $stmt = $conn->prepare("INSERT INTO students (user_id, first_name, last_name, email, created_at, attendance_status, enrollment_status, grades) VALUES (?, ?, ?, ?, NOW(), 'pending', 'pending', '')");
                    $stmt->bind_param("isss", $user_id, $first_name, $last_name, $email);
                } else {
                    $stmt = $conn->prepare("INSERT INTO instructors (user_id, first_name, last_name, email, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("isss", $user_id, $first_name, $last_name, $email);
                }

                if ($stmt->execute()) {
                    $verification_link = "http://127.0.0.1/student_managementdb/verify-email.php?token=" . $verification_token;
                    $subject = "Verify Your Email Address";
                    $message_content = "Hello $first_name,\n\nPlease verify your email address by clicking the link below:\n\n" . $verification_link;

                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'lms.sorsu@gmail.com';  // Your Gmail address
                        $mail->Password = 'ouqo pbob gquk opta';  // Your Gmail App-Specific Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('no-reply@your-lms.com', 'ACCOUNT ACTIVATION');
                        $mail->addAddress($email, $first_name);  // Recipient email address
                        $mail->isHTML(false);
                        $mail->Subject = $subject;
                        $mail->Body    = $message_content;

                        $mail->send();
                        $_SESSION['message'] = "Registration successful! Please verify your email to activate your account.";
                        $_SESSION['message_type'] = "success";

                        header("Location: register.php");
                        exit();
                    } catch (Exception $e) {
                        $message = "Registration successful, but failed to send verification email. Mailer Error: {$mail->ErrorInfo}";
                        $message_type = "warning";
                    }
                } else {
                    $message = "An error occurred while registering the user.";
                    $message_type = "danger";
                }
            }
            $stmt->close();
        }
    }
}

if (!empty($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(45deg, #f3f4f7, #e9ecef); /* Soft gradient background */
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        .text-maroon { color: #800000; }
        .btn-maroon { background-color: #800000; color: white; border: none; }
        .btn-maroon:hover { background-color: #b30000; }
        .form-control, .form-select { border: 1px solid #800000; }
        .form-control:focus, .form-select:focus { border-color: #b30000; box-shadow: none; }
        .card {
            border: none;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .icon-container { text-align: center; margin-bottom: 20px; }
        .icon-container i { font-size: 4rem; color: #800000; }
        .back-to-login { text-decoration: none; color: #000000; font-weight: normal; }
        .back-to-login:hover { color: #b30000; }
        .alert { margin-top: 20px; }
    </style>
</head>
<body>

<div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4 w-50">
        <div class="icon-container">
            <i class="fas fa-user-plus"></i>
        </div>
        <h2 class="text-center text-maroon">Register</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mt-3" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="first_name" class="form-label"><i class="fas fa-user"></i> First Name:</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo isset($first_name) ? $first_name : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label"><i class="fas fa-user"></i> Last Name:</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo isset($last_name) ? $last_name : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($email) ? $email : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fas fa-lock"></i> Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label"><i class="fas fa-user-tag"></i> Role:</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="student" <?php echo (isset($role) && $role == 'student') ? 'selected' : ''; ?>>Student</option>
                    <option value="instructor" <?php echo (isset($role) && $role == 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label"><i class="fas fa-building"></i> Department:</label>
                <select name="department" id="department" class="form-select" required>
                    <option value="DICT" <?php echo (isset($department) && $department == 'DICT') ? 'selected' : ''; ?>>DICT</option>
                    <option value="BEED" <?php echo (isset($department) && $department == 'BME') ? 'selected' : ''; ?>>BME</option>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-maroon"><i class="fas fa-user-plus"></i> Register</button>
            </div>
        </form>

        <div class="mt-3 text-center">
            <p>Already have an account? <a href="login.php" class="back-to-login">Login here</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
