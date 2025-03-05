<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Ensure project ID is provided in the URL
if (!isset($_GET['projectid'])) {
    $_SESSION['error_message'] = "Project ID is missing.";
    header("Location: project.php");
    exit();
}

$projectid = $_GET['projectid'];

// Check if the user is part of the project and has 'Admin' or 'Super Admin' role
$sql = "SELECT pm.userid 
        FROM project_members pm 
        WHERE pm.projectid = ? AND pm.userid = ? AND (pm.role = 'Admin' OR pm.role = 'Super Admin')";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
    header("Location: project.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $projectid, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "You do not have permission to delete this project.";
    header("Location: project.php");
    exit();
}

// Soft delete: Mark project as deleted and store deletion timestamp
$sql = "UPDATE projects 
        SET is_projectdeleted = 1, projectdeleted_at = NOW() 
        WHERE projectid = ? AND is_projectdeleted = 0";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    $_SESSION['error_message'] = "Database error: " . mysqli_error($conn);
    header("Location: project.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $projectid);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_message'] = "Project deleted successfully.";
} else {
    $_SESSION['error_message'] = "Error deleting project: " . mysqli_error($conn);
}

mysqli_close($conn);
header("Location: project.php");
exit();
?>
