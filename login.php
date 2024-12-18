<?php
session_start();
require 'config.php';

// Include PHPMailer classes
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['forgot_password'])) {
        // Process forgot password
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        
        if (!$email) {
            $error = "Invalid email format.";
        } else {
            $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                // Generate a reset token
                $token = bin2hex(random_bytes(50));
                $stmt->bind_result($id, $user_email);
                $stmt->fetch();

                // Store token in the database (you may want to store it in a separate table for security)
                $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
                $stmt->bind_param("ss", $token, $email);
                $stmt->execute();

                // Notify the admin about the reset request
                $admin_email = 'crisvinhabitsuela211@gmail.com';  // Admin's email

                $mail = new PHPMailer(true);  // Instantiate PHPMailer
                try {
                    // Server settings for admin notification email
                    $mail->isSMTP();  // Send using SMTP
                    $mail->Host = 'smtp.gmail.com';  // Set the SMTP server (use Gmail, etc.)
                    $mail->SMTPAuth = true;
                    $mail->Username = 'lms.sorsu@gmail.com';  // Your email address
                    $mail->Password = 'ouqo pbob gquk opta';  // Your email password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;  // SMTP port (587 is standard for TLS)

                    // Admin email content
                    $mail->setFrom('lms.sorsu@gmail.com', 'Mailer');
                    $mail->addAddress($admin_email);  // Admin email address

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request Notification';
                    $mail->Body    = 'A password reset request has been made for the user with the email: ' . $email . '. The admin can proceed to change the password manually and notify the user.';

                    // Send admin email
                    $mail->send();
                    
                    $message = "An email will be sent to your email address, with instructions how to get access again.";
                } catch (Exception $e) {
                    $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "No account found with that email address.";
            }
        }
    } elseif (isset($_POST['login'])) {
        // Process login
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$email) {
            $error = "Invalid email format.";
        } else {
            $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role, status FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $first_name, $last_name, $hashed_password, $role, $status);
                $stmt->fetch();

                if ($status !== 'approved') {
                    $error = "Your email is not verified. Please check your inbox.";
                } else {
                    if (password_verify($password, $hashed_password)) {
                        session_regenerate_id(true);
                        $_SESSION['user'] = $first_name . ' ' . $last_name;
                        $_SESSION['role'] = $role;
                        $_SESSION['user_id'] = $id;

                        if ($role === 'admin') {
                            header("Location: admin_dashboard.php");
                        } elseif ($role === 'instructor') {
                            header("Location: instructor_dashboard.php");
                        } else {
                            header("Location: student_dashboard.php");
                        }
                        exit();
                    } else {
                        $error = "Invalid credentials.";
                    }
                }
            } else {
                $error = "Invalid credentials.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <title>Login</title>
    <style>

        /* Add the contents of your login.css and styles.css here */
        /* Basic Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body, html {
    height: 100%;
    font-family: 'Roboto', sans-serif;
    margin: 0;
    background-color: #f4f4f4;
}

/* Flexbox layout for the container */
.container {
    display: flex;
    height: 100vh;
}

/* Left side: Image */
.image-container {
    flex: 1;
    background-color: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    max-width: 100%;
}

/* Right side: Login Form */
.login-form-container {
    flex: 1;
    background-color: #800000;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 40px;
    box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
}

/* Logo styling */
.logo-container {
    text-align: center;
    margin-bottom: 20px;
}

.logo {
    max-width: 120px;
    height: auto;
}

/* Student Portal Heading */
.portal-text {
    font-size: 36px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
    color: #fff;
}

/* Subtext Styling */
.subtext {
    font-size: 18px;
    color: #ddd;
    margin-top: -10px;
    text-align: center;
    font-weight: 500;
}

/* Form Styling */
.login-form {
    background-color: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    display: flex;
    flex-direction: column;
}
/* Snowflake Styling */
.snowflake {
            position: absolute;
            color: white;
            font-size: 1rem;
            opacity: 0.8;
            animation: fall 8s linear infinite;
        }

        /* Animation for falling snowflakes */
        @keyframes fall {
            0% {
                transform: translateY(-100%);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh);
                opacity: 0;
            }
        }

.login-form .form-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    width: 100%;
}

/* Label Styling */
.login-form .form-label {
    flex: 1;
    font-size: 16px;
    color: #333;
    text-align: left;
}

/* Input Field Styling */
.login-form .form-control {
    flex: 2;
    border-radius: 4px;
    padding: 12px;
    border: 1px solid #ccc;
    font-size: 14px;
    width: 100%;
}

.login-form .form-control:focus {
    border-color: #800000;
    box-shadow: 0 0 5px rgba(128, 0, 0, 0.2);
}

/* Button Styling */
.btn-maroon {
    background-color: #800000;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 4px;
    font-size: 16px;
    transition: background-color 0.3s ease;
    margin-top: 20px;
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 100%; 
    max-width: 200px;
    text-align: center;
}

.btn-maroon:hover {
    background-color: #b30000;
}

/* Footer Text (Register Link) */
p {
    font-size: 14px;
    text-align: center;
    margin-top: 15px;
}

p a {
    color: #fff;
    text-decoration: none;
}

p a:hover {
    text-decoration: underline;
}

/* Popup styling */
.popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    padding: 20px;
    width: 300px;
    max-width: 90%;
    text-align: center;
}

.popup h3 {
    margin-bottom: 10px;
    font-size: 18px;
    color: #800000;
}

.popup p {
    font-size: 14px;
    color: #333;
}

.popup button {
    background-color: #800000;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    font-size: 14px;
    cursor: pointer;
    margin-top: 15px;
}

.popup button:hover {
    background-color: #b30000;
}

/* Overlay styling */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
}

.overlay.active {
    display: block;
}




/* Forgot Password Form */
#forgot-password-form {
    position: relative; /* Needed for manual adjustments */
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    margin-right: 10px; /* Adjust horizontal positioning */
    transform: translateY(50px); /* Adjust vertical positioning */
}


/* Form Group Styling */
#forgot-password-form .form-group {
    margin-bottom: 15px;
}

/* Input Field Styling */
#forgot-password-form .form-control {
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
}

/* Button Styling */
#forgot-password-form button {
    background-color: #800000;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 4px;
    width: 100%;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
}

/* Hover Effect for Button */
#forgot-password-form button:hover {
    background-color: #5c0000;
}


#forgot-password-form .form-group {
    margin-bottom: 15px;
}

#forgot-password-form .form-control {
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

#forgot-password-form button {
    background-color: #800000;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 4px;
    width: 100%;
}
body {
    font-family: 'Arial', sans-serif;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.text-maroon {
    color: #800000;
}

.btn-maroon {
    background-color: #800000;
    color: white;
    border-radius: 5px;
    font-weight: bold;
}

.btn-maroon:hover {
    background-color: #5c0000;
}

.card {
    background-color: #ffffff;
    border-radius: 8px;
}

.table {
    margin-top: 20px;
}

.table th {
    background-color: #800000;
    color: white;
}

.table td {
    background-color: #f1f1f1;
}

.table a {
    color: #800000;
    font-weight: bold;
}

.table a:hover {
    color: #5c0000;
}

    </style>
</head>
<body>

<div class="container">
    <div class="image-container">
        <img src="assets/images/SorSU-BC.jpg" alt="Education">
    </div>

    <div class="login-form-container">
        <div class="logo-container">
            <img src="assets/images/logo.png" alt="Logo" class="logo">
        </div>
        <h1 class="portal-text">Learning Management System</h1>
        <p class="subtext">SorSU Bulan Campus</p>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="email" class="form-label">Email:</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="form-control" 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                    required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn-maroon">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <p><a href="#" id="forgot-password-link">Forgot your password?</a></p>
        
        <!-- Forgot Password Form -->
<div id="forgot-password-form" style="display: none;">
    <form method="POST">
        <div class="form-group">
            <label for="email" class="form-label">Enter your email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
    
        <button type="submit" name="forgot_password" class="btn-maroon">Send Recovery Request</button>
    </form>
    <p class="mt-3">
        <a href="#" id="back-to-login" class="text-maroon">
            <i class="bi bi-arrow-left"></i> Go back to log in
        </a>
    </p>
</div>

        
    </div>
</div>

<!-- Error Popup -->
<div id="popup-overlay" class="overlay">
    <div class="popup">
        <h3>Success</h3>
        <p><?php echo isset($error) ? $error : (isset($message) ? $message : ''); ?></p>
        <button onclick="closePopup()">Close</button>
    </div>
</div>

<script>
    
    // Display the popup if there's an error
    <?php if (isset($error) || isset($message)): ?>
    document.getElementById('popup-overlay').classList.add('active');
    <?php endif; ?>

    // Close the popup
    function closePopup() {
        document.getElementById('popup-overlay').classList.remove('active');
    }

    // Show forgot password form
    document.getElementById('forgot-password-link').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('.login-form').style.display = 'none';
        document.getElementById('forgot-password-form').style.display = 'block';
        const forgotPasswordLink = document.getElementById('forgot-password-link');
    const forgotPasswordForm = document.getElementById('forgot-password-form');
    const backToLoginLink = document.getElementById('back-to-login');
    const loginForm = document.querySelector('.login-form');

    forgotPasswordLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginForm.style.display = 'none';
        forgotPasswordForm.style.display = 'block';
    });

    backToLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        forgotPasswordForm.style.display = 'none';
        loginForm.style.display = 'block';
    });
    });
    
</script>

<script>
    // Snowflakes Effect
    function createSnowflake() {
        const snowflake = document.createElement('div');
        snowflake.className = 'snowflake';
        snowflake.style.left = Math.random() * 100 + 'vw';
        snowflake.style.animationDuration = Math.random() * 3 + 2 + 's';
        snowflake.style.opacity = Math.random();
        document.body.appendChild(snowflake);

        setTimeout(() => {
            snowflake.remove();
        }, 5000);
    }

    setInterval(createSnowflake, 100);
    
</script>
<style>
    .portal-text {
        font-size: 36px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
        color: #fff;
        position: relative;
        text-shadow: 0 0 10px red, 0 0 20px green, 0 0 30px blue;
        animation: glow 2s infinite alternate;
    }

    @keyframes glow {
        0% {
            text-shadow: 0 0 5px red, 0 0 10px green, 0 0 15px blue;
        }
        100% {
            text-shadow: 0 0 20px green, 0 0 25px red, 0 0 30px blue;
        }
    }
</style>

</body>
</html>
