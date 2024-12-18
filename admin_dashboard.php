<?php
// Include database connection
include 'config.php';

// Initialize variables
$total_users = 0;
$total_courses = 0;
$total_attendance = 0;
$average_grade = 0.00;

// Query to get total users
$query_users = "SELECT COUNT(*) AS total_users FROM users";
$result_users = $conn->query($query_users);
if ($result_users && $result_users->num_rows > 0) {
    $row = $result_users->fetch_assoc();
    $total_users = $row['total_users'];
}

// Query to get total courses
$query_courses = "SELECT COUNT(*) AS total_courses FROM courses";
$result_courses = $conn->query($query_courses);
if ($result_courses && $result_courses->num_rows > 0) {
    $row = $result_courses->fetch_assoc();
    $total_courses = $row['total_courses'];
}

// Query to get total attendance records
$query_attendance = "SELECT COUNT(*) AS total_attendance FROM attendance";
$result_attendance = $conn->query($query_attendance);
if ($result_attendance && $result_attendance->num_rows > 0) {
    $row = $result_attendance->fetch_assoc();
    $total_attendance = $row['total_attendance'];
}

// Query to calculate average grade
$query_grades = "SELECT AVG(grade) AS average_grade FROM grades";
$result_grades = $conn->query($query_grades);
if ($result_grades && $result_grades->num_rows > 0) {
    $row = $result_grades->fetch_assoc();
    $average_grade = $row['average_grade'];
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
            background-color: #800000;
            color: white;
            height: 100vh;
            position: fixed;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.active {
            width: 70px;
        }

        .sidebar .menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            text-decoration: none;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .sidebar .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar.active .menu-item span {
            display: none;
        }

        .sidebar.active h2 span {
            display: none;
        }

        .sidebar .menu-item:hover {
            color: #ff0000;
        }

        .sidebar .menu-item:hover i {
            color: #ff0000;
        }

        .sidebar h2 {
            margin-left: 10px;
            border-bottom: 1px solid #fff;
            padding-bottom: 5px;
        }

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

        .profile-dropdown {
            position: absolute;
            top: 10px;
            right: 20px;
        }

        .profile-btn {
            background-color: #800000;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }

        .profile-btn:hover {
            background-color: #ff0000;
        }
    </style>
</head>
<body class="bg-light">

<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="menuButton">
        <i class="fas fa-chevron-right"></i>
    </button>
    <h2><i class="fas fa-cogs"></i> <span>Admin</span></h2>
    <a href="admin_manage_users.php" class="menu-item"><i class="fas fa-users"></i> <span>Manage Users</span></a>
    <a href="admin_manage_courses.php" class="menu-item"><i class="fas fa-book"></i> <span>Manage Courses</span></a>
    <a href="admin_manage_departments.php" class="menu-item"><i class="fas fa-building"></i> <span>Manage Departments</span></a>
    <a href="admin_approve_users.php" class="menu-item"><i class="fas fa-check"></i> <span>Approve Pending Users</span></a>
    <a href="admin_manage_announcements.php" class="menu-item"><i class="fas fa-bullhorn"></i> <span>Manage Announcements</span></a>
    <a href="admin_profile.php" class="menu-item"><i class="fas fa-user"></i> <span>View Profile</span></a>
</div>

<div class="profile-dropdown">
    <div class="dropdown">
        <button class="btn profile-btn dropdown-toggle" type="button" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user"></i> Profile
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
            <li><a class="dropdown-item" href="admin_profile.php"><i class="fas fa-id-card"></i> View Profile</a></li>
            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </div>
</div>

<div class="content-container" id="contentContainer">
    <h1 class="content-header"><i class="fas fa-cogs"></i> Welcome to Your Dashboard</h1>
    <div class="card p-4">
        <h2 class="text-center text-maroon">Admin Reports</h2>

        <!-- Display total number of users -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text">
                    <i class="fas fa-users"></i> <?php echo $total_users; ?> users registered in the system.
                </p>
            </div>
        </div>

        <!-- Display total number of courses -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Courses</h5>
                <p class="card-text">
                    <i class="fas fa-book"></i> <?php echo $total_courses; ?> courses available in the system.
                </p>
            </div>
        </div>

        <!-- Display total attendance records -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Attendance Records</h5>
                <p class="card-text">
                    <i class="fas fa-clock"></i> <?php echo $total_attendance; ?> attendance records tracked in the system.
                </p>
            </div>
        </div>

        <!-- Display average grade -->
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Average Grade</h5>
                <p class="card-text">
                    <i class="fas fa-star"></i> <?php echo number_format($average_grade, 2); ?> is the average grade across all students.
                </p>
            </div>
        </div>

      <!-- Area Chart -->
<canvas id="myChart" style="height:400px;width:800px"></canvas>
<script type="text/javascript">
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
    type: 'line', // Line type chart for area chart effect
    data: {
        labels: ['Users', 'Courses', 'Attendance', 'Average Grade'],
        datasets: [{
            label: 'Reports',
            data: [
                <?php echo $total_users; ?>,
                <?php echo $total_courses; ?>,
                <?php echo $total_attendance; ?>,
                <?php echo number_format($average_grade, 2); ?>
            ],
            backgroundColor: 'rgba(128, 0, 0, 0.2)', // Maroon color with opacity
            borderColor: 'rgba(128, 0, 0, 1)', // Maroon border
            pointBackgroundColor: 'rgba(128, 0, 0, 1)', // Point color
            borderWidth: 2,
            fill: true, // Fill under the line for the area effect
            tension: 0.4 // Smooth the line
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#333' } // Darker tick color for visibility
            },
            x: {
                ticks: { color: '#333' }
            }
        },
        plugins: {
            legend: {
                display: true,
                labels: { color: '#333' } // Dark legend color
            }
        }
    }
});
</script>
        <!-- JavaScript code for chart generation -->
        <script src='https://cdn.jsdelivr.net/npm/chart.js'></script>

<script type='text/javascript'>
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
type:'bar',
data:{
labels:['Users', 'Courses', 'Attendance', 'Average Grade'],
datasets:[{
label:'Reports',
data:[<?php echo $total_users; ?>, <?php echo $total_courses; ?>, <?php echo $total_attendance; ?>, <?php echo number_format($average_grade, 2); ?>],
backgroundColor:[
'rgba(255,99,132,0.2)',
'rgba(54,162,235,0.2)',
'rgba(255,206,86,0.2)',
'rgba(75,192,192,0.2)'
],
borderColor:[
'rgba(255,99,132,1)',
'rgba(54,162,235,1)',
'rgba(255,206,86,1)',
'rgba(75,192,192,1)'
],
borderWidth:1
}]
},
options:{
scales:{
yAxes:[{
ticks:{
beginAtZero:true}
}]
}
}
});
</script>

<!-- Refined Line Chart -->
<canvas id="myLineChart" style="height:400px;width:800px"></canvas>
<script type="text/javascript">
var ctx = document.getElementById('myLineChart').getContext('2d');
var chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Monthly Reports',
            data: [
                <?php echo $total_users; ?>,
                <?php echo $total_courses; ?>,
                <?php echo $total_attendance; ?>,
                <?php echo number_format($average_grade, 2); ?>
            ],
            borderColor: 'rgba(54, 162, 235, 1)', // Blue line color
            backgroundColor: 'rgba(54, 162, 235, 0.2)', // Light fill under the line
            pointBackgroundColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4 // Smooth line curve
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#333' }
            },
            x: {
                ticks: { color: '#333' }
            }
        },
        plugins: {
            legend: {
                display: true,
                labels: { color: '#333' }
            }
        }
    }
});
</script>
<!-- JavaScript code for line chart generation -->
<script type='text/javascript'>
var ctx = document.getElementById('myLineChart').getContext('2d');
var chart = new Chart(ctx, {
type:'line',
data:{
labels:['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
datasets:[{
label:'Reports',
data:[
<?php echo $total_users; ?>,
<?php echo $total_courses; ?>,
<?php echo $total_attendance; ?>
],
backgroundColor:[
'rgba(255,99,132,0.2)',
'rgba(54,162,235,0.2)',
'rgba(255,206,86,0.2)'
],
borderColor:[
'rgba(255,99,132,1)',
'rgba(54,162,235,1)',
'rgba(255,206,86,1)'
],
borderWidth:1
}]
},
options:{
scales:{
yAxes:[{
ticks:{
beginAtZero:true}
}]
}
}
});
</script>

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
    menuButton.innerHTML = `<i class="${isActive ? 'fa-chevron-right' : 'fa-chevron-left'}"></i>`;
});

sidebar.addEventListener('mouseover', openSidebar);
sidebar.addEventListener('mouseleave', collapseSidebar);

window.addEventListener('load', () => {
    menuButton.innerHTML = `<i class='fa-chevron-right'></i>`;
});
</script>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>
