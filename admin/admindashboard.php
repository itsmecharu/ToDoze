<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_userid'])) {
    header("Location: signin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
</head>
<body>
    <div class="container">
    <h2>Admin Dashboard</h2>

    <div class="dashboard-links">
        <a href="usersinfo.php" class="nav__link">
            <div class="nav__icon">👤</div>
            <div class="nav__name">Users</div>
        </a>
        <a href="usersreview.php" class="nav__link">
            <div class="nav__icon">📝</div>
            <div class="nav__name">Reviews</div>
        </a>
        <a href="../logout.php" class="nav__link">
            <div class="nav__icon">🚪</div>
            <div class="nav__name">Logout</div>
        </a>
    </div>


</body>
</html>


