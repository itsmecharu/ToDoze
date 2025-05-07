<?php
session_start();
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$projectId = $_GET['projectid'] ?? null;

if (!$projectId) {
    die("Project not found!");
}

// Get admin (creator) of the project
$sql = "SELECT userid FROM project_members WHERE projectid = ? AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
$admin_userid = $admin['userid'];
mysqli_stmt_close($stmt);

// Fetch accepted members
$sql = "SELECT users.userid, users.useremail, project_members.role 
        FROM project_members 
        JOIN users ON project_members.userid = users.userid 
        WHERE project_members.projectid = ? AND project_members.status = 'accepted'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$accepted_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch pending members
$sql = "SELECT users.userid, users.useremail 
        FROM project_members 
        JOIN users ON project_members.userid = users.userid 
        WHERE project_members.projectid = ? 
        AND project_members.status = 'pending'
        AND users.userid != ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $projectId, $admin_userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pending_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch available users for invitation
$sql = "SELECT userid, useremail 
        FROM users 
        WHERE userid != ? 
        AND userid NOT IN (
            SELECT userid FROM project_members WHERE projectid = ?
        )";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $userid, $projectId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$available_users = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Project Members</title>
  <link rel="stylesheet" href="css/dash.css" />
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
      padding: 0;
      color: #333;
    }

    .container {
      max-width: 900px;
      margin: 100px auto 50px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    h2, h3 {
      color: #333;
      margin-bottom: 20px;
    }

    ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    li {
      background-color: #f1f1f1;
      padding: 10px 15px;
      border-radius: 6px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .role {
      font-size: 0.9rem;
      color: #555;
    }

    .admin-label {
      color: #28a745;
      font-weight: bold;
    }

    .remove-btn {
      background-color: transparent;
      color: #dc3545;
      border: none;
      cursor: pointer;
      text-decoration: underline;
      font-size: 0.9rem;
    }

    .remove-btn:hover {
      color: #a71d2a;
    }

    .message {
      padding: 10px 15px;
      border-radius: 6px;
      font-size: 0.95rem;
    }

    .message.success {
      background-color: #d4edda;
      color: #155724;
    }

    .message.error {
      background-color: #f8d7da;
      color: #721c24;
    }

    .section {
      margin-bottom: 30px;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    select, button {
      padding: 10px;
      font-size: 1rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    select:focus, button:focus {
      outline: none;
      border-color: #007bff;
    }

    button[type="submit"] {
      background-color: #007bff;
      color: white;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button[type="submit"]:hover {
      background-color:rgb(0, 179, 30);
    }

    .back-link {
      background-color: #6c757d;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      text-decoration: none;
      display: inline-block;
      transition: background-color 0.3s;
      cursor: pointer;
    }

    .back-link:hover {
      background-color: #5a6268;
    }

    .task-filter {
      padding: 10px 15px;
      margin-right: 10px;
      background-color: white;
      color: black;
      cursor: pointer;
      border-radius: 6px;
      transition: all 0.3s;
    }

    .task-filter.active {
      background-color: #007bff;
      color: white;
    }
  </style>
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
  <div class="filter-container">
    <button class="task-filter active" onclick="showSection('accepted', this)">Accepted Members</button>
    <button class="task-filter" onclick="showSection('pending', this)">Pending Invitations</button>
    <button class="task-filter" onclick="showSection('invite', this)">Send Invitation</button>
  </div>

  <?php if (isset($_GET['status'])): ?>
    <div class="message <?php echo $_GET['status'] === 'success' ? 'success' : 'error'; ?>">
      <?= $_GET['status'] === 'success' ? 'Invitation sent successfully.' : htmlspecialchars($_GET['message'] ?? "An error occurred."); ?>
    </div>
  <?php endif; ?>

  <!-- Accepted Members -->
  <div id="accepted" class="members-list section" style="display: block;">
    <h3>Accepted Members</h3>
    <?php if (!empty($accepted_members)) { ?>
      <ul>
        <?php foreach ($accepted_members as $member) { ?>
          <li>
            <?= htmlspecialchars($member['useremail']) ?>
            <span class="role">(<?= htmlspecialchars($member['role']) ?>)</span>
            <?php if ($member['userid'] != $admin_userid) { ?>
              <a href="remove_member.php?userid=<?= $member['userid'] ?>&projectid=<?= $projectId ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this member?');">Remove</a>
            <?php } else { ?>
              <span class="admin-label">[Admin]</span>
            <?php } ?>
          </li>
        <?php } ?>
      </ul>
    <?php } else { ?>
      <p>No accepted members yet.</p>
    <?php } ?>
  </div>

  <!-- Pending Invitations -->
  <div id="pending" class="pending-list section" style="display: none;">
    <h3>Pending Invitations (Request Sent)</h3>
    <?php if (!empty($pending_members)) { ?>
      <ul>
        <?php foreach ($pending_members as $pending) { ?>
          <li><?= htmlspecialchars($pending['useremail']) ?> (Pending)</li>
        <?php } ?>
      </ul>
    <?php } else { ?>
      <p>No pending invitations.</p>
    <?php } ?>
  </div>

  <!-- Send Invitation -->
  <div id="invite" class="add-task-form section" style="display: none;">
    <h3>Send Invitation</h3>
    <form action="send_invitation.php" method="POST">
      <input type="hidden" name="projectid" value="<?= $projectId ?>">
      <label for="email">Member Email:</label>
      <select name="useremail" id="useremail" required>
        <option value="" disabled selected>Select User</option>
        <?php if (!empty($available_users)) {
          foreach ($available_users as $user) { ?>
            <option value="<?= htmlspecialchars($user['useremail']) ?>"><?= htmlspecialchars($user['useremail']) ?></option>
        <?php }} else { ?>
          <option value="" disabled>No users available to invite</option>
        <?php } ?>
      </select>
      <button type="submit" style="margin-top: 20px; border-radius: 20px;">Send Invite</button>
    </form>
    <button onclick="window.history.back();" class="back-link">Go Back</button>
  </div>
</div>

<!-- JavaScript -->
<script>
  function showSection(id, btn) {
    const sections = ['accepted', 'pending', 'invite'];
    sections.forEach(sec => {
      document.getElementById(sec).style.display = (sec === id) ? 'block' : 'none';
    });

    document.querySelectorAll('.task-filter').forEach(button => {
      button.classList.remove('active');
    });

    btn.classList.add('active');
  }
</script>

<!-- Icons and Charts -->
<script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
