<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT transaction_date FROM transactions");
while ($row = $result->fetch_assoc()) {
    echo 'Transaction date: ' . $row['transaction_date'] . PHP_EOL;
}

$conn->close();
?>
