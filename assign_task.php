<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$taskid = isset($_GET['taskid']) ? (int)$_GET['taskid'] : 0;

// Fetch task and team info
$sql = "SELECT teamid FROM tasks WHERE taskid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $taskid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$task = mysqli_fetch_assoc($result);
$teamid = $task['teamid'] ?? null;

if (!$teamid) {
    echo "Task not found!";
    exit();
}

// Check if current user is admin of that team
$role_check_sql = "SELECT role FROM team_members WHERE teamid = ? AND userid = ?";
$role_stmt = mysqli_prepare($conn, $role_check_sql);
mysqli_stmt_bind_param($role_stmt, "ii", $teamid, $userid);
mysqli_stmt_execute($role_stmt);
$role_result = mysqli_stmt_get_result($role_stmt);
$user_role = mysqli_fetch_assoc($role_result)['role'] ?? '';

if ($user_role !== 'Admin') {
    echo "Access denied!";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignee'])) {
    $assignee_id = (int) $_POST['assignee'];

    // Check if the selected user is part of the team
    $check_sql = "SELECT * FROM team_members WHERE teamid = ? AND userid = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $teamid, $assignee_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        // Assign the task
        $assign_sql = "UPDATE tasks SET assigned_to = ? WHERE taskid = ?";
        $assign_stmt = mysqli_prepare($conn, $assign_sql);
        mysqli_stmt_bind_param($assign_stmt, "ii", $assignee_id, $taskid);
        mysqli_stmt_execute($assign_stmt);
        echo "<script>alert('Task assigned successfully!'); window.location.href='team_view.php?teamid=$teamid';</script>";
        exit();
    } else {
        echo "<script>alert('User is not a team member!');</script>";
    }
}

// Fetch team members for dropdown
$members_sql = "SELECT u.userid, u.username 
                FROM users u 
                JOIN team_members pm ON u.userid = pm.userid 
                WHERE pm.teamid = ?";
$members_stmt = mysqli_prepare($conn, $members_sql);
mysqli_stmt_bind_param($members_stmt, "i", $teamid);
mysqli_stmt_execute($members_stmt);
$members_result = mysqli_stmt_get_result($members_stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Task</title>
</head>
<body>
    <h2>Assign Task</h2>
    <form method="POST">
        <label for="assignee">Assign to:</label>
        <select name="assignee" required>
            <option value="">--Select Member--</option>
            <?php while ($member = mysqli_fetch_assoc($members_result)): ?>
                <option value="<?= $member['userid'] ?>"><?= htmlspecialchars($member['username']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Assign</button>
    </form>
</body>
</html>