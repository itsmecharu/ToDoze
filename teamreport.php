<?php
session_start();
include 'config/database.php';
include 'load_username.php';

if (!isset($_SESSION['userid'])) {
    header('location: Signin.php');
}
$teamid = $_GET['teamid'];

$team_sql = "SELECT teamname FROM teams WHERE teamid = ?";
$team_stmt = mysqli_prepare($conn, $team_sql);
mysqli_stmt_bind_param($team_stmt, "i", $teamid);
mysqli_stmt_execute($team_stmt);
$team_result = mysqli_stmt_get_result($team_stmt);
$team = mysqli_fetch_assoc($team_result);

$sql = "SELECT 
            u.userid,
            u.username,
            u.useremail,
            tm.role,
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
          AND tm.has_exited = 0
        GROUP BY u.userid, u.username, u.useremail, tm.role";

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
    <title>Team Report - <?php echo htmlspecialchars($team['teamname']); ?></title>
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
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .team-table th, .team-table td {
            padding: 14px 16px;
            text-align: left;
        }
        .team-table th {
            background: #f0f4f8;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        .team-table tbody tr {
            transition: background 0.2s;
        }
        .team-table tbody tr:hover {
            background: #f9fafb;
        }
        .team-table td {
            border-bottom: 1px solid #f0f0f0;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .export-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .export-btn:hover {
            background: #388e3c;
        }
        .role-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            margin-left: 8px;
            font-weight: 600;
        }
        .role-admin {
            background: #e3f2fd;
            color: #1976d2;
        }
        .role-member {
            background: #f5f5f5;
            color: #616161;
        }
    </style>
</head>

<?php include 'navbar.php'; ?>
<?php include 'toolbar.php'; ?>

<body>
    <div class="report-container">
        <div class="report-header">
            <h2>Team Report: <?php echo htmlspecialchars($team['teamname']); ?></h2>
            <button class="export-btn" onclick="exportReport()">
                <ion-icon name="download-outline"></ion-icon> Export Report
            </button>
        </div>
        
        <!-- Team Members Table -->
        <table class="team-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Role</th>
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
                    <td>
                        <span class="role-badge role-<?= strtolower($member['role']) ?>">
                            <?= htmlspecialchars($member['role']) ?>
                        </span>
                    </td>
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

        // Member Tasks Bar Graph (slim bars)
        new Chart(document.getElementById('memberTasksChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Completed',
                    data: <?= json_encode($completedData) ?>,
                    backgroundColor: '#4CAF50',
                    barPercentage: 0.4, // Slim bars
                    categoryPercentage: 0.5
                }, {
                    label: 'Pending',
                    data: <?= json_encode($pendingData) ?>,
                    backgroundColor: '#FFC107',
                    barPercentage: 0.4,
                    categoryPercentage: 0.5
                }, {
                    label: 'Overdue',
                    data: <?= json_encode($overdueData) ?>,
                    backgroundColor: '#F44336',
                    barPercentage: 0.4,
                    categoryPercentage: 0.5
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
                    x: { 
                        stacked: true,
                        grid: { display: false }
                    },
                    y: { 
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: '#eee' }
                    }
                }
            }
        });

        function exportReport() {
            // Create a table element for export
            const table = document.querySelector('.team-table').cloneNode(true);
            
            // Create CSV content
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (const row of rows) {
                const cells = row.querySelectorAll('th, td');
                const rowData = Array.from(cells).map(cell => {
                    // Remove role badge HTML and get just the text
                    if (cell.querySelector('.role-badge')) {
                        return cell.querySelector('.role-badge').textContent.trim();
                    }
                    return cell.textContent.trim();
                });
                csv.push(rowData.join(','));
            }
            
            // Create and download CSV file
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', 'team_report_<?= $team['teamname'] ?>_<?= date('Y-m-d') ?>.csv');
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

    <script src="https://unpkg.com/ionicons@5.1.2/dist/ionicons.js"></script>
    <script src="js/dash.js"></script>
</body>
</html>