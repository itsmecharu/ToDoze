<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
include 'config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];
$taskname = $taskdescription = $taskdate = $tasktime = $reminder_percentage =$reminder_repeat= "";

// Handle Task Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskname = trim($_POST['taskname']);
    $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
    $taskdate = (!empty($_POST['taskdate'])) ? ($_POST['taskdate']) : null;
    $tasktime = (!empty($_POST['tasktime'])) ? ($_POST['tasktime']) : null;
    $reminder_percentage = !empty($_POST['reminder_percentage']) ? $_POST['reminder_percentage'] : null;
    $reminder_repeat = isset($_POST['reminder_repeat']) ? $_POST['reminder_repeat'] : 'none';
    // echo $tasktime;
    // exit();
    $sql = "INSERT INTO tasks (userid, taskname, taskdescription, taskdate, tasktime, reminder_percentage, reminder_repeat, taskstatus) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issssss", $userid, $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage, $reminder_repeat);
    
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
    <title>Task</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Initially align container to the left */
.container {
    margin-left: 150px; /* This matches the navbar width */
    transition: all 0.3s ease-in-out;
}

/* When navbar is collapsed */
body.nav-collapsed .container {
    margin-left: 120px;
    margin-right: 0px;
    max-width: 800px; /* Optional: limit the width */
    text-align: center;
    
}
    </style>
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

    <h1>ToDoze</h1>



    <div class="container" style="color:rgb(52, 51, 50);">
        <!-- Add Task Section -->
        <div class="box">
        <h2 style="text-align: center;">Add Task Here </h2>
            <form class="add-task-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <!-- <label for="taskname">Task Name:</label> -->
                <input type="text" id="taskname" name="taskname" placeholder="Add task here" required>

                <!-- <label for="taskDescription">Task Description:</label> -->
                <input type="text" id="taskDescription" name="taskdescription" placeholder="Task Description"
                    style="height: 80px;">
                <div>
                    <!-- Date Section -->
                    <div style="display: inline-block; vertical-align: top; margin-right: 20px;">
                        <label for="taskdate" style="display: block;">Select Due Date 📅</label>
                        <input type="date" id="taskdate" name="taskdate" style="width: 170px;">
                    </div>

                    <!-- Time Section -->
                    <div style="display: inline-block; vertical-align: top;">
                        <label for="tasktime" style="display: block;">Select Time 🕰️</label>
                        <input type="time" id="tasktime" name="tasktime" style="width: 170px;">
                    </div>


               
                <select id="reminder" name="reminder_percentage">
                    <option value="" disabled selected>Select Reminder 🔔</option>
                    <option value="">None</option>
                    <option value="50">50% (Halfway to Due Date)</option>
                    <option value="75">75% (Closer to Due Date)</option>
                    <option value="90">90% (Near Due Date)</option>
                    <option value="100">100% (On Time)</option>
                </select>

                
                <label for="reminder_repeat">Repeat:</label>
                <select name="reminder_repeat" id="reminder_repeat">
                    <option value="none" selected>None</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>


                    <button type="submit" style="margin-top: 20px;">Done</button>
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
                width: 200px;
                /* Set the width of the card */
                padding: 20px;
                /* Optional: Add padding to adjust internal spacing */
            }

            .small-swal-title {
                font-size: 16px;
                /* Adjust font size of the title */
                font-weight: bold;
                /* Optional: Make title bold */
            }

            .small-swal-content {
                font-size: 14px;
                /* Adjust font size of the text content */
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
        // Get references to the button and container
        const addTaskButton = document.getElementById('addTaskButton');
        const container = document.querySelector('.container');

        // Add click event listener to the button
        addTaskButton.addEventListener('click', function () {
            // Toggle the 'active' class on the container
            container.classList.toggle('actives');
        });
    </script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const taskDate = document.getElementById('taskdate');
    const taskTime = document.getElementById('tasktime');
    const reminderSelect = document.getElementById('reminder');
    const repeatSelect = document.getElementById('reminder_repeat');
    const form = document.querySelector('.add-task-form');

    function hasDateTime() {
        return taskDate.value && taskTime.value;
    }

    function toggleReminderAndRepeat() {
        const enabled = hasDateTime();
        reminderSelect.disabled = !enabled;
        repeatSelect.disabled = !enabled;

        if (!enabled) {
            reminderSelect.value = "";
            repeatSelect.value = "none";
        }
    }

    taskDate.addEventListener('input', toggleReminderAndRepeat);
    taskTime.addEventListener('input', toggleReminderAndRepeat);

    form.addEventListener('submit', function (event) {
        const reminderVal = reminderSelect.value;
        const repeatVal = repeatSelect.value;

        if ((reminderVal || repeatVal !== "none") && !hasDateTime()) {
            event.preventDefault();
            alert("Please set both due date and time before setting a reminder or repeat.");
        }
    });

    toggleReminderAndRepeat();
});
</script>


</body>

</html>