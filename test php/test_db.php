<?php
require_once __DIR__ . '/../config/db.php';

echo "Database connection test:\n";

if ($conn->connect_error) {
    echo "Failed to connect: " . $conn->connect_error . "\n";
    exit;
} else {
    echo "Connected successfully to MySQL.\n";
}

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE 'car_auction'");
if ($result->num_rows > 0) {
    echo "Database 'car_auction' exists.\n";
} else {
    echo "Database 'car_auction' does not exist.\n";
}

// Select DB and check tables
if ($conn->select_db('car_auction')) {
    echo "Selected database 'car_auction'.\n";
    
    // List tables
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "Tables in database:\n";
        while ($row = $result->fetch_array()) {
            echo "- " . $row[0] . "\n";
        }
    } else {
        echo "Error listing tables: " . $conn->error . "\n";
    }
} else {
    echo "Failed to select database: " . $conn->error . "\n";
}

$conn->close();
?>
