<?php
session_start();
require 'config.php';

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch course details if an ID is passed
$course_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $course_name = filter_var($_POST['course_name'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

    if ($course_name && $description && $course_id) {
        $stmt = $conn->prepare("UPDATE courses SET course_name = ?, description = ? WHERE course_id = ?");
        $stmt->bind_param("ssi", $course_name, $description, $course_id);

        if ($stmt->execute()) {
            header("Location: admin_manage_courses.php?message=Course updated successfully.&message_type=success");
            exit();
        } else {
            $error_message = "Error updating course.";
        }

        $stmt->close();
    } else {
        $error_message = "Invalid input.";
    }
}

// Fetch course details to pre-fill the form
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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
    <h1 class="text-maroon">Edit Course</h1>
    
    <!-- Display error message if any -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="course_name" class="form-label">Course Name</label>
            <input type="text" class="form-control" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($course['description']); ?></textarea>
        </div>
        <button type="submit" name="submit" class="btn btn-maroon">Save Changes</button>
        <a href="admin_manage_courses.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
