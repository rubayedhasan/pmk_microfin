<?php

$host = 'localhost';
$user = 'root';
$password = '';
$db_name = "pmk_microfin";


$conn = new mysqli($host, $user, $password, $db_name);

// validation:: check the connection 
if ($conn->connect_error) {
    echo "Failed to connect to the database" . $conn->connect_error;
    exit();
}

// Change character set to  "utf8mb4"
$conn->set_charset("utf8mb4");
