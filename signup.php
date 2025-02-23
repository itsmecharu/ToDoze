<?php
session_start();
include 'config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// use PHPMailer\PHPMailer\SMTP;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';

$username = $useremail = $userpassword = $confirmpassword = "";
$username_err = $useremail_err = $userpassword_err = $confirmpassword_err = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Validate Username (Only alphabets, one space between words allowed)
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your name.";
    } elseif (!preg_match("/^[A-Za-z]+( [A-Za-z]+)*$/", trim($_POST["username"]))) {
        $username_err = "Name can only contain letters and one space between words.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate Email
    if (empty(trim($_POST["useremail"]))) {
        $useremail_err = "Please enter your email.";
    } elseif (!filter_var(trim($_POST["useremail"]), FILTER_VALIDATE_EMAIL)) {
        $useremail_err = "Please enter a valid email address.";
    } else {
        $useremail = trim($_POST["useremail"]);
        // Check if email already exists
        $sql = "SELECT userid FROM users WHERE useremail = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $useremail);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $useremail_err = "This email is already registered.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate Password
    if (empty(trim($_POST["userpassword"]))) {
        $userpassword_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["userpassword"])) < 8 || strlen(trim($_POST["userpassword"])) > 15) {
        $userpassword_err = "Password must be between 8 and 15 characters.";
    } else {
        $userpassword = trim($_POST["userpassword"]);
    }

    // Confirm Password
    if (empty(trim($_POST["confirmpassword"]))) {
        $confirmpassword_err = "Please confirm your password.";
    } else {
        $confirmpassword = trim($_POST["confirmpassword"]);
        if (empty($userpassword_err) && ($userpassword != $confirmpassword)) {
            $confirmpassword_err = "Passwords do not match.";
        }
    }

    // If no errors, proceed with OTP generation
    if (empty($username_err) && empty($useremail_err) && empty($userpassword_err) && empty($confirmpassword_err)) {
        // Store user details in session
        $_SESSION['username'] = $username;
        $_SESSION['useremail'] = $useremail;
        $_SESSION['userpassword'] = password_hash($userpassword, PASSWORD_DEFAULT); // Hash the password

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 180; // OTP expires in 3 minutes

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'todoze9@gmail.com';
            $mail->Password   = 'aslu umcq hqhq ebhr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('todoze9@gmail.com', 'ToDoze');
            $mail->addAddress($useremail, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for ToDoze Registration';
            $mail->Body    = "<h3>Hello $username,</h3><p>Your OTP is: <b>$otp</b></p><p>This OTP is valid for 3 minutes.</p>";

            $mail->send();
        } catch (Exception $e) {
            die("Error sending OTP: {$mail->ErrorInfo}");
        }

        header("Location: verifyotp.php");
        exit();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/form.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
        .error { color: red; font-size: 12px; }
    </style>
</head>
<body>
    
    <div class="form-wrapper">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo">
        </div>
        <div class="form-container">
            <h1>Sign Up</h1>
            <p>Create an account to start using the app!</p>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <input type="text" placeholder="username" name="username" required>
                <span class="error"><?php echo $username_err; ?></span>
                <input type="email" placeholder="Enter your email" name="useremail" required>
                <span class="error"><?php echo $useremail_err; ?></span>
                <input type="password" placeholder="Create a password" name="userpassword" required>
                <span class="error"><?php echo $userpassword_err; ?></span>
                <input type="password" placeholder="Confirm your password" name="confirmpassword" required>
                <span class="error"><?php echo $confirmpassword_err; ?></span>
                <button type="submit" name="send">Sign Up</button>
            </form>
            <p>Already have an account? <a href="signin.php" class="sign-in-link">Sign In</a></p>
        </div>
    </div>
</body>
</html>
