
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Task List</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f5f5;
      padding: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .task {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      margin-bottom: 15px;
      overflow: hidden;
      cursor: pointer;
      transition: all 0.3s ease-in-out;
      padding: 15px;
      max-width: 500px;
      width: 100%;
    }

    .task h4 {
      margin: 0;
      font-size: 18px;
    }

    .task-content {
      display: flex;
      align-items: flex-start;
      gap: 15px;
    }

    .complete-form {
      margin-top: 5px;
    }

    .complete-box {
      width: 18px;
      height: 18px;
      background-color: white;
      border: 2px solid #ccc;
      border-radius: 3px;
      cursor: pointer;
    }

    .task-details {
      display: none;
      flex-direction: column;
      font-size: 14px;
    }

    .task.expanded .task-details {
      display: flex;
    }

    .task a {
      margin-right: 10px;
      text-decoration: none;
      color: #007bff;
      font-weight: 500;
    }

    .task a:hover {
      text-decoration: underline;
    }

    .task small {
      color: #666;
    }
  </style>
</head>
<body>

<?php
while ($row = mysqli_fetch_assoc($result)):
  $taskDateTime = strtotime($row['taskdate'] . ' ' . $row['tasktime']);
  $currentDateTime = time();
  $isOverdue = $taskDateTime < $currentDateTime;
?>

  <div class="task" id="task-<?php echo $row['taskid']; ?>">
    <div class="task-content">
      <form action="task_completion.php" method="POST" class="complete-form">
        <input type="hidden" name="taskid" value="<?php echo $row['taskid']; ?>">
        <button type="submit" name="complete-box" class="complete-box" title="Tick to complete"></button>
      </form>

      <div>
        <h4 style="<?php echo $isOverdue ? 'color: red;' : ''; ?>">
          <?php echo htmlspecialchars($row['taskname']); ?>
        </h4>
        <div class="task-details">
          <?php if ($isOverdue): ?>
            <p style="color: red; font-weight: bold;">Overdue Task</p>
          <?php endif; ?>

          <p><?php echo !empty($row['taskdescription']) ? htmlspecialchars($row['taskdescription']) : ''; ?></p>
          <p><?php echo !empty($row['taskdate']) ? date('Y-m-d', strtotime($row['taskdate'])) : ''; ?></p>
          <p><?php echo !empty($row['tasktime']) ? date('H:i', strtotime($row['tasktime'])) : ''; ?></p>
          <small>Reminder: 
            <?php echo isset($row['reminder_percentage']) ? htmlspecialchars($row['reminder_percentage']) . '%' : 'Not set'; ?>
          </small><br>
          <a href="edit_task.php?taskid=<?php echo $row['taskid']; ?>">Edit</a>
          <a href="#" class="delete-task" data-taskid="<?php echo $row['taskid']; ?>">Delete</a>
        </div>
      </div>
    </div>
  </div>

<?php endwhile; ?>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const taskBoxes = document.querySelectorAll('.task');

    taskBoxes.forEach(task => {
      task.addEventListener('click', function (e) {
        // Prevent toggle when clicking buttons or links
        if (e.target.tagName === "A" || e.target.tagName === "BUTTON") return;
        task.classList.toggle('expanded');
      });
    });
  });
</script>

</body>
</html>

