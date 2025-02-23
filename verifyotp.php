<?php
session_start();
include 'config/database.php';

$otp_err = ""; // Initialize error message variable
$timeMessage = ""; // Ensure variable is always set

if (isset($_SESSION['otp_expiry'])) {
    $remainingTime = $_SESSION['otp_expiry'] - time(); // Calculate remaining time

    if ($remainingTime <= 0) {
        $otp_err = "OTP has expired. Please request a new one.";
        unset($_SESSION['otp']); // Clear expired OTP
        unset($_SESSION['otp_expiry']);
    } else {
        $minutes = floor($remainingTime / 60);
        $seconds = $remainingTime % 60;
        $timeMessage = "Time remaining: {$minutes}m {$seconds}s";
    }
} else {
    $timeMessage = "No OTP generated.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredOtp = $_POST['otp'];

    if (empty($enteredOtp)) {
        $otp_err = "Please enter your verification code.";
    } elseif (isset($_SESSION['otp']) && $enteredOtp == $_SESSION['otp'] && $remainingTime > 0) {
        echo "OTP Verified Successfully!";

        // User details from session
        $username = $_SESSION['username'];
        $useremail = $_SESSION['useremail'];
        $userpassword = $_SESSION['userpassword'];

        // Insert user into the database
        $sql = "INSERT INTO users (username, useremail, userpassword) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $useremail, $userpassword);
            if (mysqli_stmt_execute($stmt)) {
                echo "Registration successful! Please sign in.";
                unset($_SESSION['otp']); // Clear OTP session
                unset($_SESSION['otp_expiry']); // Clear expiry session
                header("Location: signin.php"); // Redirect to sign-in page
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $otp_err = "Invalid OTP or OTP expired.";
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
        .countdown { color: green; font-size: 14px; font-weight: bold; }
        .countdown.expiring { color: orange; }
        .countdown.expired { color: red; }
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
                <p id="countdown" class="countdown"><?php echo $timeMessage; ?></p> <!-- Countdown will be displayed here -->
                <input type="number" name="otp" id="otp" required>
                <span class="error"><?php echo $otp_err; ?></span>
                <button type="submit">Verify</button>
            </form>
        </div>
    </div>

    <script>
        // Get the OTP expiry time from PHP
        var otpExpiryTime = <?php echo $_SESSION['otp_expiry']; ?>;

        // Function to update the countdown
        function updateCountdown() {
            var currentTime = Math.floor(Date.now() / 1000); // Get current time in seconds
            var remainingTime = otpExpiryTime - currentTime; // Remaining time in seconds

            if (remainingTime <= 0) {
                document.getElementById('countdown').innerHTML = "OTP has expired!";
                document.getElementById('countdown').classList.add("expired");
                document.getElementById('otp').disabled = true;
                document.querySelector('button[type="submit"]').disabled = true;
            } else {
                var minutes = Math.floor(remainingTime / 60);
                var seconds = remainingTime % 60;
                document.getElementById('countdown').innerHTML = "Time remaining: " + minutes + "m " + seconds + "s";
                
                // Change color when less than 30 seconds remain
                if (remainingTime <= 30) {
                    document.getElementById('countdown').classList.add("expiring");
                } else {
                    document.getElementById('countdown').classList.remove("expiring");
                }
            }
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);

        // Call the function once to initialize the countdown
        updateCountdown();
    </script>
</body>
</html>
