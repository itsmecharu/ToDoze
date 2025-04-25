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

// Fetch current project members
$sql = "SELECT users.userid, users.useremail, project_members.role 
        FROM project_members 
        JOIN users ON project_members.userid = users.userid 
        WHERE project_members.projectid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch users eligible to be invited (not in project and not current user)
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
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
        .members-list ul {
            list-style: none;
            padding-left: 0;
        }
        .members-list li {
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

    <!-- Existing Members -->
    <div class="members-list">
        <h3>Existing Members</h3>
        <?php if (!empty($members)) { ?>
            <ul>
                <?php foreach ($members as $member) { ?>
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
                    <option value="" disabled selected>No users available</option>
                <?php } ?>
            </select>
            <button type="submit">Add</button>
        </form>
    </div>

    <button onclick="window.history.back();" class="back-btn">Go Back</button>
</div>

</body>
</html>
