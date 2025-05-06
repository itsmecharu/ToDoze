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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap"
   rel="stylesheet">
  <title>Dashboard</title>
  <style>
    /* Task container */
.task {
  margin-bottom: 20px;
  border: 1px solid #ccc;
  border-left: 4px solid green;
  border-radius: 8px;
  background-color: #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  margin-left: 70px;
  width: 1100px;
  padding: 16px;
  overflow: hidden;
}

/* Task content */
.task-content {
  display: flex;
  flex-direction: column;
}

/* Checkbox styling */
.complete-form {
  display: inline-block;
  margin-right: 10px;
  vertical-align: middle;
}

.complete-box {
  width: 20px;
  height: 20px;
  background-color: #fff;
  border: 2px solid #28a745;
  border-radius: 3px;
  cursor: pointer;
  display: inline-block;
  vertical-align: middle;
}

.complete-box:hover {
  background-color: #28a745;
}
/* Wrap DueDate, DueTime, and Reminder in a row */
.task-details-left {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* Description remains block */
.task-details-left .info:first-child {
  display: block;
}

/* Group DueDate, Time, and Reminder into a row */
.task-details-left .info-group {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  font-size: 14px;
  color: #333;
}

.task-details-left .info-group .info {
  margin: 0;
  white-space: nowrap;
}

/* Task title */
.task-details h4 {
  display: inline-block;
  vertical-align: middle;
  margin: 0;
  font-size: 18px;
  color: #333;
}

/* Task details layout */
.task-details {
  display: flex;
  flex-direction: column;
  margin-top: 10px;
}

/* Keep due info on one line */
.task-details-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 8px;
}

/* Description block with transition for toggle */
.task-details-left .info {
  font-size: 14px;
  color: #333;
  transition: all 0.3s ease-in-out;
  word-wrap: break-word;
  overflow-wrap: break-word;
  max-width: 100%;
}

/* Group due info together */
.task-details-left .info:nth-child(n+2) {
  display: inline-block;
  margin-right: 16px;
  white-space: nowrap;
}

/* Optional: wrap due items in flex row if needed */
.task-details-left .due-row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  font-size: 14px;
  color: #666;
}

/* Action buttons */
.task-actions {
  margin-top: 12px;
  display: flex;
  gap: 12px;
}

.edit-btn,
.delete-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  font-size: 14px;
  border-radius: 4px;
  text-decoration: none;
  border: none;
  cursor: pointer;
}

.edit-btn {
 
  color: black;
}

.delete-btn {
  background-color: transparent;
  color: red;
  border: 1px solid red;
}

.edit-btn:hover {
  background-color: whitesmoke;
}

.delete-btn:hover {
  background-color: whitesmoke;
  color: red;
}

.collapsed-description {
  max-height: 3.6em; /* Approx. 2 lines */
  overflow: hidden;
  cursor: pointer;
}

.expanded-description {
  max-height: none;
}
  </style>
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
<div class="wide-summary">
        <div class="wide-header">
            <!-- <span>Task Overview Dashboard</span> -->
            <span class="wide-timestamp">Last updated: Today, 10:45 AM</span>
        </div>
        
        <div class="wide-metrics">
            <!-- Total Tasks -->
            <div class="wide-metric">
                <div class="wide-label">
                    <span><span class="status-indicator total-indicator"></span>Total Tasks</span>
                    <span>100%</span>
                </div>
                <div class="wide-value wide-total">83</div>
                <div class="wide-progress-container">
                    <div class="wide-progress wide-total-progress"></div>
                </div>
               </div>
            
            <!-- Completed -->
            <div class="wide-metric">
                <div class="wide-label">
                    <span><span class="status-indicator completed-indicator"></span>Completed</span>
                </div>
                <div class="wide-value wide-completed">56</div>
                <div class="wide-progress-container">
                    <div class="wide-progress wide-completed-progress"></div>
                </div>
            </div>
            
            <!-- Pending -->
            <div class="wide-metric">
                <div class="wide-label">
                    <span><span class="status-indicator pending-indicator"></span>Pending</span>
                </div>
                <div class="wide-value wide-pending">27</div>
                <div class="wide-progress-container">
                    <div class="wide-progress wide-pending-progress"></div>
                </div>
        </div>
    </div>
    </div>
<div class="filter-container">
  <a href="dash.php" class="task-filter <?= $filter == 'all' ? 'active' : '' ?>">🕒 Pending</a>
  <a href="dash.php?filter=completed" class="task-filter <?= $filter == 'completed' ? 'active' : '' ?>">✅ Completed</a>
  <a href="dash.php?filter=overdue" class="task-filter <?= $filter == 'overdue' ? 'active' : '' ?>">⏰ Overdue</a>
</div>

  <div class="logo-container">
    <img src="img/logo.png" alt="App Logo" class="logo">
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
    echo "<div class='info-group'>"; // NEW WRAPPER
    echo (!empty($row['taskdate']) ? "<span class='info'>DueDate: " . htmlspecialchars(date('Y-m-d', strtotime($row['taskdate']))) . "</span>" : "");
    echo (!empty($row['tasktime']) ? "<span class='info'>DueTime: " . htmlspecialchars(date('H:i', strtotime($row['tasktime']))) . "</span>" : "");
    echo "<span class='info'>Reminder: " . (isset($row['reminder_percentage']) && $row['reminder_percentage'] !== null ? htmlspecialchars($row['reminder_percentage']) . "%" : "Not set") . "</span>";
    echo "</div>"; // END WRAPPER
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


<!-- Icons and Charts -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
