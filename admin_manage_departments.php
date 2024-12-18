<?php
// Include the database connection
include 'config.php'; 

// Insert "DICT" and "BME" departments if they don't exist already
$default_departments = ['DICT', 'BME'];

foreach ($default_departments as $department_name) {
    // Check if department already exists
    $stmt = $conn->prepare("SELECT * FROM departments WHERE department_name = ?");
    $stmt->bind_param("s", $department_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If the department doesn't exist, insert it
    if ($result->num_rows == 0) {
        $insert_stmt = $conn->prepare("INSERT INTO departments (department_name) VALUES (?)");
        $insert_stmt->bind_param("s", $department_name);
        $insert_stmt->execute();
    }
    $stmt->close();
}

// Handle form submission for adding a new department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    $department_name = $_POST['department_name'];

    // Insert the new department into the database
    $stmt = $conn->prepare("INSERT INTO departments (department_name) VALUES (?)");
    $stmt->bind_param("s", $department_name);

    if ($stmt->execute()) {
        $message = "Department added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments</title>
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
            text-align: center;
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

        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: var(--maroon);
            color: white;
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
    <div class="admin-header">
        <h1 class="display-5"><i class="fas fa-building me-2"></i>Manage Departments</h1>
        <p class="lead">Add, Edit, and Organize Your Departments</p>
    </div>

    <div class="container">
        <!-- Display Success or Error Message -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add Department Form -->
        <div class="mb-3 text-start">
            <form method="POST" class="d-flex">
                <input type="text" id="department_name" name="department_name" class="form-control me-2" placeholder="Enter Department Name" required>
                <button type="submit" name="add_department" class="btn btn-maroon">
                    <i class="fas fa-plus me-2"></i>Add Department
                </button>
            </form>
        </div>

        <!-- Departments Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department Name</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch all departments
                            $sql = "SELECT * FROM departments ORDER BY created_at DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['department_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        <td>
                                            <a href="admin_delete_departments.php?id=<?php echo $row['department_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this department?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="empty-state">
                                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No departments found</h4>
                                        <p>Add your first department to manage them here.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-3 bg-light text-muted">
        &copy; <?php echo date('Y'); ?> Admin Dashboard. All Rights Reserved.
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>