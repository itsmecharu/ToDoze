<?php
session_start();
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$usernameMessage = "";

// Update username
if (isset($_POST['update_username'])) {
    $newUsername = trim($_POST['username']);

    // Validation: only letters and one space between words
    if (empty($newUsername)) {
        $usernameMessage = "Username cannot be empty.";
    } elseif (!preg_match("/^[A-Za-z]+( [A-Za-z]+)*$/", $newUsername)) {
        $usernameMessage = "Name can only contain letters and one space between words.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE userid = ?");
        $stmt->bind_param("si", $newUsername, $userid);

        if ($stmt->execute()) {
            $_SESSION['username'] = $newUsername;
            $usernameMessage = "Username updated successfully.";
              header("Location: change_name.php"); // Redirect to avoid form resubmission
            exit();
        } else {
            $usernameMessage = "Error updating username: " . $conn->error;
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>change name</title>
    <link rel="stylesheet" href="css/dash.css">
         <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body id="body-pd">
  <div class="top-bar">
    <div class="top-left">
    
    </div>

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


    


  <!-- sorting ends-->
  <div class="logo-container">
    <img src="img/logo.png" alt="App Logo" class="logo">
  </div>

  <div class="l-navbar" id="navbar">
    <nav class="nav">
      <div class="nav__list">
        <a href="dash.php" class="nav__link"><ion-icon name="home-outline" class="nav__icon"></ion-icon><span
            class="nav__name">Home</span></a>
        <a href="task.php" class="nav__link"><ion-icon name="add-outline" class="nav__icon"></ion-icon><span
            class="nav__name">Task</span></a>
        <a href="team.php" class="nav__link"><ion-icon name="people-outline" class="nav__icon"></ion-icon><span
            class="nav__name">Team</span></a>
        <a href="review.php" class="nav__link"><ion-icon name="chatbox-ellipses-outline"
            class="nav__icon"></ion-icon><span class="nav__name">Review</span></a>
        <a href="change_name.php" class="nav__link active"><ion-icon name="person-circle-outline"
            class="nav__icon"></ion-icon><span class="nav__name">Change Name</span></a>
        <a href="change_password.php" class="nav__link"><ion-icon name="key-outline"
            class="nav__icon"></ion-icon><span class="nav__name">Change Password</span></a>
      </div>
      <a href="javascript:void(0)" onclick="confirmLogout(event)" class="nav__link logout">
        <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
        <span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span>
      </a>
    </nav>
  </div>

<div class="container">
    <!-- Username Update Form -->
    <h3>Change Username</h3>
    <?php if ($usernameMessage): ?>
        <p style="color: <?= strpos($usernameMessage, 'success') !== false ? 'green' : 'red'; ?>;">
            <?= $usernameMessage; ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label for="username">New Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required><br><br>
        <button type="submit" name="update_username" class="btn">Update Name</button>
    </form>
    <hr><br>
</div>

 <!-- Icons and Charts -->
  <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
  <script src="js/dash.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>