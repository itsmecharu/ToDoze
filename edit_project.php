<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Ensure project ID is provided in the URL
if (!isset($_GET['projectid'])) {
    echo "Project ID is missing.";
    exit();
}

$projectid = $_GET['projectid'];

// Fetch project details (ensure only the owner or member can edit)
$sql = "SELECT projects.* 
        FROM projects 
        JOIN project_members ON projects.projectid = project_members.projectid 
        WHERE projects.projectid = ? 
        AND project_members.userid = ? 
        AND projects.is_projectdeleted = 0";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die('Error preparing query: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $projectid, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "Project not found or you don't have permission to edit.";
    exit();
}

$project = mysqli_fetch_assoc($result);
$projectname = $project['projectname'];
$projectdescription = isset($project['projectdescription']) ? $project['projectdescription'] : '';
$projectduedate = isset($project['projectduedate']) ? $project['projectduedate'] : '';

// Handle project update submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $projectname = trim($_POST['projectname']);
    $projectdescription = isset($_POST['projectdescription']) ? trim($_POST['projectdescription']) : null;

    // Handle empty due date as null
    $projectduedate = trim($_POST['projectduedate']);
    $projectduedate = $projectduedate === '' ? null : $projectduedate;

    $sql = "UPDATE projects 
            SET projectname = ?, projectdescription = ?, projectduedate = ? 
            WHERE projectid = ? 
            AND projectid IN (SELECT projectid FROM project_members WHERE userid = ?)";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        die('Error preparing update query: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "sssii", $projectname, $projectdescription, $projectduedate, $projectid, $userid);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: project.php?projectid=$projectid");
        exit();
    } else {
        echo "Error updating project: " . mysqli_error($conn);
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

                    <a href="project.php" class="nav__link active">
                        <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Project</span>
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
                <label for="projectname">Project Name:</label>
                <input type="text" name="projectname" id="projectname" value="<?php echo htmlspecialchars($projectname); ?>" required maxlength="50"><br>

                <label for="projectdescription">Project Description:</label>
                <input type="text" name="projectdescription" id="projectdescription" value="<?php echo htmlspecialchars($projectdescription); ?>"  maxlength="140"><br>

                <label for="projectduedate">Due Date:</label>
                <input type="datetime-local" id="projectduedate" name="projectduedate" 
                    value="<?php echo $projectduedate ? date('Y-m-d\TH:i', strtotime($projectduedate)) : ''; ?>"><br>

                <button type="submit">Update Project</button>
            </form>
            <br>
            <!-- <a href="project.php">Back to Project List</a> -->
  <a href="project.php" class="back-link">← Back to Project List</a>

        </div>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>