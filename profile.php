<?php
session_start();
include 'config/database.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    
</head>
<body>
<br id="body-pd">
        <div class="l-navbar" id="navbar">
            <nav class="nav">
                <div>
                    <div class="nav__brand">
                        <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                        <span class="nav__logo">Dashboard</span>
                    </div>
                    <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                        <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Home</span>
                    </a>

                    <div class="nav__list">
                    <a href="task.php" class="nav__link">
                            <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Task</span>
                        </a>

                        <a href="project.php"  class="nav__link">
                            <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Project</span>
                        </a>

                        <a href="review.php" class="nav__link">
                            
                            <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Review</span>

                        </a>
                       
                    </div>
            

                </div>

                <a href="logout.php" class="nav__link">
                    <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Log Out</span>
                </a>
            </nav>
        </div>
        <div class="box">
            

  <!-- Profile Section -->
  <div class="container">
    <div class="box">
      <div class="profile-content">
        <div class="profile-image">
          <img src="img/userprofile.jpeg" alt="User Image" class="user-img">
        </div>
        <div class="profile-info">
          <h2 class="user-name">uncle ji</h2>
          <p class="user-email">uncleji11@gmail.com</p>
          <button id="edit-profile-btn" class="edit-btn">Edit Profile</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Profile Popup -->
  <div id="edit-profile-popup" class="popup">
    <div class="popup-content">
      <span class="close-btn" id="close-popup-btn">&times;</span>
      <h2>Edit Profile</h2>
      <form id="edit-profile-form">
        <div class="form-group">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" value="uncleji" required>
        </div>
        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" value="chowktira">
        </div>
        <div class="form-group">
          <label for="bio">Bio</label>
          <textarea id="bio" name="bio" rows="4">kuch bhi</textarea>
        </div>
        <button type="submit" class="save-btn">Save Changes</button>
      </form>
    </div>
  </div>

        <!-- Task Summary Section -->
        <div class="box task-summary">
            <div>
                <h3>Total Tasks</h3>
                <p id="totalTasks">0</p>
            </div>
            <div>
                <h3>Pending Tasks</h3>
                <p id="pendingTasks">0</p>
            </div>
            <div>
                <h3>Completed Tasks</h3>
                <p id="completedTasks">0</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="box">
            <h2>Progress</h2>
            <div class="progress-bar">
                <div class="progress-bar-fill" id="progressBar"></div>
            </div>
        </div>

        <!-- Task Graph Section -->
        <div class="box">
            <h2> Overview</h2>
            <canvas id="taskGraph"></canvas>
        </div>
    </div>
        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- <script src="script.js"></script> -->
</body>
</html>