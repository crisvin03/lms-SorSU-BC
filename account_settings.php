<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information from the database
$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email, $profile_picture);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <!-- Include your styles here -->
</head>
<body>

    <h1>Account Settings</h1>

    <!-- Display any success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php elseif (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <div>
        <h2>Profile Information</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($first_name . " " . $last_name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>

        <!-- Display the current profile picture -->
        <p><strong>Profile Picture:</strong></p>
        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="width: 100px; height: 100px;">

        <!-- Change password form -->
        <h3>Change Password</h3>
        <form action="change_password.php" method="POST">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" required><br><br>

            <label for="new_password">New Password</label>
            <input type="password" name="new_password" required><br><br>

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" required><br><br>

            <input type="submit" value="Change Password">
        </form>

        <!-- Upload Profile Picture Form -->
        <h3>Upload Profile Picture</h3>
        <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
            <label for="profile_picture">Choose Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*" required><br><br>

            <input type="submit" value="Upload Picture">
        </form>
    </div>

</body>
</html>
