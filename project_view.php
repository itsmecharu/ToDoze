<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$projectId = isset($_GET['projectid']) ? (int) $_GET['projectid'] : null;

if (!$projectId) {
    echo "Project not found!";
    exit();
}

// Fetch project details
$sql = "SELECT * FROM projects WHERE projectid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$project) {
    echo "Project not found!";
    exit();
}

// Fetch tasks
$sql = "SELECT * FROM tasks WHERE projectid = ? AND is_deleted = 0 AND taskstatus='pending' ORDER BY taskid DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['projectname']); ?></title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<div class="container">
    <div class="box">
        <h2><?php echo htmlspecialchars($project['projectname']); ?></h2>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($project['projectdescription']); ?></p>
        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($project['projectduedate']); ?></p>

        <div class="icons">
            <!-- Add Task Icon -->
            <ion-icon name="add-circle-outline" class="task-icon" onclick="window.location.href='project_task.php?projectid=<?php echo $projectId; ?>'"></ion-icon>

            <!-- Add Member Icon -->
            <a href="member.php?projectid=<?php echo $projectId; ?>">
                <ion-icon name="people-outline" class="member-icon"></ion-icon>
            </a>
           

            <a href="project.php">Back </a>
        </div>
    </div>
</div>

<!-- Displaying the tasks -->
<div class="task-container">
    <h3>Project Tasks</h3>
    <?php if (!empty($tasks)) { ?>
        <?php foreach ($tasks as $task) { ?>
            <div class='task' id='task-<?php echo $task['taskid']; ?>'>
                <div class='task-content'>
                    <form action='task_completion.php' method='POST' class='complete-form'>
                        <input type='hidden' name='taskid' value='<?php echo $task['taskid']; ?>'>
                        <button type='submit' name='complete-box' class='complete-box' title='Tick to complete'></button>
                    </form>
                    <div class='task-details'>
                        <?php 
                        $taskDateTime = strtotime($task['taskdate'] . ' ' . $task['tasktime']);
                        $currentDateTime = time();
                        $isOverdue = $taskDateTime < $currentDateTime;
                        
                        if ($isOverdue) { echo "<p style='color: red; font-weight: bold;'>Overdue Task</p>"; }
                        ?>
                        <h4 style='<?php echo $isOverdue ? "color: red;" : ""; ?>'><?php echo htmlspecialchars($task['taskname']); ?></h4>
                        <p><?php echo !empty($task['taskdescription']) ? htmlspecialchars($task['taskdescription']) : ""; ?></p>
                        <p><?php echo !empty($task['taskdate']) ? htmlspecialchars(date('Y-m-d', strtotime($task['taskdate']))) : ""; ?></p>
                        <p><?php echo !empty($task['tasktime']) ? htmlspecialchars(date('H:i', strtotime($task['tasktime']))) : ""; ?></p>
                        <small>Reminder: <?php echo isset($task['reminder_percentage']) ? htmlspecialchars($task['reminder_percentage']) . "%" : "Not Set"; ?></small><br>
                        <a href='editproject_task.php?taskid=<?php echo $task['taskid']; ?>&projectid=<?php echo $projectId; ?>'>Edit</a>
                        <a href='#' class='delete-task' data-taskid='<?php echo $task['taskid']; ?>'>Delete</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p>No tasks available for this project.</p>
    <?php } ?>
</div>

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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-task').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                var taskid = this.getAttribute('data-taskid');
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
    });
    
</script>
 <!-- IONICONS -->
 <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

<!-- MAIN JS -->
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

</body>
</html>
