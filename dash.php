<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- ===== CSS ===== -->
        <link rel="stylesheet" href="css/dash.css">
        
        <title>Dashboard</title>
    </head>
    <br id="body-pd">
        <div class="l-navbar" id="navbar">
            <nav class="nav">
                <div>
                    <div class="nav__brand">
                        <ion-icon name="menu-outline" class="nav__toggle" id="nav-toggle"></ion-icon>
                        <span class="nav__logo">ToDoze</span>
                    </div>
                    <div class="nav__list">
                        <a href="dash.php" class="nav__link active">
                            <ion-icon name="home-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Dashboard</span>
                        </a>

                        <a href="project.php"  class="nav__link">
                            <ion-icon name="folder-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Projects</span>
                        </a>

                        <a href="analytics.php" class="nav__link">
                            <ion-icon name="pie-chart-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Analytics</span>
                        </a>

                        
                        <a href="profile.php" class="nav__link">
                            
                            <ion-icon name="people-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Profile</span>

                        </a>

                        <a href="review.php" class="nav__link">
                            
                            <ion-icon name="chatbox-ellipses-outline" class="nav__icon"></ion-icon>
                            <span class="nav__name">Review</span>

                        </a>
                       
                    </div>
            

                </div>

                <a href="index.php" class="nav__link">
                    <ion-icon name="log-out-outline" class="nav__icon"></ion-icon>
                    <span class="nav__name">Log Out</span>
                </a>
            </nav>
        </div>

        <h1>Welcome Back</h1></br>
        <!-- Button to Toggle Add Task Form -->
<button class="toggle-form-btn" onclick="toggleTaskForm()">
<ion-icon name="add-outline" class="nav__icon"></ion-icon>
</button>


<!-- Add Task Form (Initially Hidden) -->
<section class="add-task-form" id="addTaskForm" style="display: none;">
    <h2>Add New Task</h2>
    <input type="text" id="taskDescription" placeholder="Task Description" required>
    <input type="date" id="setDate" required>
    <input type="date" id="dueDate" required>
    <button onclick="addTask()">Add Task</button>
</section>

    <!-- Task List -->
    <section class="task-list" id="taskList">
        <h2>Task List</h2>
    </section>

    <!-- Task Summary -->
    <section class="task-summary" id="taskSummary">
        <div>
            <h3>Total Tasks</h3>
            <p id="totalTasks">0</p>
        </div>
        <div>
            <h3>Pending Tasks</h3>
            <p id="pendingTasks">0</p>
        </div>
        <div>
            <h3>Completed Tasks</h3>
            <p id="completedTasks">0</p>
        </div>
    </section>

    <!-- Graph Section -->
    <section class="graph-section">
        <h2>Task Overview</h2>
        <canvas id="taskGraph"></canvas>
    </section>

        <!-- ===== IONICONS ===== -->
        <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
        
        <!-- ===== MAIN JS ===== -->
        <script src="js/dash.js"></script>
        <!-- <script src="script.js"></script> -->
    </body>
</html>