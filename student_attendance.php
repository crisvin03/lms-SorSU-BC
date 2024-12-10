<?php
session_start();
require 'config.php';

// Restrict access to logged-in students
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    die("Access denied! Students only.");
}

$user_id = $_SESSION['user_id'];

// Fetch attendance data
$attendance_query = $conn->query("
    SELECT c.course_name, a.date AS attendance_date, a.status 
    FROM attendance a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.student_id = $user_id
    ORDER BY a.date DESC
");

// Fetch attendance percentage per course
$percentage_query = $conn->query("
    SELECT c.course_name, 
           ROUND((SUM(a.status = 'present') / COUNT(*)) * 100, 2) AS attendance_percentage
    FROM attendance a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.student_id = $user_id
    GROUP BY c.course_id
");

$courses_attendance = $percentage_query->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">My Attendance</h1>
    
    <?php if (!empty($courses_attendance)): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Attendance Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses_attendance as $attendance): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($attendance['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($attendance['attendance_percentage']); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No attendance records available.</div>
    <?php endif; ?>

    <!-- Detailed Attendance Records Section -->
    <h2>Detailed Attendance Records</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Course</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($record = $attendance_query->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['attendance_date']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($record['status'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
