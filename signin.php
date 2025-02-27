<?php
session_start();
include 'config/database.php';

$useremail = $userpassword = "";
$useremail_err = $userpassword_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $useremail = trim($_POST['useremail']);
    $userpassword = trim($_POST['userpassword']);
    
    // First, check if the email belongs to an admin
    $stmt = mysqli_prepare($conn, "SELECT admin_userid, admin_userpassword FROM admin WHERE admin_useremail = ?");
    mysqli_stmt_bind_param($stmt, "s", $useremail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Admin found
        mysqli_stmt_bind_result($stmt, $admin_userid, $hashedadmin_userpassword);
        mysqli_stmt_fetch($stmt);

        // Verify admin password
        if (password_verify($userpassword, $hashedadmin_userpassword)) {
            // Admin credentials are correct
            $_SESSION['admin_userid'] = $admin_userid;
            $_SESSION['admin_useremail'] = $useremail;
            header("Location: admin/admindashboard.php"); // Redirect to admin dashboard
            exit();
        } else {
            $userpassword_err = "Incorrect password for admin.";
        }
    } else {
        // If not admin, check if it's a normal user
        $stmt = mysqli_prepare($conn, "SELECT userid, userpassword FROM users WHERE useremail = ?");
        mysqli_stmt_bind_param($stmt, "s", $useremail);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            // User found
            mysqli_stmt_bind_result($stmt, $userid, $hashed_password);
            mysqli_stmt_fetch($stmt);

            // Verify password
            if (password_verify($userpassword, $hashed_password)) {
                $_SESSION['userid'] = $userid;
                $_SESSION['useremail'] = $useremail;
                header("Location: dash.php"); // Redirect to user dashboard
                exit();
            } else {
                $userpassword_err = "Incorrect password for user.";
            }
        } else {
            $useremail_err = "Email not registered.";
        }
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
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .error {
            color: red;
            font-size: 12px;
            
        }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <!-- <h3>ToDoze</h3>
        <p>Boost Your Productivity – Organize, Prioritize, and Achieve More with Ease.</p>
    --><div class="logo-container">
                <img src="img/form.png" alt="Logo">
            </div> 
        <div class="form-container">
            <h1>Sign In</h1>
            <p>Welcome back</p>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" >
                <input type="email" name="useremail" placeholder="Enter your email" value="<?php echo htmlspecialchars($useremail); ?>" required>
                <?php if (!empty($useremail_err)): ?>
                    <span class="error"><?php echo $useremail_err; ?></span>
                <?php endif; ?>

                <!-- <input type="password" name="userpassword" placeholder="Enter your password" required> -->
                <div class="password-wrapper">
    <input type="password" name="userpassword" id="password" placeholder="Enter your password" required>
    <div class="password-wrapper">
                <!-- Initial hide image -->
                <img src="img/hide.png" id="toggle-icon" alt="Hide Icon" onclick="togglePassword()">
            </div>
</div>

                <?php if (!empty($userpassword_err)): ?>
                    <span class="error"><?php echo $userpassword_err; ?></span>
                <?php endif; ?>

                <button type="submit">Sign In</button>
            </form>
            <p>Don't have an account? <a href="signup.php" class="sign-up-link">Sign up</a></p>
        </div>
    </div>
    <script>
    function togglePassword() {
    var passwordField = document.getElementById('password');
    var icon = document.getElementById('toggle-icon');  // Get the image element

    // Check if the password is currently hidden or visible
    if (passwordField.type === "password") {
        passwordField.type = "text";  // Show password
        icon.src = "img/show.png";  // Change icon to show image
    } else {
        passwordField.type = "password";  // Hide password
        icon.src = "img/hide.png";  // Change icon to hide image
    }
}

</script>
</body>
</html>
