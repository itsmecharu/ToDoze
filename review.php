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
    $rating = $_POST['rating'] ?? null ;  // Get the rating from the form

    // Insert review and rating into the database
    if(empty($rating)){
     echo"<div class='popup success'>Please select rating before submmiting </div>";
    }
    else{
    $sql = "INSERT INTO reviews (userid, review, rating) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $userid, $review, $rating);

    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='popup success'>Review submitted successfully!</div>";
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
    <link rel="icon" type="image/x-icon" href="img/todoze.png">

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
                        <a href="dash.php" class="nav__link ">
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
        <h2>Submit Your Review</h2>
        <form method="POST">
            <label for="rating">Rate the System (1 to 5):</label>
            <div class="rating-stars">
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

            <label for="review">Write your review:</label>
            <textarea name="review" placeholder="Tell us your thoughts about the system!"></textarea>

            <button type="submit" class="submit-btn">Submit Review</button>
        </form>
    </div>
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
</body>
</html>