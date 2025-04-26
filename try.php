<?php
session_start();
date_default_timezone_set('Asia/Kathmandu'); 
include 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
  header("Location: signin.php");
  exit();
}

$userid = $_SESSION['userid'];

if (!isset($_GET['taskid'])) {
  echo "Task ID is missing.";
  exit();
}

$taskid = $_GET['taskid'];

// Fetch task details (only if not deleted)
$sql = "SELECT * FROM tasks WHERE taskid = ? AND userid = ? AND is_deleted = 0";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $taskid, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
  echo "Task not found.";
  exit();
}

$task = mysqli_fetch_assoc($result);
$taskname = $task['taskname'];
$taskdescription = $task['taskdescription'];
$reminder_percentage = $task['reminder_percentage'];

$taskdate = isset($task['taskdate']) ? $task['taskdate'] : '';  
$tasktime = isset($task['tasktime']) ? $task['tasktime'] : ''; 




// Handle Task Update Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $taskname = trim($_POST['taskname']);
  $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
  $taskdate = isset($_POST['taskdate']) ? trim($_POST['taskdate']) : null;
  $tasktime = isset($_POST['tasktime']) ? trim($_POST['tasktime']) : null;
  $reminder_percentage = isset($_POST['reminder_percentage']) ? $_POST['reminder_percentage'] : null;


  $sql = "UPDATE tasks SET taskname = ?, taskdescription = ?, taskdate = ?,tasktime = ?,  reminder_percentage = ? WHERE taskid = ? AND userid = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "sssssii", $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage, $taskid, $userid);
  if (mysqli_stmt_execute($stmt)) {
    header("Location: dash.php");
    exit();
  } else {
    echo "Error updating task: " . mysqli_error($conn);
  }
}
?>



  
<!DOCTYPE html>
<head>
<html lang="en">
<meta charset="UTF-8">
<title>Edit Task</title>
<link rel="stylesheet" href="css/dash.css">
<link rel="icon" type="image/x-icon" href="img/favicon.ico">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.back-link {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 18px;
    font-size: 14px;
    color: white;
    background-color: #007BFF;
    border-radius: 6px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.back-link:hover {
    background-color: #0056b3;
}

h2 {
    text-align: center;
}
</style>
<style>
        /* Initially align container to the left */
.container {
    margin-left: 150px; /* This matches the navbar width */
    transition: all 0.3s ease-in-out;
}

/* When navbar is collapsed */
body.nav-collapsed .container {
    margin-left: 120px;
    margin-right: 0px;
    max-width: 800px; /* Optional: limit the width */
    text-align: center;
}
    </style>
</head>


  
<body id="body-pd">
    <!-- Navbar -->
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__brand">
                    <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                    <span class="nav__logo" style="display: flex; align-items: center;">
                        ToDoze
                        <a href="invitation.php"> <!-- Added a link to redirect to the invitations page -->
                            <ion-icon name="notifications-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                        </a>
                    </span>
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
  <div class="container">
    <div class="box">
      <h2>Edit Task</h2>
      <form method="POST" action="" class="add-task-form">
        <!-- <label for="taskname">Task Name:</label> -->
        <input type="text" name="taskname" id="taskname" placeholder="Add task here" value="<?php echo htmlspecialchars($taskname); ?>"
          required>

        <!-- <label for="taskdescription">Task Description:</label> -->
        <input type="text" name="taskdescription" id="taskdescription" placeholder="Task Description" style="height: 80px;" value="<?php echo htmlspecialchars($taskdescription); ?>">

        <div>
                <div style="display: inline-block; vertical-align: top; margin-right: 20px;">
                        <label for="taskdate" style="display: block;">Select Due Date 📅</label>
                        <input type="date" id="taskdate" name="taskdate" value="<?php echo htmlspecialchars($taskdate); ?>" style="width: 170px;">
                </div>

                <div style="display: inline-block; vertical-align: top;">
                        <label for="tasktime" style="display: block;">Select Time 🕰️</label>
                        <input type="time" id="tasktime" name="tasktime" value="<?php echo htmlspecialchars($tasktime); ?>" style="width: 170px;">
                    </div>
        <!-- <label for="reminder">Set Reminder:</label> -->
<select id="reminder" name="reminder_percentage">
    <option value="" disabled selected >Set Reminder Here 🔔</option>
    <option value="50" <?php if ($reminder_percentage == 50) echo "selected"; ?>>50% (Halfway to Due Date)</option>
    <option value="75" <?php if ($reminder_percentage == 75) echo "selected"; ?>>75% (Closer to Due Date)</option>
    <option value="90" <?php if ($reminder_percentage == 90) echo "selected"; ?>>90% (Near Due Date)</option>
    <option value="100" <?php if ($reminder_percentage == 100) echo "selected"; ?>>100% (On Time)</option>
</select>



        <button type="submit">Update Task</button>
      </form>
      <br>
      <a href="dash.php" class="back-link">← Back to Task List</a>

    </div>
  </div>
  <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

<!-- MAIN JS -->
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>

