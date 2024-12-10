<?php
session_start();
include 'config.php';

// Check if the user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

// Fetch course ID and semester from GET parameters
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
$semester = isset($_GET['semester']) ? $_GET['semester'] : null;

if (!$course_id || !$semester) {
    die("Invalid parameters.");
}

// Fetch students' attendance data
$query = $conn->prepare("SELECT students.id, students.first_name, students.last_name, attendance.date, attendance.status 
                        FROM students 
                        LEFT JOIN attendance ON students.id = attendance.student_id 
                        WHERE students.course_id = ? AND attendance.semester = ?");
$query->bind_param("is", $course_id, $semester);
if ($query->execute()) {
    $result = $query->get_result();
    $attendance_records = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error retrieving attendance records: " . $conn->error);
}

$query->close();

// Handle attendance update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_data = $_POST['attendance'];

    foreach ($attendance_data as $student_id => $status) {
        $stmt = $conn->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND semester = ?");
        $stmt->bind_param("sis", $status, $student_id, $semester);
        if (!$stmt->execute()) {
            $error_message = "Failed to update attendance for student ID: $student_id";
        }
        $stmt->close();
    }

    if (!isset($error_message)) {
        $success_message = "Attendance updated successfully!";
    }
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
        body {
            background-color: #f8f9fa;
        }
        .attendance-card {
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-maroon">Manage Attendance</h2>
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card attendance-card mb-4">
        <div class="card-body">
            <form method="POST">
                <table class="table table-bordered">
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
                                <td>
                                    <select name="attendance[<?php echo htmlspecialchars($record['id']); ?>]" class="form-control">
                                        <option value="Present" <?php echo $record['status'] === 'Present' ? 'selected' : ''; ?>>Present</option>
                                        <option value="Absent" <?php echo $record['status'] === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                        <option value="Late" <?php echo $record['status'] === 'Late' ? 'selected' : ''; ?>>Late</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">Update Attendance</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
