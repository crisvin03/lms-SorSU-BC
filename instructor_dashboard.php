<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
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

        .sidebar.active h2 span {
            display: none; /* Hide the "Instructor" text when collapsed */
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
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="menuButton">
        <i class="fas fa-chevron-right"></i>
    </button>
    <h2><i class="fas fa-chalkboard-teacher"></i> <span>Instructor</span></h2>
    <a href="instructor_courses.php" class="menu-item"><i class="fas fa-book"></i> <span>My Course</span></a>
    <a href="instructor_students.php" class="menu-item"><i class="fas fa-users"></i> <span>Manage Students</span></a>
    <a href="instructor_update_grades.php" class="menu-item"><i class="fas fa-pencil-alt"></i> <span>Update Grades</span></a>
    <a href="instructor_manage_attendance.php" class="menu-item"><i class="fas fa-calendar-check"></i> <span>Manage Attendance</span></a>
    <a href="instructor_profile.php" class="menu-item"><i class="fas fa-user"></i> <span>View Profile</span></a>
    <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i> <span>Log Out</span></a>
</div>

<!-- Main Content -->
<div class="content-container" id="contentContainer">
    <h1 class="content-header"><i class="fas fa-chalkboard"></i> Welcome to Your Dashboard</h1>
    <div class="card p-4">
        <h2 class="text-center text-maroon">Dashboard Overview</h2>
        <p class="text-center">This section contains an overview of instructor-specific features and tools.</p>
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
