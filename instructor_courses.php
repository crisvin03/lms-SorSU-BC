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
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-primary">My Courses</h1>
    <p>Welcome, Instructor! Here are the courses assigned to you.</p>

    <!-- Success/Error Messages -->
    <?php if (isset($message)): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Course List -->
    <h3>Assigned Courses</h3>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>