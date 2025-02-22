<?php
session_start();
include 'config/database.php';

// Ensure userid is set before accessing it
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid']; // Correct way to get the user ID

$taskname = $taskdescription = $taskreminder = "";

// Handle Task Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskname = trim($_POST['taskname']);
    $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
    $taskreminder = isset($_POST['taskreminder']) ? $_POST['taskreminder'] : null;

    // Insert Task into Database
    $sql = "INSERT INTO tasks (userid, taskname, taskdescription, taskreminder) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $userid, $taskname, $taskdescription, $taskreminder);
        if (mysqli_stmt_execute($stmt)) {
           // echo "<script>alert('Task Added Successfully!'); window.location='task.php';</script>";
        } else {
            echo "Error executing query: " . mysqli_error($conn);
        }
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}

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
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <title>Home</title>
</head>
<body id="body-pd">
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
                <div class="nav__list">
                    <a href="task.php" class="nav__link active">
                        <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Task</span>
                    </a>
                    <a href="project.php" class="nav__link">
                        <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Project</span>
                    </a>
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

  <!-- User Profile Section -->
  <div class="profile-section">
        <a href="profile.php" class="profile-link" target="_blank">
            <div class="profile">
                <img src="img/userprofile.jpeg" class="profile-pic">
                
                <span class="profile-name"><a href="profile.php"></a>uncle ji</span> <!-- Replace with dynamic name if needed -->
            </div>
        </div>

    <h1>ToDoze</h1>
    <div class="container">
        <!-- Add Task Section -->
        <div class="box">
            <h2>Add Task</h2>
            <form class="add-task-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="text" id="taskname" name="taskname" placeholder="Add task here" required>
                <input type="text" id="taskDescription" name="taskdescription" placeholder="Task Description">
                <input type="datetime-local" id="taskreminder" name="taskreminder">
                <button type="submit">Done</button>
            </form>
        </div>

        <!-- Task List Section  -->
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
    </div>

    
        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- <script src="script.js"></script> -->
</body>
</html>
