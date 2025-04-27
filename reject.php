<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Check if project ID is provided
if (isset($_GET['projectid'])) {
    $projectId = intval($_GET['projectid']);

    // Check if the user is already a member of the project or has an invitation
    $sql = "SELECT * FROM project_members 
            WHERE userid = ? AND projectid = ? AND status = 'Pending'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userid, $projectId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // If the user has a pending invitation, update status to 'Rejected'
    if (mysqli_num_rows($result) > 0) {
        $sql = "UPDATE project_members 
                SET status = 'Rejected' 
                WHERE userid = ? AND projectid = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $userid, $projectId);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Invitation rejected. You can be re-invited later.";
        } else {
            $_SESSION['error_message'] = "Failed to reject invitation.";
        }
    } else {
        // If no pending invitation exists, inform the user
        $_SESSION['error_message'] = "No invitation found for this project.";
    }

    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error_message'] = "Project ID is missing.";
}

header("Location: invitation.php");
exit();
?>
