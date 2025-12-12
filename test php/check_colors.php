<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT COUNT(*) as count FROM colors");
$row = $result->fetch_assoc();
echo "Total colors: " . $row['count'] . PHP_EOL;

$result = $conn->query("SELECT * FROM colors LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "Color ID: " . $row['color_id'] . ", Name: " . $row['color_name'] . PHP_EOL;
}

$conn->close();
?>
