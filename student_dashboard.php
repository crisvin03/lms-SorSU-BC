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
$stmt = $conn->prepare("SELECT courses.course_name, grades.grade FROM courses
                        JOIN grades ON courses.course_id = grades.course_id
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
                        JOIN attendance ON courses.course_id = attendance.course_id
                        WHERE attendance.student_id = ?
                        GROUP BY courses.course_id");
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
$department = isset($_SESSION['department']) ? $_SESSION['department'] : 'DCIT';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    
    <style>
        /* Sidebar styling */
        .sidebar {
            width: 250px;
            background-color: #800000; /* Menu background color */
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.active {
            width: 70px; /* Collapsed sidebar width */
        }

        .sidebar .menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            text-decoration: none;
            color: white; /* Text color remains white */
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .sidebar .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar.active .menu-item span {
            display: none; /* Hide text when menu is collapsed */
        }

        /* Only change text color to red on hover, not the background */
        .sidebar .menu-item:hover {
            color: #ff0000; /* Change text color to red on hover */
        }

        .sidebar .menu-item:hover .menu-item i {
            color: #ff0000; /* Also change the icon color to red */
        }

        .sidebar h2 {
            margin-left: 10px;
            border-bottom: 1px solid #fff; /* Add separator line */
            padding-bottom: 5px; /* Ensure the text doesn't overlap with the border */
        }

        .sidebar.active h2 span {
            display: none; /* Hide the "Student" text when collapsed */
        }

        /* Toggle button */
        .toggle-btn {
            position: absolute;
            top: 10px;
            right: -25px;
            background-color: transparent;
            border: none;
            border-radius: 50%;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
        }

        .content-container {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .content-container.expand {
            margin-left: 70px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="menuButton">
        <i class="fas fa-chevron-right"></i>
    </button>
    <h2><i class="fas fa-chalkboard-teacher"></i> <span>Student</span></h2>
    <a href="student_grades.php" class="menu-item"><i class="fas fa-book"></i> <span>Grades</span></a>
    <a href="student_attendance.php" class="menu-item"><i class="fas fa-users"></i> <span>Attendance</span></a>
    <a href="student_courses.php" class="menu-item"><i class="fas fa-calendar-check"></i> <span>Courses</span></a>
    <a href="student_profile.php" class="menu-item"><i class="fas fa-user"></i> <span>View Profile</span></a>
    <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
</div>

<!-- Main Content -->
<div class="content-container" id="contentContainer">
    <h1 class="content-header"><i class="fas fa-chalkboard"></i> Welcome to Your Dashboard</h1>
    <div class="card p-4">
        <h2 class="text-center text-maroon">Dashboard Overview</h2>
        <p class="text-center">This section contains an overview of instructor-specific features and tools.</p>
    </div>
   <!-- Announcements -->
   <div id="announcements-section" class="mb-4">
                <h3>Announcements</h3>
                <?php if (count($announcements) > 0): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="card mb-2">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                <small class="text-muted">Posted on: <?php echo htmlspecialchars($announcement['date_posted']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <p>No announcements available.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
                    
<script>
    const sidebar = document.getElementById('sidebar');
    const contentContainer = document.getElementById('contentContainer');
    const menuButton = document.getElementById('menuButton');

    const openSidebar = () => {
        sidebar.classList.remove('active');
        contentContainer.classList.remove('expand');
    };

    const collapseSidebar = () => {
        sidebar.classList.add('active');
        contentContainer.classList.add('expand');
    };

    menuButton.addEventListener('click', () => {
        const isActive = sidebar.classList.contains('active');
        if (isActive) {
            openSidebar();
        } else {
            collapseSidebar();
        }
        menuButton.innerHTML = `<i class="fas ${isActive ? 'fa-chevron-right' : 'fa-chevron-left'}"></i>`;
    });

    sidebar.addEventListener('mouseover', openSidebar);
    sidebar.addEventListener('mouseleave', collapseSidebar);

    window.addEventListener('load', () => {
        menuButton.innerHTML = `<i class="fas fa-chevron-right"></i>`;
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
