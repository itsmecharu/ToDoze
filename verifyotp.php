<?php
session_start();
include 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredOtp = $_POST['otp'];

    // Verify OTP
    if (isset($_SESSION['otp']) && $enteredOtp == $_SESSION['otp']) {
        echo "OTP Verified Successfully!"; // Proceed with registration

        // Prepare user data from session
        $username = $_SESSION['username'];
        $useremail = $_SESSION['useremail'];
        $userpassword = $_SESSION['userpassword'];

        // Insert into database
        $sql = "INSERT INTO users (username, useremail, userpassword) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $useremail, $userpassword);
            if (mysqli_stmt_execute($stmt)) {
                echo "Registration successful! Please sign in.";
                unset($_SESSION['otp']); // Clear OTP session
                header("Location: signin.php"); // Redirect to sign in page
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }

    } else {
        echo "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/form.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>
<body>

    <form method="POST" action="verifyotp.php">
        <label>Enter OTP:</label>
        <input type="number" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
