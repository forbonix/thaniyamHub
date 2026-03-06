<?php
// backend/db.config.php
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password is empty
$dbname = "thaniyamhub";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

// Select DB
$conn->select_db($dbname);

// Set charset to UTF8
$conn->set_charset("utf8mb4");
?>
