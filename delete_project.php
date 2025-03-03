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
if (!isset($_GET['id'])) {
    echo "Project ID is missing.";
    exit();
}

$projectid = $_GET['id'];

// Check if the user is part of the project and has 'Admin' role in the project_members table
$sql = "SELECT pm.userid 
        FROM project_members pm 
        WHERE pm.projectid = ? AND pm.userid = ? AND pm.role = 'Admin'";
$stmt = mysqli_prepare($conn, $sql);

// Ensure the query was prepared successfully
if ($stmt === false) {
    echo "Error preparing query: " . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $projectid, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// If no project is found or the user does not have permission to delete the project
if (mysqli_num_rows($result) == 0) {
    echo "You do not have permission to delete this project or the project does not exist.";
    exit();
}

// Debugging: Output the project ID to verify it's correct
echo "Attempting to delete project with ID: $projectid<br>";

// Soft delete: Mark project as deleted and store deletion timestamp
$sql = "UPDATE projects 
        SET is_projectdeleted = 1, projectdeleted_at = NOW() 
        WHERE projectid = ? AND is_projectdeleted = 0"; // Ensure we only delete if not already deleted
$stmt = mysqli_prepare($conn, $sql);

// Ensure the query was prepared successfully
if ($stmt === false) {
    echo "Error preparing query: " . mysqli_error($conn);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $projectid);

// Execute the deletion query
if (mysqli_stmt_execute($stmt)) {
    // Output success message for debugging
    echo "Project successfully deleted.<br>";
    $_SESSION['success_message'] = "Project deleted successfully.";
    header("Location: project.php"); // Redirect to project list
    exit();
} else {
    echo "Error executing query: " . mysqli_error($conn);
    exit();
}
?>
