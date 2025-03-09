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
$sql = "SELECT * FROM tasks WHERE reminder_percentage IS NOT NULL AND reminder_sent = 0 AND taskstatus != 'complete'";
$result = mysqli_query($conn, $sql);

// If no tasks are found, log this
if (mysqli_num_rows($result) == 0) {
    error_log("No tasks found that need reminders.");
}

while ($task = mysqli_fetch_assoc($result)) {
    $reminder_time = calculateReminderTime($task['taskdate'], $task['tasktime'], $task['taskcreated_at'], $task['reminder_percentage']);
    
    error_log("Task ID: {$task['taskid']} | Reminder Time: $reminder_time | Current Time: $current_time");

    if ($current_time >= $reminder_time) { 
        error_log("Sending reminder for Task ID: {$task['taskid']}");

        sendReminderEmail($task['userid'], $task['taskname'], $task['taskdate'], $task['tasktime'], $task['reminder_percentage'], $task['projectid']);

        // Mark the reminder as sent
        $update_sql = "UPDATE tasks SET reminder_sent = 1 WHERE taskid = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "i", $task['taskid']);
        if (mysqli_stmt_execute($stmt)) {
            error_log("Reminder marked as sent for Task ID: {$task['taskid']}");
        } else {
            error_log("Failed to update reminder_sent for Task ID: {$task['taskid']}");
        }
    }
}

// Function to calculate the exact reminder time
function calculateReminderTime($taskdate, $tasktime, $taskcreated_at, $reminder_percentage) {
    $taskdue_time = strtotime("$taskdate $tasktime");
    $taskcreated_time = strtotime($taskcreated_at);

    $time_diff = $taskdue_time - $taskcreated_time; // Total time from creation to due
    $reminder_time = $taskdue_time - ($time_diff * ($reminder_percentage / 100)); // Calculate exact reminder time

    error_log("Task Due: " . date("Y-m-d H:i:s", $taskdue_time) . " | Created At: " . date("Y-m-d H:i:s", $taskcreated_time) . " | Reminder Time: " . date("Y-m-d H:i:s", $reminder_time));

    return date("Y-m-d H:i:s", $reminder_time);
}

// Function to send email reminder
function sendReminderEmail($userid, $taskname, $taskdate, $tasktime, $reminder_percentage, $projectid) {
    global $conn;

    // Get user email
    $sql = "SELECT useremail FROM users WHERE userid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    $useremail = $user['useremail'];

    if (!$useremail) {
        error_log("No email found for User ID: $userid");
        return;
    }

    // Get project name if the task belongs to a project
    $projectname = null;
    if ($projectid != null) {
        $project_sql = "SELECT projectname FROM projects WHERE projectid = ?";
        $stmt_project = mysqli_prepare($conn, $project_sql);
        mysqli_stmt_bind_param($stmt_project, "i", $projectid);
        mysqli_stmt_execute($stmt_project);
        $result_project = mysqli_stmt_get_result($stmt_project);
        $project = mysqli_fetch_assoc($result_project);
        $projectname = $project['projectname'];
    }

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

        // Construct email body
        if ($projectname) {
            $mail->Body = "<h3>Reminder for your task: $taskname</h3>
                            <p>Project: $projectname</p>
                            <p>Due date: $taskdate $tasktime.</p>
                            <p>Reminder sent at $reminder_percentage% of the way to the due date.</p>";
        } else {
            $mail->Body = "<h3>Reminder for your task: $taskname</h3>
                            <p>Due date: $taskdate $tasktime.</p>
                            <p>Reminder sent at $reminder_percentage% of the way to the due date.</p>";
        }

        $mail->send();
        error_log("Email sent successfully to: $useremail");

    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
    }
}
?>
