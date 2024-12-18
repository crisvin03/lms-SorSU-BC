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
    LEFT JOIN instructors i ON c.instructor_courses_id = i.instructor_id
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
    <h1><i class="fas fa-book me-2"></i>My Courses</h1>
    <p class="lead">View your enrolled courses and details</p>
</div>

<!-- Main Content -->
<div class="container">
    <!-- Courses Table -->
    <div class="card">
        <h3 class="mb-4">Courses Overview</h3>
        <?php if (count($courses_grades) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
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
                                <td><?php echo htmlspecialchars($course['instructor_name']) ?: '<em>Not Assigned</em>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No Courses Available</h4>
                <p>Courses you enroll in will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> My Courses Dashboard. All Rights Reserved.
</footer>

<!-- Bootstrap JS & FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
