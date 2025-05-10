<?php
session_start();
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$teamId = $_GET['teamid'] ?? null;

if (!$teamId) {
    die("Project not found!");
}

// fetching role 
$role_sql = "SELECT role FROM team_members WHERE userid = ? AND teamid = ?";
$role_stmt = mysqli_prepare($conn, $role_sql);
mysqli_stmt_bind_param($role_stmt, "ii", $userid, $teamId);
mysqli_stmt_execute($role_stmt);
$role_result = mysqli_stmt_get_result($role_stmt);
$user_role_data = mysqli_fetch_assoc($role_result);
$user_role = $user_role_data['role'] ?? 'Member'; // default to Member if role not found



// Get admin (creator) of the team
$sql = "SELECT userid FROM team_members WHERE teamid = ? AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
$admin_userid = $admin['userid'];
mysqli_stmt_close($stmt);

// Fetch accepted members
$sql = "SELECT users.userid, users.useremail, team_members.role 
        FROM team_members 
        JOIN users ON team_members.userid = users.userid 
        WHERE team_members.teamid = ? AND team_members.status = 'accepted'AND team_members.has_exited = 0";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$accepted_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);


// Fetch ex  members
$sql = "SELECT users.userid, users.useremail, team_members.role 
        FROM team_members 
        JOIN users ON team_members.userid = users.userid 
        WHERE team_members.teamid = ? AND team_members.has_exited = 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teamId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ex_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch pending members
$sql = "SELECT users.userid, users.useremail 
        FROM team_members 
        JOIN users ON team_members.userid = users.userid 
        WHERE team_members.teamid = ? 
        AND team_members.status = 'pending'
        AND users.userid != ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $teamId, $admin_userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pending_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$sql = "
SELECT userid, useremail 
FROM users 
WHERE userid != ? 
AND userid NOT IN (
    SELECT userid 
    FROM team_members 
    WHERE teamid = ? 
      AND status IN ('Accepted', 'Pending') 
      AND has_exited = 0
)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $userid, $teamId);
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
  <title> Members</title>
  <link rel="stylesheet" href="css/dash.css" />
  <link rel="icon" type="image/x-icon" href="img/favicon.ico">
  
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
        <a href="team.php" class="nav__link active">
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

  <div class="container">
  <div class="filter-container">
    <button class="member-task-filter active" onclick="showSection('accepted', this)">Members</button>
    <button class="member-task-filter" onclick="showSection('ex', this)">Ex Members</button>

    <?php if ($user_role === 'Admin'): ?>
    <button class="member-task-filter" onclick="showSection('pending', this)">Pending Invitations</button>
    <button class="member-task-filter" onclick="showSection('invite', this)">Send Invitation</button>
    <!-- <button class="member-task-filter" onclick="showSection('ex', this)">Ex Members</button> -->
    <?php endif; ?>
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
      

            <?php if ($user_role === 'Admin'): ?>
            <?php if ($member['userid'] != $admin_userid) { ?>
              <a href="remove_member.php?userid=<?= $member['userid'] ?>&teamid=<?= $teamId ?>" class="remove-btn" onclick="return confirm('Are you sure you want to remove this member?');">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                </svg>
                Remove
              </a>
              
            <?php }  else { ?>
              <span class="admin-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                </svg>
                Admin
              </span>
            <?php } ?>

            <?php endif; ?>
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
  <!-- only admin can see this -->
  <?php if ($user_role === 'Admin'): ?>
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
      <input type="hidden" name="teamid" value="<?= $teamId ?>">
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
  <!-- ex section -->
<div id="ex" class="ex_members-list section" style="display: none;">
    <h3>Ex Members</h3>
    <?php if (!empty($ex_members)) { ?>
      <ul class="member-cards">
        <?php foreach ($ex_members as $member) { ?>
          <li class="member-card">
            <div class="member-info">
              <span class="member-email"><?= htmlspecialchars($member['useremail']) ?></span>
            </div>
      

            <?php if ($user_role === 'Admin'): ?>
            <?php if ($member['userid'] != $admin_userid) { ?>
              
              
            <?php }  else { ?>
              <span class="admin-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                </svg>
                Admin
              </span>
            <?php } ?>

            <?php endif; ?>
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
        <p>No Ex members yet.</p>
      </div>
    <?php } ?>
  </div>

</div>
<?php endif; ?>

<style>
 
</style>

<script>
  function showSection(id, btn) {
    const sections = ['accepted', 'pending', 'invite','ex'];
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