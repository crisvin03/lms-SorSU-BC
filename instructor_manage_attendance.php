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
$stmt_courses = $conn->prepare("SELECT course_id, course_name FROM courses WHERE instructor_course_id = ?");
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
</head>
<body>
<div class="container mt-5">
    <h2 class="text-maroon">Manage Attendance</h2>

    <form method="GET">
        <div class="mb-3">
            <label for="course_id">Course:</label>
            <select name="course_id" id="course_id" class="form-control">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php echo ($course_id == $course['course_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="semester">Semester:</label>
            <select name="semester" id="semester" class="form-control">
                <option value="1" <?php echo ($semester == '1') ? 'selected' : ''; ?>>Semester 1</option>
                <option value="2" <?php echo ($semester == '2') ? 'selected' : ''; ?>>Semester 2</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">View Attendance</button>
    </form>

    <?php if ($attendance_records): ?>
        <table class="table mt-4">
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
        <p>No attendance records found for this course and semester.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
