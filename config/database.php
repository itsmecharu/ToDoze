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

// Create the 'projects' table first
$sql = "CREATE TABLE IF NOT EXISTS projects(
    projectid INT PRIMARY KEY AUTO_INCREMENT,
    projectname VARCHAR(30) NOT NULL,
    projectdescription VARCHAR(255),
    projectduedate DATETIME NULL,
    projectstatus ENUM('Pending', 'Completed') DEFAULT 'Pending',
    projectcreated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    projectstarted_at TIMESTAMP NULL DEFAULT NULL,
    projectcompleted_at TIMESTAMP NULL DEFAULT NULL,
    projectdeleted_at TIMESTAMP NULL DEFAULT Null,
    is_projectdeleted TINYINT(1) DEFAULT 0,
    is_overdue TINYINT(1) DEFAULT 0,
    p_priority ENUM('High','Medium','Low','none') DEFAULT 'none'    
)";

if (mysqli_query($conn, $sql)) {
    // echo "Table 'projects' created successfully.<br>";
} else {
    echo "Error creating 'projects' table: " . mysqli_error($conn) . "<br>";
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




//  Create 'project_members' table (Many-to-Many Relationship + Invitations)
$sql = "CREATE TABLE IF NOT EXISTS project_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    projectid INT NOT NULL,
    userid INT NOT NULL,
    role ENUM('Admin', 'Member') DEFAULT 'Member',
    status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
    invited_at TIMESTAMP NULL DEFAULT NULL,
    joinedproject_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (projectid) REFERENCES projects(projectid) ON DELETE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'users' created successfully.";
} else {
    echo "Error creating 'users' table: " . mysqli_error($conn);
}

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

// Create 'tasks' table after 'users' and 'projects'
$sql = "CREATE TABLE IF NOT EXISTS tasks(
    taskid INT PRIMARY KEY AUTO_INCREMENT,
    projectid INT NULL,
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
    FOREIGN KEY (projectid) REFERENCES projects(projectid) ON DELETE CASCADE,
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
    FOREIGN KEY (userid) REFERENCES users(userid)
)";


if (mysqli_query($conn, $sql)) {
    // echo "'reviews' table created successfully.<br>";
} else {
    echo "Error creating 'reviews' table: " . mysqli_error($conn) . "<br>";
}

// $sql="DROP DATABASE todoze";
// if (mysqli_query($conn, $sql)) {
//     // echo "'reviews' table created successfully.<br>";
// } else {
//     echo "Error creating 'reviews' table: " . mysqli_error($conn)."<br>";

// }