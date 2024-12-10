<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $course = $_POST['course'];

    $stmt = $conn->prepare("INSERT INTO students (name, email, age, course) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $email, $age, $course);
    
    if ($stmt->execute()) {
        echo "Student added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <form method="POST" action="">
        <h2>Add New Student</h2>
        <input type="text" name="name" placeholder="Student Name" required>
        <input type="email" name="email" placeholder="Student Email" required>
        <input type="number" name="age" placeholder="Student Age" required>
        <input type="text" name="course" placeholder="Course" required>
        <button type="submit">Add Student</button>
    </form>
</body>
</html>
