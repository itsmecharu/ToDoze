<?php

session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Handle project creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $projectName = trim($_POST['projectname']);
    $projectDescription = trim($_POST['projectdescription']);
    $projectDueDate = trim($_POST['projectduedate']);

    // Insert into 'projects' table
    $sql = "INSERT INTO projects (projectname, projectdescription, projectduedate) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $projectName, $projectDescription, $projectDueDate);
        if (mysqli_stmt_execute($stmt)) {
            $projectId = mysqli_insert_id($conn);

            // Assign the creator as "Admin" in project_members
            $sql = "INSERT INTO project_members (userid, projectid, role) VALUES (?, ?, 'Admin')";
            $stmt2 = mysqli_prepare($conn, $sql);
            if ($stmt2) {
                mysqli_stmt_bind_param($stmt2, "ii", $userid, $projectId);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
            }

            $_SESSION['success_message'] = "Project created successfully!";
            header("Location: project.php");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch projects based on membership
$sql = $sql = "SELECT DISTINCT p.* 
FROM projects p
JOIN project_members pm ON p.projectid = pm.projectid
WHERE 
    (
        pm.userid = ? AND 
        (pm.role = 'Admin' OR pm.status = 'Accepted')
    )
    AND p.is_projectdeleted = 0";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <a href="invitation.php"> <!-- Added a link to redirect to the invitations page -->
                            <ion-icon name="notifications-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
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

                    <a href="project.php" class="nav__link active">
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
        <button id="createProjectBtn" class="create-btn"> + Create New Project</button>
        <div id="projectForm" class="box" style="display:none;">
            <h2>Create New Project</h2>
            <form method="POST">
                <label for="projectname">Project Name:</label>
                <input type="text" id="projectname" name="projectname" required>

                <label for="projectdescription">Project Description:</label>
                <input type="text" id="projectdescription" name="projectdescription" style="height: 80px;">

                <label for="projectduedate">Due Date:</label>
                <input type="datetime-local" id="projectduedate" name="projectduedate" style="width:35%">

                <button type="submit">Create Project</button>

            </form>
        </div>
    </div>

    <div class="container">
        <div class="box">
            <h2>Your Projects</h2>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="project-list">
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="project-box">


                            <a href="project_view.php?projectid=<?php echo $row['projectid']; ?>" class="project-link">
                                <h3><?php echo htmlspecialchars($row['projectname']); ?></h3>

                            </a>
                            <div class="project-actions">
                                <a href="edit_project.php?projectid=<?php echo $row['projectid']; ?>" class="edit-btn">Edit</a>
                                <a href="#" class="delete-btn"
                                    onclick="confirmDelete(<?php echo $row['projectid']; ?>)">Delete</a>
                            </div>

                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No projects found.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- for delete msg -->
    <script>
        function confirmDelete(projectId) {
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "delete_project.php?projectid=" + projectId;
                }
            });
        }
    </script>



    <script>
        document.getElementById("createProjectBtn").addEventListener("click", function () {
            document.getElementById("projectForm").style.display = "block";
            this.style.display = "none";
        });
    </script>

    <!-- IONICONS -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

    <!-- MAIN JS -->
    <script src="js/dash.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

</body >
</html >

            <?php mysqli_close($conn); ?>