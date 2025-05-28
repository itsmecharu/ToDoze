<?php
session_start();
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header('location: Signin.php');
}
$teamid = $_GET['teamid'];

$sql = "SELECT 
            u.userid,
            u.username,
            u.useremail,
            COUNT(t.taskid) AS total_tasks,
            SUM(t.taskstatus = 'Completed') AS completed_tasks,
            SUM(t.taskstatus = 'Pending' AND t.is_overdue = 0) AS pending_tasks,
            SUM(t.taskstatus = 'Pending' AND t.is_overdue = 1) AS overdue_tasks
        FROM team_members tm
        JOIN users u ON tm.userid = u.userid
        LEFT JOIN tasks t ON t.assigned_to = u.userid 
            AND t.teamid = tm.teamid 
            AND t.is_deleted = 0
        WHERE tm.teamid = ?
          AND tm.status = 'Accepted'
        GROUP BY u.userid, u.username, u.useremail";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $teamid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Prepare data for charts
$labels = [];
$completedData = [];
$pendingData = [];
$overdueData = [];
$memberData = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['username'];
    $completedData[] = $row['completed_tasks'] ?: 0;
    $pendingData[] = $row['pending_tasks'] ?: 0;
    $overdueData[] = $row['overdue_tasks'] ?: 0;
    $memberData[] = $row;
}

// Calculate overall statistics
$totalTasks = array_sum(array_column($memberData, 'total_tasks'));
$totalCompleted = array_sum($completedData);
$totalPending = array_sum($pendingData);
$totalOverdue = array_sum($overdueData);
$overallProgress = $totalTasks > 0 ? round(($totalCompleted / $totalTasks) * 100) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Report</title>
    <link rel="stylesheet" href="css/dash.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .report-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .team-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .team-table th, .team-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .team-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .overall-progress {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .progress-circle {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .progress-text {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>

<?php include 'navbar.php'; ?>
<?php include 'toolbar.php'; ?>

<body>
    <div class="report-container">
        <h2>Team Report</h2>
        
        <!-- Overall Progress -->
        <div class="overall-progress">
            <h3>Overall Team Progress</h3>
            <div class="progress-circle">
                <canvas id="progressChart"></canvas>
                <div class="progress-text"><?= $overallProgress ?>%</div>
            </div>
        </div>

        <!-- Team Members Table -->
        <table class="team-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Total Tasks</th>
                    <th>Completed</th>
                    <th>Pending</th>
                    <th>Overdue</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($memberData as $member): ?>
                <tr>
                    <td><?= htmlspecialchars($member['username']) ?></td>
                    <td><?= htmlspecialchars($member['useremail']) ?></td>
                    <td><?= $member['total_tasks'] ?></td>
                    <td><?= $member['completed_tasks'] ?></td>
                    <td><?= $member['pending_tasks'] ?></td>
                    <td><?= $member['overdue_tasks'] ?></td>
                    <td><?= $member['total_tasks'] > 0 ? round(($member['completed_tasks'] / $member['total_tasks']) * 100) : 0 ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="report-grid">
            <!-- Task Distribution Pie Chart -->
            <div class="chart-container">
                <canvas id="taskDistributionChart"></canvas>
            </div>
            
            <!-- Member Tasks Bar Graph -->
            <div class="chart-container">
                <canvas id="memberTasksChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Progress Chart
        new Chart(document.getElementById('progressChart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [<?= $overallProgress ?>, <?= 100 - $overallProgress ?>],
                    backgroundColor: ['#4CAF50', '#f0f0f0']
                }]
            },
            options: {
                cutout: '80%',
                plugins: { legend: { display: false } }
            }
        });

        // Task Distribution Pie Chart
        new Chart(document.getElementById('taskDistributionChart'), {
            type: 'pie',
            data: {
                labels: ['Completed', 'Pending', 'Overdue'],
                datasets: [{
                    data: [<?= $totalCompleted ?>, <?= $totalPending ?>, <?= $totalOverdue ?>],
                    backgroundColor: ['#4CAF50', '#FFC107', '#F44336']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Task Distribution'
                    }
                }
            }
        });

        // Member Tasks Bar Graph
        new Chart(document.getElementById('memberTasksChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Completed',
                    data: <?= json_encode($completedData) ?>,
                    backgroundColor: '#4CAF50'
                }, {
                    label: 'Pending',
                    data: <?= json_encode($pendingData) ?>,
                    backgroundColor: '#FFC107'
                }, {
                    label: 'Overdue',
                    data: <?= json_encode($overdueData) ?>,
                    backgroundColor: '#F44336'
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Tasks by Member'
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true }
                }
            }
        });
    </script>

    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
    <script src="js/dash.js"></script>
</body>
</html>