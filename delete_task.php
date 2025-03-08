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

// Fetch task details to check if it belongs to a project
$sql = "SELECT projectid FROM tasks WHERE taskid = ? AND userid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $taskid, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$task = mysqli_fetch_assoc($result);

if (!$task) {
    echo "Task not found or you do not have permission to delete it.";
    exit();
}

// Update the task to mark it as deleted and store the deletion date
$sql = "UPDATE tasks SET is_deleted = 1, deleted_at = NOW() WHERE taskid = ? AND userid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $taskid, $userid);
if (mysqli_stmt_execute($stmt)) {
    // If task belongs to a project, redirect to project view page
    if ($task['projectid'] !== null) {
        header("Location: project_view.php?projectid=" . $task['projectid']);
        exit();
    } else {
        // Otherwise, redirect to dashboard
        header("Location: dash.php");
        exit();
    }
} else {
    echo "Error deleting task: " . mysqli_error($conn);
}
?>
