<?php
// Script to generate a bcrypt hash for a password

// Define the password to hash
$password_to_hash = 'hana1234567890';

// Hash the password using bcrypt
$new_hashed_password = password_hash($password_to_hash, PASSWORD_BCRYPT);

// Display the generated hash
echo "Hashed Password: " . $new_hashed_password;
?>
