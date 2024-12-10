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
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">Manage Announcements</h1>
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Add Announcement Form -->
    <form method="POST" enctype="multipart/form-data" class="mb-4">
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
            <input type="file" class="form-control" id="image" name="image" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Announcement</button>
    </form>

    <!-- List of Announcements -->
    <h2>All Announcements</h2>
    <ul class="list-group">
        <?php foreach ($announcements as $announcement): ?>
            <li class="list-group-item">
                <h5><?php echo htmlspecialchars($announcement['title']); ?></h5>
                <p><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                <small>Posted on: <?php echo htmlspecialchars($announcement['date_posted']); ?></small>
                <?php if (isset($announcement['image_path']) && !empty($announcement['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($announcement['image_path']); ?>" alt="Announcement Image" class="img-fluid mt-2">
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
