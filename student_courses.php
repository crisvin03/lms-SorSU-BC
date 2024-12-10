<?php
session_start();
require 'config.php';

// Restrict access to logged-in students
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    die("Access denied! Students only.");
}

$user_id = $_SESSION['user_id'];

// Fetch enrolled courses
$courses_query = $conn->prepare("
    SELECT c.course_name, c.description, CONCAT(i.first_name, ' ', i.last_name) AS instructor_name
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    LEFT JOIN instructors i ON c.instructor_course_id = i.instructor_id
    WHERE e.student_id = ?
");
$courses_query->bind_param("i", $user_id);
$courses_query->execute();
$result = $courses_query->get_result();
$courses_grades = $result->fetch_all(MYSQLI_ASSOC);

$courses_query->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">My Courses</h1>
    
    <?php if (count($courses_grades) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Description</th>
                        <th>Instructor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses_grades as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['description']); ?></td>
                            <td><?php echo htmlspecialchars($course['instructor_name']) ?: 'Not Assigned'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No courses available.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
