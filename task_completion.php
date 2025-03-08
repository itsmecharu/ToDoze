<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['taskid'])) {
    echo "Invalid request.";
    exit();
}

$userid = $_SESSION['userid'];
$taskid = $_POST['taskid'];

// Update the task: set status to completed and record the completion time in taskdate
$sql = "UPDATE tasks SET taskstatus = 'completed', taskdate = NOW() WHERE taskid = ? AND userid = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo "Error preparing statement: " . mysqli_error($conn);
    exit();
}
mysqli_stmt_bind_param($stmt, "ii", $taskid, $userid);
if (mysqli_stmt_execute($stmt)) {
    // Check if the task has a projectid (not null)
    $sql = "SELECT projectid FROM tasks WHERE taskid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $taskid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $task = mysqli_fetch_assoc($result);
    
    if ($task && $task['projectid'] !== null) {
        // Redirect to the project view page if projectid is not null
        
        header("Location: project_view.php?projectid=" . $task['projectid']);
    } else {
        // Otherwise, redirect to the dashboard
        header("Location: dash.php");
    }
    exit();
} else {
    echo "Error marking task as completed: " . mysqli_error($conn);
}
?>
