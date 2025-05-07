<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
include 'config/database.php';
include 'load_username.php';

// Safe session check
if (!isset($_SESSION['userid'])) {
  header("Location: signin.php");
  exit();
}

$userid = $_SESSION['userid'];


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




// For current month daily stats
$currentYear = date('Y');
$currentMonth = date('m');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

$dailyLabels = [];
$dailyTotal = [];
$dailyCompleted = [];
$dailyPending = [];

for ($day = 1; $day <= $daysInMonth; $day++) {
  $date = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
  $dailyLabels[] = date('M j', strtotime($date)); // e.g., "May 7"

  // Total tasks
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE userid = ? AND DATE(taskcreated_at) = ?");
  $stmt->bind_param("is", $userid, $date);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $dailyTotal[] = $res['count'];
  $stmt->close();

  // Completed tasks
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE userid = ? AND taskstatus = 'Completed' AND DATE(taskcreated_at) = ?");
  $stmt->bind_param("is", $userid, $date);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $dailyCompleted[] = $res['count'];
  $stmt->close();

  // Pending tasks
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tasks WHERE userid = ? AND taskstatus = 'Pending' AND DATE(taskcreated_at) = ?");
  $stmt->bind_param("is", $userid, $date);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $dailyPending[] = $res['count'];
  $stmt->close();
}






?>
<!DOCTYPE html>
<lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/dash.css" />
    <link rel="stylesheet" href="css/extra.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico" />
    <title>Dashboard</title>

  </head>
  <div>

    <div class="top-right-icons">
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
    <div class="logo-container">
      <img src="img/logo.png" alt="App Logo" class="logo">
    </div>

    <div class="l-navbar" id="navbar">
      <nav class="nav">
        <div class="nav__list">
          <a href="dash.php" class="nav__link active"><ion-icon name="home-outline" class="nav__icon"></ion-icon><span
              class="nav__name">Home</span></a>
          <a href="task.php" class="nav__link"><ion-icon name="add-outline" class="nav__icon"></ion-icon><span
              class="nav__name">Task</span></a>
          <a href="project.php" class="nav__link"><ion-icon name="folder-outline" class="nav__icon"></ion-icon><span
              class="nav__name">Project</span></a>
          <a href="review.php" class="nav__link"><ion-icon name="chatbox-ellipses-outline"
              class="nav__icon"></ion-icon><span class="nav__name">Review</span></a>
        </div>
        <a href="logout.php" class="nav__link logout"><ion-icon name="log-out-outline"
            class="nav__icon"></ion-icon><span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span></a>
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


        <!-- Bottom row with Task Distribution and Member sections -->
        <div class="top-row">
          <!-- Task Distribution Section -->
          <div class="chart-card">
            <div class="chart-header">
              <div class="chart-title">Task Distribution</div>
            </div>
            <div class="chart-wrapper">
              <canvas id="combinedTasksChart"></canvas>
            </div>
          </div>


          <div class="task-card">
  <div class="task-header">
    <div class="task-catchup">
      <?php
      $todayDate = date('Y-m-d');
      $currentTime = date('H:i:s');

      // Count today's tasks
      $sql = "SELECT COUNT(*) FROM tasks 
              WHERE taskdate = ? 
              AND (tasktime <= ? OR tasktime IS NULL) 
              AND taskstatus != 'Completed' 
              AND is_deleted = 0";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ss", $todayDate, $currentTime);
      $stmt->execute();
      $stmt->bind_result($taskCount);
      $stmt->fetch();
      $stmt->close();

      // Count overdue tasks
      $sql = "SELECT COUNT(*) FROM tasks 
              WHERE taskdate < ? 
              AND taskstatus != 'Completed' 
              AND is_deleted = 0";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $todayDate);
      $stmt->execute();
      $stmt->bind_result($overdueCount);
      $stmt->fetch();
      $stmt->close();

      echo "<p>Hi! You have <strong>{$taskCount}</strong> task(s) to do today";
      if ($overdueCount > 0) echo " and <strong>{$overdueCount}</strong> overdue task(s)";
      echo ". Wanna see?</p>";
      ?>

      <button onclick="toggleTasks()" style="margin-top: 10px; padding: 6px 12px;">Show me</button>
    </div>
  </div>

  <!-- Hidden task list initially -->
  <div id="task-details" style="display:none; padding: 10px;">
    <?php
    // Fetch today's tasks
    $sql = "SELECT taskid, taskname, taskdate, tasktime FROM tasks 
            WHERE taskdate = ? 
            AND (tasktime <= ? OR tasktime IS NULL) 
            AND taskstatus != 'Completed' 
            AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $todayDate, $currentTime);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($taskid, $taskname, $taskdate, $tasktime);

    echo "<h4>Tasks Due Today:</h4>";
    if ($stmt->num_rows > 0) {
        echo "<ul>";
        while ($stmt->fetch()) {
            echo "<li><strong>$taskname</strong></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No tasks due today 🎉</p>";
    }
    $stmt->close();

    // Fetch overdue tasks
    $sql = "SELECT taskid, taskname, taskdate, tasktime FROM tasks 
            WHERE taskdate < ? 
            AND taskstatus != 'Completed' 
            AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $todayDate);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($taskid, $taskname, $taskdate, $tasktime);

    echo "<h4>Overdue Tasks:</h4>";
    if ($stmt->num_rows > 0) {
        echo "<ul style='color: red;'>";
        while ($stmt->fetch()) {
            echo "<li><strong>$taskname</strong></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No overdue tasks 🥳</p>";
    }
    $stmt->close();
    ?>
  </div>
</div>

<!-- JavaScript to toggle task visibility -->
<script>
function toggleTasks() {
  const details = document.getElementById("task-details");
  details.style.display = (details.style.display === "none") ? "block" : "none";
}
</script>



          <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
          <script>
            const ctx = document.getElementById('combinedTasksChart').getContext('2d');
            new Chart(ctx, {
              type: 'line',
              data: {
                labels: <?php echo json_encode($dailyLabels); ?>,
                datasets: [
                  {
                    label: 'Total Tasks',
                    data: <?php echo json_encode($dailyTotal); ?>,
                    borderColor: '#2980b9',
                    backgroundColor: 'rgba(41, 128, 185, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#2980b9',
                    tension: 0.3,
                    fill: false
                  },
                  {
                    label: 'Completed Tasks',
                    data: <?php echo json_encode($dailyCompleted); ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#2ecc71',
                    tension: 0.3,
                    fill: false
                  },
                  {
                    label: 'Pending Tasks',
                    data: <?php echo json_encode($dailyPending); ?>,
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#f39c12',
                    tension: 0.3,
                    fill: false
                  }
                ]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                  y: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'Number of Tasks'
                    }
                  },
                  x: {
                    title: {
                      display: true,
                      text: 'Date (Current Month)'
                    },
                    ticks: {
                      maxRotation: 90,
                      minRotation: 45
                    }
                  }
                },
                plugins: {
                  tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                      label: function (context) {
                        return `${context.dataset.label}: ${context.raw}`;
                      }
                    }
                  },
                  legend: {
                    position: 'top'
                  }
                },
                interaction: {
                  mode: 'index',
                  intersect: false
                }
              }
            });
          </script>



          <!-- ===== MAIN JS ===== -->
          <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
          <script src="js/dash.js"></script>
          </body>

          </html>