<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT auction_id, status, end_date FROM auction WHERE status='active' AND end_date < NOW()");
while ($row = $result->fetch_assoc()) {
    echo 'ID: ' . $row['auction_id'] . ', Status: ' . $row['status'] . ', End Date: ' . $row['end_date'] . PHP_EOL;
}

$conn->close();
?>
