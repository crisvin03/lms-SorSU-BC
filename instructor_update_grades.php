<?php
session_start();
require 'config.php';

// Check if the user is logged in as an instructor
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

$instructor_id = $_SESSION['user_id']; // Logged-in instructor ID

// Fetch courses assigned to this instructor
$stmt_courses = $conn->prepare("
    SELECT c.course_id, c.course_name 
    FROM courses c 
    INNER JOIN instructors i ON JSON_CONTAINS(i.assigned_courses, JSON_QUOTE(c.course_id)) 
    WHERE i.instructor_id = ?
");
$stmt_courses->bind_param("i", $instructor_id);
$stmt_courses->execute();
$assigned_courses = $stmt_courses->get_result();
$stmt_courses->close();

// Handle grade updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grades'])) {
    $grades = $_POST['grades']; // Array of grades with student IDs

    foreach ($grades as $student_id => $grade) {
        $course_id = $_POST['course_id'];

        // Validate inputs
        if (!is_numeric($grade)) {
            $message = "Please provide valid grades for all students.";
            continue;
        }

        // Update or insert the grade
        $stmt_update = $conn->prepare("
            INSERT INTO grades (student_id, course_id, grade, updated_at) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE grade = ?, updated_at = NOW()
        ");
        $stmt_update->bind_param("iisi", $student_id, $course_id, $grade, $grade);
        $stmt_update->execute();
        $stmt_update->close();
    }

    $message = "Grades updated successfully.";
}

// Fetch students for a selected course
$students = [];
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    $stmt_students = $conn->prepare("
        SELECT s.student_id, s.first_name, s.last_name, g.grade 
        FROM students s
        LEFT JOIN grades g ON s.student_id = g.student_id AND g.course_id = ?
        WHERE EXISTS (
            SELECT 1 
            FROM enrollments e 
            WHERE e.student_id = s.student_id AND e.course_id = ?
        )
    ");
    $stmt_students->bind_param("ii", $course_id, $course_id);
    $stmt_students->execute();
    $students = $stmt_students->get_result();
    $stmt_students->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Grades</title>
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

        .form-label {
            font-weight: bold;
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
        .btn-maroon {
    background-color: #800000; /* Maroon color */
    color: white;
    border: none;
}

.btn-maroon:hover {
    background-color: #5c0000; /* Darker maroon on hover */
    color: white;
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
    <h1><i class="fas fa-graduation-cap me-2"></i>Update Student Grades</h1>
    <p class="lead">Update the grades for students enrolled in your courses</p>
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

        <!-- Course Selection -->
        <form method="GET" class="mb-3">
            <div class="mb-3">
                <label for="course_id" class="form-label">Select a Course</label>
                <select name="course_id" id="course_id" class="form-select" required>
                    <option value="" disabled selected>Select a course</option>
                    <?php while ($course = $assigned_courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['course_id']; ?>" 
                            <?php if (isset($_GET['course_id']) && $_GET['course_id'] == $course['course_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-maroon">View Students</button>
        </form>

        <!-- Students List -->
        <?php if (!empty($students)): ?>
            <form method="POST" class="mt-4">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Current Grade</th>
                        <th>Update Grade</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($student['grade'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <input type="number" name="grades[<?php echo $student['student_id']; ?>]" 
                                       class="form-control" required>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="update_grades" class="btn btn-success mt-3">Save Grades</button>
            </form>
        <?php elseif (isset($_GET['course_id'])): ?>
            <p class="no-students-msg">No students enrolled in this course.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date('Y'); ?> Update Grades Dashboard. All Rights Reserved.
</footer>

<!-- Bootstrap JS & FontAwesome -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html>
