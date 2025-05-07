<?php
session_start();
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$projectId = isset($_GET['projectid']) ? (int) $_GET['projectid'] : null;

if (!$projectId) {
    echo "Project not found!";
    exit();
}

// Fetch project details
$sql = "SELECT * FROM projects WHERE projectid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$project) {
    echo "Project not found!";
    exit();
}

// Fetch tasks
$sql = "SELECT * FROM tasks WHERE projectid = ? AND is_deleted = 0 AND taskstatus='pending' ORDER BY taskid DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// mysqli_close($conn);

$now = date('Y-m-d H:i:s');
$update_sql = "UPDATE tasks
    SET is_overdue = 
        CASE 
            WHEN CONCAT(taskdate, ' ', IFNULL(tasktime, '00:00:00')) < ? 
            THEN 1 
            ELSE 0 
        END 
    WHERE userid = ? AND taskstatus != 'Completed' AND is_deleted = 0 AND projectid = ?
";
$update_stmt = mysqli_prepare($conn, $update_sql);
if ($update_stmt) {
    mysqli_stmt_bind_param($update_stmt, "sii", $now, $userid, $projectid);
    mysqli_stmt_execute($update_stmt);
}


// --- Task query based on filter ---
// Get the filter from URL if set (default to pending)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

switch ($filter) {
    case 'completed':
        $sql = "SELECT * FROM tasks 
                WHERE projectid = ? 
                AND taskstatus = 'completed' AND is_deleted = 0 
                ORDER BY completed_at DESC";
        break;
    
    case 'overdue':
        $sql = "SELECT * FROM tasks 
                WHERE projectid = ? 
                AND is_overdue = 1 AND is_deleted = 0 AND taskstatus != 'completed' 
                ORDER BY taskid DESC";
        break;

    case 'pending':
    default:
        $sql = "SELECT * FROM tasks 
                WHERE projectid = ? 
                AND taskstatus != 'completed' AND is_deleted = 0 
                ORDER BY taskid DESC";
        break;
}

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    // Adjusted to bind only the projectId, not the userId
    mysqli_stmt_bind_param($stmt, "i", $projectId);  // Bind only the projectId
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    echo "Error preparing filtered task statement: " . mysqli_error($conn);
    exit();
}


// fetching role 
$role_sql = "SELECT role FROM project_members WHERE userid = ? AND projectid = ?";
$role_stmt = mysqli_prepare($conn, $role_sql);
mysqli_stmt_bind_param($role_stmt, "ii", $userid, $projectId);
mysqli_stmt_execute($role_stmt);
$role_result = mysqli_stmt_get_result($role_stmt);
$user_role_data = mysqli_fetch_assoc($role_result);
$user_role = $user_role_data['role'] ?? 'Member'; // default to Member if role not found





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['projectname']); ?></title>
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

<div class="container">
    <div class="box">
        <h2><?php echo htmlspecialchars($project['projectname']); ?></h2>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($project['projectdescription']); ?></p>
        <p><strong>Due Date:</strong> <?php echo htmlspecialchars($project['projectduedate']); ?></p>

        <div class="icons">
        <div class="icons" style="display: flex; gap: 20px; margin-top: 15px;">
  <div class="project-actions">
    <?php if ($user_role === 'Admin'): ?>
      <a href="project_task.php?projectid=<?php echo $projectId; ?>" class="edit-btn" title="Edit">
        <ion-icon name="add-circle-outline"></ion-icon> Task
      </a>
      <a href="member.php?projectid=<?php echo $projectId; ?>" class="edit-btn" title="Edit">
        <ion-icon name="people-outline"></ion-icon> Member
      </a>
    <?php else: ?>
      <span class="view-only-msg">🔒 View Only</span>
    <?php endif; ?>
  </div>
</div>

</div>

<div class="filter-container">
  <div style="display: flex; justify-content: center;">
</div>
<a href="project_view.php?projectid=<?= $projectId ?>&filter=pending" class="task-filter <?= $filter == 'pending' ? 'active' : '' ?>">🕒 Pending</a>
<a href="project_view.php?projectid=<?= $projectId ?>&filter=completed" class="task-filter <?= $filter == 'completed' ? 'active' : '' ?>">✅ Completed</a>
<a href="project_view.php?projectid=<?= $projectId ?>&filter=overdue" class="task-filter <?= $filter == 'overdue' ? 'active' : '' ?>">⏰ Overdue</a>
</div>


<!-- Displaying the tasks -->

<?php
if ($result && mysqli_num_rows($result) > 0) {
    $currentUserId = $userid; // From session

    while ($row = mysqli_fetch_assoc($result)) {
        $isOverdue = $row['is_overdue'] == 1;
        $isCompleted = strtolower($row['taskstatus']) === 'completed';
        $assignedTo = $row['assigned_to']; // Get assigned user
    
        echo "<div class='task' id='task-" . $row['taskid'] . "'>";
        echo "<div class='task-content'>";
    
        // Show tick box only if the user is assigned to this task and not completed
        if (!$isCompleted && $assignedTo == $currentUserId) {
            echo "<form action='task_completion.php' method='POST' class='complete-form'>";
            echo "<input type='hidden' name='taskid' value='" . $row['taskid'] . "'>";
            echo "<button type='submit' name='complete-box' class='complete-box' title='Tick to complete'></button>";
            echo "</form>";
        }
    
        echo "<div class='task-details'>";
        echo "<h4 style='display: inline; margin-right: 10px;";
        if ($isOverdue && !$isCompleted) echo "color: red;";
        echo "'>" . htmlspecialchars($row['taskname']) . "</h4>";
    
        if ($isOverdue && !$isCompleted) {
            echo "<span style='color: red;'>(Overdue)</span>";
        }
    
        echo "<div class='task-info-line'><div class='task-details-left'>";
        if (!empty($row['taskdescription'])) {
            echo "<div class='task-description'><span class='info'>Description: " . htmlspecialchars($row['taskdescription']) . "</span></div>";
        }
        echo (!empty($row['taskdate']) ? "<span class='info'>DueDate: " . htmlspecialchars(date('Y-m-d', strtotime($row['taskdate']))) . "</span>" : "");
        echo (!empty($row['tasktime']) ? "<span class='info'>DueTime: " . htmlspecialchars(date('H:i', strtotime($row['tasktime']))) . "</span>" : "");
        echo "<span class='info'>Reminder: " . (isset($row['reminder_percentage']) ? htmlspecialchars($row['reminder_percentage']) . "%" : "Not set") . "</span>";
        echo "</div>"; // task-details-left
    
        echo "<div class='task-actions'>";
        if ($user_role === 'Admin') {
            // Admin can edit and delete
            if (!$isCompleted) {
                echo "<a href='editproject_task.php?projectid=" . $projectId . "&taskid=" . $row['taskid'] . "' class='edit-btn'><ion-icon name='create-outline'></ion-icon> Edit</a>";
            }
            echo "<a href='#' class='delete-btn' data-taskid='" . $row['taskid'] . "'><ion-icon name='trash-outline'></ion-icon> Delete</a>";
        } elseif ($isCompleted && $assignedTo == $currentUserId) {
            // Assigned user sees completed date
            if (!empty($row['completed_at'])) {
                echo "<span class='info' style='color: green;'><ion-icon name='checkmark-done-outline'></ion-icon> Completed on: " . date('Y-m-d H:i', strtotime($row['completed_at'])) . "</span>";
            }
        }
        echo "</div>"; // task-actions
    
        echo "</div></div></div></div>"; // Close all divs
    }
    
} else {
    echo '
<div class="centered-content">
  <div class="content-wrapper">
    <img src="img/notask.png" alt="No tasks yet" />
    <h3><p>No tasks yet 🚀</p></h3>
  </div>
</div>';
}
?>

<!-- for filters -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Optional: If "showFiltersBtn" exists
  const showFiltersBtn = document.getElementById("showFiltersBtn");
  const taskCategories = document.getElementById("taskCategories");
  if (showFiltersBtn && taskCategories) {
    showFiltersBtn.addEventListener("click", function () {
      if (taskCategories.style.display === "none" || taskCategories.style.display === "") {
        taskCategories.style.display = "flex";
        this.textContent = "Hide Filters";
      } else {
        taskCategories.style.display = "none";
        this.textContent = "Show Filters";
      }
    });
  }

  document.querySelectorAll('.delete-btn').forEach(function (button) {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      var taskid = this.getAttribute('data-taskid');
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'delete_task.php?taskid=' + taskid;
        }
      });
    });
  });

  document.querySelectorAll(".complete-form").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      Swal.fire({
        text: "Task completed?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes"
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
});
</script>


<!-- too toggll full test  -->
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
