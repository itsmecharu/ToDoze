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

// Update the task to mark it as deleted and store the deletion date
$sql = "UPDATE tasks SET is_deleted = 1, deleted_at = NOW() WHERE taskid = ? AND userid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $taskid, $userid);
if (mysqli_stmt_execute($stmt)) {
    header("Location: task.php");
    exit();
} else {
    echo "Error deleting task: " . mysqli_error($conn);
}
?>
