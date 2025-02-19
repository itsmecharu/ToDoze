<?php
session_start();
include '../config/database.php';

// Check if the admin is logged in 
if (!isset($_SESSION['admin_userid'])) {
    header("Location: ../signin.php");
    exit();
}
//counting total num of users
$sql = "SELECT COUNT(*) AS total_users FROM users";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_users = $row['total_users'];


// Fetch all users
$sql = "SELECT userid, username, useremail, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information</title>
    <link rel="stylesheet" href="../css/dash.css">
</head>
<body>
    <div class="container">
        <h2>User Information</h2>
        <table border="1">
            <thead>
            <p class="total-users">Total Users Registered till date: <?php echo $total_users; ?></p>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['userid']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['useremail']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
