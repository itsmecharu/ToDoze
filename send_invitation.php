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

$message = ""; // To hold feedback message

if (!$project_id || !$user_email) {
    $message = "Missing project ID or user email.";
} else {
    // Fetch the user ID for the email provided
    $sql = "SELECT userid FROM users WHERE useremail = ? ";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        $message = "User not found.";
    } else {
        $invitee_id = $user['userid'];

        // Prevent self-invitation
        if ($inviter_id == $invitee_id) {
            $message = "You cannot invite yourself to the project.";
        } else {
            // Check if already invited or member
            $sql = "SELECT * FROM project_members WHERE projectid = ? AND userid = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $project_id, $invitee_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $existing = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($existing) {
                $message = "This user is already a member or has a pending invitation.";
            } else {
                // Send the invitation
                $sql = "INSERT INTO project_members (projectid, userid, role, status) VALUES (?, ?, 'Member', 'Pending')";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $project_id, $invitee_id);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Invitation sent successfully.";
                } else {
                    $message = "Failed to send invitation: " . mysqli_error($conn);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

mysqli_close($conn);
?>

<!-- HTML to show SweetAlert -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Send Invitation</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php if ($message): ?>
                Swal.fire({
                    title: "<?php echo addslashes($message); ?>",
                    icon: "<?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>",
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    window.location.href = "member.php?projectid=<?php echo $project_id; ?>"; // Redirect to members page after showing the message
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
