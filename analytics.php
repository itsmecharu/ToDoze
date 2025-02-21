<?php
session_start();
include 'config/database.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    
</head>
<body>
<br id="body-pd">
        <div class="l-navbar" id="navbar">
            <nav class="nav">
                <div>
                    <div class="nav__brand">
                        <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                        <span class="nav__logo">Dashboard</span>
                    </div>
                    <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                        <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Home</span>
                    </a>

                    <div class="nav__list">
                    <a href="task.php" class="nav__link">
                            <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Task</span>
                        </a>

                        <a href="project.php"  class="nav__link">
                            <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Project</span>
                        </a>

                        <a href="analytics.php" class="nav__link active">
                            <ion-icon name="pie-chart-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Report</span>
                        </a>

                        
                        <!-- <a href="profile.php" class="nav__link">
                            
                            <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Profile</span>

                        </a> -->

                        <a href="review.php" class="nav__link">
                            
                            <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Review</span>

                        </a>
                       
                    </div>
            

                </div>

                <a href="logout.php" class="nav__link">
                    <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Log Out</span>
                </a>
            </nav>
        </div>
        <div class="box">
            

        <!-- Task Summary Section -->
        <div class="box task-summary">
            <div>
                <h3>Total Tasks</h3>
                <p id="totalTasks">0</p>
            </div>
            <div>
                <h3>Pending Tasks</h3>
                <p id="pendingTasks">0</p>
            </div>
            <div>
                <h3>Completed Tasks</h3>
                <p id="completedTasks">0</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="box">
            <h2>Progress</h2>
            <div class="progress-bar">
                <div class="progress-bar-fill" id="progressBar"></div>
            </div>
        </div>

        <!-- Task Graph Section -->
        <div class="box">
            <h2> Overview</h2>
            <canvas id="taskGraph"></canvas>
        </div>
    </div>
        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- <script src="script.js"></script> -->
</body>
</html>