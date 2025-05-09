<?php
session_start();
include 'config/database.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskid'], $_POST['taskpriority'])) {
    $taskid = $_POST['taskid'];
    $priority = $_POST['taskpriority'];

    $sql = "UPDATE tasks SET taskpriority = ? WHERE taskid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $priority, $taskid);
    mysqli_stmt_execute($stmt);
}

header("Location: task.php");
exit();
?>
