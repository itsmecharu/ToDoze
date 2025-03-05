<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$projectId = $_GET['projectid'] ?? null;

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

if (!$project) {
    echo "Project not found!";
    exit();
}

mysqli_stmt_close($stmt);

// Fetch all tasks related to this project
$sql = "SELECT * FROM tasks WHERE projectid = ? AND is_deleted = 0 ORDER BY taskid DESC";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $projectId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['projectname']); ?></title>
    <link rel="stylesheet" href="css/project_view.css">
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
            <ion-icon name="person-add-outline" class="member-icon" onclick="openMemberForm()"></ion-icon>

            <p><strong>Status:</strong> <?php echo htmlspecialchars($project['projectstatus']); ?></p>
        </div>
    </div>

    <!-- Hidden Add Member Form -->
    <div id="memberForm" class="member-form" style="display: none;">
        <h3>Add Member</h3>
        <form action="add_member.php" method="POST">
            <input type="hidden" name="projectid" value="<?php echo $projectId; ?>">
            <label for="email">Member Email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Add</button>
            <button type="button" onclick="closeMemberForm()">Cancel</button>
        </form>
    </div>
</div>

<!-- Displaying the tasks -->
<div class="task-container">
    <h3>Project Tasks</h3>
    <?php if (!empty($tasks)) { ?>
        <ul>
            <?php foreach ($tasks as $task) { ?>
                <li>
                    <strong><?php echo htmlspecialchars($task['taskname']); ?></strong> -  
                    <?php echo htmlspecialchars($task['taskstatus']); ?>

                    <!-- Edit Button -->
                    <a href="edit_task.php?taskid=<?php echo $task['taskid']; ?>&projectid=<?php echo $projectId; ?>" class="edit-btn">Edit</a>

                    <!-- Delete Button -->
                    <a href="delete_task.php?taskid=<?php echo $task['taskid']; ?>&projectid=<?php echo $projectId; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>No tasks available for this project.</p>
    <?php } ?>
</div>

<!-- IONICONS -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

<!-- MAIN JS -->
<script src="js/dash.js"></script>

<script>
    function openMemberForm() {
        document.getElementById("memberForm").style.display = "block";
    }

    function closeMemberForm() {
        document.getElementById("memberForm").style.display = "none";
    }
</script>

</body>
</html>
