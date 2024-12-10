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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        .message {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .btn-custom {
            background-color: #800000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #800000;
        }
        table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        table th {
            background-color: #007bff;
            color: #fff;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Manage Departments</h1>

        <!-- Display success or error message -->
        <?php if (isset($message)) { ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php } ?>

        <!-- Form to Add Department -->
        <form method="POST" class="d-flex justify-content-between align-items-center">
            <input type="text" id="department_name" name="department_name" class="form-control me-2" placeholder="Enter Department Name" required>
            <button type="submit" name="add_department" class="btn btn-custom">Add Department</button>
        </form>

        <!-- Display Existing Departments -->
        <h2 class="text-center mt-4">Existing Departments</h2>
        <?php
        // Fetch all departments
        $sql = "SELECT * FROM departments ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Department Name</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['department_id'] . "</td>
                        <td>" . $row['department_name'] . "</td>
                        <td>" . $row['created_at'] . "</td>
                      </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='text-center'>No departments found.</p>";
        }
        ?>
    </div>
</body>
</html>
