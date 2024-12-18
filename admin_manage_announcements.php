<?php 
session_start();

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Check if upload directory exists, if not, create it
$target_dir = "uploads/announcements/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true); // Create the directory with appropriate permissions
}

// Handle form submission for adding announcements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'], $_FILES['image'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Handling image upload
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a valid image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        $error_message = "File is not an image.";
    } elseif ($_FILES["image"]["size"] > 500000) { // 500KB limit
        $error_message = "Sorry, your file is too large.";
    } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
        $error_message = "Sorry, only JPG, JPEG, & PNG files are allowed.";
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Insert announcement into database with image path
            $stmt = $conn->prepare("INSERT INTO announcements (title, content, image_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $content, $target_file);

            if ($stmt->execute()) {
                $success_message = "Announcement added successfully!";
            } else {
                $error_message = "Failed to add announcement. Please try again.";
            }

            $stmt->close();
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch all announcements
$result = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");
$announcements = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header text-center">
        <div class="container">
            <h1 class="display-5">
                <i class="fas fa-bullhorn me-2"></i>Announcements Management
            </h1>
            <p class="lead">Create, Update, and Manage Announcements</p>
        </div>
    </div>

    <div class="container">
        <!-- Feedback Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add Announcement Form -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Add New Announcement</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Image</label>
                        <input type="file" class="form-control" id="image" name="image">
                    </div>
                    <button type="submit" class="btn btn-maroon">Add Announcement</button>
                </form>
            </div>
        </div>

        <!-- List of Announcements -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">All Announcements</h3>
                <?php if (!empty($announcements)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($announcements as $announcement): ?>
                            <li class="list-group-item">
                                <h5><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                <small class="text-muted">Posted on: <?php echo htmlspecialchars($announcement['date_posted']); ?></small>
                                <?php if (!empty($announcement['image_path'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo htmlspecialchars($announcement['image_path']); ?>" alt="Announcement Image" class="img-fluid rounded">
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No announcements available</h4>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
