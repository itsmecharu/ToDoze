<?php
session_start();
include '../config/database.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_userid'])) {
    header("Location: signin.php");
    exit();
}

// Fetch all reviews from the database
$sql = "SELECT users.userid,users.username, reviews.review, reviews.rating, reviews.created_at 
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
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            display: flex;
            justify-content: center;
            padding: 50px;
            background: linear-gradient(135deg, #4b6cb7, #182848);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 150px;
            
        }

        .navbar img {
            height: 100px;
            transition: transform 0.3s ease;
        }

        .navbar img:hover {
            transform: rotate(-5deg) scale(1.1);
        }

        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .review-container {
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 5%;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
            font-size: 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .no-reviews {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-size: 18px;
        }

        .rating {
            color: #f39c12;
            font-size: 18px;
            white-space: nowrap;
        }

        /* Animation for table rows */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        tbody tr {
            animation: fadeIn 0.4s ease-out forwards;
            opacity: 0;
        }

        /* Responsive table */
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            th, td {
                padding: 12px 10px;
            }
        }

        /* Back button styles */
        .back-btn {
            display: inline-flex;
            align-items: center;
            background: #3498db;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .back-icon {
            margin-right: 8px;
            font-size: 18px;
        }
    </style>
</head>
<body>
     <nav class="navbar">
        <img src="../img/logo.png" alt="Logo">
    </nav>
    <div class="review-container">
        <h2>User Reviews & Ratings</h2>

        <?php if (mysqli_num_rows($result) > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Userid</th>
                        <th>Username</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Submitted On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 0;
                    while ($row = mysqli_fetch_assoc($result)) { 
                        $counter++;
                    ?>
                        <tr style="animation-delay: <?php echo $counter * 0.1; ?>s">
                            <td><?php echo htmlspecialchars($row['userid']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="rating"><?php echo str_repeat("⭐", $row['rating']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['review'])); ?></td>
                            <td><?php echo date("Y-m-d H:i", strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="no-reviews">No reviews available.</p>
        <?php } ?>

        <a href="admindashboard.php" class="back-btn">
            <span class="back-icon">←</span>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add click effect to table rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('click', function() {
                    this.style.backgroundColor = '#e3f2fd';
                    setTimeout(() => {
                        this.style.backgroundColor = '';
                    }, 200);
                });
            });

            // Add hover effect to ratings
            const ratings = document.querySelectorAll('.rating');
            ratings.forEach(rating => {
                rating.addEventListener('mouseover', function() {
                    this.style.transform = 'scale(1.1)';
                    this.style.transition = 'transform 0.2s ease';
                });
                rating.addEventListener('mouseout', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>
