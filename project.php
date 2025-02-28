<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$projectname = $projectdescription =  "";

// Handle Project Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $projectname = trim($_POST['projectname']);
    $projectdescription = isset($_POST['projectdescription']) ? trim($_POST['projectdescription']) : null;


    $sql = "INSERT INTO projects (userid, projectname, projectdescription) 
            VALUES (?, ?, ? )";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $userid, $projectname, $projectdescription);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Project added successfully!";
            header("Location: project.php"); // Redirect to avoid form resubmission
            exit();
        } else {
            echo "Error executing query: " . mysqli_error($conn);
        }
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Project</title>
</head>
<body id="body-pd">

    <!-- Navbar -->
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__brand">
                    <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                    <span class="nav__logo">ToDoze</span>
                </div>
                <div class="nav__list">
                    <a href="dash.php" class="nav__link ">
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
                    <a href="profile.php" class="nav__link ">
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

    <!-- Project Form -->
    <h1>ToDoze</h1>
    <div class="container">
        <div class="box">
            <h2>Create Your Projects Here</h2>

            <form class="add-project-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <label for="projectname">Project Name:</label>
                <input type="text" id="projectname" name="projectname" placeholder="Enter project name" required>

                <label for="projectdescription">Project Description:</label>
                <input type="text" id="projectdescription" name="projectdescription" placeholder="Enter project description">

             
 
                <button type="submit">Done</button>
            </form>
        </div>
    </div>

    <!-- IONICONS -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

    <!-- MAIN JS -->
    <script src="js/dash.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
