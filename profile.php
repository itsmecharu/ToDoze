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


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile</title>
  <link rel="stylesheet" href="css/dash.css">
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">

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
        <a href="team.php" class="nav__link">
          <ion-icon name="people-outline" class="nav__icon"></ion-icon>
          <span class="nav__name">Team </span>
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




<!-- Scripts -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

    <script src="js/dash.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>