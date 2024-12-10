<?php
// The password you want to hash
$password = "admin123"; // Replace with your desired password

// Hash the password using BCRYPT
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Display the hashed password
echo "Hashed Password: " . $hashedPassword;
?>
