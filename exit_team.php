<?php
session_start();
include 'config/database.php'; // Your DB connection

if (isset($_SESSION['userid']) && isset($_GET['teamid'])) {
    $userid = $_SESSION['userid'];
    $teamid = intval($_GET['teamid']);

    $sql = "UPDATE team_members 
            SET has_exited = 1, exited_at = NOW() ,status = 'Removed'
            WHERE userid = ? AND teamid = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $userid, $teamid);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "You have exited the team.";
            header("Location: team.php");
            exit();
        } else {
            echo "Failed to exit team: " . mysqli_error($conn);
        }
    } else {
        echo "Failed to prepare statement: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
