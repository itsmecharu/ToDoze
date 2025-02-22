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
    projectdate DATE,
    projectstatus VARCHAR(30) NOT NULL,
    projectcreated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
   // echo "Table 'projects' created successfully.<br>";
} else {
    echo "Error creating 'projects' table: " . mysqli_error($conn) . "<br>";
}

// Create 'users' table after 'projects' table
$sql = "CREATE TABLE IF NOT EXISTS users (
    userid INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(30) NOT NULL,
    useremail VARCHAR(50) NOT NULL UNIQUE,                    
    userpassword VARCHAR(255) NOT NULL,
    otp VARCHAR(10),
    otp_expiry DATETIME,
    is_verified BOOLEAN DEFAULT 0,
    projectid INT NULL,  -- Links the user to a project
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- When the user joined the project
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projectid) REFERENCES projects(projectid) ON DELETE SET NULL
)";
if (mysqli_query($conn, $sql)) {
    // echo "Table 'users' created successfully.";
} else {
    echo "Error creating 'users' table: " . mysqli_error($conn);
}

// Create admin table
$sql = "CREATE TABLE IF NOT EXISTS admin(
    admin_userid INT PRIMARY KEY AUTO_INCREMENT,
    admin_useremail VARCHAR(30) NOT NULL,
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
$sql = "INSERT IGNORE INTO admin (admin_userid, admin_useremail, admin_userpassword) VALUES (1, 'admin123@gmail.com', ?)";
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
    taskdate DATE,
    taskreminder DATETIME,
    taskstatus VARCHAR(30) NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0,       -- 0 = active, 1 = deleted
    deleted_at DATETIME NULL,              -- When the task was marked as deleted
    taskcreated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    review TEXT NOT NULL,
    rating INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userid) REFERENCES users(userid) ON DELETE CASCADE
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
//     echo "Error creating 'reviews' table: " . mysqli_error($conn) . "<br>";
// }