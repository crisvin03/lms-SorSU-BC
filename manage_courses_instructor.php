<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'instructor') {
    die("Access denied! Instructors only.");
}

$user_id = $_SESSION['user_id'];

// Fetch instructor's courses
$courses = $conn->query("
    SELECT c.id, c.course_name, c.description, c.instructor_name
    FROM instructor_courses ic
    JOIN courses c ON ic.course_id = c.id
    WHERE ic.instructor_id = $user_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">Manage Courses</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Description</th>
                <th>Instructor</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = $courses->fetch_assoc()): ?>
            <tr>
                <td><?php echo $course['course_name']; ?></td>
                <td><?php echo $course['description']; ?></td>
                <td><?php echo $course['instructor_name']; ?></td>
                <td>
                    <a href="update_course.php?id=<?php echo $course['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
