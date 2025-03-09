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
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">

</head>

<body id="body-pd">
    <!-- Navbar -->
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__brand">
                    <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                    <span class="nav__logo" style="display: flex; align-items: center;">
                        ToDoze
                        <a href="invitation.php "> <!-- Added a link to redirect to the invitations page -->
                            <ion-icon name="notifications-outline" class="nav__toggle " id="nav-toggle" ></ion-icon>
                        </a>
                    </span>
                </div>

                <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                        <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Home</span>
                    </a>

                    <a href="task.php" class="nav__link ">
                        <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Task</span>
                    </a>

                    <a href="project.php" class="nav__link">
                        <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Project</span>
                    </a>

                    <a href="review.php" class="nav__link">
                        <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Review</span>
                    </a>

                    <a href="profile.php" class="nav__link">
                        <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Profile</span>
                    </a>
                </div>
            </div>

            <a href="logout.php" class="nav__link logout">
                <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                <span class="nav__name">Log Out</span>
            </a>
        </nav>
    </div>



    <div class="container">
        <!-- Add Task Section -->
        <div class="box" >
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
                <h3 style="center" ;>You have no pending invitations.</h3>
            <?php } ?>
            </div>
            </div>

            <!-- IONICONS -->
            <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

            <!-- MAIN JS -->
            <script src="js/dash.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>