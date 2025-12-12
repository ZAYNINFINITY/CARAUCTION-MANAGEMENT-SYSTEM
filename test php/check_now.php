<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT NOW() as now_time");
echo 'Current time: ' . $result->fetch_assoc()['now_time'];

$conn->close();
?>
