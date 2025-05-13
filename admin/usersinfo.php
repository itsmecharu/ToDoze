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
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
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

        .user--nav {
            background: #242481;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            margin-left: 35px;
            margin-right: 35px;
        }

        .total-users {
            font-size: 18px;
            font-weight: 500;
            margin-top: 5%;
            margin-left: 3%;
            margin-right: 3%;
            margin-bottom: 20px;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 5%;
            margin-left: 3%;
            margin-right: 3%;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
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

        .back {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
        }

        .back:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            
        }

        .back__icon {
            font-size: 20px;
            margin-right: 8px;
        }

        .back__name {
            font-size: 16px;
            font-weight: 500;
        }

        /* Animation for table rows */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        tbody tr {
            animation: fadeIn 0.3s ease-out forwards;
            opacity: 0;
        }

        tbody tr:nth-child(1) { animation-delay: 0.1s; }
        tbody tr:nth-child(2) { animation-delay: 0.2s; }
        tbody tr:nth-child(3) { animation-delay: 0.3s; }
        tbody tr:nth-child(4) { animation-delay: 0.4s; }
        tbody tr:nth-child(5) { animation-delay: 0.5s; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            th, td {
                padding: 8px 10px;
                font-size: 14px;
            }
            
            .total-users {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
     <nav class="navbar">
        <img src="../img/logo.png" alt="Logo">
    </nav>
    <div class="user--nav">
        <p class="total-users"><h1>Total Users Registered till date: <span id="userCount"><?php echo $total_users; ?></h1></span></p>
    </div>
    
    <div class="container">
        <h2>User Information</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 0;
                while ($row = mysqli_fetch_assoc($result)) : 
                    ;
                ?>
                    <tr style="animation-delay: <?php echo $counter * 0.1; ?>s">
                        <td><?php echo htmlspecialchars($row['userid']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['useremail']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
         <a href="admindashboard.php" class="back">
        <div class="back__icon">↩</div>
        <div class="back__name">Back</div>
    </a>

    </div>
    
   
    <script>
        // Animated counter for total users
        document.addEventListener('DOMContentLoaded', function() {
            const userCount = document.getElementById('userCount');
            const finalNumber = parseInt(userCount.textContent);
            let currentNumber = 0;
            const duration = 1000; // animation duration in ms
            const increment = finalNumber / (duration / 16); // 60fps
            
            const updateCounter = () => {
                currentNumber += increment;
                if (currentNumber < finalNumber) {
                    userCount.textContent = Math.floor(currentNumber);
                    requestAnimationFrame(updateCounter);
                } else {
                    userCount.textContent = finalNumber;
                }
            };
            
            updateCounter();
            
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
        });
    </script>
    </body>
    </html>
