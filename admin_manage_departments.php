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
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        form {
            margin-bottom: 20px;
        }
        .message {
            color: green;
        }
    </style>
</head>
<body>
    <h1>Manage Departments</h1>

    <!-- Display success or error message -->
    <?php if (isset($message)) { ?>
        <p class="message"><?php echo $message; ?></p>
    <?php } ?>

    <!-- Form to Add Department -->
    <form method="POST">
        <label for="department_name">Department Name:</label>
        <input type="text" id="department_name" name="department_name" placeholder="Enter Department Name" required>
        <button type="submit" name="add_department">Add Department</button>
    </form>

    <!-- Display Existing Departments -->
    <h2>Existing Departments</h2>
    <?php
    // Fetch all departments
    $sql = "SELECT * FROM departments ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Created At</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row['department_id'] . "</td>
                    <td>" . $row['department_name'] . "</td>
                    <td>" . $row['created_at'] . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No departments found.</p>";
    }
    ?>
</body>
</html>
