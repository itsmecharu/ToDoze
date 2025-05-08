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

// // Get user's role in this project
// $sql = "SELECT role FROM project_members WHERE userid = ? AND projectid = ?";
// $stmt = mysqli_prepare($conn, $sql);
// mysqli_stmt_bind_param($stmt, "ii", $userid, $projectid);
// mysqli_stmt_execute($stmt);
// $result = mysqli_stmt_get_result($stmt);
// $row = mysqli_fetch_assoc($result);

// if (!$row || $row['role'] !== 'Admin') {
//   // Not allowed
//   header("Location: unauthorized.php");
//   exit();
// }


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
      <ul class="member-cards">
        <?php foreach ($accepted_members as $member) { ?>
          <li class="member-card">
            <div class="member-info">
              <span class="member-email"><?= htmlspecialchars($member['useremail']) ?></span>
              <span class="role-badge"><?= htmlspecialchars($member['role']) ?></span>
            </div>
            <?php if ($member['userid'] != $admin_userid) { ?>
              <a href="remove_member.php?userid=<?= $member['userid'] ?>&projectid=<?= $projectId ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this member?');">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                </svg>
                Remove
              </a>
            <?php } else { ?>
              <span class="admin-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                </svg>
                Admin
              </span>
            <?php } ?>
          </li>
        <?php } ?>
      </ul>
    <?php } else { ?>
      <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="9" cy="7" r="4"></circle>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <p>No accepted members yet.</p>
      </div>
    <?php } ?>
  </div>

  <!-- Pending Invitations -->
  <div id="pending" class="pending-list section" style="display: none;">
    <h3>Pending Invitations</h3>
    <?php if (!empty($pending_members)) { ?>
      <ul class="pending-cards">
        <?php foreach ($pending_members as $pending) { ?>
          <li class="pending-card">
            <span class="pending-email"><?= htmlspecialchars($pending['useremail']) ?></span>
            <span class="pending-badge">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zm.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535zm-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
                <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/>
                <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z"/>
              </svg>
              Pending
            </span>
          </li>
        <?php } ?>
      </ul>
    <?php } else { ?>
      <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <p>No pending invitations.</p>
      </div>
    <?php } ?>
  </div>

  <!-- Send Invitation -->
  <div id="invite" class="add-task-form section" style="display: none;">
    <h3>Send Invitation</h3>
    <form action="send_invitation.php" method="POST" class="invite-form">
      <input type="hidden" name="projectid" value="<?= $projectId ?>">
      <div class="form-group">
        <label for="useremail">Member Email:</label>
        <select name="useremail" id="useremail" required class="form-select">
          <option value="" disabled selected>Select User</option>
          <?php if (!empty($available_users)) {
            foreach ($available_users as $user) { ?>
              <option value="<?= htmlspecialchars($user['useremail']) ?>"><?= htmlspecialchars($user['useremail']) ?></option>
          <?php }} else { ?>
            <option value="" disabled>No users available to invite</option>
          <?php } ?>
        </select>
      </div>
      <div class="form-actions">
        <button type="submit" class="submit-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.5.5 0 0 1-.928.086L7.5 12.5V6.707L1.793.854a.5.5 0 0 1 .108-.64l1.5-1.5A.5.5 0 0 1 3.5.5H8v5.293L15.146.146a.5.5 0 0 1 .708 0z"/>
          </svg>
          Send Invite
        </button>
        <button type="button" onclick="window.history.back();" class="back-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
          </svg>
          Go Back
        </button>
      </div>
    </form>
  </div>
</div>

<style>
  .container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .filter-container {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 1px solid #e1e4e8;
    padding-bottom: 10px;
  }

  .task-filter {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    background-color: #f6f8fa;
    color: #24292e;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .task-filter:hover {
    background-color: #e1e4e8;
  }

  .task-filter.active {
    background-color: #0366d6;
    color: white;
  }

  .section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
  }

  h3 {
    margin-top: 0;
    color: #24292e;
    font-size: 18px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eaecef;
  }

  /* Accepted Members */
  .member-cards {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .member-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background-color: #f6f8fa;
    border-radius: 6px;
    transition: background-color 0.2s ease;
  }

  .member-card:hover {
    background-color: #e1e4e8;
  }

  .member-info {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .member-email {
    font-weight: 500;
  }

  .role-badge {
    background-color: #e1e4e8;
    color: #24292e;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
  }

  .remove-btn {
    color: #d73a49;
    text-decoration: none;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
  }

  .remove-btn:hover {
    background-color: rgba(215, 58, 73, 0.1);
  }

  .admin-badge {
    background-color: #0366d6;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  /* Pending Invitations */
  .pending-cards {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .pending-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background-color: #fff8e6;
    border-radius: 6px;
  }

  .pending-email {
    font-weight: 500;
  }

  .pending-badge {
    background-color: #ffd33d;
    color: #24292e;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  /* Send Invitation */
  .invite-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .form-select {
    padding: 8px 12px;
    border: 1px solid #e1e4e8;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s ease;
  }

  .form-select:focus {
    outline: none;
    border-color: #0366d6;
    box-shadow: 0 0 0 3px rgba(3, 102, 214, 0.1);
  }

  .form-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
  }

  .submit-btn {
    background-color: #2ea44f;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background-color 0.2s ease;
  }

  .submit-btn:hover {
    background-color: #2c974b;
  }

  .back-btn {
    background-color: #f6f8fa;
    color: #24292e;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background-color 0.2s ease;
  }

  .back-btn:hover {
    background-color: #e1e4e8;
  }

  /* Empty State */
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px 20px;
    color: #586069;
  }

  .empty-state svg {
    color: #e1e4e8;
  }

  /* Message */
  .message {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 14px;
  }

  .message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
</style>

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