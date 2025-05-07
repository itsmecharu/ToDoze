<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');
include 'config/database.php';
include 'load_username.php';

// Safe session check
if (!isset($_SESSION['userid'])) {
    header("Location: signin.php");
    exit();
}

$userid = $_SESSION['userid'];

// Initialize variables with default values
$totalTasks = 0;
$pendingTasks = 0;
$completedTasks = 0;
$overdueTasks = 0;

try {
    // Fetch task statistics for the logged-in user
    $sqlTotalTasks = "SELECT COUNT(*) as total FROM tasks WHERE userid = ?";
    $sqlPendingTasks = "SELECT COUNT(*) as pending FROM tasks WHERE userid = ? AND taskstatus = 'Pending'";
    $sqlCompletedTasks = "SELECT COUNT(*) as completed FROM tasks WHERE userid = ? AND taskstatus = 'Completed'";
    $sqlOverdueTasks = "SELECT COUNT(*) as overdue FROM tasks WHERE userid = ? AND taskstatus = 'Overdue'";

    // Total Tasks
    $stmtTotal = $conn->prepare($sqlTotalTasks);
    $stmtTotal->bind_param("i", $userid);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result();
    if ($row = $resultTotal->fetch_assoc()) {
        $totalTasks = $row['total'];
    }
    $stmtTotal->close();

    // Pending Tasks
    $stmtPending = $conn->prepare($sqlPendingTasks);
    $stmtPending->bind_param("i", $userid);
    $stmtPending->execute();
    $resultPending = $stmtPending->get_result();
    if ($row = $resultPending->fetch_assoc()) {
        $pendingTasks = $row['pending'];
    }
    $stmtPending->close();

    // Completed Tasks
    $stmtCompleted = $conn->prepare($sqlCompletedTasks);
    $stmtCompleted->bind_param("i", $userid);
    $stmtCompleted->execute();
    $resultCompleted = $stmtCompleted->get_result();
    if ($row = $resultCompleted->fetch_assoc()) {
        $completedTasks = $row['completed'];
    }
    $stmtCompleted->close();

    // Overdue Tasks
    $stmtOverdue = $conn->prepare($sqlOverdueTasks);
    $stmtOverdue->bind_param("i", $userid);
    $stmtOverdue->execute();
    $resultOverdue = $stmtOverdue->get_result();
    if ($row = $resultOverdue->fetch_assoc()) {
        $overdueTasks = $row['overdue'];
    }
    $stmtOverdue->close();

} catch (Exception $e) {
    // Log error but don't show to user
    error_log("Database error: " . $e->getMessage());
    // You might want to set default values here if the queries fail
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Task Summary Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
        }
        
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 20px;
        }
        
        .chart-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
        }
        
        .chart-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .chart-wrapper {
            position: relative;
            height: 200px;
            margin-bottom: 15px;
        }
        
        .chart-data {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .controls {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 15px;
        }
        
        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .task-input {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }
        
        input, select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .task-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
        }
        
        .task-list h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .status-completed {
            color: #27ae60;
        }
        
        .status-pending {
            color: #f39c12;
        }
        
        .status-overdue {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>Task Summary Dashboard</h1>
            <p>Visual overview of your tasks with dynamic updates</p>
        </div>
        
        <div class="task-input">
            <input type="text" id="taskName" placeholder="Task name">
            <input type="date" id="taskDueDate">
            <select id="taskPriority">
                <option value="low">Low Priority</option>
                <option value="medium">Medium Priority</option>
                <option value="high">High Priority</option>
            </select>
            <button onclick="addTask()">Add Task</button>
        </div>
        
        <div class="controls">
            <button onclick="filterTasks('all')">Show All</button>
            <button onclick="filterTasks('completed')">Completed Only</button>
            <button onclick="filterTasks('pending')">Pending Only</button>
            <button onclick="filterTasks('overdue')">Overdue Only</button>
        </div>
        
        <div class="chart-container">
            <div class="chart-card">
                <div class="chart-title">All Tasks</div>
                <div class="chart-wrapper">
                    <canvas id="allTasksChart"></canvas>
                </div>
                <div class="chart-data" id="allTasksData">Total: 0</div>
            </div>
            
            <div class="chart-card">
                <div class="chart-title">Completed Tasks</div>
                <div class="chart-wrapper">
                    <canvas id="completedTasksChart"></canvas>
                </div>
                <div class="chart-data" id="completedTasksData">Completed: 0 (0%)</div>
            </div>
            
            <div class="chart-card">
                <div class="chart-title">Pending Tasks</div>
                <div class="chart-wrapper">
                    <canvas id="pendingTasksChart"></canvas>
                </div>
                <div class="chart-data" id="pendingTasksData">Pending: 0 (0%)</div>
            </div>
            
            <div class="chart-card">
                <div class="chart-title">Overdue Tasks</div>
                <div class="chart-wrapper">
                    <canvas id="overdueTasksChart"></canvas>
                </div>
                <div class="chart-data" id="overdueTasksData">Overdue: 0 (0%)</div>
            </div>
        </div>
        
        <div class="task-list">
            <h2>Task Details</h2>
            <table id="taskTable">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="taskTableBody">
                    <!-- Tasks will be added here dynamically -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Sample initial tasks
        let tasks = [
            { id: 1, name: 'Complete project proposal', dueDate: '2023-06-15', priority: 'high', completed: true },
            { id: 2, name: 'Review team reports', dueDate: '2023-06-20', priority: 'medium', completed: false },
            { id: 3, name: 'Schedule meeting', dueDate: '2023-06-10', priority: 'low', completed: false },
            { id: 4, name: 'Update documentation', dueDate: '2023-06-05', priority: 'medium', completed: false }
        ];
        
        // Chart instances
        let allTasksChart, completedTasksChart, pendingTasksChart, overdueTasksChart;
        
        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            renderCharts();
            renderTaskTable();
        });
        
        // Create or update all charts
        function renderCharts() {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            
            // Calculate task counts
            const totalTasks = tasks.length;
            const completedTasks = tasks.filter(task => task.completed).length;
            const pendingTasks = tasks.filter(task => !task.completed && new Date(task.dueDate) >= today).length;
            const overdueTasks = tasks.filter(task => !task.completed && new Date(task.dueDate) < today).length;
            
            // Update data displays
            document.getElementById('allTasksData').textContent = `Total: ${totalTasks}`;
            document.getElementById('completedTasksData').textContent = `Completed: ${completedTasks} (${totalTasks ? Math.round((completedTasks / totalTasks) * 100) : 0}%)`;
            document.getElementById('pendingTasksData').textContent = `Pending: ${pendingTasks} (${totalTasks ? Math.round((pendingTasks / totalTasks) * 100) : 0}%)`;
            document.getElementById('overdueTasksData').textContent = `Overdue: ${overdueTasks} (${totalTasks ? Math.round((overdueTasks / totalTasks) * 100) : 0}%)`;
            
            // All Tasks Chart
            const allTasksCtx = document.getElementById('allTasksChart').getContext('2d');
            if (allTasksChart) allTasksChart.destroy();
            allTasksChart = new Chart(allTasksCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending', 'Overdue'],
                    datasets: [{
                        data: [completedTasks, pendingTasks, overdueTasks],
                        backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    animation: {
                        animateScale: true
                    }
                }
            });
            
            // Completed Tasks Chart
            const completedTasksCtx = document.getElementById('completedTasksChart').getContext('2d');
            if (completedTasksChart) completedTasksChart.destroy();
            completedTasksChart = new Chart(completedTasksCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Remaining'],
                    datasets: [{
                        data: [completedTasks, totalTasks - completedTasks],
                        backgroundColor: ['#27ae60', '#ecf0f1'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Pending Tasks Chart
            const pendingTasksCtx = document.getElementById('pendingTasksChart').getContext('2d');
            if (pendingTasksChart) pendingTasksChart.destroy();
            pendingTasksChart = new Chart(pendingTasksCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Other'],
                    datasets: [{
                        data: [pendingTasks, totalTasks - pendingTasks],
                        backgroundColor: ['#f39c12', '#ecf0f1'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Overdue Tasks Chart
            const overdueTasksCtx = document.getElementById('overdueTasksChart').getContext('2d');
            if (overdueTasksChart) overdueTasksChart.destroy();
            overdueTasksChart = new Chart(overdueTasksCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Overdue', 'Other'],
                    datasets: [{
                        data: [overdueTasks, totalTasks - overdueTasks],
                        backgroundColor: ['#e74c3c', '#ecf0f1'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        // Render the task table
        function renderTaskTable(filter = 'all') {
            const tbody = document.getElementById('taskTableBody');
            tbody.innerHTML = '';
            
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            
            let filteredTasks = tasks;
            
            if (filter === 'completed') {
                filteredTasks = tasks.filter(task => task.completed);
            } else if (filter === 'pending') {
                filteredTasks = tasks.filter(task => !task.completed && new Date(task.dueDate) >= today);
            } else if (filter === 'overdue') {
                filteredTasks = tasks.filter(task => !task.completed && new Date(task.dueDate) < today);
            }
            
            filteredTasks.forEach(task => {
                const dueDate = new Date(task.dueDate);
                const isOverdue = !task.completed && dueDate < today;
                
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${task.name}</td>
                    <td>${dueDate.toDateString()}</td>
                    <td>${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}</td>
                    <td class="status-${task.completed ? 'completed' : isOverdue ? 'overdue' : 'pending'}">
                        ${task.completed ? 'Completed' : isOverdue ? 'Overdue' : 'Pending'}
                    </td>
                    <td>
                        <button onclick="toggleTaskStatus(${task.id})" style="background-color: ${task.completed ? '#7f8c8d' : '#27ae60'}; padding: 5px 10px; margin-right: 5px;">
                            ${task.completed ? 'Undo' : 'Complete'}
                        </button>
                        <button onclick="deleteTask(${task.id})" style="background-color: #e74c3c; padding: 5px 10px;">
                            Delete
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }
        
        // Filter tasks
        function filterTasks(filter) {
            renderTaskTable(filter);
        }
        
        // Add a new task
        function addTask() {
            const nameInput = document.getElementById('taskName');
            const dueDateInput = document.getElementById('taskDueDate');
            const priorityInput = document.getElementById('taskPriority');
            
            if (!nameInput.value || !dueDateInput.value) {
                alert('Please enter task name and due date');
                return;
            }
            
            const newTask = {
                id: tasks.length > 0 ? Math.max(...tasks.map(task => task.id)) + 1 : 1,
                name: nameInput.value,
                dueDate: dueDateInput.value,
                priority: priorityInput.value,
                completed: false
            };
            
            tasks.push(newTask);
            
            // Clear inputs
            nameInput.value = '';
            dueDateInput.value = '';
            priorityInput.value = 'low';
            
            // Update UI
            renderCharts();
            renderTaskTable();
        }
        
        // Toggle task completion status
        function toggleTaskStatus(taskId) {
            const taskIndex = tasks.findIndex(task => task.id === taskId);
            if (taskIndex !== -1) {
                tasks[taskIndex].completed = !tasks[taskIndex].completed;
                renderCharts();
                renderTaskTable();
            }
        }
        
        // Delete a task
        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                tasks = tasks.filter(task => task.id !== taskId);
                renderCharts();
                renderTaskTable();
            }
        }
        
        // Simulate real-time updates (for demo purposes)
        setInterval(() => {
            // In a real app, you might check for new tasks from an API here
            renderCharts();
        }, 60000); // Update every minute
    </script>
</body>
</html>