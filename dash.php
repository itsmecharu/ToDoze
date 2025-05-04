<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}
$userid = $_SESSION['userid'];
$filter = $_GET['filter'] ?? 'all';

// for overdue part
$now = date('Y-m-d H:i:s');
$update_sql = "UPDATE tasks 
    SET is_overdue = 
        CASE 
            WHEN CONCAT(taskdate, ' ', IFNULL(tasktime, '00:00:00')) < ? 
            THEN 1 
            ELSE 0 
        END 
    WHERE userid = ? AND taskstatus != 'Completed' AND is_deleted = 0 AND projectid IS NULL
";
$update_stmt = mysqli_prepare($conn, $update_sql);
if ($update_stmt) {
    mysqli_stmt_bind_param($update_stmt, "si", $now, $userid);
    mysqli_stmt_execute($update_stmt);
}

// --- Task query based on filter ---
if ($filter === 'completed') {
  $sql = "SELECT * FROM tasks WHERE userid = ? AND taskstatus = 'completed' AND is_deleted = 0 AND projectid IS NULL ORDER BY completed_at DESC";
}elseif ($filter === 'overdue') {
    $sql = "SELECT * FROM tasks WHERE userid = ? AND is_overdue = 1 AND is_deleted = 0 AND taskstatus != 'completed' AND projectid IS NULL";
} else {
    $sql = "SELECT * FROM tasks WHERE userid = ? AND taskstatus != 'completed' AND is_deleted = 0 AND projectid IS NULL";
}

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/dash.css"/>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Dashboard</title>
</head>
<body>

<div class="top-right-icons">
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

<div class="filter-container">
  <a href="dash.php" class="task-filter <?= $filter == 'all' ? 'active' : '' ?>">🕒 Pending</a>
  <a href="dash.php?filter=completed" class="task-filter <?= $filter == 'completed' ? 'active' : '' ?>">✅ Completed</a>
  <a href="dash.php?filter=overdue" class="task-filter <?= $filter == 'overdue' ? 'active' : '' ?>">⏰ Overdue</a>
</div>





<div class="logo-container">
  <img src="img/logo.png" alt="Logo" class="logo">
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
<?php
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $isOverdue = $row['is_overdue'] == 1;
    $isCompleted = strtolower($row['taskstatus']) === 'completed';

    echo "<div class='task' id='task-" . $row['taskid'] . "'>";
    echo "<div class='task-content'>";

    // Tick box (only if not completed)
    if (!$isCompleted) {
      echo "<form action='task_completion.php' method='POST' class='complete-form'>";
      echo "<input type='hidden' name='taskid' value='" . $row['taskid'] . "'>";
      echo "<button type='submit' name='complete-box' class='complete-box' title='Tick to complete'></button>";
      echo "</form>";
    }

    echo "<div class='task-details'>";
    echo "<h4 style='display: inline; margin-right: 10px;";
    if ($isOverdue && !$isCompleted) {
        echo "color: red;";
    }
    echo "'>" . htmlspecialchars($row['taskname']) . "</h4>";
    
    if ($isOverdue && !$isCompleted) {
        echo "<span style='color: red; font-weight: light;'>(Overdue)</span>";
    }
    

    echo "<div class='task-info-line'>";
    echo "<div class='task-details-left'>";
    echo (!empty($row['taskdescription']) ? "<span class='info'>Description: " . htmlspecialchars($row['taskdescription']) . "</span>" : "");
    echo (!empty($row['taskdate']) ? "<span class='info'>DueDate: " . htmlspecialchars(date('Y-m-d', strtotime($row['taskdate']))) . "</span>" : "");
    echo (!empty($row['tasktime']) ? "<span class='info'>DueTime: " . htmlspecialchars(date('H:i', strtotime($row['tasktime']))) . "</span>" : "");
    echo "<span class='info'>Reminder: " . (isset($row['reminder_percentage']) && $row['reminder_percentage'] !== null ? htmlspecialchars($row['reminder_percentage']) . "%" : "Not set") . "</span>";
    echo "</div>";

    // Task Actions (Edit/Delete or Completed Date)
    echo "<div class='task-actions'>";
    if (!$isCompleted) {
      echo "<a href='edit_task.php?taskid=" . $row['taskid'] . "' class='edit-btn'><ion-icon name='create-outline'></ion-icon> Edit</a>";
      echo "<a href='#' class='delete-btn' data-taskid='" . $row['taskid'] . "'><ion-icon name='trash-outline'></ion-icon> Delete</a>";
    } else {
      if (!empty($row['completed_at'])) {
        echo "<span class='info' style='color: green;'><ion-icon name='checkmark-done-outline'></ion-icon> Completed on: " . date('Y-m-d H:i', strtotime($row['completed_at'])) . "</span>";
      }
      echo "<a href='#' class='delete-btn' data-taskid='" . $row['taskid'] . "' ><ion-icon name='trash-outline'></ion-icon> Delete</a>";
    }
    
    echo "</div>"; // task-actions

    echo "</div></div></div></div>"; // task-info-line, task-details, task-content, task
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
document.getElementById("showFiltersBtn").addEventListener("click", function() {
  var filters = document.getElementById("taskCategories");
  // Toggle visibility
  if (filters.style.display === "none" || filters.style.display === "") {
    filters.style.display = "flex";
    this.textContent = "Hide Filters"; // Change button text
  } else {
    filters.style.display = "none";
    this.textContent = "Show Filters"; // Change button text back
  }
});


<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.delete-task').forEach(function (button) {
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

<!-- Icons and Charts -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
