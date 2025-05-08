<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Ensure team ID is provided in the URL
if (!isset($_GET['teamid'])) {
    echo "Project ID is missing.";
    exit();
}

$teamid = $_GET['teamid'];

// Fetch team details (ensure only the owner or member can edit)
$sql = "SELECT teams.* 
        FROM teams 
        JOIN team_members ON teams.teamid = team_members.teamid 
        WHERE teams.teamid = ? 
        AND team_members.userid = ? 
        AND teams.is_teamdeleted = 0";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die('Error preparing query: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $teamid, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "Project not found or you don't have permission to edit.";
    exit();
}

$team = mysqli_fetch_assoc($result);
$teamname = $team['teamname'];
$teamdescription = isset($team['teamdescription']) ? $team['teamdescription'] : '';


// Handle team update submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teamname = trim($_POST['teamname']);
    $teamdescription = isset($_POST['teamdescription']) ? trim($_POST['teamdescription']) : null;


    $sql = "UPDATE teams 
            SET teamname = ?, teamdescription = ?
            WHERE teamid = ? 
            AND teamid IN (SELECT teamid FROM team_members WHERE userid = ?)";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        die('Error preparing update query: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssii", $teamname, $teamdescription, $teamid, $userid);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: team.php?teamid=$teamid");
        exit();
    } else {
        echo "Error updating team: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Project</title>
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
                        <a href="invitation.php">
                            <ion-icon name="notifications-outline" class="nav__toggle"></ion-icon>
                        </a>
                    </span>
                </div>

                <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                        <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Home</span>
                    </a>

                    <a href="task.php" class="nav__link">
                        <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Task</span>
                    </a>

                    <a href="team.php" class="nav__link active">
                        <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Team </span>
                    </a>

                    <a href="review.php" class="nav__link">
                        <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Review</span>
                    </a>

                    <a href="#" class="nav__link">
                        <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Profile</span>
                    </a>
                </div>
            </div>

            <a href="logout.php" class="nav__link logout">
                <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                <span class="nav__name" style="color: red;">Log Out</span>
            </a>
        </nav>
    </div>

    <div class="container">
        <div class="box">
            <h2>Edit Project</h2>
            <form method="POST" action="">
                <label for="teamname">Project Name:</label>
                <input type="text" name="teamname" id="teamname" value="<?php echo htmlspecialchars($teamname); ?>" required maxlength="50"><br>

                <label for="teamdescription">Project Description:</label>
                <input type="text" name="teamdescription" id="teamdescription" value="<?php echo htmlspecialchars($teamdescription); ?>"  maxlength="140"><br>


                <button type="submit">Update Project</button>
            </form>
            <br>
            <!-- <a href="team.php">Back to Project List</a> -->
  <a href="team.php" class="back-link">← Back</a>

        </div>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>