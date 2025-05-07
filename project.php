<?php

session_start();
include 'config/database.php';
include 'load_username.php';

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
  $projectDueDate = $projectDueDate === '' ? null : $projectDueDate;

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


// Initialize variables with default values
$totalTasks = 0;
$pendingTasks = 0;
$completedTasks = 0;
$overdueTasks = 0;

try {
  // Fetch task statistics for the logged-in user
  $sqlTotalTasks = "SELECT COUNT(*) as total FROM tasks WHERE userid = ?";
  $sqlPendingTasks = "SELECT COUNT(*) as pending FROM tasks WHERE userid = ? AND taskstatus = 'Pending'";
  $sqlCompletedTasks = "SELECT COUNT(*) as completed FROM tasks WHERE userid = ? AND taskstatus = 'Completed'";
  $sqlOverdueTasks = "SELECT COUNT(*) as overdue FROM tasks WHERE userid = ? AND is_overdue = 1";

  // Total Tasks
  $stmtTotal = $conn->prepare($sqlTotalTasks);
  $stmtTotal->bind_param("i", $userid);
  $stmtTotal->execute();
  $resultTotal = $stmtTotal->get_result();
  if ($row = $resultTotal->fetch_assoc()) {
    $totalTasks = $row['total'];
  }
  $stmtTotal->close();

  // Pending Tasks
  $stmtPending = $conn->prepare($sqlPendingTasks);
  $stmtPending->bind_param("i", $userid);
  $stmtPending->execute();
  $resultPending = $stmtPending->get_result();
  if ($row = $resultPending->fetch_assoc()) {
    $pendingTasks = $row['pending'];
  }
  $stmtPending->close();

  // Completed Tasks
  $stmtCompleted = $conn->prepare($sqlCompletedTasks);
  $stmtCompleted->bind_param("i", $userid);
  $stmtCompleted->execute();
  $resultCompleted = $stmtCompleted->get_result();
  if ($row = $resultCompleted->fetch_assoc()) {
    $completedTasks = $row['completed'];
  }
  $stmtCompleted->close();
  $stmtOverdue = $conn->prepare($sqlOverdueTasks);
  $stmtOverdue->bind_param("i", $userid);
  $stmtOverdue->execute();
  $resultOverdue = $stmtOverdue->get_result();
  if ($row = $resultOverdue->fetch_assoc()) {
    $overdueTasks = $row['overdue'];
  }
  $stmtOverdue->close();





} catch (Exception $e) {
  // Log error but don't show to user
  error_log("Database error: " . $e->getMessage());
  // You might want to set default values here if the queries fail
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Project</title>
  <link rel="stylesheet" href="css/dash.css">
  <link rel="stylesheet" href="css/extra.css">

  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body id="body-pd">
  <div class="top-bar">
    <div class="top-right-icons">
      <!-- Notification Icon -->
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
  </div>

  <!-- Logo Above Sidebar -->
  <div class="logo-container">
    <img src="img/logo.png" alt="Logo" class="logo">
  </div>

  <!-- Sidebar Navigation -->
  <div class="l-navbar" id="navbar">
    <nav class="nav">
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
      </div>
      <a href="logout.php" class="nav__link logout">
        <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
        <span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span>
      </a>
    </nav>
  </div>
   <!-- Task Summary Section -->
   <div class="bottom-container-wrapper">
      <!-- Task Statistics at the top -->
      <div class="bottom-container">
        <h2>Task Statistics</h2>
        <div class="stats-grid">
          <div class="stat-item"style="background-color: lightblue">
            <div class="stat-label">Total</div>
            <div class="stat-value" ><?php echo $totalTasks; ?></div>
          </div>
          <div class="stat-item" style="background-color: #2ecc71;">
            <div class="stat-label">Completed</div>
            <div class="stat-value" ><?php echo $completedTasks; ?></div>
          </div>
          <div class="stat-item"style="background-color: #f39c12;">
            <div class="stat-label">Pending</div>
            <div class="stat-value" ><?php echo $pendingTasks; ?></div>
          </div>
          <div class="stat-item"style="background-color:rgb(216, 39, 57);">
            <div class="stat-label">Overdue</div>
            <div class="stat-value" ><?php echo $overdueTasks; ?></div>
          </div>
        </div> 

</div>
  <div class="project-container">
    <!-- Button -->
    <button id="createProjectBtn" class="create-btn"> + Create New Project</button>

    <!-- Modal -->
    <div id="projectModal" class="modal-overlay" style="display: none;">
      <div class="modal-content">
        <span class="close-modal" id="closeModalBtn">&times;</span>
        <h2>Create New Project</h2>
        <form method="POST">
          <label for="projectname">Project Name:</label>
          <input type="text" id="projectname" name="projectname" required>

          <label for="projectdescription">Project Description:</label>
          <input type="text" id="projectdescription" name="projectdescription" style="height: 80px;">

          <label for="projectduedate">Select Due Date 📅:</label>
          <input type="datetime-local" id="projectduedate" name="projectduedate" style="width:35%">

          <button type="submit">Create Project</button>

        </form>
      </div>
    </div>
  </div>
  </div>

  <div class="project-box">
    <h2>Your Projects</h2>
    <?php if (mysqli_num_rows($result) > 0): ?>
      <div class="project-list">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="project-box">
            <!-- Project Name on its own line -->
            <div class="project-title">
              <a href="project_view.php?projectid=<?php echo $row['projectid']; ?>">
                <h3><?php echo htmlspecialchars($row['projectname']); ?></h3>
              </a>
            </div>

            <!-- All other info in a single line -->
            <div class="project-info-line">
              <?php if (!empty($row['projectdescription'])): ?>
                <div class="project-description">
                  <strong>Description:</strong> <?php echo htmlspecialchars($row['projectdescription']); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($row['projectduedate'])): ?>
                <div class="project-duedate">
                  <strong>Due:</strong> <?php echo htmlspecialchars($row['projectduedate']); ?>
                </div>
              <?php endif; ?>

              

              <div class="project-actions">
              <a href="project_task.php?projectid=<?php echo $row['projectid']; ?>" class="edit-btn" title="Edit">
                  <ion-icon name="add-circle-outline"></ion-icon> Edit
                </a>
              <a href="member.php?projectid=<?php echo $row['projectid']; ?>" class="edit-btn" title="Edit">
                  <ion-icon name="people-outline"></ion-icon>Member
                </a>
                <a href="edit_project.php?projectid=<?php echo $row['projectid']; ?>" class="edit-btn" title="Edit">
                  <ion-icon name="create-outline"></ion-icon> Edit
                </a>
                <a href="#" class="delete-btn" title="Delete" onclick="confirmDelete(<?php echo $row['projectid']; ?>)">
                  <ion-icon name="trash-outline"></ion-icon> Delete
                </a>
              </div>
            </div>

          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="centered-content">
        <div class="content-wrapper">
          <img src="img/noproject.svg" alt="No tasks yet" />
          <h3>
            <p>No project yet. Add your first one! 🚀</p>
          </h3>
        </div>
      </div>
    <?php endif; ?>
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
    const modal = document.getElementById("projectModal");
    const openBtn = document.getElementById("createProjectBtn");
    const closeBtn = document.getElementById("closeModalBtn");

    // Open modal
    openBtn.addEventListener("click", () => {
      modal.style.display = "flex";
      openBtn.style.display = "none";
    });

    // Close modal
    closeBtn.addEventListener("click", closeModal);

    // Close modal when clicking outside the content
    window.addEventListener("click", function (event) {
      if (event.target === modal) {
        closeModal();
      }
    });

    function closeModal() {
      modal.style.display = "none";
      openBtn.style.display = "inline-block"; // or "block" based on styling
    }
  </script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const descriptions = document.querySelectorAll('.task-details-left .info');

  descriptions.forEach(desc => {
    if (desc.textContent.startsWith("Description:")) {
      const fullText = desc.textContent.trim().replace("Description:", "").trim();
      if (fullText.length > 8) {
        const shortText = fullText.substring(0, 8) + "..........";

        let toggled = false;
        desc.textContent = "Description: " + shortText;
        desc.classList.add("truncated");

        desc.addEventListener("click", function () {
          toggled = !toggled;
          desc.textContent = "Description: " + (toggled ? fullText : shortText);
        });
      }
    }
  });
});
</script>

  <!-- IONICONS -->
  <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

  <!-- MAIN JS -->
  <script src="js/dash.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>

<?php mysqli_close($conn); ?>