<?php
include 'config/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';

// Get current time
$current_time = date("Y-m-d H:i:s");

// Fetch tasks where reminder is set but hasn't been sent
$sql = "SELECT * FROM tasks WHERE reminder_percentage IS NOT NULL AND reminder_sent = 0";
$result = mysqli_query($conn, $sql);

while ($task = mysqli_fetch_assoc($result)) {
    $reminder_time = calculateReminderTime($task['taskdate'], $task['tasktime'], $task['created_at'], $task['reminder_percentage']);
    
    if ($current_time >= $reminder_time) { 
        sendReminderEmail($task['userid'], $task['taskname'], $task['taskdate'], $task['tasktime'], $task['reminder_percentage']);
        
        // Mark the reminder as sent
        $update_sql = "UPDATE tasks SET reminder_sent = 1 WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "i", $task['id']);
        mysqli_stmt_execute($stmt);
    }
}

// Function to calculate the exact reminder time
function calculateReminderTime($taskdate, $tasktime, $created_at, $percentage) {
    $task_due_time = strtotime("$taskdate $tasktime");
    $task_created_time = strtotime($created_at);

    $time_diff = $task_due_time - $task_created_time; // Total time from creation to due
    $reminder_time = $task_due_time - ($time_diff * ($percentage / 100)); // Calculate exact reminder time

    return date("Y-m-d H:i:s", $reminder_time);
}

// Function to send email reminder
function sendReminderEmail($userid, $taskname, $taskdate, $tasktime, $reminder_percentage) {
    global $conn;

    // Get user email
    $sql = "SELECT useremail FROM users WHERE userid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userid);
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
        $mail->Body = "<h3>Reminder for your task: $taskname</h3>
                        <p>Due date: $taskdate $tasktime.</p>
                        <p>Reminder sent at $reminder_percentage% of the way to the due date.</p>";

        $mail->send();
    } catch (Exception $e) {
        echo "Error sending reminder: {$mail->ErrorInfo}";
    }
}
?>
