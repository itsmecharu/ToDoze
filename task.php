<?php
session_start();
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$taskname = $taskdescription = $taskdate = $tasktime = $reminder_percentage = "";

// Handle Task Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskname = trim($_POST['taskname']);
    $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
    $taskdate = isset($_POST['taskdate']) ? $_POST['taskdate'] : null;
    $tasktime = isset($_POST['tasktime']) ? $_POST['tasktime'] : null;
    $reminder_percentage = isset($_POST['reminder_percentage']) ? trim($_POST['reminder_percentage']) : null;

    $sql = "INSERT INTO tasks (userid, taskname, taskdescription, taskdate, tasktime, reminder_percentage, taskstatus) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssis", $userid, $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Task added successfully!";
            header("Location: task.php"); // Redirect to avoid form resubmission
            exit();
        } else {
            echo "Error executing query: " . mysqli_error($conn);
        }
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <title>Task</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body id="body-pd">
    <!-- Navbar -->
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__brand">
                    <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                    <span class="nav__logo">ToDoze</span>
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
                <span class="nav__name">Log Out</span>
            </a>
        </nav>
    </div>

    <h1>ToDoze</h1>
    <div class="container">
        <!-- Add Task Section -->
        <div class="box">
            <h2>Add Task Here</h2>

            <form class="add-task-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <label for="taskname">Task Name:</label>
                <input type="text" id="taskname" name="taskname" placeholder="Add task here" required>

                <label for="taskDescription">Task Description:</label>
                <input type="text" id="taskDescription" name="taskdescription" placeholder="Task Description">

                <label for="taskdate">Due Date:</label>
                <input type="date" id="taskdate" name="taskdate">
                <input type="time" id="tasktime" name="tasktime">

                <label for="reminder">Set Reminder:</label>
                <select id="reminder" name="reminder_percentage">
                    <option value="" disabled selected>Set Reminder Here</option>
                    <option value="50">50% (Halfway to Due Date)</option>
                    <option value="75">75% (Closer to Due Date)</option>
                    <option value="90">90% (Near Due Date)</option>
                    <option value="100">100% (On Time)</option>
                </select>

                <button type="submit">Done</button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
            title: "Task added successfully!",
            text: "", // Empty text since you only want "Task added successfully"
            // icon: "success",
            timer: 1000,
            showConfirmButton: false,
            customClass: {
                popup: 'small-swal', // Custom class for SweetAlert popup
                title: 'small-swal-title', // Custom class for the title
                content: 'small-swal-content' // Custom class for the content
            }
        });
    });
</script>

<style>
    .small-swal {
        width: 200px; /* Set the width of the card */
        padding: 20px; /* Optional: Add padding to adjust internal spacing */
    }

    .small-swal-title {
        font-size: 16px; /* Adjust font size of the title */
        font-weight: bold; /* Optional: Make title bold */
    }

    .small-swal-content {
        font-size: 14px; /* Adjust font size of the text content */
    }
</style>


    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- IONICONS -->
    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>

    <!-- MAIN JS -->
    <script src="js/dash.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const taskDate = document.getElementById('taskdate');
        const taskTime = document.getElementById('tasktime');
        const reminderSelect = document.getElementById('reminder');
        const form = document.querySelector('.add-task-form');

        function checkDateAndTime() {
            reminderSelect.disabled = !(taskDate.value && taskTime.value);
            if (reminderSelect.disabled) reminderSelect.value = "";
        }

        taskDate.addEventListener('input', checkDateAndTime);
        taskTime.addEventListener('input', checkDateAndTime);

        reminderSelect.addEventListener('change', function () {
            if (!taskDate.value || !taskTime.value) {
                alert("Set both date and time before selecting a reminder.");
                this.value = "";
            }
        });

        form.addEventListener('submit', function (event) {
            if (reminderSelect.value && (!taskDate.value || !taskTime.value)) {
                alert("Set both date and time before setting a reminder.");
                event.preventDefault();
            }
        });
    });
    </script>
</body>

</html>
