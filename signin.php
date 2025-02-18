<?php
session_start();
include 'config/database.php';

$useremail = $userpassword = "";
$useremail_err = $userpassword_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $useremail = trim($_POST['useremail']);
    $userpassword = trim($_POST['userpassword']);

    // Prepared statement for security
    $stmt = mysqli_prepare($conn, "SELECT userid, userpassword FROM users WHERE useremail = ?");
    mysqli_stmt_bind_param($stmt, "s", $useremail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    // Check if user exists
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $userid, $hashed_password);
        mysqli_stmt_fetch($stmt);

        // Verify password
        if (password_verify($userpassword, $hashed_password)) {
            $_SESSION['userid'] = $userid;
            $_SESSION['useremail'] = $useremail;
            header("Location: dash.php");
            exit();
        } else {
            $userpassword_err = "Incorrect password.";
        }
    } else {
        $useremail_err = "Email not registered.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="css/form.css">
    <style>
        .error {
            color: red;
            font-size: 12px;
            
        }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logo">
        </div>
        <div class="form-container">
            <h1>Sign In</h1>
            <p>Welcome back to your favorite productivity app!</p>
            <form method="POST" action="">
                <input type="email" name="useremail" placeholder="Enter your email" value="<?php echo htmlspecialchars($useremail); ?>" required>
                <?php if (!empty($useremail_err)): ?>
                    <span class="error"><?php echo $useremail_err; ?></span>
                <?php endif; ?>

                <input type="password" name="userpassword" placeholder="Enter your password" required>
                <?php if (!empty($userpassword_err)): ?>
                    <span class="error"><?php echo $userpassword_err; ?></span>
                <?php endif; ?>

                <button type="submit">Sign In</button>
            </form>
            <p>Don't have an account? <a href="signup.php" class="sign-up-link">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
