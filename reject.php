<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

if (isset($_GET['projectid'])) {
    $projectId = intval($_GET['projectid']);

    $sql = "UPDATE project_members 
            SET status = 'Rejected' 
            WHERE userid = ? AND projectid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userid, $projectId);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Invitation rejected.";
    } else {
        $_SESSION['error_message'] = "Failed to reject invitation.";
    }

    mysqli_stmt_close($stmt);
}

header("Location: invitation.php");
exit();
?>
