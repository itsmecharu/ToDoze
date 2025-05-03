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

    // Initialize update values with the current data
    $update_values = [
        'taskname' => $taskname,
        'taskdescription' => $taskdescription,
        'taskdate' => $taskdate,
        'tasktime' => $tasktime,
        'reminder_percentage' => $reminder_percentage
    ];

    // Check if any field is empty, and if so, don't update it in the database
    $sql = "UPDATE tasks SET ";

    $fields = [];
    $params = [];
    $types = "";

    if (!empty($taskname) && $taskname !== $task['taskname']) {
        $fields[] = "taskname = ?";
        $params[] = $taskname;
        $types .= "s";
    }
    if (!empty($taskdescription) && $taskdescription !== $task['taskdescription']) {
        $fields[] = "taskdescription = ?";
        $params[] = $taskdescription;
        $types .= "s";
    }
    if (!empty($taskdate) && $taskdate !== $task['taskdate']) {
        $fields[] = "taskdate = ?";
        $params[] = $taskdate;
        $types .= "s";
    }
    if (!empty($tasktime) && $tasktime !== $task['tasktime']) {
        $fields[] = "tasktime = ?";
        $params[] = $tasktime;
        $types .= "s";
    }
    if (isset($reminder_percentage) && $reminder_percentage !== $task['reminder_percentage']) {
        $fields[] = "reminder_percentage = ?";
        $params[] = $reminder_percentage;
        $types .= "i";
    }

    // Only execute the update if there are any fields to update
    if (count($fields) > 0) {
        $sql .= implode(", ", $fields) . " WHERE taskid = ? AND userid = ?";
        $params[] = $taskid;
        $params[] = $userid;
        $types .= "ii";

        // Prepare and execute the statement
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: dash.php");
            exit();
        } else {
            echo "Error updating task: " . mysqli_error($conn);
        }
    } else {
        // No fields to update
        header("Location: dash.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<head>
<html lang="en">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
.box {
    width: 550px; /* adjust size as you like */
    margin: 50px 0 0 200px; /* top, right, bottom, left */
    transition: all 0.3s ease-in-out;
}

/* When nav is collapsed (body has nav-collapsed class) */
body.nav-collapsed .box {
    margin: 50px auto; /* center horizontally */
}
     
.profile-circle {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #ccc;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #fff;
    }

    .username {
      font-weight: 600;
      color: #333;
    }

    .top-right-icons {
      position: fixed;
      top: 20px;
      right: 20px;
      display: flex;
      align-items: center;
      z-index: 1000; /* Ensure it is above other content */
    }

    /* Notification Icon Styling */
    .top-icon {
      margin-right: 20px; /* Space between notification and profile icon */
    }

    .top-icon ion-icon {
      font-size: 28px; /* Size of the notification icon */
      color: #333; /* Icon color */
      cursor: pointer; /* Change cursor to pointer on hover */
    }

    /* Optional: Add a hover effect */
    .top-icon ion-icon:hover {
      color: #007bff; /* Change color on hover */
    }

    /* Optional: Adding a notification badge */
    .top-icon {
      position: relative;
    }
    .logo-container {
  position: fixed;
  top: 5px;  /* Adjust the position from the top */
  left: 35px;  /* Adjust the position from the left */
  z-index: 1000;  /* Ensure it's above the sidebar */
}

.logo {
  width: 120px;  /* Adjust the width of the logo */
  height: auto;
}
</style>
</head>

<body id="body-pd">
<div class="top-bar">
    <div class="top-left">
      <!-- Removed profile from here -->
    </div>

    <div class="top-right-icons">
      <!-- Notification Icon -->
      <a href="invitation.php" class="top-icon">
        <ion-icon name="notifications-outline"></ion-icon>
      </a>
      
      <!-- Profile Icon -->
      <a href="profile.php" class="profile-circle">
        <ion-icon name="person-outline"></ion-icon>
      </a>
    </div>
  </div>

  <!-- Logo Above Sidebar -->
  <div class="logo-container">
    <img src="img/logo.png" alt="Logo" class="logo">
  </div>

  <!-- Sidebar Navigation -->
  <div class="l-navbar" id="navbar">
    <nav class="nav">
      <div class="nav__list">
        <a href="dash.php" class="nav__link ">
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
      </div>
      <a href="logout.php" class="nav__link logout">
        <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
        <span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span>
      </a>
    </nav>
  </div>

    <div class="box">
      <h2 style="text-align: center; ">Edit Task</h2>
      <form method="POST" action="" class="add-task-form">
        <input type="text" name="taskname" id="taskname" placeholder="Add task here" value="<?php echo htmlspecialchars($taskname); ?>" required>

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
        </div>

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
  </script>
  <script>
document.addEventListener("DOMContentLoaded", function() {
    const toggle = document.getElementById('nav-toggle');
    const body = document.getElementById('body-pd');

    toggle.addEventListener('click', () => {
        body.classList.toggle('nav-collapsed');
    });
});
</script>


<!-- ===== IONICONS ===== -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

<!-- ===== MAIN JS ===== -->
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>