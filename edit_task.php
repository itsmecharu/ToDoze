<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
include 'config/database.php';

if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

if (!isset($_GET['taskid'])) {
    echo "Task ID missing.";
    exit();
}

$taskid = $_GET['taskid'];

$sql = "SELECT * FROM tasks WHERE taskid = ? AND userid = ? AND is_deleted = 0";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $taskid, $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "Task not found.";
    exit();
}

$task = mysqli_fetch_assoc($result);

$taskname = $task['taskname'];
$taskdescription = $task['taskdescription'];
$taskdate = $task['taskdate'];
$tasktime = $task['tasktime'];
$reminder_percentage = $task['reminder_percentage'];
$reminder_repeat = $task['reminder_repeat'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskname_new = trim($_POST['taskname']);
    $taskdescription_new = trim($_POST['taskdescription'] ?? '');
    $taskdate_new = trim($_POST['taskdate'] ?? '');
    $tasktime_new = trim($_POST['tasktime'] ?? '');
    $reminder_percentage_new = $_POST['reminder_percentage'] ?? null;
    $reminder_repeat_new = $_POST['reminder_repeat'] ?? 'none';

    $fields = [];
    $params = [];
    $types = "";

    if ($taskname_new !== $taskname && $taskname_new !== '') {
        $fields[] = "taskname = ?";
        $params[] = $taskname_new;
        $types .= "s";
    }
    if ($taskdescription_new !== $taskdescription) {
        $fields[] = "taskdescription = ?";
        $params[] = $taskdescription_new;
        $types .= "s";
    }
    if ($taskdate_new !== $taskdate) {
        $fields[] = "taskdate = ?";
        $params[] = $taskdate_new;
        $types .= "s";
    }
    if ($tasktime_new !== $tasktime) {
        $fields[] = "tasktime = ?";
        $params[] = $tasktime_new;
        $types .= "s";
    }
    if ($reminder_percentage_new !== $reminder_percentage) {
        $fields[] = "reminder_percentage = ?";
        $params[] = $reminder_percentage_new;
        $types .= "i";
    }
    if ($reminder_repeat_new !== $reminder_repeat) {
        $fields[] = "reminder_repeat = ?";
        $params[] = $reminder_repeat_new;
        $types .= "s";
    }

    if (!empty($fields)) {
        $params[] = $taskid;
        $params[] = $userid;
        $types .= "ii";

        $sql_update = "UPDATE tasks SET " . implode(", ", $fields) . " WHERE taskid = ? AND userid = ?";
        $stmt = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Task updated successfully!";
            header("Location: task.php");
            exit();
        } else {
            echo "Update failed: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <link rel="stylesheet" href="css/dash.css">
</head>
<body id="body-pd">
    <!-- Navbar -->
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__brand">
                    <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                    <span class="nav__logo" style="display: flex; align-items: center;">
                        ToDoze
                        <a href="invitation.php"> <!-- Added a link to redirect to the invitations page -->
                            <ion-icon name="notifications-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                        </a>
                    </span>
                </div>

                <div class="nav__list">
                    <a href="dash.php" class="nav__link">
                        <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Home</span>
                    </a>

                    <a href="task.php" class="nav__link active">
                        <ion-icon name="add-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Task</span>
                    </a>

                    <a href="project.php" class="nav__link">
                        <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Project</span>
                    </a>

                    <a href="review.php" class="nav__link">
                        <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Review</span>
                    </a>

                    <a href="profile.php" class="nav__link">
                        <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                        <span class="nav__name">Profile</span>
                    </a>
                </div>
            </div>

            <a href="logout.php" class="nav__link logout">
                <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                <span class="nav__name"style="color: red;">Log Out</span>
            </a>
        </nav>
    </div>
    <div class="container" style="margin-top: 80px;">
        <div class="box">
            <h2>Edit Task</h2>
            <form class="add-task-form" method="POST" action="">
                <input type="text" name="taskname" value="<?php echo htmlspecialchars($taskname); ?>" required>

                <input type="text" name="taskdescription" placeholder="Task Description"
                    style="height: 80px;" value="<?php echo htmlspecialchars($taskdescription); ?>">

                <div style="margin-bottom: 10px;">
                    <label>Select Due Date 📅</label><br>
                    <input type="date" name="taskdate" value="<?php echo htmlspecialchars($taskdate); ?>">
                </div>

                <div style="margin-bottom: 10px;">
                    <label>Select Time 🕰️</label><br>
                    <input type="time" name="tasktime" value="<?php echo htmlspecialchars($tasktime); ?>">
                </div>

                <label for="reminder">Reminder:</label>
                <select name="reminder_percentage" id="reminder">
                    <option value="" <?php if ($reminder_percentage === null) echo "selected"; ?>>None</option>
                    <option value="50" <?php if ($reminder_percentage == 50) echo "selected"; ?>>50%</option>
                    <option value="75" <?php if ($reminder_percentage == 75) echo "selected"; ?>>75%</option>
                    <option value="90" <?php if ($reminder_percentage == 90) echo "selected"; ?>>90%</option>
                    <option value="100" <?php if ($reminder_percentage == 100) echo "selected"; ?>>100%</option>
                </select>

                <label for="reminder_repeat">Repeat:</label>
                <select name="reminder_repeat" id="reminder_repeat">
                    <option value="none" <?php if ($reminder_repeat === 'none') echo 'selected'; ?>>None</option>
                    <option value="daily" <?php if ($reminder_repeat === 'daily') echo 'selected'; ?>>Daily</option>
                    <option value="weekly" <?php if ($reminder_repeat === 'weekly') echo 'selected'; ?>>Weekly</option>
                    <option value="monthly" <?php if ($reminder_repeat === 'monthly') echo 'selected'; ?>>Monthly</option>
                </select>

                <button type="submit" style="margin-top: 20px;">Update Task</button>
            </form>
        </div>
    </div>
</body>
</html>
 <!-- IONICONS -->
 <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

<!-- MAIN JS -->
<script src="js/dash.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
