<?php
session_start();
include 'config/database.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_SESSION['userid'];  // Assuming the user is logged in and their user ID is stored in the session
    $review = $_POST['review'];
    $rating = $_POST['rating'] ?? null;  // Get the rating from the form

    // Insert review and rating into the database
    if (empty($rating)) {

    } else {
        $sql = "INSERT INTO reviews (userid, review, rating) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isi", $userid, $review, $rating);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Review sent sucessfully!";
        } else {
            echo "<div class='popup error'>Error submitting review: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Review</title>
    <link rel="stylesheet" href="css/review.css">
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Highlight the rating container when error occurs */
        .rating-stars.error {
            border: 2px solid red;
            padding: 5px;
            border-radius: 5px;
        }
    </style>
</head>

<body id="body-pd">
    <!-- Navbar -->
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__brand">
                    <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                    <span class="nav__logo">ToDoze</span>
                </div>
                <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                        <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Home</span>
                    </a>
                    <a href="task.php" class="nav__link">
                        <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Task</span>
                    </a>
                    <a href="project.php" class="nav__link">
                        <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Project</span>
                    </a>
                    <a href="review.php" class="nav__link active">
                        <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Review</span>
                    </a>
                    <a href="profile.php" class="nav__link">
                        <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Profile</span>
                    </a>
                </div>
            </div>
            <a href="logout.php" class="nav__link logout">
                <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                <span class="nav__name">Log Out</span>
            </a>
        </nav>
    </div>

    <div class="review-form-container">
        <h3>Share Review</h3>
        <form method="POST">
            <label for="rating"><h2>How was your experience?</h2></label>
           <p>Your review will help our product and make
                 it user friendly for more users.</>
            <div class="rating-stars" id="rating-stars">
                <input type="radio" name="rating" value="5" id="star1">
                <label for="star1">&#9733;</label>
                <input type="radio" name="rating" value="4" id="star2">
                <label for="star2">&#9733;</label>
                <input type="radio" name="rating" value="3" id="star3">
                <label for="star3">&#9733;</label>
                <input type="radio" name="rating" value="2" id="star4">
                <label for="star4">&#9733;</label>
                <input type="radio" name="rating" value="1" id="star5">
                <label for="star5">&#9733;</label>
            </div>

            <label for="review"></label>
            <textarea name="review" placeholder="Share feedback..."></textarea>

            <!-- The submit button is enabled by default -->
            <button type="submit" class="submit-btn" id="submitReview">Submit Review</button>
        </form>
    </div>

    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
    <!-- ===== MAIN JS ===== -->
    <script src="js/dash.js"></script>

    <!-- JavaScript to disable submit if no rating is selected and highlight rating container -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector('form');
            const ratingInputs = document.querySelectorAll('input[name="rating"]');
            const ratingContainer = document.getElementById('rating-stars');

            form.addEventListener('submit', function (event) {
                const ratingChecked = document.querySelector('input[name="rating"]:checked');
                if (!ratingChecked) {
                    event.preventDefault(); // Prevent submission
                    // Highlight the rating container
                    ratingContainer.classList.add('error');
                    //         Swal.fire({
                    //             title: "Rating Required",
                    //             text: "Please select a rating before submitting your review.",
                    //             icon: "warning",
                    //             confirmButtonText: "OK"
                    //         });
                }
            });

            // Remove the highlight when a rating is selected
            ratingInputs.forEach(input => {
                input.addEventListener('change', function () {
                    ratingContainer.classList.remove('error');
                });
            });
        });
    </script>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    toast: true,
                    position: 'top-center',
                    icon: 'success',
                    title: "Review sent successfully!",
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'swal-toast'
                    }
                });

            });
        </script>
        <style>
            .small-swal {
                width: 200px;
                padding: 20px;
            }

            .small-swal-title {
                font-size: 16px;
                font-weight: bold;
            }

            .small-swal-content {
                font-size: 14px;
            }
        </style>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
</body>

</html>