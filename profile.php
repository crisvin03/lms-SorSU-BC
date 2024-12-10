<?php
session_start();

// Check if the user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Database connection
include 'config.php';

// Fetch the student's department if not already in session
if (!isset($_SESSION['department']) && isset($_SESSION['department_id'])) {
    $stmt = $conn->prepare("SELECT department_name FROM departments WHERE department_id = ?");
    $stmt->bind_param("i", $_SESSION['department_id']);
    $stmt->execute();
    $stmt->bind_result($department_name);
    if ($stmt->fetch()) {
        $_SESSION['department'] = $department_name;
    }
    $stmt->close();
}

// Fetch student details
$stmt = $conn->prepare("SELECT first_name, last_name, email, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email, $profile_picture);
if ($stmt->fetch()) {
    $student_name = $first_name . " " . $last_name;
    $profile_picture = $profile_picture ?: 'placeholder.png';
} else {
    // Set default values if no data is found
    $student_name = "Unknown Student";
    $email = "N/A";
    $profile_picture = 'placeholder.png';
}
$stmt->close();

// Fetch the student's courses and grades
$stmt = $conn->prepare("SELECT course_name, grades.grade FROM courses
                        JOIN grades ON courses.id = grades.course_id
                        WHERE grades.student_id = ?");
if (!$stmt) {
    die("Query Error: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($course_name, $grade);
$courses_grades = [];
while ($stmt->fetch()) {
    $courses_grades[] = ['course_name' => $course_name, 'grade' => $grade];
}
$stmt->close();

// Fetch attendance information
$stmt = $conn->prepare("SELECT courses.course_name, 
                               (SUM(attendance.status = 'Present') / COUNT(*)) * 100 AS attendance_percentage
                        FROM courses
                        JOIN attendance ON courses.id = attendance.course_id
                        WHERE attendance.student_id = ?
                        GROUP BY courses.id");
if (!$stmt) {
    die("Query Error: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($course_name, $attendance_percentage);
$courses_attendance = [];
while ($stmt->fetch()) {
    $courses_attendance[] = ['course_name' => $course_name, 'attendance_percentage' => $attendance_percentage];
}
$stmt->close();

// Fetch announcements
$stmt = $conn->prepare("SELECT title, content, date_posted FROM announcements ORDER BY date_posted DESC LIMIT 5");
if (!$stmt) {
    die("Query Error: " . $conn->error);
}
$stmt->execute();
$stmt->bind_result($announcement_title, $announcement_content, $announcement_date);
$announcements = [];
while ($stmt->fetch()) {
    $announcements[] = [
        'title' => $announcement_title,
        'content' => $announcement_content,
        'date_posted' => $announcement_date
    ];
}
$stmt->close();

// Get department name from session
$department = isset($_SESSION['department']) ? $_SESSION['department'] : 'Department Not Found';
?>

<?php
// Determine which section to display based on the URL parameter
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Add custom styles here */
    </style>
</head>
<body>

    <!-- Sidebar and Main Content here -->

    <div class="main-content">
        <?php if ($section == 'profile'): ?>
            <!-- Profile Section -->
            <h2>Profile</h2>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body d-flex align-items-center">
                    <img src="uploads/<?php echo $profile_picture; ?>" alt="Profile Picture">
                    <div>
                        <h5><?php echo htmlspecialchars($student_name); ?></h5>
                        <p>Email: <?php echo htmlspecialchars($email); ?></p>
                        <p>Department: <?php echo htmlspecialchars($department); ?></p>
                    </div>
                </div>
            </div>

            <!-- Change Password Form here -->

        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Luxeira</p>
    </footer>

</body>
</html>
