<?php
session_start();

include 'config/database.php';



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <div class="form-wrapper">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo">
        </div>
        <div class="form-container">
            <h1>Sign In</h1>
            <p>Welcome back to your favorite productivity app!</p>
            <form>
                <input type="email" placeholder="Enter your email" required>
                <input type="password" placeholder="Enter your password" required>
                <button type="submit">Sign In<a href="dash.php"></a></button>
            </form>
            <!-- <a href="#" class="forgot-password">Forgot your password?</a> -->
            <p>Don't have an account? <a href="signup.php" class="sign-up-link">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
