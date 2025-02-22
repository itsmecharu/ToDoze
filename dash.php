<?php
session_start();
include 'config/database.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}
$userid = $_SESSION['userid'];

// Retrieve all active tasks for the user
$sql = "SELECT * FROM tasks WHERE userid = ? AND taskstatus != 'completed' AND is_deleted = 0";
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
    <body id="body-pd">
        <!-- User Profile Section -->
        <div class="profile-section">
        <a href="profile.php" class="profile-link" target="_blank">
            <div class="profile">
                <img src="img/userprofile.jpeg" class="profile-pic">
                
                <span class="profile-name"><a href="profile.php"></a>Uncle ji</span> <!-- Replace with dynamic name if needed -->
            </div>
        </div>

        <!-- Navbar -->
        <div class="l-navbar" id="navbar">
            <nav class="nav">
                <div>
                    <div class="nav__brand">
                        <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                        <span class="nav__logo">ToDoze</span>
                    </div>

                    <div class="nav__list">
                        <a href="dash.php" class="nav__link active">
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
                    </div>
                </div>

                <a href="logout.php" class="nav__link logout">
                    <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Log Out</span>
                </a>
            </nav>
        </div>

        <!-- Task List Section -->
        <div class="container">
            <div class="box">
                
            <!-- </div> -->
            <div class="box">
            <h2>Task List</h2>
      <?php
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='task' id='task-" . $row['taskid'] . "'>";

        echo "<div class='task-content'>";
        
        // Square box for marking the task as completed
        echo "<form action='task_completion.php' method='POST' class='complete-form'>";
        echo "<input type='hidden' name='taskid' value='" . $row['taskid'] . "'>";
        echo "<button type='submit' class='complete-box' title='Tick to complete'></button>";
        echo "</form>";

        // Task name (clickable to view full details)
        echo "<div class='task-details'>";
        echo "<h3><a href='task_page.php?taskid=" . $row['taskid'] . "'>" . htmlspecialchars($row['taskname']) . "</a></h3>";
        echo "</div>"; // Close task-details

        echo "</div>"; // Close task-content
        echo "</div>"; // Close task
    }
} else {
    echo "<p>No tasks added yet.</p>";
}

    
      ?>
    </div>
    </div>
        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </body>
</html>