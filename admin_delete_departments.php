<?php
// Database connection
require 'config.php';

if (isset($_GET['id'])) {
    $department_id = intval($_GET['id']);

    // Delete the department
    $sql = "DELETE FROM departments WHERE department_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);

    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: admin_manage_departments.php?message=Department deleted successfully.");
        exit();
    } else {
        // Redirect with failure message
        header("Location: admin_manage_departments.php?message=Failed to delete department.");
        exit();
    }
} else {
    die("Invalid request.");
}
?>
