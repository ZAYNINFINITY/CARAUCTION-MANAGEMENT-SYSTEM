<?php
require 'config/db.php';

$result = $conn->query('SHOW TABLES');
while ($row = $result->fetch_row()) {
    echo $row[0] . PHP_EOL;
}

$conn->close();
?>
