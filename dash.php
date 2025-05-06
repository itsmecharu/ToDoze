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

<!-- for filters -->
<script>

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
</script>

<!-- Icons and Charts -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
