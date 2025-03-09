<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$projectId = $_GET['projectid'] ?? null;

if (!$projectId) {
    die("Project not found!");
}

// Fetch the admin (project creator) of the project
$sql = "SELECT userid FROM project_members WHERE projectid = ? AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
$admin_userid = $admin['userid']; // Admin's (creator's) userid
mysqli_stmt_close($stmt);

// Fetch users who are not the admin and who are not already part of the project
$sql = "SELECT userid, useremail FROM users WHERE userid != ? AND userid NOT IN (SELECT userid FROM project_members WHERE projectid = ?)";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $admin_userid, $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Members</title>
    <link rel="stylesheet" href="css/dash.css">
</head>
<body>

<div class="container">
    <h2>Project Members</h2>

    <div class="members-list">
        <?php if (!empty($members)) { ?>
            <ul>
                <?php foreach ($members as $member) { ?>
                    <li>
                        <strong>Email:</strong> <?php echo htmlspecialchars($member['useremail']); ?>
                        <span class="role">Role: <?php echo htmlspecialchars($member['role']); ?></span>

                        <!-- Remove Member Button -->
                        <a href="remove_member.php?userid=<?php echo $member['userid']; ?>&projectid=<?php echo $projectId; ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this member?');">Remove</a>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>No members found for this project.</p>
        <?php } ?>
    </div>

   <!-- Add Member Form -->
<div class="add-member-form">
    <h3>Add Member</h3>
    <form action="send_invitation.php" method="POST">
        <input type="hidden" name="projectid" value="<?php echo $projectId; ?>">
        <label for="email">Member Email:</label>
        <select name="useremail" id="useremail" required>
            <option value="" disabled selected>Select User</option>
            <?php if (!empty($users)) { ?>
                <?php foreach ($users as $user) { ?>
                    <option value="<?php echo htmlspecialchars($user['useremail']); ?>"><?php echo htmlspecialchars($user['useremail']); ?></option>
                <?php } ?>
            <?php } else { ?>
                <option value="">No users available</option>
            <?php } ?>
        </select>
        <button > <a href="send_reminder">Add</a> </button>
    </form>
</div>


    <button onclick="window.history.back();" class="back-btn">Go Back</button>
</div>

</body>
</html>
