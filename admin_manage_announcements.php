<?php
session_start();

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Handle form submission for adding announcements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['content'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);

    if ($stmt->execute()) {
        $success_message = "Announcement added successfully!";
    } else {
        $error_message = "Failed to add announcement. Please try again.";
    }

    $stmt->close();
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
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
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
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
