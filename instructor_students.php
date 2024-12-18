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
    <style>
        :root {
            --maroon: #800000;
            --maroon-light: #a52a2a;
            --maroon-dark: #5c0000;
            --light-gray: #f4f6f9;
        }

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header Section */
        .header {
            background: linear-gradient(to right, var(--maroon), var(--maroon-light));
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 0 0 20px 20px;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: bold;
        }

        /* Card Section */
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: white;
            padding: 30px;
            margin-bottom: 30px;
        }

        .table {
            margin-bottom: 0;
            border-radius: 10px;
            overflow: hidden;
            background-color: white;
        }

        .table thead {
            background-color: var(--maroon);
            color: white;
        }

        .table-striped tbody tr:nth-child(odd) {
            background-color: #f9ecec;
        }

        .table-hover tbody tr:hover {
            background-color: #f0e0e0;
        }

        .table th, .table td {
            padding: 15px;
            text-align: center;
        }

        .alert {
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .no-students-msg {
            text-align: center;
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 30px;
        }

        .no-students-msg i {
            font-size: 3rem;
            color: #a52a2a;
        }

        footer {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 20px 0;
            text-align: center;
            margin-top: 50px;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1><i class="fas fa-user-graduate me-2"></i>Manage Students</h1>
    <p class="lead">View and manage students assigned to your courses</p>
</div>

<!-- Main Content -->
<div class="container">
    <div class="card">
        <!-- Success/Error Messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Students Table -->
        <?php if ($students->num_rows > 0): ?>
            <table class="table table-striped table-hover">
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
            <div class="no-students-msg">
                <i class="fas fa-exclamation-circle"></i>
                <p>No students found for your courses.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> Manage Students Dashboard. All Rights Reserved.
</footer>

<!-- Bootstrap JS & FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
