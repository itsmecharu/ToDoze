<?php
include 'config/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';

// Get current time
$current_time = date("Y-m-d H:i:s");

// Fetch tasks with reminders due now
$sql = "SELECT * FROM tasks WHERE reminder_percentage IS NOT NULL";
$result = mysqli_query($conn, $sql);

while ($task = mysqli_fetch_assoc($result)) {
    $reminder_time = calculateReminderTime($task['taskdate'], $task['tasktime'], $task['reminder_percentage']);
    
    if ($reminder_time <= $current_time) {
        sendReminderEmail($task['userid'], $task['taskname'], $task['taskdate'], $task['tasktime'], $task['reminder_percentage']);
    }
}

// Function to calculate reminder time
function calculateReminderTime($taskdate, $tasktime, $percentage) {
    $datetime = strtotime("$taskdate $tasktime");
    $reminder_time = $datetime - (($percentage / 100) * ($datetime - time()));
    return date("Y-m-d H:i:s", $reminder_time);
}

// Function to send email
function sendReminderEmail($userid, $taskname, $taskdate, $tasktime, $reminder_percentage) {
    global $conn;

    // Get user email
    $sql = "SELECT useremail FROM users WHERE userid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userid); // Bind the userid parameter
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    $useremail = $user['useremail'];

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'todoze9@gmail.com';
        $mail->Password = 'aslu umcq hqhq ebhr'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('todoze9@gmail.com', 'ToDoze');
        $mail->addAddress($useremail);

        $mail->isHTML(true);
        $mail->Subject = "Reminder for Task: $taskname";
        $mail->Body = "<h3>Reminder for your task: $taskname</h3><p>Due date: $taskdate $tasktime.</p><p>Reminder set for: you have reached $reminder_percentage% of the way to the due date.</p>";

        $mail->send();
    } catch (Exception $e) {
        echo "Error sending reminder: {$mail->ErrorInfo}";
    }
}
?>
