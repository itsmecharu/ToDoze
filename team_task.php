<?php
session_start();
include 'config/database.php';
include 'load_username.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$taskname = $taskdescription = $taskdate = $tasktime = $reminder_percentage = "";
// $teamid = isset($_GET['teamid']) ? $_GET['teamid'] : null; // Get team ID from URL
$teamid = $_GET['teamid'] ?? $_POST['teamid'] ?? null;

if (!$teamid || !is_numeric($teamid)) {
    echo "Project ID is missing or invalid.";
    exit();
}


// Handle Task Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskname = trim($_POST['taskname']);
    $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
    $taskdate = !empty($_POST['taskdate']) ? $_POST['taskdate'] : null;
    $tasktime = !empty($_POST['tasktime']) ? $_POST['tasktime'] : null;
    $reminder_percentage = isset($_POST['reminder_percentage']) ? trim($_POST['reminder_percentage']) : null;
    // $teamid = isset($_POST['teamid']) ? $_POST['teamid'] : null; // Get team ID from form submission

    // Insert Task
    $sql = "INSERT INTO tasks (userid, teamid, taskname, taskdescription, taskdate, tasktime, reminder_percentage, taskstatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iisssss", $userid, $teamid, $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Task added successfully!";
            mysqli_stmt_close($stmt);
            header("Location: team_task.php?teamid=" . $teamid);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<div class="top-bar">
 <div class="top-right-icons">
      <!-- Notification Icon -->
      <a href="invitation.php" class="top-icon">
        <ion-icon name="notifications-outline"></ion-icon>
      </a>
      
        <!-- Profile Icon -->
        <div class="profile-info">
  <a href="#" class="profile-circle" title="<?= htmlspecialchars($username) ?>">
    <ion-icon name="person-outline"></ion-icon>
  </a>
  <span class="username-text"><?= htmlspecialchars($username) ?></span>
</div>
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
        <a href="task.php" class="nav__link ">
          <ion-icon name="add-outline" class="nav__icon"></ion-icon>
          <span class="nav__name">Task</span>
        </a>
        <a href="team.php" class="nav__link active">
          <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
          <span class="nav__name">Team </span>
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
    <div class="container">
    <h2>Add New Tasks</h2>
    <a href="team_view.php?teamid=<?php echo $teamid; ?>"class="back-link">View Project</a>

        <div class="box">
        
            <form class="add-task-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?teamid=' . $teamid; ?>"
                method="POST">
                <input type="hidden" name="teamid" value="<?php echo $teamid; ?>">

                <!-- <label for="taskname">Task Name:</label> -->
                <input type="text" id="taskname" name="taskname" placeholder="Add task here"  maxlength="50" required>

                <!-- <label for="taskDescription">Task Description:</label> -->
                <input type="text" id="taskDescription" name="taskdescription" placeholder="Task Description" maxlength="140"
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

        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: "Task added successfully!",
                    text: "", // Empty text since you only want "Task added successfully"
                    // icon: "success",
                    timer: 1000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'small-swal', // Custom class for SweetAlert popup
                        title: 'small-swal-title', // Custom class for the title
                        content: 'small-swal-content' // Custom class for the content
                    }
                });
            });
        </script>

        <style>
            .small-swal {
                width: 200px;
                /* Set the width of the card */
                padding: 20px;
                /* Optional: Add padding to adjust internal spacing */
            }

            .small-swal-title {
                font-size: 16px;
                /* Adjust font size of the title */
                font-weight: bold;
                /* Optional: Make title bold */
            }

            .small-swal-content {
                font-size: 14px;
                /* Adjust font size of the text content */
            }
        </style>


        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

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

    // Disable reminder initially
    reminderSelect.disabled = true;

    function checkDateAndTime() {
        // Enable reminder only if both date and time are set
        reminderSelect.disabled = !(taskDate.value && taskTime.value);
        if (reminderSelect.disabled) reminderSelect.value = ""; // Reset reminder if disabled
    }

    // Event listeners for date and time changes
    taskDate.addEventListener('input', checkDateAndTime);
    taskTime.addEventListener('input', checkDateAndTime);

    // Ensure user selects both date and time before setting a reminder
    reminderSelect.addEventListener('change', function () {
        if (!taskDate.value || !taskTime.value) {
            alert("Set both date and time before selecting a reminder.");
            this.value = "";  // Clear the reminder selection
        }
    });

    // Prevent form submission if reminder is selected without date/time
    form.addEventListener('submit', function (event) {
        if (reminderSelect.value && (!taskDate.value || !taskTime.value)) {
            alert("Set both date and time before setting a reminder.");
            event.preventDefault();  // Prevent form submission
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

    