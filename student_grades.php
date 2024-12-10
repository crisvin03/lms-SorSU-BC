<?php
session_start();
require 'config.php';

// Restrict access to logged-in students
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    die("Access denied! Students only.");
}

$user_id = $_SESSION['user_id'];

// Fetch grades
$grades_query = $conn->prepare("
    SELECT c.course_name, g.grade 
    FROM grades g
    JOIN courses c ON g.course_id = c.course_id
    WHERE g.student_id = ?
");
$grades_query->bind_param("i", $user_id);
$grades_query->execute();
$result = $grades_query->get_result();
$courses_grades = $result->fetch_all(MYSQLI_ASSOC);

$grades_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">My Grades</h1>
    
    <?php if (count($courses_grades) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses_grades as $course_grade): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course_grade['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course_grade['grade']) ?: 'Not Graded'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No grades available.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
