<?php
session_start();
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_userid'])) {
    header("Location: signin.php");
    exit();
}

// Fetch all reviews from the database
$sql = "SELECT users.username, reviews.review, reviews.rating, reviews.created_at 
        FROM reviews 
        JOIN users ON reviews.userid = users.userid 
        ORDER BY reviews.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Reviews</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="review-container">
        <h2>User Reviews & Ratings</h2>

        <?php if (mysqli_num_rows($result) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo str_repeat("⭐", $row['rating']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['review'])); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No reviews available.</p>
        <?php } ?>
    </div>
</body>
</html>
