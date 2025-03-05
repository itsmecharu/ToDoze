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
    $taskdate = (!empty($_POST['taskdate'])) ? $_POST['taskdate'] : null;
    $tasktime = (!empty($_POST['tasktime'])) ? $_POST['tasktime'] : null;
    $reminder_percentage = isset($_POST['reminder_percentage']) ? trim($_POST['reminder_percentage']) : null;
    $projectid = isset($_POST['projectid']) ? $_POST['projectid'] : null; // Get project ID from form submission

    $sql = "INSERT INTO tasks (userid, projectid, taskname, taskdescription, taskdate, tasktime, reminder_percentage, taskstatus) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iisssss", $userid, $projectid, $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Task added successfully!";
            header("Location: project_task.php?projectid=" . $projectid);
            exit();
        } else {
            echo "Error executing query: " . mysqli_error($conn);
        }
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
</head>
<body>
    <h1>ToDoze - Project Tasks</h1>
    <div class="container">
        <div class="box">
            <h2>Add Task to Project</h2>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?projectid=' . $projectid; ?>" method="POST">
                <input type="hidden" name="projectid" value="<?php echo $projectid; ?>">
                <label for="taskname">Task Name:</label>
                <input type="text" id="taskname" name="taskname" required>

                <label for="taskDescription">Task Description:</label>
                <input type="text" id="taskDescription" name="taskdescription">

                <label for="taskdate">Due Date:</label>
                <input type="date" id="taskdate" name="taskdate">
                <input type="time" id="tasktime" name="tasktime">

                <label for="reminder">Set Reminder:</label>
                <select id="reminder" name="reminder_percentage">
                    <option value="" disabled selected>Set Reminder</option>
                    <option value="50">50% (Halfway to Due Date)</option>
                    <option value="75">75% (Closer to Due Date)</option>
                    <option value="90">90% (Near Due Date)</option>
                    <option value="100">100% (On Time)</option>
                </select>

                <button type="submit">Add Task</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="box">
            <h2>Project Tasks</h2>
            <ul>
                <?php
                $sql = "SELECT * FROM tasks WHERE projectid = ? AND userid = ? AND is_deleted = 0";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $projectid, $userid);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<li>{$row['taskname']} - Status: {$row['taskstatus']}</li>";
                    }
                } else {
                    echo "<li>No tasks found for this project.</li>";
                }
                ?>
            </ul>
        </div>
    </div>
</body>
</html>
