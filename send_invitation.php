<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$inviter_id = $_SESSION['userid'];
$project_id = $_POST['projectid'] ?? $_GET['projectid'] ?? null;
$user_email = $_POST['useremail'] ?? null;

if (!$project_id || !$user_email) {
    die("Missing project ID or user email.");
}



// Fetch the user ID for the email provided
$sql = "SELECT userid FROM users WHERE useremail = ? ";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $user_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    die("User not found.");
}

$invitee_id = $user['userid'];

// Prevent sending an invitation to self
if ($inviter_id == $invitee_id) {
    die("You cannot invite yourself to the project.");
}

// Check if the user is already in the project (including pending)
$sql = "SELECT * FROM project_members WHERE projectid = ? AND userid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $project_id, $invitee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($existing) {
    die("This user is already a member or has a pending invitation.");
}

// Send the invitation (add to project_members with 'Pending' status)
$sql = "INSERT INTO project_members (projectid, userid, role, status) VALUES (?, ?, 'Member', 'Pending')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $project_id, $invitee_id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: members.php?projectid=$project_id&status=success");
} else {
    $error = urlencode("Failed to send invitation: " . mysqli_error($conn));
    header("Location: members.php?projectid=$project_id&status=error&message=$error");
}
// exit();


mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
