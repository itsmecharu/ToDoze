<?php
// Safe session start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Fetch task statistics for the logged-in user
$sqlTotalTasks = "SELECT COUNT(*) as total FROM tasks WHERE userid = ?";
$sqlPendingTasks = "SELECT COUNT(*) as pending FROM tasks WHERE userid = ? AND taskstatus = 'Pending'";
$sqlCompletedTasks = "SELECT COUNT(*) as completed FROM tasks WHERE userid = ? AND taskstatus = 'Completed'";
$sqlOverdueTasks = "SELECT COUNT(*) as overdue FROM tasks WHERE userid = ? AND taskstatus = 'Overdue'";

$stmtTotal = $conn->prepare($sqlTotalTasks);
$stmtTotal->bind_param("i", $userid);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$totalTasks = $resultTotal->fetch_assoc()['total'] ?? 0;

$stmtPending = $conn->prepare($sqlPendingTasks);
$stmtPending->bind_param("i", $userid);
$stmtPending->execute();
$resultPending = $stmtPending->get_result();
$pendingTasks = $resultPending->fetch_assoc()['pending'] ?? 0;

$stmtCompleted = $conn->prepare($sqlCompletedTasks);
$stmtCompleted->bind_param("i", $userid);
$stmtCompleted->execute();
$resultCompleted = $stmtCompleted->get_result();
$completedTasks = $resultCompleted->fetch_assoc()['completed'] ?? 0;

$stmtOverdue = $conn->prepare($sqlOverdueTasks);
$stmtOverdue->bind_param("i", $userid);
$stmtOverdue->execute();
$resultOverdue = $stmtOverdue->get_result();
$overdueTasks = $resultOverdue->fetch_assoc()['overdue'] ?? 0;

$stmtTotal->close();
$stmtPending->close();
$stmtCompleted->close();
$stmtOverdue->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile</title>
  <link rel="stylesheet" href="css/dash.css">
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <style>
        /* Initially align container to the left */
.container {
    margin-left: 150px; /* This matches the navbar width */
    transition: all 0.3s ease-in-out;
}

/* When navbar is collapsed */
body.nav-collapsed .container {
    margin-left: 120px;
    margin-right: 0px;
    max-width: 800px; /* Optional: limit the width */
    text-align: center;
}
body {
      font-family: 'Poppins', sans-serif;
      background: #f5f6fa;
      margin: 0;
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }

    /* Top Container */
    .top-container {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 600px;
      text-align: center;
      margin-bottom: 30px;
    }

    .top-container h2 {
      color: #333;
      font-weight: 600;
      margin-bottom: 15px;
    }

    .top-container div {
      font-size: 1.1rem;
      color: #7f8c8d;
    }

    /* Bottom Containers Wrapper */
    .bottom-container-wrapper {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
      width: 100%;
      max-width: 1000px;
    }

    /* Bottom Containers */
    .bottom-container {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      flex: 0 0 45%;
      min-width: 320px;
      box-sizing: border-box;
      text-align: center;
    }

    .bottom-container.graph-container {
      flex: 0 0 100%;
      margin-top: 20px;
    }

    .bottom-container h2 {
      color: #333;
      font-weight: 600;
      margin-bottom: 20px;
    }

    /* Progress Bar only for Progress section */
    .progress-bar {
      background-color: #f1f1f1;
      border-radius: 12px;
      height: 15px;
      margin-top: 20px;
      overflow: hidden;
    }

    .progress-bar-fill {
      background-color: #2ecc71;
      border-radius: 12px;
      height: 100%;
      transition: width 0.4s ease;
    }

    /* Graph Canvas */
    .task-graph {
      width: 60%;
      height: 300px;
      margin: 20px auto 0 auto;
    }
    .profile-circle {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #ccc;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #fff;
    }

    .username {
      font-weight: 600;
      color: #333;
    }

    .top-right-icons {
      position: fixed;
      top: 20px;
      right: 20px;
      display: flex;
      align-items: center;
      z-index: 1000; /* Ensure it is above other content */
    }

    /* Notification Icon Styling */
    .top-icon {
      margin-right: 20px; /* Space between notification and profile icon */
    }

    .top-icon ion-icon {
      font-size: 28px; /* Size of the notification icon */
      color: #333; /* Icon color */
      cursor: pointer; /* Change cursor to pointer on hover */
    }

    /* Optional: Add a hover effect */
    .top-icon ion-icon:hover {
      color: #007bff; /* Change color on hover */
    }

    /* Optional: Adding a notification badge */
    .top-icon {
      position: relative;
    }
    .logo-container {
  position: fixed;
  top: 5px;  /* Adjust the position from the top */
  left: 35px;  /* Adjust the position from the left */
  z-index: 1000;  /* Ensure it's above the sidebar */
}

.logo {
  width: 120px;  /* Adjust the width of the logo */
  height: auto;
}

    </style>
</head>

<body id="body-pd">
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
        <a href="project.php" class="nav__link">
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

<!-- Profile Section -->
<div class="top-container">
 
  <div class="profile-content">
    <div class="profile-image">
      <img src="<?php echo isset($_SESSION['profile_pic']) && $_SESSION['profile_pic'] ? 'uploads/' . $_SESSION['profile_pic'] : 'img/userprofile.jpeg'; ?>" alt="User Image" class="user-img">
    </div>
    <div class="profile-info">
      <h2 class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
      <p class="user-email"><?php echo htmlspecialchars($_SESSION['useremail']); ?></p>
    </div>
  </div>

  <!-- Edit name button -->
  <div style="margin-top: 10px;">
    <a href="edit_profile.php" class="btn">Edit Name</a>
  </div>

 </div>


<!-- Task Summary Section -->
<div class="bottom-container-wrapper">
  <div class="bottom-container">
  <h2>Task Statistics</h2>
  <div>
    Total: <?php echo $totalTasks; ?> |
    Completed: <?php echo $completedTasks; ?> |
    Pending: <?php echo $pendingTasks; ?> |
    Overdue: <?php echo $overdueTasks; ?>
  
</div>
</div>
  <div class="bottom-container">
  <h2>Progress</h2>
  <div class="progress-bar">
    <div class="progress-bar-fill" id="progressBar"
      style="width: <?php echo $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0; ?>%;">
      <div class="progress-bar-fill" style="width: 50%;"></div>
  </div>
</div>
</div>
<div class="bottom-container graph-container">
  <h2>Task Overview</h2>
  <div style="width: 30%; height: 200px; margin: 0 auto;">
    <canvas id="taskGraph"></canvas>
  </div>
</div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('taskGraph').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Pending', 'Completed', 'Overdue'],
        datasets: [{
            label: 'Tasks',
            data: [<?php echo $pendingTasks; ?>, <?php echo $completedTasks; ?>, <?php echo $overdueTasks; ?>],
            backgroundColor: ['#f1c40f', '#2ecc71', '#e74c3c'],
            borderRadius: 8,
            barThickness: 30
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#333',
                titleColor: '#fff',
                bodyColor: '#fff'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { borderDash: [5, 5] }
            },
            x: {
                grid: { display: false }
            }
        },
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1200,
            easing: 'easeOutCubic'
        }
    }
});
</script>

  
    <!-- ===== IONICONS ===== -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

    <!-- ===== MAIN JS ===== -->
    <script src="js/dash.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
