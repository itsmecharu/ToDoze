<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
include 'config/database.php';
include 'load_username.php';


// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
  header("Location: signin.php");
  exit();
}

$userid = $_SESSION['userid'];
$filter = $_GET['filter'] ?? 'all';
$taskname = $taskdescription = $taskdate = $tasktime = $reminder_percentage = "";

// Handle Task Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $taskname = trim($_POST['taskname']);
  $taskdescription = isset($_POST['taskdescription']) ? trim($_POST['taskdescription']) : null;
  $taskdate = (!empty($_POST['taskdate'])) ? ($_POST['taskdate']) : null;
  $tasktime = (!empty($_POST['tasktime'])) ? ($_POST['tasktime']) : null;
  $reminder_percentage = (!empty($_POST['reminder_percentage'])) ? $_POST['reminder_percentage'] : null;

  // echo $tasktime;
  // exit();
  $sql = "INSERT INTO tasks (userid, taskname, taskdescription, taskdate, tasktime, reminder_percentage, taskstatus) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "isssss", $userid, $taskname, $taskdescription, $taskdate, $tasktime, $reminder_percentage);
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



// for overdue part
$now = date('Y-m-d H:i:s');
$update_sql = "UPDATE tasks 
    SET is_overdue = 
        CASE 
            WHEN CONCAT(taskdate, ' ', IFNULL(tasktime, '00:00:00')) < ? 
            THEN 1 
            ELSE 0 
        END 
    WHERE userid = ? AND taskstatus != 'Completed' AND is_deleted = 0 AND teamid IS NULL
";
$update_stmt = mysqli_prepare($conn, $update_sql);
if ($update_stmt) {
  mysqli_stmt_bind_param($update_stmt, "si", $now, $userid);
  mysqli_stmt_execute($update_stmt);
}

// --- Task query based on filter ---
if ($filter === 'completed') {
  $sql = "SELECT * FROM tasks WHERE userid = ? AND taskstatus = 'completed' AND is_deleted = 0 AND teamid IS NULL ORDER BY completed_at DESC";
} elseif ($filter === 'overdue') {
  $sql = "SELECT * FROM tasks WHERE userid = ? AND is_overdue = 1 AND is_deleted = 0 AND taskstatus != 'completed' AND teamid IS NULL";
} else {
  $sql = "SELECT * FROM tasks WHERE userid = ? AND taskstatus != 'completed' AND is_deleted = 0 AND teamid IS NULL";
}

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
  mysqli_stmt_bind_param($stmt, "i", $userid);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
} else {
  echo "Error preparing statement: " . mysqli_error($conn);
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
</head>

<body id="body-pd">
  <div class="top-bar">
    <div class="top-left">
      <!-- Removed profile from here -->
    </div>

    <div class="top-right-icons">
      <!-- Notification Icon -->
      <a href="invitation.php" class="top-icon">
        <ion-icon name="notifications-outline"></ion-icon>
      </a>

      <!-- Profile Icon -->
      <div class="profile-info">
        <a href="profile.php" class="profile-circle" title="<?= htmlspecialchars($username) ?>">
          <ion-icon name="person-outline"></ion-icon>
        </a>
        <span class="username-text"><?= htmlspecialchars($username) ?></span>
      </div>


    </div>
  </div>

  <div class="filter-container">
    <div style="display: flex; justify-content: center;">
      <button id="AddTaskBtn" class="create-btn"> + Add task</button>
    </div>

    <a href="task.php" class="task-filter <?= $filter == 'all' ? 'active' : '' ?>">🕒 Pending</a>
    <a href="task.php?filter=completed" class="task-filter <?= $filter == 'completed' ? 'active' : '' ?>">✅
      Completed</a>
    <a href="task.php?filter=overdue" class="task-filter <?= $filter == 'overdue' ? 'active' : '' ?>">⏰ Overdue</a>
  </div>

  <div class="logo-container">
    <img src="img/logo.png" alt="App Logo" class="logo">
  </div>

  <div class="l-navbar" id="navbar">
    <nav class="nav">
      <div class="nav__list">
        <a href="dash.php" class="nav__link "><ion-icon name="home-outline" class="nav__icon"></ion-icon><span
            class="nav__name">Home</span></a>
        <a href="task.php" class="nav__link active"><ion-icon name="add-outline" class="nav__icon"></ion-icon><span
            class="nav__name">Task</span></a>
        <a href="team.php" class="nav__link"><ion-icon name="people-outline" class="nav__icon"></ion-icon><span
            class="nav__name">Team</span></a>
        <a href="review.php" class="nav__link"><ion-icon name="chatbox-ellipses-outline"
            class="nav__icon"></ion-icon><span class="nav__name">Review</span></a>
      </div>
      <a href="logout.php" class="nav__link logout"><ion-icon name="log-out-outline" class="nav__icon"></ion-icon><span
          class="nav__name" style="color: #d96c4f;"><b>Log Out</b></span></a>
    </nav>
  </div>




  <?php
  if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $isOverdue = $row['is_overdue'] == 1;
      $isCompleted = strtolower($row['taskstatus']) === 'completed';

      echo "<div class='task' id='task-" . $row['taskid'] . "'>";
      echo "<div class='task-content'>";

      // Tick box (only if not completed)
      if (!$isCompleted) {
        echo "<form action='task_completion.php' method='POST' class='complete-form'>";
        echo "<input type='hidden' name='taskid' value='" . $row['taskid'] . "'>";
        echo "<button type='submit' name='complete-box' class='complete-box' title='Tick to complete'></button>";
        echo "</form>";
      }

      echo "<div class='task-details'>";
      echo "<h4 style='display: inline; margin-right: 10px;";
      if ($isOverdue && !$isCompleted) {
        echo "color: red;";
      }
      echo "'>" . htmlspecialchars($row['taskname']) . "</h4>";
  
      if ($isOverdue && !$isCompleted) {
        echo "<span style='color: red; font-weight: light;'>(Overdue)</span>";
      }
      echo "<div class='task-info-line'>";
      echo "<div class='task-details-left'>";

      if (!empty($row['taskdescription'])) {
        echo "<div class='task-description'><span class='info'>Description: " . htmlspecialchars($row['taskdescription']) . "</span></div>";
      }
      echo (!empty($row['taskdate']) ? "<span class='info'>DueDate: " . htmlspecialchars(date('Y-m-d', strtotime($row['taskdate']))) . "</span>" : "");
      echo (!empty($row['tasktime']) ? "<span class='info'>DueTime: " . htmlspecialchars(date('H:i', strtotime($row['tasktime']))) . "</span>" : "");
      echo "<span class='info'>Reminder: " . (isset($row['reminder_percentage']) && $row['reminder_percentage'] !== null ? htmlspecialchars($row['reminder_percentage']) . "%" : "Not set") . "</span>";
      echo "</div>";

      // Task Actions (Edit/Delete or Completed Date)
      echo "<div class='task-actions'>";
      if (!$isCompleted) {

        // priority section 
        $currentPriority = $row['taskpriority'] ?? 'none';
        $taskId = $row['taskid'];

        // Map priorities to circle icons
        $priorityIcons = [
          'High' => '🔴',
          'Medium' => '🟡',
          'Low' => '🟢',
          'none' => '⚫'
        ];

         echo "<div class='priority-wrapper' style='display: inline-block; position: relative;'>";

        // Toggle icon
        echo "<button type='button' class='priority-toggle' onclick=\"toggleDropdown('dropdown-$taskId')\" title='Change Priority' style=' background: none; border: none; font-size: 12px; cursor: pointer;'>";
        echo $priorityIcons[$currentPriority];
        echo "</button>";

        // Hidden dropdown form
        echo "<form method='POST' action='update_priority.php' class='priority-dropdown' id='dropdown-$taskId' style='display: none; position: absolute; top: 25px; left: 0; background: #fff; border: 1px solid #ccc; padding: 5px; border-radius: 6px; z-index: 10;'>";
        echo "<input type='hidden' name='taskid' value='" . $taskId . "'>";
        echo "<select name='taskpriority' onchange='this.form.submit();'>";
        foreach ($priorityIcons as $key => $icon) {
          $selected = ($currentPriority === $key) ? 'selected' : '';
          echo "<option value='$key' $selected>$icon $key</option>";
        }
        echo "</select>";
        echo "</form>";

        echo "</div>";
        // priority section ends


        echo "<a href='edit_task.php?taskid=" . $row['taskid'] . "' class='edit-btn' title='Edit'><ion-icon name='create-outline'></ion-icon>Edit</a>";
        echo "<a href='#' class='delete-btn'   title='Delete'  data-taskid='" . $row['taskid'] . "'><ion-icon name='trash-outline'></ion-icon>Delete</a>";
      } else {
        if (!empty($row['completed_at'])) {
          echo "<span class='info' style='color: green;'><ion-icon name='checkmark-done-outline'></ion-icon> Completed on: " . date('Y-m-d H:i', strtotime($row['completed_at'])) . "</span>";
        }
        echo "<a href='#' title='Delete' class='delete-btn' data-taskid='" . $row['taskid'] . "' ><ion-icon  name='trash-outline'></ion-icon>Delete</a>";
      }

      echo "</div>"; // task-actions
  
      echo "</div></div></div></div>"; // task-info-line, task-details, task-content, task
    }
  } else {
    echo '
<div class="centered-content">
  <div class="content-wrapper">
    <img src="img/notask.png" alt="No tasks yet" />
    <h3><p>No tasks yet 🚀</p></h3>
  </div>
</div>';
  }
  ?>

  <div class="container">

    <!-- Modal -->
    <div id="TaskModal" class="modal-overlay" style="display: none;">
      <div class="modal-content">
        <span class="close-modal" id="closeModalBtn">&times;</span>
        <h2 style="text-align: center;">Add Task Here </h2>
        <form class="add-task-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
          <!-- <label for="taskname">Task Name:</label> -->
          <input type="text" id="taskname" name="taskname" placeholder="Add task here" maxlength="50" required>

          <!-- <label for="taskDescription">Task Description:</label> -->
          <input type="text" id="taskDescription" name="taskdescription" placeholder="Task Description" maxlength="140"
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
          </div>
          <!-- <label for="reminder">Set Reminder:</label> -->
          <select id="reminder" name="reminder_percentage">
            <option value="" disabled selected>Set Reminder Here 🔔</option>
            <option value="50">50% (Halfway to Due Date)</option>
            <option value="75">75% (Closer to Due Date)</option>
            <option value="90">90% (Near Due Date)</option>
            <option value="100">100% (On Time)</option>
          </select>
          <button type="submit" style="margin-top: 20px; border-radius: 20px;">Done</button>
        </form>
      </div>
    </div>
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


  <script>
    // Get references to the button and container
    const addTaskButton = document.getElementById('AddTaskButton');
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
      const form = document.querySelector('.add-task-form');

      // Disable reminder initially
      function checkDateAndTime() {
        const disableReminder = !(taskDate.value && taskTime.value);
        reminderSelect.disabled = disableReminder;
        if (disableReminder) reminderSelect.value = "";
      }

      // Run check on load and on input
      checkDateAndTime();
      taskDate.addEventListener('input', checkDateAndTime);
      taskTime.addEventListener('input', checkDateAndTime);

      // Prevent setting reminder without date & time (extra safety)
      reminderSelect.addEventListener('change', function () {
        if (!taskDate.value || !taskTime.value) {
          alert("Please set both date and time before choosing a reminder.");
          this.value = "";
        }
      });

      // Prevent form submit if reminder is set but date/time is missing
      form.addEventListener('submit', function (event) {
        if (reminderSelect.value && (!taskDate.value || !taskTime.value)) {
          alert("You must select both date and time if you want to set a reminder.");
          event.preventDefault();
        }
      });
    });
  </script>
  <!-- for filters -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Optional: If "showFiltersBtn" exists
      const showFiltersBtn = document.getElementById("showFiltersBtn");
      const taskCategories = document.getElementById("taskCategories");
      if (showFiltersBtn && taskCategories) {
        showFiltersBtn.addEventListener("click", function () {
          if (taskCategories.style.display === "none" || taskCategories.style.display === "") {
            taskCategories.style.display = "flex";
            this.textContent = "Hide Filters";
          } else {
            taskCategories.style.display = "none";
            this.textContent = "Show Filters";
          }
        });
      }

      document.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          var taskid = this.getAttribute('data-taskid');
          Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = 'delete_task.php?taskid=' + taskId;
            }
          });
        });
      });

      document.querySelectorAll(".complete-form").forEach(function (form) {
        form.addEventListener("submit", function (e) {
          e.preventDefault();
          Swal.fire({
            text: "Task completed?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes"
          }).then((result) => {
            if (result.isConfirmed) {
              form.submit();
            }
          });
        });
      });
    });
  </script>

  <!-- for floooting  -->
  <script>
    const modal = document.getElementById("TaskModal");
    const openBtn = document.getElementById("AddTaskBtn");
    const closeBtn = document.getElementById("closeModalBtn");

    // Open modal
    openBtn.addEventListener("click", () => {
      modal.style.display = "flex";
      openBtn.style.display = "none";
    });

    // Close modal
    closeBtn.addEventListener("click", closeModal);

    // Close modal when clicking outside the content
    window.addEventListener("click", function (event) {
      if (event.target === modal) {
        closeModal();
      }
    });

    function closeModal() {
      modal.style.display = "none";
      openBtn.style.display = "inline-block"; // or "block" based on styling
    }
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const descriptions = document.querySelectorAll('.task-details-left .info');

      descriptions.forEach(desc => {
        if (desc.textContent.startsWith("Description:")) {
          const fullText = desc.textContent.trim().replace("Description:", "").trim();
          if (fullText.length > 8) {
            const shortText = fullText.substring(0, 8) + "..........";

            let toggled = false;
            desc.textContent = "Description: " + shortText;
            desc.classList.add("truncated");

            desc.addEventListener("click", function () {
              toggled = !toggled;
              desc.textContent = "Description: " + (toggled ? fullText : shortText);
            });
          }
        }
      });
    });
  </script>

  <script>
    function toggleDropdown(id) {
      const dropdown = document.getElementById(id);
      const allDropdowns = document.querySelectorAll('.priority-dropdown');
      allDropdowns.forEach(el => {
        if (el.id !== id) el.style.display = 'none';
      });
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
  </script>


  <!-- Icons and Charts -->
  <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
  <script src="js/dash.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>



</body>

</html>