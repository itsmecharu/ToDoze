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

    <!-- Task List Section -->
    <div class="container">
        <!-- <div class="box"> -->
            <h1 style="margin:10%; ;">Task List</h1>
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
                    
                    echo "<p>" . (!empty($row['taskdescription']) ? htmlspecialchars($row['taskdescription']) : "No description provided") . "</p>";
                    echo "<p>" . (!empty($row['taskdate']) ? htmlspecialchars(date('Y-m-d', strtotime($row['taskdate']))) : "No time provided") . "</p>";
                    echo "<p>" . (!empty($row['tasktime']) ? htmlspecialchars(date('H:i', strtotime($row['tasktime']))) : "No time provided") . "</p>";
                    
                    echo "<small>Reminder: " . (isset($row['reminder_percentage']) && $row['reminder_percentage'] !== null ? htmlspecialchars($row['reminder_percentage']) . "%" : "No reminder set") . "</small><br>";
                    echo "<a href='edit_task.php?taskid=" . $row['taskid'] . "'>Edit</a>  ";
                    echo "<a href='#' class='delete-task' data-taskid='" . $row['taskid'] . "'>Delete</a>";
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
    <!-- for showing update message -->

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: "Task updated successfully!",
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
                    // title: "Mark Task as Completed?",
                    text: "Are you sure ?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes"
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Completed!",
                            // text: "Task Finished",
                            icon: "success"
                        }).then(() => {
                            form.submit(); // Submit after confirmation
                        });
                    }
                });
            });
        });
    });
    
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
</body>
</html>
