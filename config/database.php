<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todoze";

// Create connection
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    // echo "Database created successfully.";
} else {
    die("ERROR: Could not create database. " . mysqli_error($conn));
}

// Connect to the newly created database
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if ($conn === false) {
    die("ERROR: Could not connect to the database. " . mysqli_connect_error());
}

// Create the 'teams' table first
$sql = "CREATE TABLE IF NOT EXISTS teams(
    teamid INT PRIMARY KEY AUTO_INCREMENT,
    teamname VARCHAR(30) NOT NULL,
    teamdescription VARCHAR(255),
    teamstatus ENUM('Active', 'Inactive') DEFAULT 'Active',
    teamcreated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    teamdeleted_at TIMESTAMP NULL DEFAULT Null,
    is_teamdeleted TINYINT(1) DEFAULT 0,
    p_priority ENUM('High','Medium','Low','none') DEFAULT 'none'    
)";

if (mysqli_query($conn, $sql)) {
    // echo "Table 'teams' created successfully.<br>";
} else {
    echo "Error creating 'teams' table: " . mysqli_error($conn) . "<br>";
}

// this is users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    userid INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(30) NOT NULL,
    useremail VARCHAR(50) NOT NULL UNIQUE,                    
    userpassword VARCHAR(255) NOT NULL,
    otp VARCHAR(10),
    otp_expiry DATETIME,
    is_verified BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    userstatus ENUM('Active', 'Inactive') DEFAULT 'Active'
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'users' created successfully.";
} else {
    echo "Error creating 'users' table: " . mysqli_error($conn);
}




//  Create 'team_members' table (Many-to-Many Relationship + Invitations)
$sql = "CREATE TABLE IF NOT EXISTS team_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teamid INT NOT NULL,
    userid INT NOT NULL,
    role ENUM('Admin', 'Member') DEFAULT 'Member',
    status ENUM('Pending', 'Accepted', 'Rejected', 'Removed') DEFAULT 'Pending',
    invited_at TIMESTAMP NULL DEFAULT NULL,
    joinedteam_at TIMESTAMP NULL DEFAULT NULL,
    removed_at TIMESTAMP NULL DEFAULT NULL,
    has_exited TINYINT(1) DEFAULT 0,
    exited_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (teamid) REFERENCES teams(teamid) ON DELETE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE,
    is_hidden TINYINT(1) DEFAULT 0
   
)";



if (mysqli_query($conn, $sql)) {
    // echo "Table 'users' created successfully.";
} else {
    echo "Error creating 'team members ' table: " . mysqli_error($conn);
}

// $sql_alter = "ALTER TABLE team_members ADD COLUMN exited_at TIMESTAMP NULL DEFAULT NULL ";
//     if (!mysqli_query($conn, $sql_alter)) {
//         echo "Error altering 'team_members' table: " . mysqli_error($conn);
//     }
// Create admin table
$sql = "CREATE TABLE IF NOT EXISTS admin(
    admin_userid INT PRIMARY KEY NOT NULL,
    admin_useremail VARCHAR(50) NOT NULL,
    admin_userpassword VARCHAR(255) NOT NULL  
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'admin' created successfully.";
} else {
    echo "Error creating 'admin' table: " . mysqli_error($conn);
}

// Hash the admin password before inserting
$hashedadmin_userpassword = password_hash('admin123', PASSWORD_DEFAULT);

// Insert default admin user
$sql = "INSERT IGNORE INTO admin ( admin_userid,admin_useremail, admin_userpassword) VALUES (1,'todoze9@gmail.com', ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $hashedadmin_userpassword);
if (mysqli_stmt_execute($stmt)) {
    // echo "Admin user inserted successfully.";
} else {
    echo "Error inserting admin user: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);

// Create 'tasks' table after 'users' and 'teams'
$sql = "CREATE TABLE IF NOT EXISTS tasks(
    taskid INT PRIMARY KEY AUTO_INCREMENT,
    teamid INT NULL,
    userid INT NOT NULL,
    assigned_to INT NULL,
    taskname VARCHAR(255) NOT NULL,
    taskdescription VARCHAR(255),
    taskdate DATE NULL,
    tasktime TIME NULL,
    reminder_percentage INT NULL, 
    reminder_sent TINYINT(1) DEFAULT 0,
    reminder_repeat ENUM('none', 'daily', 'weekly', 'monthly') DEFAULT 'none',
    last_reminder_sent DATETIME NULL,
    taskstatus ENUM('Pending','Completed') DEFAULT 'Pending',
    is_deleted TINYINT(1) DEFAULT 0,       -- 0 = active, 1 = deleted
    deleted_at DATETIME NULL,              -- When the task was marked as deleted
    taskcreated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_overdue TINYINT(1) DEFAULT 0,
    completed_at DATETIME NULL,
    taskpriority ENUM('High','Medium','Low','none') DEFAULT 'none',
    FOREIGN KEY (teamid) REFERENCES teams(teamid) ON DELETE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(userid) ON DELETE SET NULL
)";






if (mysqli_query($conn, $sql)) {
    // echo "Table 'tasks' created successfully.<br>";
} else {
    echo "Error creating 'tasks' table: " . mysqli_error($conn) . "<br>";
}

// Create 'reviews' table
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    reviewid INT PRIMARY KEY AUTO_INCREMENT,
    userid INT NOT NULL,
    review TEXT NULL,
    rating INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userid) REFERENCES users(userid)
)";
// $sql = "ALTER TABLE reviews ADD COLUMN  last_updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP";

if (mysqli_query($conn, $sql)) {
    // echo "'reviews' table created successfully.<br>";
} else {
    echo "Error creating 'reviews' table: " . mysqli_error($conn) . "<br>";
}

// Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    notificationid INT PRIMARY KEY AUTO_INCREMENT,
    userid INT NOT NULL,
    teamid INT,
    taskid INT,
    message TEXT NOT NULL,
    type ENUM('task_assignment', 'invitation', 'removal') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE,
    FOREIGN KEY (teamid) REFERENCES teams(teamid) ON DELETE CASCADE,
    FOREIGN KEY (taskid) REFERENCES tasks(taskid) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating notifications table: " . mysqli_error($conn));
}

// $sql="DROP DATABASE todoze";
// if (mysqli_query($conn, $sql)) {
//     // echo "'reviews' table created successfully.<br>";
// } else {
//     echo "Error creating 'reviews' table: " . mysqli_error($conn)."<br>";

// }