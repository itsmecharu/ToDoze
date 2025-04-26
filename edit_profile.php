<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $newEmail = trim($_POST['email']);

    if (!empty($newUsername) && !empty($newEmail)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, useremail = ? WHERE userid = ?");
        $stmt->bind_param("ssi", $newUsername, $newEmail, $userid);
        $stmt->execute();
        $stmt->close();

        // Update session
        $_SESSION['username'] = $newUsername;
        $_SESSION['useremail'] = $newEmail;

        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "All fields are required.";
    }

    header("Location: profile.php");
    exit();
}

// Fetch current data
$stmt = $conn->prepare("SELECT username, useremail FROM users WHERE userid = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="css/dash.css">
</head>
<body>

<div class="box">
  <h2>Edit Profile</h2>
  <form action="edit_profile.php" method="POST">
    <label for="username">Username:</label><br>
    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($user['useremail']); ?>" required><br><br>

    <button type="submit">Save Changes</button>
  </form>
  <a href="profile.php" style="display:inline-block; margin-top:10px;">Back to Profile</a>
</div>

</body>
</html>
