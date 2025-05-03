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
  <style>
    .back-link {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 18px;
    font-size: 14px;
    color: white;
    background-color: #007BFF;
    border-radius: 6px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.back-link:hover {
    background-color: #0056b3;
}

  </style>
</head>
<body>

<div class="box">
  <h2>Edit Profile</h2>
  <form action="edit_profile.php" method="POST">
    <label for="username">Username:</label><br>
    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($user['useremail']); ?>" required><br><br>
     
     <!-- Upload Profile Picture Form -->
  <form action="upload_profile.php" method="POST" enctype="multipart/form-data" style="margin-top: 10px;">
    <input type="file" name="profile_pic" accept="image/*" required>
    <button type="submit" class="btn">Upload Photo</button>
  </form>
</div>

    <button type="submit">Save Changes</button>
  </form> <a href="dash.php" class="back-link">← Back to Task List</a>
</div>

</body>
</html>
