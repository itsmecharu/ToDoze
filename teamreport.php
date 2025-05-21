<?php
session_start();
include 'config/database.php';
include 'load_username.php';
include 'navbar.php'; 
include 'toolbar.php';

if (!isset($_SESSION['userid'])) {
    header('location: Signin.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>report</title>
    <link rel ="stylesheet" href="css/dash.css" >
    
</head>

<body>

    <body id="body-pd">
       
  <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
  <script src="js/dash.js"></script>
</html>