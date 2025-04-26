<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $userid = $_SESSION['userid'];
    $file = $_FILES['profile_pic'];

    // Check if file is uploaded without error
    if ($file['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName = uniqid() . "_" . basename($file["name"]);
        $targetFile = $targetDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            // Update user profile photo path in DB
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE userid = ?");
            $stmt->bind_param("si", $fileName, $userid);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success'] = "Profile picture updated!";
        } else {
            $_SESSION['error'] = "Error uploading file.";
        }
    } else {
        $_SESSION['error'] = "No file uploaded or upload error.";
    }
}

$conn->close();
header("Location: profile.php");
exit();
?>
