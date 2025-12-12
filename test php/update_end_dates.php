<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("UPDATE auction SET end_date = DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE status='active'");
echo 'Updated rows: ' . $conn->affected_rows;

$conn->close();
?>
