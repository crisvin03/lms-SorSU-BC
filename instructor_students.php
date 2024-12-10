<?php
session_start();
require 'config.php';

// Check if the user is logged in as an instructor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

// Fetch students for the instructor's courses
$instructor_id = $_SESSION['user_id'];
$stmt_students = $conn->prepare("
    SELECT s.student_id, s.first_name, s.last_name, e.course_id, c.course_name
    FROM students s
    INNER JOIN enrollments e ON s.student_id = e.student_id
    INNER JOIN courses c ON e.course_id = c.course_id
    WHERE EXISTS (
        SELECT 1 
        FROM instructors i 
        WHERE i.instructor_id = ? AND JSON_CONTAINS(i.assigned_courses, JSON_QUOTE(c.course_id))
    )
");
$stmt_students->bind_param("i", $instructor_id);
$stmt_students->execute();
$students = $stmt_students->get_result();
$stmt_students->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="text-primary">Manage Students</h1>

    <?php if ($students->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Course</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No students found for your courses.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
