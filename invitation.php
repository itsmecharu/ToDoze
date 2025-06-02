<?php
session_start();
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Fetch all invitations
$sql = "SELECT 
    p.teamid,
    p.teamname,
    u_admin.username AS adminname,
    pm_user.status,
    pm_user.invited_at
FROM 
    team_members pm_user
JOIN 
    teams p ON pm_user.teamid = p.teamid
JOIN 
    team_members pm_admin ON pm_admin.teamid = p.teamid AND pm_admin.role = 'Admin'
JOIN 
    users u_admin ON pm_admin.userid = u_admin.userid
WHERE 
    pm_user.userid = ? AND
    pm_user.userid != pm_admin.userid
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$invitations = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// 🔴 Check for pending invitations
$_SESSION['has_pending_invites'] = false;
foreach ($invitations as $inv) {
    if ($inv['status'] === 'Pending') {
        $_SESSION['has_pending_invites'] = true;
        break;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<?php include 'navbar.php'; ?>
<?php include 'toolbar.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .invitation-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            flex-wrap: wrap;
        }

        .invitation-info {
            flex: 1;
            min-width: 200px;
        }

        .invitation-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .action-btn,
        .accepted-btn,
        .rejected-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .action-btn.accept-btn {
            background-color: #007bff;
            color: white;
        }

        .action-btn.reject-btn {
            background-color: #ef552a;
            color: white;
        }

        .accepted-btn {
            background-color: #28a745;
            color: white;
            cursor: not-allowed;
            opacity: 0.8;
        }

        .rejected-btn {
            background-color: red;
            color: white;
            cursor: not-allowed;
            opacity: 0.8;
        }
    </style>
</head>

<body>


    

    <div class="container">
        <div class="box">
            <h2>Notifications</h2>

            <?php if (!empty($invitations)) { ?>
                <ul>
                    <?php foreach ($invitations as $invitation) { ?>
                        <li class="invitation-item">
                            <div class="invitation-info">
                                <div><strong>Hi! You have a request to join:</strong> <?= htmlspecialchars($invitation['teamname']) ?></div>
                                <div>From: <?= htmlspecialchars($invitation['adminname']) ?></div>
                                <div>Date: <?= htmlspecialchars($invitation['invited_at']) ?></div>
                            </div>

                            <div class="invitation-actions">
                                <?php if ($invitation['status'] === 'Pending') { ?>
                                    <a href="accept.php?teamid=<?= $invitation['teamid'] ?>"
                                        class="action-btn accept-btn">Accept</a>
                                    <a href="reject.php?teamid=<?= $invitation['teamid'] ?>"
                                        class="action-btn reject-btn">Reject</a>
                                <?php } elseif ($invitation['status'] === 'Accepted') { ?>
                                    <button class="accepted-btn" style="background-color : red;" disabled>Accepted</button>
                                <?php } elseif ($invitation['status'] === 'Removed') { ?>
                                    <button class="rejected-btn" style="background-color : red;" disabled>Removed</button>
                                <?php } elseif ($invitation['status'] === 'Rejected') { ?>
                                    <button class="rejected-btn" style="background-color : red;" disabled>Rejected</button>
                                <?php } ?>
                            </div>
                        </li>

                    <?php } ?>
                </ul>
            <?php } else { ?>
                <div class="centered-content">
                    <div class="content-wrapper">
                        <img src="img/notify.svg" alt="No tasks yet" />
                        <h3>
                            <p>No invitation yet 🚀</p>
                        </h3>
                    </div>
                </div>

            <?php } ?>
        </div>
    </div>
<script>
// Dropdown functionality
document.querySelectorAll('.nav__dropdown-btn').forEach(button => {
  button.addEventListener('click', () => {
    const dropdown = button.closest('.nav__dropdown');
    dropdown.classList.toggle('active');
  });
});
</script>
    <!-- IONICONS -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
    <script src="js/dash.js"></script>
    
</body>

</html>