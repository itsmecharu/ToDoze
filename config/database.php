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

$sql = "CREATE TABLE IF NOT EXISTS users(
    userid INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(30) NOT NULL,
    useremail VARCHAR(50) NOT NULL,                    
    userpassword VARCHAR(255) NOT NULL
)";

if (mysqli_query($conn, $sql)) {
    // echo "Table 'users' created successfully.";
} else {
    echo "Error creating 'users' table: " . mysqli_error($conn);
}

// Create table for Admin
$sql = "CREATE TABLE IF NOT EXISTS admin(
    adminid INT PRIMARY KEY AUTO_INCREMENT,
    adminusername VARCHAR(30) NOT NULL,
    adminpassword VARCHAR(255) NOT NULL
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'sadmin' created successfully.";
} else {
    echo "Error creating 'sadmin' table: " . mysqli_error($conn);
}

// Insert default admin user (INSERT IGNORE ensures no duplicate entry if it already exists)
$sql = "INSERT IGNORE INTO admin (adminid, adminusername, adminpassword) VALUES (1,'admin123', 'admin123')";
if (mysqli_query($conn, $sql)) {
    // echo "Admin user inserted successfully.";
} else {
    echo "Error inserting admin user: " . mysqli_error($conn);
}
//table for tasks
$sql = "CREATE TABLE IF NOT EXISTS tasks(
    taskid INT PRIMARY KEY AUTO_INCREMENT,
    taskname VARCHAR(30) NOT NULL,
    taskdescription VARCHAR(255),
    taskdate DATE,
    taskreminder DATETIME,
    taskstatus VARCHAR(30) NOT NULL,
    taskcreated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
    //echo "Table 'project_tasks' created successfully.<br>";
} else {
    echo "Error creating 'tasks' table: " . mysqli_error($conn) . "<br>";
}
//table for project
$sql = "CREATE TABLE IF NOT EXISTS projects(
    projectid INT PRIMARY KEY AUTO_INCREMENT,
    projectname VARCHAR(30) NOT NULL,
    projectdescription VARCHAR(255),
    projectdate DATE,
    projectstatus VARCHAR(30) NOT NULL,
    projectcreated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
   // echo "Table 'project_tasks' created successfully.<br>";
} else {
    echo "Error creating 'projects' table: " . mysqli_error($conn) . "<br>";
}

// Create table for project members (Linking existing users to projects)
$sql = "CREATE TABLE IF NOT EXISTS project_members(
    memberid INT PRIMARY KEY AUTO_INCREMENT,
    projectid INT,
    userid INT,
    -- role VARCHAR(50) DEFAULT 'member',  -- Role of the user (e.g., member, admin)
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projectid) REFERENCES projects(projectid) ON DELETE CASCADE,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE
)";
if (mysqli_query($conn, $sql)) {
    //echo "Table 'project_members' created successfully.<br>";
} else {
    echo "Error creating 'project_members' table: " . mysqli_error($conn) . "<br>";
}

// Create table for project tasks (Tasks within projects)
$sql = "CREATE TABLE IF NOT EXISTS project_tasks(
    project_taskid INT PRIMARY KEY AUTO_INCREMENT,
    projectid INT,
    p_taskname VARCHAR(255) NOT NULL,
    p_taskdescription VARCHAR(255),
    p_taskdate DATE,                   -- Due date of the task
    p_taskreminder DATETIME,               -- Reminder date and time
    
    -- p_taskcompleted TINYINT(1) DEFAULT 0,  -- 0 = Not Completed, 1 = Completed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projectid) REFERENCES projects(projectid) ON DELETE CASCADE
)";
if (mysqli_query($conn, $sql)) {
    //echo "Table 'project_tasks' created successfully.<br>";
} else {
    echo "Error creating 'project_tasks' table: " . mysqli_error($conn) . "<br>";
}

