<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$taskname = $taskdescription = $taskdate = $tasktime = $reminder_percentage = "";
$projectid = isset($_GET['projectid']) ? $_GET['projectid'] : null; // Get project ID from URL

// Handle Task Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskname = trim($_POST['taskname']);
    $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
    $taskdate = !empty($_POST['taskdate']) ? $_POST['taskdate'] : null;
    $tasktime = !empty($_POST['tasktime']) ? $_POST['tasktime'] : null;
    $reminder_percentage = isset($_POST['reminder_percentage']) ? trim($_POST['reminder_percentage']) : null;
    $projectid = isset($_POST['projectid']) ? $_POST['projectid'] : null; // Get project ID from form submission

    // Insert Task
    $sql = "INSERT INTO tasks (userid, projectid, taskname, taskdescription, taskdate, tasktime, reminder_percentage, taskstatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iisssss", $userid, $projectid, $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Task added successfully!";
            mysqli_stmt_close($stmt);
            header("Location: project_task.php?projectid=" . $projectid);
            exit();
        } else {
            echo "Error executing query: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Tasks</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
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
<body>
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
    <h1>ToDoze - Project Tasks</h1>
    <div class="container">
        <div class="box">
            <h2>Add Task to Project</h2>
            <form class="add-task-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?projectid=' . $projectid; ?>"
                method="POST">
                <input type="hidden" name="projectid" value="<?php echo $projectid; ?>">

                <!-- <label for="taskname">Task Name:</label> -->
                <input type="text" id="taskname" name="taskname" placeholder="Add task here" required>

                <!-- <label for="taskDescription">Task Description:</label> -->
                <input type="text" id="taskDescription" name="taskdescription" placeholder="Task Description"
                    style="height: 80px;">
                <div>
                    <!-- Date Section -->
                    <div style="display: inline-block; vertical-align: top; margin-right: 20px;">
                        <label for="taskdate" style="display: block;">Select Due Date 📅</label>
                        <input type="date" id="taskdate" name="taskdate" style="width: 170px;">
                    </div>

                    <!-- Time Section -->
                    <div style="display: inline-block; vertical-align: top;">
                        <label for="tasktime" style="display: block;">Select Time 🕰️</label>
                        <input type="time" id="tasktime" name="tasktime" style="width: 170px;">
                    </div>

                    <!-- <label for="reminder">Set Reminder:</label> -->
                    <select id="reminder" name="reminder_percentage">
                        <option value="" disabled selected>Set Reminder Here 🔔</option>
                        <option value="50">50% (Halfway to Due Date)</option>
                        <option value="75">75% (Closer to Due Date)</option>
                        <option value="90">90% (Near Due Date)</option>
                        <option value="100">100% (On Time)</option>
                    </select>
                    <button type="submit" style="margin-top: 20px;">Done</button>
            
           
            </form>
</br>
            <a href="project_view.php?projectid=<?php echo $projectid; ?>">Back</a>
        </div>
    </div>


<script>
    // Get references to the button and container
    const addTaskButton = document.getElementById('addTaskButton');
    const container = document.querySelector('.container');

    // Add click event listener to the button
    addTaskButton.addEventListener('click', function () {
        // Toggle the 'active' class on the container
        container.classList.toggle('actives');
    });
</script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const taskDate = document.getElementById('taskdate');
            const taskTime = document.getElementById('tasktime');
            const reminderSelect = document.getElementById('reminder');
            const form = document.querySelector('.add-task-form');

            function checkDateAndTime() {
                reminderSelect.disabled = !(taskDate.value && taskTime.value);
                if (reminderSelect.disabled) reminderSelect.value = "";
            }

            taskDate.addEventListener('input', checkDateAndTime);
            taskTime.addEventListener('input', checkDateAndTime);

            reminderSelect.addEventListener('change', function () {
                if (!taskDate.value || !taskTime.value) {
                    alert("Set both date and time before selecting a reminder.");
                    this.value = "";
                }
            });

            form.addEventListener('submit', function (event) {
                if (reminderSelect.value && (!taskDate.value || !taskTime.value)) {
                    alert("Set both date and time before setting a reminder.");
                    event.preventDefault();
                }
            });
        });
    </script>
     <!-- IONICONS -->
 <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

<!-- MAIN JS -->
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>

    