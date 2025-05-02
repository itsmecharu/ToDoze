<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
include 'config/database.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}
$userid = $_SESSION['userid'];

// Retrieve all active tasks for the user
$sql = "SELECT * FROM tasks WHERE userid = ? AND taskstatus != 'completed' AND is_deleted = 0 AND projectid IS NULL";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $userid);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 120px;
            margin-left: 220px;
            padding: 0;
            background-color: #fdfdfd;
        }

      
        .task-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .task-box {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-left: 5px solid #4CAF50;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .task-box:hover {
            transform: translateY(-3px);
        }

        .task-title {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 8px;
            color: #007BFF;
        }

        .task-detail {
            display: block;
        }

        .task-box.minimized .task-detail {
            display: none;
        }

        .task-description,
        .task-meta {
            font-size: 14px;
            color: #555;
        }

        .task-actions {
            margin-top: 10px;
        }

        .task-actions a {
            margin-right: 10px;
            font-size: 14px;
            color: #007BFF;
            text-decoration: none;
        }

        .task-actions a:hover {
            text-decoration: underline;
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
.delete-task {
  color: red !important;
}
.delete-task:hover {
  text-decoration: underline;
}
    </style>
</head>

<body>
  <!-- Top Bar -->
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
  
  <div class="task-categories">
    <div class="task-category active" id="all-tasks">All Tasks</div>
    <div class="task-category" id="completed-tasks">Completed Tasks</div>
  </div>

  <!-- Logo Above Sidebar -->
  <div class="logo-container">
    <img src="img/logo.png" alt="Logo" class="logo">
  </div>

  <!-- Sidebar Navigation -->
  <div class="l-navbar" id="navbar">
    <nav class="nav">
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
      <a href="logout.php" class="nav__link logout">
        <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
        <span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span>
      </a>
    </nav>
  </div>


    
        
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $taskDateTime = strtotime($row['taskdate'] . ' ' . $row['tasktime']);
                $currentDateTime = time();
                $isOverdue = $taskDateTime < $currentDateTime;

                echo "<div class='task' id='task-" . $row['taskid'] . "'>";
                echo "<div class='task-content'>";



                // Square box for marking the task as completed
                echo "<form action='task_completion.php' method='POST' class='complete-form' >";
                echo "<input type='hidden' name='taskid' value='" . $row['taskid'] . "'>";
                echo "<button type='submit'  name='complete-box' class='complete-box' title='Tick to complete'></button>";
                echo "</form>";

                // Task name and details
                echo "<div class='task-details'>";
                // Display overdue message if the task is past due
                if ($isOverdue) {
                    echo "<p style='color: red; font-weight: bold;'>Overdue Task</p>";
                }

                // Change task name color to red if overdue
                echo "<h4 style='" . ($isOverdue ? "color: red;" : "") . "'>" . htmlspecialchars($row['taskname']) . "</h4>";

                echo "<p>" . (!empty($row['taskdescription']) ? htmlspecialchars($row['taskdescription']) : "") . "</p>";
                echo "<p>" . (!empty($row['taskdate']) ? htmlspecialchars(date('Y-m-d', strtotime($row['taskdate']))) : "") . "</p>";
                echo "<p>" . (!empty($row['tasktime']) ? htmlspecialchars(date('H:i', strtotime($row['tasktime']))) : "") . "</p>";

                echo "<small>Reminder: " . (isset($row['reminder_percentage']) && $row['reminder_percentage'] !== null ? htmlspecialchars($row['reminder_percentage']) . "%" : "Not set") . "</small><br>";
                echo "<a href='edit_task.php?taskid=" . $row['taskid'] . "'>Edit</a>  ";
                echo "<a href='#' class='delete-task' data-taskid=' " . $row['taskid'] . "'>Delete</a>";
                echo "</div>"; // Close task-details
        
                echo "</div>"; // Close task-content
                echo "</div>"; // Close task
            }

        } else {
            echo '
  <div class="centered-content">
    <!-- Centered Image and Text -->
    <div class="content-wrapper">
      <img src="img/notask.png" alt="No tasks yet" />
      <h3><p>No tasks yet. Add your first one! 🚀</p></h3>
    </div>
  </div>';
        }
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ensure that all delete links with the class 'delete-task' are properly selected
            document.querySelectorAll('.delete-task').forEach(function (button) {
                button.addEventListener('click', function (e) {
                    e.preventDefault(); // Prevent the default link action (redirect)

                    var taskid = this.getAttribute('data-taskid'); // Get the taskid from the data attribute

                    // Use SweetAlert to confirm deletion
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to delete_task.php with the task ID
                            window.location.href = 'delete_task.php?taskid=' + taskid;
                        }
                    });
                });
            });

            // COMPLETE TASK CONFIRMATION
            document.querySelectorAll(".complete-form").forEach(function (form) {
                form.addEventListener("submit", function (e) {
                    e.preventDefault();
                    Swal.fire({
                        text: "Are you sure?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Submit the form after confirmation
                        }
                    });
                });
            });

        }); // Closing bracket for DOMContentLoaded


    </script>
    <script>
        // Function to toggle task details
        function toggleTaskDetails(taskElement) {
            taskElement.classList.toggle('expanded');
        }

        // Add click event listeners to all task names
        document.querySelectorAll('.task-details h4').forEach(taskName => {
            taskName.addEventListener('click', () => {
                const taskElement = taskName.closest('.task');
                toggleTaskDetails(taskElement);
            });
        });
    </script>

    <!-- ===== IONICONS ===== -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

    <!-- ===== MAIN JS ===== -->
    <script src="js/dash.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <!-- Ionicons CDN -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>

</html>