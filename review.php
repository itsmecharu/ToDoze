<?php
session_start();
include 'config/database.php';
include 'load_username.php';
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Check if user has been registered for at least 2 days and has 5 tasks
$sql = "SELECT u.created_at, COUNT(t.taskid) as task_count 
        FROM users u 
        LEFT JOIN tasks t ON u.userid = t.userid AND t.is_deleted = 0
        WHERE u.userid = ?
        GROUP BY u.userid, u.created_at";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$registrationDate = new DateTime($user['created_at']);
$currentDate = new DateTime();
$daysSinceRegistration = $currentDate->diff($registrationDate)->days;
$taskCount = $user['task_count'];

// Check if user already has a review
$sql = "SELECT * FROM reviews WHERE userid = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existingReview = mysqli_fetch_assoc($result);

$canReview = ($daysSinceRegistration >= 2 && $taskCount >= 5) || $existingReview;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = trim($_POST['review']);
    $rating = $_POST['rating'] ?? null;

    if (empty($rating)) {
        $_SESSION['error_message'] = "Please select a rating before submitting.";
    } else {
        if ($existingReview) {
            // Update existing review
            $sql = "UPDATE reviews SET review = ?, rating = ? WHERE userid = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sii", $review, $rating, $userid);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Review updated successfully!";
                header("Location: review.php");
                exit();
            }
        } elseif ($canReview) {
            // Insert new review
            $sql = "INSERT INTO reviews (userid, review, rating) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isi", $userid, $review, $rating);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Review submitted successfully!";
                header("Location: review.php");
                exit();
            }
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
</head>

<body id="body">
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-left">
            <!-- Removed profile from here -->
        </div>

        <div class="top-right-icons">
            <!-- Notification Icon -->
            <a href="invitation.php" class="top-icon">
                <ion-icon name="notifications-outline"></ion-icon>
            </a>

            <!-- Profile Icon -->
                    <div class="profile-info">
  <div class="profile-circle" title="<?= htmlspecialchars($username) ?>">
    <ion-icon name="person-outline"></ion-icon>
  </div>
  <span class="username-text"><?= htmlspecialchars($username) ?></span>
</div>
        </div>
    </div>

    <!-- Logo Above Sidebar -->
    <div class="logo-container">
        <img src="img/logo.png" alt="Logo" class="logo">
    </div>

<div class="l-navbar" id="navbar">
  <nav class="nav">
    <div class="nav__list">
      <a href="dash.php" class="nav__link "><ion-icon name="home-outline" class="nav__icon"></ion-icon><span
          class="nav__name">Home</span></a>
      <a href="task.php" class="nav__link"><ion-icon name="add-outline" class="nav__icon"></ion-icon><span
          class="nav__name">Task</span></a>
      <a href="team.php" class="nav__link"><ion-icon name="people-outline" class="nav__icon"></ion-icon><span
          class="nav__name">Team</span></a>
      
      <!-- Dropdown Section -->
      <div class="nav__dropdown">
        <button class="nav__dropdown-btn">
          <ion-icon name="Others-outline" class="nav__icon"></ion-icon>
          <span class="nav__name active">Others</span>
          <i class="nav__dropdown-icon fa fa-caret-down"></i>
        </button>
        <div class="nav__dropdown-content nav__link">
          <a href="review.php" class="nav__link active"><ion-icon name="chatbox-ellipses-outline"
              class="nav__icon"></ion-icon><span class="nav__name">Review</span></a>
          <a href="change_name.php" class="nav__link"><ion-icon name="person-circle-outline"
              class="nav__icon"></ion-icon><span class="nav__name">Change Name</span></a>
          <a href="change_password.php" class="nav__link"><ion-icon name="key-outline"
              class="nav__icon"></ion-icon><span class="nav__name">Change Password</span></a>
        </div>
      </div>
    </div>
    
    <!-- Logout button centered and positioned 40px from bottom -->
    <div class="nav__logout-container">
      <a href="javascript:void(0)" onclick="confirmLogout(event)" class="nav__link logout">
        <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
        <span class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span>
      </a>
    </div>
  </nav>
</div>

    <div class="main-container">
        <?php if (!$canReview): ?>
            <div class="review-form-container">
                <h3>Cannot Submit Review Yet</h3>
                <p>To submit a review, you need:</p>
                <div class="requirements">
                    <p>
                        <ion-icon name="time-outline"></ion-icon> 
                        2 days since registration: 
                        <span class="<?= $daysSinceRegistration >= 2 ? 'met' : 'not-met' ?>">
                            <?= $daysSinceRegistration ?>/2 days
                        </span>
                    </p>
                    <p>
                        <ion-icon name="list-outline"></ion-icon> 
                        5 tasks created: 
                        <span class="<?= $taskCount >= 5 ? 'met' : 'not-met' ?>">
                            <?= $taskCount ?>/5 tasks
                        </span>
                    </p>
                </div>
                <img src="img/wait.svg" alt="Please wait" style="width: 200px; margin-top: 30px;">
            </div>
        <?php else: ?>
            <div class="review-form-container">
                <h3><?= $existingReview ? 'Update Your Review' : 'Share Review' ?></h3>
                <form method="POST">
                    <label for="rating">
                        <h2>How was your experience?</h2>
                    </label>
                    <p>Your review will help improve our product and make it more user-friendly.</p>
                    <div class="rating-stars" id="rating-stars">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= 6-$i ?>" 
                                <?= ($existingReview && $existingReview['rating'] == $i) ? 'checked' : '' ?>>
                            <label for="star<?= 6-$i ?>" title="<?= $i ?> stars">&#9733;</label>
                        <?php endfor; ?>
                    </div>

                    <textarea name="review" placeholder="Share your feedback here..."><?= $existingReview ? htmlspecialchars($existingReview['review']) : '' ?></textarea>

                    <button type="submit" class="submit-btn" id="submitReview">
                        <?= $existingReview ? 'Update Review' : 'Submit Review' ?>
                    </button>
                </form>
            </div>

            <?php if ($existingReview): ?>
                <div class="no-reviews-container">
                    <div class="no-reviews-image">
                        <img src="img/review.svg" alt="Your review">
                    </div>
                    <h3>Your Current Review</h3>
                    <p>Rating: <?= str_repeat('⭐', $existingReview['rating']) ?></p>
                    <p style="white-space: pre-wrap;"><?= htmlspecialchars($existingReview['review']) ?></p>
                    <p style="color: #6c757d; font-size: 0.9em; margin-top: 20px;">
                        Last updated: <?= date('F j, Y g:i A', strtotime($existingReview['last_updated_at'] ?? $existingReview['created_at'])) ?>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
    <script src="js/dash.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector('form');
            const ratingInputs = document.querySelectorAll('input[name="rating"]');
            const ratingContainer = document.getElementById('rating-stars');

            if (form) {
                form.addEventListener('submit', function (event) {
                    const ratingChecked = document.querySelector('input[name="rating"]:checked');
                    if (!ratingChecked) {
                        event.preventDefault();
                        ratingContainer.classList.add('error');
                        Swal.fire({
                            title: "Rating Required",
                            text: "Please select a rating before submitting your review.",
                            icon: "warning",
                            confirmButtonText: "OK"
                        });
                    }
                });

                ratingInputs.forEach(input => {
                    input.addEventListener('change', function () {
                        ratingContainer.classList.remove('error');
                    });
                });
            }
        });
    </script>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    toast: true,
                    position: 'top-center',
                    icon: 'success',
                    title: "<?php echo $_SESSION['success_message']; ?>",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
            });
        </script>
            <script>
// Dropdown functionality
document.querySelectorAll('.nav__dropdown-btn').forEach(button => {
  button.addEventListener('click', () => {
    const dropdown = button.closest('.nav__dropdown');
    dropdown.classList.toggle('active');
  });
});
</script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: "<?php echo $_SESSION['error_message']; ?>"
                });
            });
        </script>
        
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</body>

</html>