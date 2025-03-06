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

$projectid = $_GET['projectid'];  // Get the project ID from the URL

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
    $projectduedate = isset($_POST['projectduedate']) ? trim($_POST['projectduedate']) : null;

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
        $_SESSION['success_message'] = "Project updated successfully!";
        header("Location: project_view.php?projectid=$projectid");
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
<body>
    <div class="container">
        <div class="box">
            <h2>Edit Project</h2>
            <form method="POST" action="">
                <label for="projectname">Project Name:</label>
                <input type="text" name="projectname" id="projectname" value="<?php echo htmlspecialchars($projectname); ?>" required><br>

                <label for="projectdescription">Project Description:</label>
                <input type="text" name="projectdescription" id="projectdescription" value="<?php echo htmlspecialchars($projectdescription); ?>"> <br>

                <label for="projectduedate">Due Date:</label>
                <input type="datetime-local" id="projectduedate" name="projectduedate" 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($projectduedate)); ?>"><br>

                <button type="submit">Update Project</button>
            </form>
            <br>
            <a href="project.php">Back to Project List</a>
        </div>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
