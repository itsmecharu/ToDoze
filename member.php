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

// Get admin (creator) of the project
$sql = "SELECT userid FROM project_members WHERE projectid = ? AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
$admin_userid = $admin['userid'];
mysqli_stmt_close($stmt);

// Fetch accepted members
$sql = "SELECT users.userid, users.useremail, project_members.role 
        FROM project_members 
        JOIN users ON project_members.userid = users.userid 
        WHERE project_members.projectid = ? AND project_members.status = 'accepted'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$accepted_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch pending (request sent) members
// Fetch pending (request sent) members, excluding the admin
$sql = "SELECT users.userid, users.useremail 
        FROM project_members 
        JOIN users ON project_members.userid = users.userid 
        WHERE project_members.projectid = ? 
        AND project_members.status = 'pending'
        AND users.userid != ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $projectId, $admin_userid);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pending_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch users who are eligible to be invited
$sql = "SELECT userid, useremail 
        FROM users 
        WHERE userid != ? 
        AND userid NOT IN (
            SELECT userid FROM project_members WHERE projectid = ?
        )";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $userid, $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$available_users = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Members</title>
    <link rel="stylesheet" href="css/dash.css">
    <style>
        .members-list ul, .pending-list ul {
            list-style: none;
            padding-left: 0;
        }
        .members-list li, .pending-list li {
            margin-bottom: 10px;
        }
        .remove-btn {
            margin-left: 10px;
            color: red;
            text-decoration: none;
        }
        .admin-label {
            color: green;
            font-weight: bold;
            margin-left: 10px;
        }
        .message.success {
            color: green;
            margin: 10px 0;
        }
        .message.error {
            color: red;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Project Members</h2>

    <?php if (isset($_GET['status'])): ?>
        <div class="message <?php echo $_GET['status'] === 'success' ? 'success' : 'error'; ?>">
            <?php
            if ($_GET['status'] === 'success') {
                echo "Invitation sent successfully.";
            } else {
                echo htmlspecialchars($_GET['message'] ?? "An error occurred.");
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Accepted Members -->
    <div class="members-list section">
        <h3>Accepted Members</h3>
        <?php if (!empty($accepted_members)) { ?>
            <ul>
                <?php foreach ($accepted_members as $member) { ?>
                    <li>
                        <?php echo htmlspecialchars($member['useremail']); ?>
                        <span class="role">(<?php echo htmlspecialchars($member['role']); ?>)</span>
                        <?php if ($member['userid'] != $admin_userid) { ?>
                            <a href="remove_member.php?userid=<?php echo $member['userid']; ?>&projectid=<?php echo $projectId; ?>" 
                               class="remove-btn" 
                               onclick="return confirm('Are you sure you want to remove this member?');">
                               Remove
                            </a>
                        <?php } else { ?>
                            <span class="admin-label">[Admin]</span>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>No accepted members yet.</p>
        <?php } ?>
    </div>

    <!-- Pending Invitations -->
    <div class="pending-list section">
        <h3>Pending Invitations (Request Sent)</h3>
        <?php if (!empty($pending_members)) { ?>
            <ul>
                <?php foreach ($pending_members as $pending) { ?>
                    <li>
                        <?php echo htmlspecialchars($pending['useremail']); ?> (Pending)
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <p>No pending invitations.</p>
        <?php } ?>
    </div>

    <!-- Add Member Form -->
    <div class="add-member-form section">
        <h3>Send Invitation</h3>
        <form action="send_invitation.php" method="POST">
            <input type="hidden" name="projectid" value="<?php echo $projectId; ?>">
            <label for="email">Member Email:</label>
            <select name="useremail" id="useremail" required>
                <option value="" disabled selected>Select User</option>
                <?php if (!empty($available_users)) { ?>
                    <?php foreach ($available_users as $user) { ?>
                        <option value="<?php echo htmlspecialchars($user['useremail']); ?>">
                            <?php echo htmlspecialchars($user['useremail']); ?>
                        </option>
                    <?php } ?>
                <?php } else { ?>
                    <option value="" disabled>No users available to invite</option>
                <?php } ?>
            </select>
            <button type="submit">Send Invite</button>
        </form>
    </div>

    <button onclick="window.history.back();" class="back-btn">Go Back</button>
</div>

</body>
</html>
