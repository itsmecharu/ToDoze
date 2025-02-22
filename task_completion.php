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
    header("Location: task.php");
    exit();
} else {
    echo "Error marking task as completed: " . mysqli_error($conn);
}
?>
