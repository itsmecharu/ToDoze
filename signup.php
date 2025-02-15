<?php 
session_start();
include 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <div class="form-wrapper">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo">
        </div>
        <div class="form-container">
            <h1>Sign Up</h1>
            <p>Create an account to start using the app!</p>
            <form>
                <input type="text" placeholder="Enter your full name" required>
                <input type="email" placeholder="Enter your email" required>
                <input type="password" placeholder="Create a password" required>
                <input type="password" placeholder="Confirm your password" required>
                <button type="submit">Sign Up</button>
            </form>
            <p>Already have an account? <a href="signin.php" class="sign-in-link">Sign In</a></p>
        </div>
    </div>
</body>
</html>
