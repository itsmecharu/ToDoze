<?php
session_start();
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
$taskdate = $task['taskdate'];
$tasktime = $task['tasktime'];
$reminder_percentage = $task['reminder_percentage'];
$projectId = $task['projectid'];

// Handle Task Update Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskname = trim($_POST['taskname']);
    $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
    $taskdate = isset($_POST['taskdate']) ? trim($_POST['taskdate']) : null;
    $tasktime = isset($_POST['tasktime']) ? trim($_POST['tasktime']) : null;
    $reminder_percentage = isset($_POST['reminder_percentage']) ? $_POST['reminder_percentage'] : null;

    $sql = "UPDATE tasks SET taskname = ?, taskdescription = ?, taskdate = ?, tasktime = ?, reminder_percentage = ? WHERE taskid = ? AND userid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssiii", $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage, $taskid, $userid);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Task updated successfully!";
        header("Location: project_view.php?projectid=" . $projectId);
        exit();
    } else {
        echo "Error updating task: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="box">
            <h2>Edit Task</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?taskid=' . $taskid; ?>" class="add-task-form">
                <label for="taskname">Task Name:</label>
                <input type="text" name="taskname" id="taskname" value="<?php echo htmlspecialchars($taskname); ?>" required><br>

                <label for="taskdescription">Task Description:</label>
                <input type="text" name="taskdescription" id="taskdescription" value="<?php echo htmlspecialchars($taskdescription); ?>"><br>

                <label for="taskdate">Due Date:</label>
                <input type="date" id="taskdate" name="taskdate" value="<?php echo htmlspecialchars($taskdate); ?>">
                <input type="time" id="tasktime" name="tasktime" value="<?php echo htmlspecialchars($tasktime); ?>">

                <label for="reminder">Set Reminder:</label>
                <select id="reminder" name="reminder_percentage">
                    <option value="" disabled>Select Reminder</option>
                    <option value="50" <?php if ($reminder_percentage == 50) echo "selected"; ?>>50% (Halfway to Due Date)</option>
                    <option value="75" <?php if ($reminder_percentage == 75) echo "selected"; ?>>75% (Closer to Due Date)</option>
                    <option value="90" <?php if ($reminder_percentage == 90) echo "selected"; ?>>90% (Near Due Date)</option>
                    <option value="100" <?php if ($reminder_percentage == 100) echo "selected"; ?>>100% (On Time)</option>
                </select>

                <button type="submit">Update Task</button>
            </form>
            <br>
            <a href="project_view.php?projectid=<?php echo $projectId; ?>">Back to Task List</a>
        </div>
    </div>
</body>
</html>
