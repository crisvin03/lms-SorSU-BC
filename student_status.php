<?php
session_start();
require 'config.php';

// Restrict access to logged-in students
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    die("Access denied! Students only.");
}

$user_id = $_SESSION['user_id'];

// Fetch the enrollment statuses for the student
$query = $conn->prepare("
    SELECT c.course_name, e.status, e.updated_at
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.student_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$enrollment_statuses = $result->fetch_all(MYSQLI_ASSOC);
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Status</title>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .table {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: var(--maroon);
            color: #ffffff;
        }

        .table tbody tr:hover {
            background-color: #f2f2f2;
        }

        .status-icon {
            margin-right: 5px;
        }

        .btn-maroon {
            background-color: var(--maroon);
            color: white;
            border: none;
        }

        .btn-maroon:hover {
            background-color: var(--maroon-dark);
            color: white;
        }

        footer {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 10px 0;
            text-align: center;
            margin-top: 30px;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .alert {
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1><i class="fas fa-graduation-cap me-2"></i> My Enrollment Status</h1>
    <p class="lead">View the status of your course enrollments</p>
</div>

<div class="container">

    <!-- Enrollment Table -->
    <?php if (count($enrollment_statuses) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollment_statuses as $status): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($status['course_name']); ?></td>
                            <td>
                                <?php 
                                    switch ($status['status']) {
                                        case 'Enrolled':
                                            echo '<i class="fas fa-user-check status-icon text-success"></i> Enrolled';
                                            break;
                                        case 'Pending':
                                            echo '<i class="fas fa-hourglass-half status-icon text-warning"></i> Pending';
                                            break;
                                        case 'Waitlisted':
                                            echo '<i class="fas fa-clipboard-list status-icon text-secondary"></i> Waitlisted';
                                            break;
                                        case 'Rejected':
                                            echo '<i class="fas fa-times-circle status-icon text-danger"></i> Rejected';
                                            break;
                                        default:
                                            echo '<i class="fas fa-question-circle status-icon text-muted"></i> Unknown';
                                    }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($status['updated_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No enrollment status available</h4>
            <p>Your enrollment status will appear here once available.</p>
        </div>
    <?php endif; ?>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> Enrollment System. All Rights Reserved.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
