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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-light: #a52a2a;
            --maroon-dark: #5c0000;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header Section */
        .header {
            background: linear-gradient(to right, var(--maroon), var(--maroon-light));
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background-color: white;
            margin-bottom: 20px;
            padding: 20px;
        }

        .table {
            margin-bottom: 0;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--maroon);
            color: white;
        }

        .table-hover tbody tr:hover {
            background-color: #f9ecec;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
        }

        .alert {
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Footer */
        footer {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 10px 0;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1><i class="fas fa-calendar-check me-2"></i>My Attendance</h1>
    <p class="lead">Monitor your attendance records</p>
</div>

<!-- Main Content -->
<div class="container">
    <!-- Overall Attendance Summary -->
    <div class="card">
        <h3 class="mb-4">Attendance Summary</h3>
        <?php if (!empty($courses_attendance)): ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
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
            <div class="empty-state">
                <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No attendance records available</h4>
                <p>Attendance records will appear here once available.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Detailed Attendance Records -->
    <div class="card">
        <h3 class="mb-4">Detailed Attendance Records</h3>
        <?php if (!empty($attendance_query) && $attendance_query->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
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
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No detailed attendance records</h4>
                <p>Detailed records will appear here once available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> My Attendance Dashboard. All Rights Reserved.
</footer>

<!-- Bootstrap JS & FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
