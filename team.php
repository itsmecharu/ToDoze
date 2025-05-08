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
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';


// Handle team creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $teamName = trim($_POST['teamname']);
  $teamDescription = trim($_POST['teamdescription']);
  $teamDueDate = trim($_POST['teamduedate']);
  $teamDueDate = $teamDueDate === '' ? null : $teamDueDate;

  // Insert into 'teams' table
  $sql = "INSERT INTO teams (teamname, teamdescription, teamduedate) VALUES (?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sss", $teamName, $teamDescription, $teamDueDate);
    if (mysqli_stmt_execute($stmt)) {
      $teamId = mysqli_insert_id($conn);

      // Assign the creator as "Admin" in team_members
      $sql = "INSERT INTO team_members (userid, teamid, role) VALUES (?, ?, 'Admin')";
      $stmt2 = mysqli_prepare($conn, $sql);
      if ($stmt2) {
        mysqli_stmt_bind_param($stmt2, "ii", $userid, $teamId);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
      }

      $_SESSION['success_message'] = "Project created successfully!";
      header("Location: team.php");
      exit();
    }
    mysqli_stmt_close($stmt);
  }
}
// fetching 
$baseQuery = "
SELECT DISTINCT p.*, pm.role 
FROM teams p
JOIN team_members pm ON p.teamid = pm.teamid
WHERE 
    (
        pm.userid = ? AND 
        (pm.role = 'Admin' OR pm.status = 'Accepted')
    )
    AND p.is_teamdeleted = 0
";

if ($filter === 'completed') {
    $baseQuery .= " AND p.teamstatus = 'Completed'";
} elseif ($filter === 'pending' || $filter === 'all') {
    $baseQuery .= " AND (p.teamstatus IS NULL OR p.teamstatus = 'Pending')";
}

$stmt = mysqli_prepare($conn, $baseQuery);
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
  <div class="top-bar">
    <div class="top-right-icons">
      <!-- Notification Icon -->
      <a href="invitation.php" class="top-icon">
        <ion-icon name="notifications-outline"></ion-icon>
      </a>

      <!-- Profile Icon -->
      <div class="profile-info">
        <a href="profile.php" class="profile-circle" title="<?= htmlspecialchars($username) ?>">
          <ion-icon name="person-outline"></ion-icon>
        </a>
        <span class="username-text"><?= htmlspecialchars($username) ?></span>
      </div>
    </div>
  </div>

<!-- filters -->
  <div class="filter-container">
  <div style="display: flex; justify-content: center;">
   <!-- Button -->
   <button id="createProjectBtn" class="create-btn"> + Create New Team</button>
</div>

<a href="team.php?filter=pending" class="task-filter <?= $filter == 'pending' || $filter == 'all' ? 'active' : '' ?>">🕒 Pending</a>
<a href="team.php?filter=completed" class="task-filter <?= $filter == 'completed' ? 'active' : '' ?>">✅ Completed</a>

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
        <a href="team.php" class="nav__link active">
          <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
          <span class="nav__name">Team</span>
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

  <div class="container">
    <!-- Modal -->
    <div id="teamModal" class="modal-overlay" style="display: none;">
      <div class="modal-content">
        <span class="close-modal" id="closeModalBtn">&times;</span>
        <h2>Create New Team</h2>
        <form method="POST">
          <label for="teamname">Team Name:</label>
          <input type="text" id="teamname" name="teamname" required>

          <label for="teamdescription">Team  Description:</label>
          <input type="text" id="teamdescription" name="teamdescription" style="height: 80px;">

          <label for="teamduedate">Select Due Date 📅:</label>
          <input type="datetime-local" id="teamduedate" name="teamduedate" style="width:35%">

          <button type="submit">Create Team </button>

        </form>
      </div>
    </div>
  </div>
  </div>

  <div class="box">
    <h2>Your Projects</h2>
    <?php if (mysqli_num_rows($result) > 0): ?>
      <div class="team-list">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <?php $role = $row['role']; ?>

          <div class="team-box">
            <!-- Project Name on its own line -->
            <div class="team-title">
              <a href="team_view.php?teamid=<?php echo $row['teamid']; ?>">
                <h3><?php echo htmlspecialchars($row['teamname']); ?></h3>
              </a>
            </div>

            <!-- All other info in a single line -->
            <div class="team-info-line">
              <?php if (!empty($row['teamdescription'])): ?>
                <div class="team-description">
                  <strong>Description:</strong> <?php echo htmlspecialchars($row['teamdescription']); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($row['teamduedate'])): ?>
                <div class="team-duedate">
                  <strong>Due:</strong> <?php echo htmlspecialchars($row['teamduedate']); ?>
                </div>
              <?php endif; ?>



              <div class="team-actions">
                <?php if ($role === 'Admin'): ?>
                  <a href="team_task.php?teamid=<?php echo $row['teamid']; ?>" class="edit-btn" title="Edit">
                    <ion-icon name="add-circle-outline"></ion-icon>Task
                  </a>
                  <a href="member.php?teamid=<?php echo $row['teamid']; ?>" class="edit-btn" title="Edit">
                    <ion-icon name="people-outline"></ion-icon> Member
                  </a>
                  <a href="edit_team.php?teamid=<?php echo $row['teamid']; ?>" class="edit-btn" title="Edit">
                    <ion-icon name="create-outline"></ion-icon> Edit
                  </a>
                  <a href="#" class="delete-btn" title="Delete" onclick="confirmDelete(<?php echo $row['teamid']; ?>)">
                    <ion-icon name="trash-outline"></ion-icon> Delete
                  </a>
                <?php else: ?>
                  <span class="view-only-msg">🔒 View Only</span>
                <?php endif; ?>
              </div>

            </div>

          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="centered-content">
        <div class="content-wrapper">
          <img src="img/notask.svg" alt="No tasks yet" />
          <h3>
            <p>Nothing yet! 🚀</p>
          </h3>
        </div>
      </div>
    <?php endif; ?>
  </div>


  <!-- for delete msg -->
  <script>
    function confirmDelete(teamId) {
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
          window.location.href = "delete_team.php?teamid=" + teamId;
        }
      });
    }
  </script>



  <script>
    const modal = document.getElementById("teamModal");
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



  <!-- IONICONS -->
  <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

  <!-- MAIN JS -->
  <script src="js/dash.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>

<?php mysqli_close($conn); ?>