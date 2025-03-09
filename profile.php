<?php
session_start();
include 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Fetch task statistics for the logged-in user
$sqlTotalTasks = "SELECT COUNT(*) as total FROM tasks WHERE userid = ?";
$sqlPendingTasks = "SELECT COUNT(*) as pending FROM tasks WHERE userid = ? AND taskstatus = 'Pending'";
$sqlCompletedTasks = "SELECT COUNT(*) as completed FROM tasks WHERE userid = ? AND taskstatus = 'Completed'";

$stmtTotal = $conn->prepare($sqlTotalTasks);
$stmtTotal->bind_param("i", $userid);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$totalTasks = $resultTotal->fetch_assoc()['total'] ?? 0;

$stmtPending = $conn->prepare($sqlPendingTasks);
$stmtPending->bind_param("i", $userid);
$stmtPending->execute();
$resultPending = $stmtPending->get_result();
$pendingTasks = $resultPending->fetch_assoc()['pending'] ?? 0;

$stmtCompleted = $conn->prepare($sqlCompletedTasks);
$stmtCompleted->bind_param("i", $userid);
$stmtCompleted->execute();
$resultCompleted = $stmtCompleted->get_result();
$completedTasks = $resultCompleted->fetch_assoc()['completed'] ?? 0;

$stmtTotal->close();
$stmtPending->close();
$stmtCompleted->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body id="body-pd">

    <!-- Navbar -->
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__brand">
                    <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                    <span class="nav__logo">ToDoze</span>
                </div>
                <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                        <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Home</span>
                    </a>
                    <a href="task.php" class="nav__link">
                        <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Task</span>
                    </a>
                    <a href="project.php" class="nav__link">
                        <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Project</span>
                    </a>
                    <a href="review.php" class="nav__link">
                        <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Review</span>
                    </a>
                    <a href="profile.php" class="nav__link active">
                        <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Profile</span>
                    </a>
                </div>
            </div>
            <a href="logout.php" class="nav__link logout">
                <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                <span class="nav__name">Log Out</span>
            </a>
        </nav>
    </div>

    <!-- Profile Section -->
    <div class="box">
        <h2>Profile</h2>
        <div class="profile-content">
            <div class="profile-image">
                <img src="img/userprofile.jpeg" alt="User Image" class="user-img">
            </div>
            <div class="profile-info">
                <h2 class="user-name"><?php echo $_SESSION['username'] ?? "User"; ?></h2>
                <p class="user-email"><?php echo $_SESSION['useremail'] ?? "user@example.com"; ?></p>
            </div>
        </div>
    </div>

    <!-- Task Summary Section -->
    <div class="box task-summary">
        <div>
            <h3>Total Tasks</h3>
            <p id="totalTasks"><?php echo $totalTasks; ?></p>
        </div>
        <div>
            <h3>Pending Tasks</h3>
            <p id="pendingTasks"><?php echo $pendingTasks; ?></p>
        </div>
        <div>
            <h3>Completed Tasks</h3>
            <p id="completedTasks"><?php echo $completedTasks; ?></p>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="box">
        <h2>Progress</h2>
        <div class="progress-bar">
            <div class="progress-bar-fill" id="progressBar" style="width: <?php echo $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0; ?>%;"></div>
        </div>
    </div>

    <!-- Task Graph Section -->
    <div class="box">
        <h2>Overview</h2>
        <canvas id="taskGraph"></canvas>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Task overview chart
        const ctx = document.getElementById('taskGraph').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Completed'],
                datasets: [{
                    data: [<?php echo $pendingTasks; ?>, <?php echo $completedTasks; ?>],
                    backgroundColor: ['#f39c12', '#2ecc71']
                }]
            }
        });
    </script>
</body>
</html>
