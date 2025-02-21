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

        <!-- ===== CSS ===== -->
        <link rel="stylesheet" href="css/dash.css">
        
        <title>Dashboard</title>
    </head>
    <br id="body-pd">
        <div class="l-navbar" id="navbar">
            <nav class="nav">
                <div>
                    <div class="nav__brand">
                        <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                        <span class="nav__logo">ToDoze</span>
                    </div>
                    <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                            <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Task</span>
                        </a>

                        <a href="project.php"  class="nav__link">
                            <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Projects</span>
                        </a>

                        <a href="analytics.php" class="nav__link">
                            <ion-icon name="pie-chart-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Report</span>
                        </a>

                        
                        <a href="profile.php" class="nav__link">
                            
                            <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Profile</span>

                        </a>

                        <a href="review.php" class="nav__link">
                            
                            <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Review</span>

                        </a>
                       
                    </div>
            

                </div>

                <a href="index.php" class="nav__link">
                    <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Log Out</span>
                </a>
            </nav>
        </div>

        <h1>Welcome Back</h1>
<div class="container">
        <!-- Add Task Section -->
        <div class="box">
            <h2>Add Task</h2>
            <form class="add-task-form" onsubmit="event.preventDefault(); addTask();">
                <input type="text" id="taskDescription" placeholder="Task Description" required>
                <input type="date" id="setDate" required>
                <input type="date" id="dueDate" required>
                <input type="" id="remainder" required>
                <button type="submit">Add Task</button>
            </form>
        </div>

        <!-- Task List Section -->
        <div class="box">
            <h2>Task List</h2>
            <div id="taskList"></div>
        </div>

        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- <script src="script.js"></script> -->
    </body>
</html>