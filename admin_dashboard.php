<?php
session_start();

// Ensure only admin users can access this page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Sidebar styling */
        .sidebar {
            width: 250px;
            background-color: #800000; /* Menu background color */
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.active {
            width: 70px; /* Collapsed sidebar width */
        }

        .sidebar .menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            text-decoration: none;
            color: white; /* Text color remains white */
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .sidebar .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar.active .menu-item span {
            display: none; /* Hide text when menu is collapsed */
        }

        .sidebar.active h2 span {
            display: none; /* Hide the "Admin" text when collapsed */
        }

        /* Only change text color to red on hover, not the background */
        .sidebar .menu-item:hover {
            color: #ff0000; /* Change text color to red on hover */
        }

        .sidebar .menu-item:hover .menu-item i {
            color: #ff0000; /* Also change the icon color to red */
        }

        .sidebar h2 {
            margin-left: 10px;
            border-bottom: 1px solid #fff; /* Add separator line */
            padding-bottom: 5px; /* Ensure the text doesn't overlap with the border */
        }

        /* Toggle button */
        .toggle-btn {
            position: absolute;
            top: 10px;
            right: -25px;
            background-color: transparent;
            border: none;
            border-radius: 50%;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
        }

        .content-container {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .content-container.expand {
            margin-left: 70px;
        }
    </style>
</head>
<body class="bg-light">

<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="menuButton">
        <i class="fas fa-chevron-right"></i>
    </button>
    <h2><i class="fas fa-cogs"></i> <span>Admin</span></h2> <!-- The "Admin" text is hidden when collapsed -->
    <a href="admin_manage_users.php" class="menu-item"><i class="fas fa-users"></i> <span>Manage Users</span></a>
    <a href="admin_manage_courses.php" class="menu-item"><i class="fas fa-book"></i> <span>Manage Courses</span></a>
    <a href="admin_manage_departments.php" class="menu-item"><i class="fas fa-building"></i> <span>Manage Departments</span></a>
    <a href="admin_approve_users.php" class="menu-item"><i class="fas fa-check"></i> <span>Approve Pending Users</span></a>
    <a href="admin_manage_announcements.php" class="menu-item"><i class="fas fa-bullhorn"></i> <span>Manage Announcements</span></a>
    <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
</div>

<div class="content-container" id="contentContainer">
    <h1 class="content-header"><i class="fas fa-cogs"></i> Welcome to Your Dashboard</h1>
    <div class="card p-4">
        <h2 class="text-center text-maroon">Dashboard Overview</h2>
        <p class="text-center">This section contains an overview of admin-specific features and tools.</p>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const contentContainer = document.getElementById('contentContainer');
    const menuButton = document.getElementById('menuButton');

    const openSidebar = () => {
        sidebar.classList.remove('active');
        contentContainer.classList.remove('expand');
    };

    const collapseSidebar = () => {
        sidebar.classList.add('active');
        contentContainer.classList.add('expand');
    };

    menuButton.addEventListener('click', () => {
        const isActive = sidebar.classList.contains('active');
        if (isActive) {
            openSidebar();
        } else {
            collapseSidebar();
        }
        menuButton.innerHTML = `<i class="fas ${isActive ? 'fa-chevron-right' : 'fa-chevron-left'}"></i>`;
    });

    sidebar.addEventListener('mouseover', openSidebar);
    sidebar.addEventListener('mouseleave', collapseSidebar);

    window.addEventListener('load', () => {
        menuButton.innerHTML = `<i class="fas fa-chevron-right"></i>`;
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
