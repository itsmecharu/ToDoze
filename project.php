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
   <title>Task</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                    <a href="task.php" class="nav__link active">
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

                    <a href="profile.php" class="nav__link">
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

  <!-- IONICONS -->
  <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

<!-- MAIN JS -->
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>