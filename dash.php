<?php
session_start();
include 'config/database.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}
$userid = $_SESSION['userid'];
// Retrieve all tasks
$sql = "SELECT * FROM tasks WHERE userid = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- ===== CSS ===== -->
        <link rel="stylesheet" href="css/dash.css">
        <link rel="icon" type="image/x-icon" href="img/favicon.ico">
        
        <title>Dashboard</title>
    </head>
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

                        <a href="analytics.php" class="nav__link">
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

        <!-- Task List Section -->
        <div class="box">
            <h2>Your Tasks</h2>
            <div id="taskList"></div>
        </div>
        <div class="box">
            <h2>Task List</h2>
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='task'>";
                    echo "<h3>" . htmlspecialchars($row['taskname']) . "</h3>";
                    echo "<p>" . (!empty($row['taskdescription']) ? htmlspecialchars($row['taskdescription']) : "No description provided") . "</p>";
                    echo "<small>Reminder: " . (!empty($row['taskreminder']) ? htmlspecialchars($row['taskreminder']) : "No reminder set") . "</small>";
                    echo "</div>";
                }
            } else {
                echo "<p>No tasks added yet.</p>";
            }
            ?>
        </div>

        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- <script src="script.js"></script> -->
    </body>
</html>