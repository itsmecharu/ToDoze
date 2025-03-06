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

// Fetch existing members of the project
$sql = "SELECT users.userid, users.useremail, project_members.role 
        FROM project_members 
        JOIN users ON project_members.userid = users.userid 
        WHERE project_members.projectid = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Members</title>
    <link rel="stylesheet" href="css/form.cssS">
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
        <form action="add_member.php" method="POST">
            <input type="hidden" name="projectid" value="<?php echo $projectId; ?>">
            <label for="email">Member Email:</label>
            <input type="email" id="useremail" name="useremail" required>
            <button type="submit">Add</button>
        </form>
    </div>

    <button onclick="window.history.back();" class="back-btn">Go Back</button>
</div>

</body>
</html>
