<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_GET['userid'] ?? null;
$projectId = $_GET['projectid'] ?? null;

if (!$userid || !$projectId) {
    die("Invalid request!");
}

// Ensure the user attempting removal is authorized (e.g., project owner)
$loggedInUser = $_SESSION['userid'];
$checkRoleSql = "SELECT role FROM project_members WHERE userid = ? AND projectid = ?";
$stmt = mysqli_prepare($conn, $checkRoleSql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $loggedInUser, $projectId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $userRole = mysqli_fetch_assoc($result)['role'] ?? null;
    mysqli_stmt_close($stmt);

    // Only allow removal if the user is an "admin" or "owner" of the project
    if ($userRole !== 'Admin' && $userRole !== 'Owner') {
        {
        die("You do not have permission to remove members.");
    }}
}

// Remove member from the project
$sql = "DELETE FROM project_members WHERE userid = ? AND projectid = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $userid, $projectId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
header("Location: member.php?projectid=$projectId");
exit();
?>
