<?php
session_start();
require 'config.php';

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables for feedback messages
$message = '';
$message_type = '';

// Handle course deletion
if (isset($_GET['delete'])) {
    $course_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT); // Validate as integer
    if ($course_id) {
        $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);

        if ($stmt->execute()) {
            $message = "Course deleted successfully.";
            $message_type = "success";
        } else {
            $message = "Error deleting course.";
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Invalid course ID.";
        $message_type = "danger";
    }
}

// Pagination setup
$limit = 10; // Courses per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page
$offset = ($page - 1) * $limit;

// Fetch courses with pagination
$stmt = $conn->prepare("SELECT * FROM courses LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$courses = $stmt->get_result();

// Get total number of courses for pagination
$total_courses = $conn->query("SELECT COUNT(*) AS total FROM courses")->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-maroon {
            color: #800000;
        }
        .btn-maroon {
            background-color: #800000;
            color: #fff;
        }
        .btn-maroon:hover {
            background-color: #b30000;
            color: #fff;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">Manage Courses</h1>
    <a href="admin_add_course.php" class="btn btn-maroon mb-3">Add New Course</a>

    <!-- Display feedback messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Courses Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Course Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($courses->num_rows > 0): ?>
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($course['description']); ?></td>
                        <td>
                            <a href="admin_edit_courses.php?id=<?php echo $course['course_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="admin_manage_courses.php?delete=<?php echo $course['course_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No courses found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="admin_manage_courses.php?page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
