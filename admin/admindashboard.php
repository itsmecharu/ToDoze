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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico"> <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        .nav-container {
            display: flex;
            justify-content: center;
            padding: 30px;
        }

        .nav-menu {
            display: flex;
            background: white;
            border-radius: 80px;
            padding: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .nav__link {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #555;
            padding: 15px 25px;
            margin: 0 5px;
            border-radius: 900px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav__link:hover {
            background: linear-gradient(90deg, #f39c12 0%, #e67e22 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
        }

        .nav__link.active {
            background: linear-gradient(90deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(155, 89, 182, 0.4);
        }

        .nav__icon {
            font-size: 22px;
            margin-bottom: 5px;
            transition: transform 0.3s ease;
        }

        .nav__link:hover .nav__icon {
            transform: scale(1.2);
        }

        .nav__name {
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Bubble effect */
        .bubble {
            position: absolute;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: bubble 0.6s ease-out;
            pointer-events: none;
        }

        @keyframes bubble {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <img src="../img/logo.png" alt="Logo">
    </nav>

    <div class="nav-container">
        <div class="nav-menu">
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
    </div>

    <script>
        // Add bubble effect to navigation links
        document.querySelectorAll('.nav__link').forEach(link => {
            link.addEventListener('click', function(e) {
                // Remove any existing bubbles
                const existingBubbles = this.querySelectorAll('.bubble');
                existingBubbles.forEach(bubble => bubble.remove());

                // Create new bubble
                const bubble = document.createElement('span');
                bubble.classList.add('bubble');
                
                // Position the bubble
                const rect = this.getBoundingClientRect();
                bubble.style.width = bubble.style.height = `${rect.width}px`;
                bubble.style.left = '0';
                bubble.style.top = '0';
                
                this.appendChild(bubble);
                
                // Remove bubble after animation
                setTimeout(() => {
                    bubble.remove();
                }, 600);
            });
        });

        // Set active state based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.nav__link').forEach(link => {
                const linkPage = link.getAttribute('href').split('/').pop();
                if (currentPage === linkPage || 
                    (currentPage === '' && linkPage === 'usersinfo.php')) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>