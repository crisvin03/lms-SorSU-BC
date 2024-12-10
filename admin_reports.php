<?php
session_start();
require 'config.php';

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Access denied! Admins only.");
}

// Fetch total number of users
$user_result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$user_data = $user_result->fetch_assoc();
$total_users = $user_data['total_users'];

// Fetch total number of courses
$course_result = $conn->query("SELECT COUNT(*) AS total_courses FROM courses");
$course_data = $course_result->fetch_assoc();
$total_courses = $course_data['total_courses'];

// Fetch total number of attendance records
$attendance_result = $conn->query("SELECT COUNT(*) AS total_attendance FROM attendance");
$attendance_data = $attendance_result->fetch_assoc();
$total_attendance = $attendance_data['total_attendance'];

// Fetch average grade (example)
$grades_result = $conn->query("SELECT AVG(grade) AS average_grade FROM grades");
$grades_data = $grades_result->fetch_assoc();
$average_grade = $grades_data['average_grade'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">Admin Reports</h1>

    <!-- Display total number of users -->
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Total Users</h5>
            <p class="card-text"><?php echo $total_users; ?> users registered in the system.</p>
        </div>
    </div>

    <!-- Display total number of courses -->
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Total Courses</h5>
            <p class="card-text"><?php echo $total_courses; ?> courses available in the system.</p>
        </div>
    </div>

    <!-- Display total attendance records -->
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Total Attendance Records</h5>
            <p class="card-text"><?php echo $total_attendance; ?> attendance records tracked in the system.</p>
        </div>
    </div>

    <!-- Display average grade -->
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Average Grade</h5>
            <p class="card-text"><?php echo number_format($average_grade, 2); ?> is the average grade across all students.</p>
        </div>
    </div>

    <!-- Back to Dashboard link -->
    <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
