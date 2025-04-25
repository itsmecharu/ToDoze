<?php
session_start();
include 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Check if projectid is passed
if (isset($_GET['projectid'])) {
    $projectId = intval($_GET['projectid']);

    // Update invitation to accepted and set joinedproject_at timestamp
    $sql = "UPDATE project_members 
            SET status = 'Accepted', joinedproject_at = NOW() 
            WHERE userid = ? AND projectid = ? AND status = 'Pending'";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $userid, $projectId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Invitation accepted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to accept the invitation.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_message'] = "Database error.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

mysqli_close($conn);
header("Location: invitation.php");
exit();
?>
