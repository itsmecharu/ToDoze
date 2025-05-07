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
    $sqlOverdueTasks = "SELECT COUNT(*) as overdue FROM tasks WHERE userid = ? AND taskstatus = 'Overdue'";

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

  

} catch (Exception $e) {
    // Log error but don't show to user
    error_log("Database error: " . $e->getMessage());
    // You might want to set default values here if the queries fail
}

$conn->close();
?>
<!DOCTYPE html>
<lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/dash.css"/>
  <link rel="icon" type="image/x-icon" href="img/favicon.ico"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <title>Dashboard</title>
  <style>
   
.bottom-container-wrapper {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
  box-sizing: border-box;
}

.bottom-container {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 20px;
  margin-bottom: 20px;
}

.bottom-container h2 {
  color: #2c3e50;
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 1.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  gap: 15px;
  text-align: center;
}

.stat-item {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 15px 10px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.stat-label {
  font-size: 1rem;
  color: #7f8c8d;
  font-weight: 500;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .stat-item {
    padding: 12px 8px;
  }
  
  .stat-label {
    font-size: 0.9rem;
  }
  
  .stat-value {
    font-size: 1.3rem;
  }
}

@media (max-width: 400px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
   
  </style>
</head>
<div>

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


<!-- Task Summary Section -->
<div class="bottom-container-wrapper">
  <!-- Task Statistics at the top -->
  <div class="bottom-container">
    <h2>Task Statistics</h2>
    <div class="stats-grid">
      <div class="stat-item">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?php echo $totalTasks; ?></div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Completed</div>
        <div class="stat-value"><?php echo $completedTasks; ?></div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?php echo $pendingTasks; ?></div>
      </div>
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

    <!-- Member Section -->
    <div class="member-section">
      <div class="chart-header">
        <div class="chart-title">Members</div>
      </div>
      <div class="member">
        <?php echo isset($memberName) ? $memberName : 'No member added yet'; ?>
      </div>
    </div>
  </div>
  <style>
  /* Bottom Row Layout */
  .bottom-row , .top-row{
    display: flex;
    gap: 20px;
    width: 100%;
    margin-top: 20px;
  }

  /* Task Card Section */
  .task-card , .chart-card {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width:400px;
    flex: 2; /* Takes more space than projects */
  }

  /* Project Section */
  .project-section , .member{
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 600px;
    flex: 1; /* Takes less space than tasks */
  }

  /* Common Header Styles */
  .task-header {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
  }

  .task-title {
    font-size: 18px;
    font-weight: bold;
    color: #343a40;
  }

  /* Content Styles */
  .task-wrapper, .member {
    font-size: 14px;
    color: #6c757d;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .bottom-row {
      flex-direction: column;
    }
    
    .task-card, .project-section {
      flex: 1;
      width: 100%;
    }
  }
</style>

<div class="bottom-row">
  <!-- Task Distribution Section -->
  <div class="task-card"> 
    <div class="task-header">
      <div class="task-title">Recent Projects</div>
    </div>
    <div class="task-wrapper">
      <?php echo isset($taskname) ? $taskname : 'No task added yet'; ?>
    </div>
  </div>

  <!-- Project Section -->
  <div class="project-section">
    <div class="task-header">
      <div class="task-title">Recent tasks</div>
    </div>
    <div class="member">
      <?php echo isset($projectname) ? $projectname : 'No project added yet'; ?>
    </div>
  </div>
</div>


<script>
// Dual-Line Graph (Completed vs Pending)
const combinedCtx = document.getElementById('combinedTasksChart').getContext('2d');
new Chart(combinedCtx, {
    type: 'line',
    data: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], // Example time labels
        datasets: [
            {
                label: 'Completed Tasks',
                data: [15, 22, 18, 25], // Replace with your PHP data
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                borderColor: '#2ecc71',
                borderWidth: 3,
                pointBackgroundColor: '#2ecc71',
                pointRadius: 5,
                tension: 0.3,
                fill: false
            },
            {
                label: 'Pending Tasks',
                data: [10, 8, 12, 5], // Replace with your PHP data
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                borderColor: '#f39c12',
                borderWidth: 3,
                pointBackgroundColor: '#f39c12',
                pointRadius: 5,
                tension: 0.3,
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Tasks'
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const dataset = context.dataset;
                        const total = dataset.data.reduce((a, b) => a + b, 0);
                        const value = context.raw;
                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                        return `${dataset.label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>
<style>
    /* Task Statistics Section */
    .bottom-container {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 100%;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 15px;
  }

  .stat-item {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
  }

  .stat-label {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 5px;
  }

  .stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #343a40;
  }

  /* Bottom Row Layout */
  .bottom-row {
    display: flex;
    gap: 20px;
    width: 100%;
  }

  /* Chart Section */
  .chart-card {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-top: 20px;
    flex: 2;
  }

  .chart-header {
    margin-bottom: 15px;
  }

  .chart-title {
    font-size: 18px;
    font-weight: bold;
    color: #343a40;
  }

  .chart-wrapper {
    width: 100%;
    height: 300px;
  }

  /* Member Section */
  .member-section {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    flex: 1;
    margin-top: 20px;
  }

  .member {
    font-size: 14px;
    color: #6c757d;
    margin-top: 15px;
  }
  .chart-card {
  background: #fff;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.chart-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.member {
  font-size: 14px;
  color: #666;
  background: #f5f5f5;
  padding: 4px 8px;
  border-radius: 4px;
}

.chart-wrapper {
  height: 300px;
}
.chart-data {
  text-align: center;
  font-size: 0.9rem;
  color: #7f8c8d;
  padding: 10px;
  border-top: 1px solid #eee;
  margin-top: 15px;
}
/* --------------------------------------------- */

@media (max-width: 768px) {
  .chart-wrapper {
    height: 200px;
  }
}
</style>

<!-- ===== MAIN JS ===== -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="js/dash.js"></script>
</body>
</html>