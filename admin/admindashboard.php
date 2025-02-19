<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_userid'])) {
    header("Location: ../signin.php");
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
</head>
<body>
    <div class="container">
        <!-- Sidebar with Icons -->
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="#" onclick="showContent('userInfo')">Users</a></li>
                <li><a href="#" onclick="showContent('userReview')">Reviews</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- User Info Section -->
            <div id="userInfo" class="content-section">
                <h2>User Information</h2>
                <!-- User info content goes here -->
            </div>

            <!-- Review Section -->
            <div id="userReview" class="content-section" style="display:none;">
                <h2>User Reviews</h2>
                <!-- Review content goes here -->
            </div>
        </div>
    </div>

    <script>
        // Function to display the corresponding content
        function showContent(contentId) {
            // Hide all content sections
            const contentSections = document.querySelectorAll('.content-section');
            contentSections.forEach(section => {
                section.style.display = 'none';
            });

            // Show the selected content
            const activeContent = document.getElementById(contentId);
            if (activeContent) {
                activeContent.style.display = 'block';
            }
        }

        // Default view: Show User Info
        showContent('userInfo');
    </script>
</body>
</html>
