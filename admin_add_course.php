<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Access denied! Admins only.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO courses (course_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $course_name, $description);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Course added successfully!";
        header("Location: admin_manage_courses.php");
        exit();
    } else {
        $error = "Failed to add course.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
    <style>
        .bg-maroon {
            background-color: #8B0A1A;
        }
        .text-maroon {
            color: #8B0A1A;
        }
        .btn-maroon {
            background-color: #8B0A1A;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-maroon:hover {
            background-color: #6f0a0a;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-maroon">
        <div class="container-fluid">
            <a class="navbar-brand text-light text-center" href="#">Course Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="admin_manage_courses.php">Manage Courses</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-maroon text-light">
                        <h2 class="card-title">Add New Course</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="course_name" class="form-label">Course Name</label>
                                <input type="text" name="course_name" id="course_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-maroon">Add Course</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>