<?php
session_start();
require 'config.php';

// Check if the user is logged in and has the instructor role
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

$instructor_id = $_SESSION['user_id']; // Get the logged-in instructor's ID

// Handle Add Course Assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_course'])) {
    $course_id = $_POST['course_id'];

    // Prepare statement to assign the course to the instructor
    $stmt = $conn->prepare("INSERT INTO instructor_courses (instructor_id, course_id) VALUES (?, ?)");
$stmt->bind_param("ii", $instructor_db_id, $course_id);

if ($stmt->execute()) {
    $message = "Course assigned successfully.";
} else {
    $message = "Error assigning course: " . $stmt->error;
}
$stmt->close();
}

// Fetch courses assigned to the instructor
$stmt = $conn->prepare("
    SELECT c.course_id, c.course_name, c.description, ic.assigned_date 
    FROM courses c
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ?
");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();

// Fetch all courses for dropdown (optional)
$all_courses_stmt = $conn->prepare("SELECT course_id, course_name FROM courses");
$all_courses_stmt->execute();
$all_courses = $all_courses_stmt->get_result();
$all_courses_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
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

        .table-hover tbody tr:hover {
            background-color: #f9ecec;
        }

        .list-group-item {
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .alert {
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Form Styling */
        .form-select, .form-label, .btn {
            border-radius: 10px;
        }

        .form-select:focus, .btn:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background-color: var(--maroon);
            border-color: var(--maroon);
        }

        .btn-primary:hover {
            background-color: var(--maroon-dark);
            border-color: var(--maroon-dark);
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
    <h1><i class="fas fa-chalkboard-teacher me-2"></i>My Courses</h1>
    <p class="lead">Manage the courses assigned to you</p>
</div>

<!-- Main Content -->
<div class="container">
    <div class="card">
        <h3 class="text-primary">Assigned Courses</h3>
        <p>Welcome, Instructor! Here are the courses assigned to you.</p>

        <!-- Success/Error Messages -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Course List -->
        <ul class="list-group">
            <?php if ($courses->num_rows > 0): ?>
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($course['course_name']); ?></strong>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <small>Assigned on: <?php echo htmlspecialchars($course['assigned_date']); ?></small>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="list-group-item">No courses assigned to you.</li>
            <?php endif; ?>
        </ul>

        <!-- Add Course Assignment -->
        <h3 class="mt-4">Assign a New Course</h3>
        <form method="POST" class="mt-3">
            <div class="mb-3">
                <label for="course_id" class="form-label">Select a Course</label>
                <select name="course_id" id="course_id" class="form-select" required>
                    <option value="" disabled selected>Select a course</option>
                    <?php while ($course = $all_courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['course_id']; ?>">
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="assign_course" class="btn btn-primary">Assign Course</button>
        </form>
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
