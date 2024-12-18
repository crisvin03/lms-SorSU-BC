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
    $course_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($course_id) {
        // First, check if course is used in any enrollments
        $check_stmt = $conn->prepare("SELECT COUNT(*) AS enrollment_count FROM enrollments WHERE course_id = ?");
        $check_stmt->bind_param("i", $course_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $enrollment_count = $result->fetch_assoc()['enrollment_count'];

        if ($enrollment_count > 0) {
            $message = "Cannot delete course. It has active enrollments.";
            $message_type = "warning";
        } else {
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
        }
    } else {
        $message = "Invalid course ID.";
        $message_type = "danger";
    }

    
}

// Pagination setup
$limit = 10; // Courses per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = $search_query ? "WHERE course_name LIKE ?" : "";

// Fetch courses with pagination and optional search
if ($search_query) {
    $search_param = "%{$search_query}%";
    $stmt = $conn->prepare("SELECT * FROM courses {$search_condition} LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $search_param, $limit, $offset);
} else {
    $stmt = $conn->prepare("SELECT * FROM courses LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$courses = $stmt->get_result();

// Get total number of courses for pagination
$total_courses_query = $search_query 
    ? $conn->prepare("SELECT COUNT(*) AS total FROM courses {$search_condition}")
    : $conn->prepare("SELECT COUNT(*) AS total FROM courses");

if ($search_query) {
    $total_courses_query->bind_param("s", $search_param);
}
$total_courses_query->execute();
$total_courses = $total_courses_query->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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

        .admin-header {
            background: linear-gradient(to right, var(--maroon), var(--maroon-light));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--maroon);
            color: white;
        }

        .btn-maroon {
            background-color: var(--maroon);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-maroon:hover {
            background-color: var(--maroon-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        .pagination .page-link {
            color: var(--maroon);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--maroon);
            border-color: var(--maroon);
        }

        .search-container {
            margin-bottom: 20px;
        }

        .course-actions {
            display: flex;
            gap: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header text-center">
        <div class="container">
            <h1 class="display-5">
                <i class="fas fa-book-open me-2"></i>Course Management
            </h1>
            <p class="lead">Manage and Organize Your Courses</p>
        </div>
    </div>

    <div class="container">
        <!-- Feedback Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                <i class="fas <?php 
                    echo $message_type === 'success' ? 'fa-check-circle' : 
                         ($message_type === 'danger' ? 'fa-times-circle' : 'fa-info-circle'); 
                ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Search and Add Course Section -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="search-container">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search courses..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                        <button class="btn btn-maroon" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="admin_add_course.php" class="btn btn-maroon">
                    <i class="fas fa-plus me-2"></i>Add New Course
                </a>
            </div>
        </div>

        <!-- Courses Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course ID</th>
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
                                        <td><?php echo htmlspecialchars(substr($course['description'], 0, 100) . '...'); ?></td>
                                        <td class="course-actions">
                                            <a href="admin_edit_courses.php?id=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="admin_manage_courses.php?delete=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this course?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No courses found</h4>
                                        <p>Create your first course or adjust your search criteria</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="admin_manage_courses.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>