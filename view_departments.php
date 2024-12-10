<?php
session_start();

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include 'config.php';

// Fetch all departments from the database
$sql = "SELECT * FROM departments ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Departments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-maroon">View Departments</h1>
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <?php if ($result->num_rows > 0) { ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display the departments
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row['department_id'] . "</td>
                            <td>" . $row['department_name'] . "</td>
                            <td>" . $row['created_at'] . "</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No departments found.</p>
    <?php } ?>

</div>

</body>
</html>
