<?php
session_start();
include 'config/database.php';
include 'load_username.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Fetch all invitations (Pending/Accepted/Rejected)
$sql = "SELECT 
    p.projectid,
    p.projectname,
    u_admin.username AS adminname,
    pm_user.status
FROM 
    project_members pm_user
JOIN 
    projects p ON pm_user.projectid = p.projectid
JOIN 
    project_members pm_admin ON pm_admin.projectid = p.projectid AND pm_admin.role = 'Admin'
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Invitations</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
        .accepted-btn, .rejected-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: not-allowed;
            opacity: 0.75;
            font-weight: bold;
        }

        .accepted-btn {
            background-color: #28a745;
            color: white;
        }

        .rejected-btn {
            background-color: #dc3545;
            color: white;
        }

        .action-btn {
            margin-right: 10px;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }

        .accept-btn {
            background-color: #007bff;
        }

        .reject-btn {
            background-color: #6c757d;
        }

    </style>
</head>

<body>
        

<div class="top-right-icons">
  <a href="invitation.php" class="top-icon">
    <ion-icon name="notifications-outline"></ion-icon>
  </a>
    <!-- Profile Icon -->
    <div class="profile-info">
  <a href="#" class="profile-circle" title="<?= htmlspecialchars($username) ?>">
    <ion-icon name="person-outline"></ion-icon>
  </a>
  <span class="username-text"><?= htmlspecialchars($username) ?></span>
</div>
</div>

<div class="logo-container" >
    <img src="img/logo.png" alt="App Logo" class="logo">
  </div>

<div class="l-navbar" id="navbar">
  <nav class="nav">
    <div class="nav__list">
      <a href="dash.php" class="nav__link active"><ion-icon name="home-outline" class="nav__icon"></ion-icon><span class="nav__name">Home</span></a>
      <a href="task.php" class="nav__link"><ion-icon name="add-outline" class="nav__icon"></ion-icon><span class="nav__name">Task</span></a>
      <a href="project.php" class="nav__link"><ion-icon name="folder-outline" class="nav__icon"></ion-icon><span class="nav__name">Project</span></a>
      <a href="review.php" class="nav__link"><ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon><span class="nav__name">Review</span></a>
    </div>
    <a href="logout.php" class="nav__link logout"><ion-icon name="log-out-outline" class="nav__icon"></ion-icon><span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span></a>
  </nav>
</div>



    <div class="i-container">
        <div class="i-box">
            <h2>Project Invitations</h2>

            <?php if (!empty($invitations)) { ?>
                <ul>
                    <?php foreach ($invitations as $invitation) { ?>
                        <li style="margin-bottom: 10px;">
                            Project: <strong><?php echo htmlspecialchars($invitation['projectname']); ?></strong><br>
                            Created by: <?php echo htmlspecialchars($invitation['adminname']); ?><br>

                            <?php if ($invitation['status'] === 'Pending') { ?>
                                <a href="accept.php?projectid=<?php echo $invitation['projectid']; ?>" class="action-btn accept-btn">Accept</a>
                                <a href="reject.php?projectid=<?php echo $invitation['projectid']; ?>" class="action-btn reject-btn">Reject</a>
                            <?php } elseif ($invitation['status'] === 'Accepted') { ?>
                                <button class="accepted-btn" disabled>Accepted</button>
                            <?php } elseif ($invitation['status'] === 'Rejected') { ?>
                                <button class="rejected-btn" disabled>Rejected</button>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
              <div class="centered-content">
              <div class="content-wrapper">
              <img src="img/notify.svg" alt="No tasks yet" />
              <h3><p>No nofication yet 🚀</p></h3>
              </div>
              </div>

            <?php } ?>
        </div>
    </div>

    <!-- IONICONS -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
    <script src="js/dash.js"></script>
</body>
</html>
