<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Task List - ToDoze</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f4f4;
      color: #333;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1100px;
      margin: 30px auto 10px auto;
      padding: 0 20px;
    }

    .header h1 {
      font-size: 2em;
      color: #2c3e50;
    }

    .filter {
      display: flex;
      gap: 10px;
    }

    .filter button {
      padding: 8px 16px;
      background-color: #3498db;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .filter button:hover {
      background-color: #2980b9;
    }

    .container {
      max-width: 1100px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .task {
      background-color: #fff;
      border: 1px solid #e0e0e0;
      border-left: 5px solid #4CAF50;
      border-radius: 10px;
      padding: 15px;
      height: 200px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: transform 0.2s;
    }

    .task:hover {
      transform: translateY(-5px);
    }

    .complete-box {
      width: 18px;
      height: 18px;
      border: 2px solid #4CAF50;
      border-radius: 3px;
      background-color: white;
      cursor: pointer;
      margin-bottom: 10px;
      position: relative;
    }

    .complete-box.checked {
      background-color: #4CAF50;
    }

    .complete-box.checked::after {
      content: '✔';
      color: white;
      font-size: 14px;
      font-weight: bold;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -60%);
    }

    .task h4 {
      margin: 0;
      font-size: 1.1em;
      font-weight: bold;
    }

    .task p {
      font-size: 0.9em;
      margin: 4px 0;
      color: #555;
    }

    .task small {
      color: #888;
      font-size: 0.8em;
    }

    .task a {
      margin-right: 10px;
      text-decoration: none;
      font-size: 0.85em;
      color: #3498db;
    }

    .overdue {
      color: red;
      font-weight: bold;
    }

    .hidden {
      display: none;
    }
  </style>
</head>
<body>

  <!-- Header with Filter -->
  <div class="header">
    <h1>Task List</h1>
    <div class="filter">
      <button onclick="showAll()">All Tasks</button>
      <button onclick="showCompleted()">Completed Tasks</button>
    </div>
  </div>

  <!-- Task Container -->
  <div class="container" id="taskContainer">
    <!-- Task 1 -->
    <div class="task" data-completed="false">
      <div class="complete-box" onclick="toggleComplete(this)"></div>
      <h4 class="overdue">Finish Assignment</h4>
      <p>Complete the IT ethics report</p>
      <p>Due: 2025-04-20, 10:00</p>
      <small>Reminder: 70%</small>
      <div>
        <a href="#">Edit</a>
        <a href="#">Delete</a>
      </div>
    </div>

    <!-- Task 2 -->
    <div class="task" data-completed="false">
      <div class="complete-box" onclick="toggleComplete(this)"></div>
      <h4>Buy Groceries</h4>
      <p>Milk, eggs, bread, fruits</p>
      <p>Due: 2025-04-28, 17:30</p>
      <small>Reminder: 50%</small>
      <div>
        <a href="#">Edit</a>
        <a href="#">Delete</a>
      </div>
    </div>

    <!-- Task 3 -->
    <div class="task" data-completed="true">
      <div class="complete-box checked" onclick="toggleComplete(this)"></div>
      <h4>Team Meeting</h4>
      <p>Discuss final slides</p>
      <p>Due: 2025-04-27, 14:00</p>
      <small>Reminder: 60%</small>
      <div>
        <a href="#">Edit</a>
        <a href="#">Delete</a>
      </div>
    </div>
  </div>

  <script>
    function toggleComplete(box) {
      box.classList.toggle('checked');
      const task = box.closest('.task');
      const isDone = box.classList.contains('checked');
      task.setAttribute('data-completed', isDone);
    }

    function showCompleted() {
      const tasks = document.querySelectorAll('.task');
      tasks.forEach(task => {
        task.style.display = task.getAttribute('data-completed') === 'true' ? 'flex' : 'none';
      });
    }

    function showAll() {
      const tasks = document.querySelectorAll('.task');
      tasks.forEach(task => {
        task.style.display = 'flex';
      });
    }
  </script>

</body>
</html>

