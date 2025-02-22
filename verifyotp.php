<?php
session_start();
include 'config/database.php';

$otp_err = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredOtp = $_POST['otp'];

    // Check if OTP is empty
    if (empty($enteredOtp)) {
        $otp_err = "Please enter your verification code."; // Error message if OTP is empty
    } elseif (isset($_SESSION['otp']) && $enteredOtp == $_SESSION['otp']) {
        //echo "OTP Verified Successfully!"; // Proceed with registration

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
        $otp_err = "Invalid OTP. Please try again."; // Error message if OTP doesn't match
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/form.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
        .error { color: red; font-size: 14px; }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo">
        </div>
        <div class="form-container">
            <h3>OTP Verification</h3>
            <form method="POST" action="verifyotp.php">
                <label for="otp">Enter OTP that has been sent to your email:</label>
                <input type="number" name="otp" id="otp" required>  <br>
                <span class="error"><?php echo $otp_err; ?></span> <!-- Display error message here -->
                <button type="submit">Verify</button>
            </form>
        </div>
    </div>
</body>
</html>
