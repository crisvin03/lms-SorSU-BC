<?php
session_start();
include 'config.php';

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
$semester = isset($_GET['semester']) ? $_GET['semester'] : null;

// Fetch courses assigned to the instructor
$stmt_courses = $conn->prepare("SELECT course_id, course_name FROM courses WHERE instructor_courses_id = ?");
$stmt_courses->bind_param("i", $_SESSION['user_id']);
$stmt_courses->execute();
$courses_result = $stmt_courses->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);
$stmt_courses->close();

$attendance_records = [];
if ($course_id && $semester) {
    // Fetch attendance records
    $query = $conn->prepare("
        SELECT students.id, students.first_name, students.last_name, attendance.date, attendance.status 
        FROM students 
        LEFT JOIN attendance ON students.id = attendance.student_id 
        WHERE attendance.course_id = ? AND attendance.semester = ?
    ");
    $query->bind_param("is", $course_id, $semester);
    if ($query->execute()) {
        $result = $query->get_result();
        $attendance_records = $result->fetch_all(MYSQLI_ASSOC);
    }
    $query->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance</title>
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

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background-color: white;
            padding: 20px;
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

        .table-hover tbody tr:hover {
            background-color: #f9ecec;
        }

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

        footer {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 10px 0;
            text-align: center;
            margin-top: 30px;
        }

        /* Custom Maroon Button */
        .btn-maroon {
            background-color: var(--maroon);
            border-color: var(--maroon);
        }

        .btn-maroon:hover {
            background-color: var(--maroon-dark);
            border-color: var(--maroon-dark);
        }

        .btn-maroon:focus, .btn-maroon:active {
            background-color: var(--maroon-dark);
            border-color: var(--maroon-dark);
        }

        .form-control, .form-select {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1><i class="fas fa-users me-2"></i>Manage Attendance</h1>
    <p class="lead">Track student attendance for your courses</p>
</div>

<!-- Main Content -->
<div class="container">
    <div class="card">
        <!-- Course and Semester Selection -->
        <form method="GET">
            <div class="mb-3">
                <label for="course_id" class="form-label">Course:</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php echo ($course_id == $course['course_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="semester" class="form-label">Semester:</label>
                <select name="semester" id="semester" class="form-select">
                    <option value="1" <?php echo ($semester == '1') ? 'selected' : ''; ?>>Semester 1</option>
                    <option value="2" <?php echo ($semester == '2') ? 'selected' : ''; ?>>Semester 2</option>
                </select>
            </div>
            <button type="submit" class="btn btn-maroon text-white">View Attendance</button>
        </form>

        <!-- Attendance Table -->
        <?php if ($attendance_records): ?>
            <table class="table mt-4 table-hover">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($course_id && $semester): ?>
            <div class="empty-state">
                <i class="fas fa-exclamation-circle fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No attendance records found</h4>
                <p>There are no attendance records for this course and semester.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> Attendance Management System. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>

