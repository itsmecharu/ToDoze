<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Fetch pending invitations
$sql = "SELECT projectid, role FROM project_members WHERE userid = ? AND status = 'Pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$invitations = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Invitations</title>
</head>
<body>

<h2>Pending Invitations</h2>

<?php if (!empty($invitations)) { ?>
    <ul>
        <?php foreach ($invitations as $invitation) { ?>
            <li>
                Project ID: <?php echo $invitation['projectid']; ?>
                Role: <?php echo $invitation['role']; ?>
                <a href="accept_invitation.php?projectid=<?php echo $invitation['projectid']; ?>">Accept</a> |
                <a href="reject_invitation.php?projectid=<?php echo $invitation['projectid']; ?>">Reject</a>
            </li>
        <?php } ?>
    </ul>
<?php } else { ?>
    <p>You have no pending invitations.</p>
<?php } ?>

</body>
</html>
