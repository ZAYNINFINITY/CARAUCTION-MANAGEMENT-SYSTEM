<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("DESCRIBE transactions");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
}

$conn->close();
?>
