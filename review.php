<?php
session_start();
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_SESSION['userid'];  // Assuming the user is logged in and their user ID is stored in the session
    $review = $_POST['review'];
    $rating = $_POST['rating'];  // Get the rating from the form

    // Insert review and rating into the database
    $sql = "INSERT INTO reviews (userid, review, rating) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $userid, $review, $rating);

    if (mysqli_stmt_execute($stmt)) {
        echo "Review submitted successfully!";
    } else {
        echo "Error submitting review: " . mysqli_error($conn);
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
</head>
<body>
    <div class="review-form-container">
        <h2>Submit Your Review</h2>
        <form method="POST">
            <label for="rating">Rate the System (1 to 5):</label>
            <div class="rating-stars">
                <input type="radio" name="rating" value="1" id="star1">1
                <label for="star1">&#9733;</label>
                <input type="radio" name="rating" value="2" id="star2">2
                <label for="star2">&#9733;</label>
                <input type="radio" name="rating" value="3" id="star3">3
                <label for="star3">&#9733;</label>
                <input type="radio" name="rating" value="4" id="star4">4
                <label for="star4">&#9733;</label>
                <input type="radio" name="rating" value="5" id="star5">5
                <label for="star5">&#9733;</label>
            </div>

            <label for="review">Write your review:</label>
            <textarea name="review" placeholder="Tell us your thoughts about the system!" ></textarea>

            <button type="submit" class="submit-btn">Submit Review</button>
        </form>
    </div>
</body>
</html>
