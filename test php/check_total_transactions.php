<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT COUNT(*) as count FROM transactions");
echo 'Total transactions: ' . $result->fetch_assoc()['count'];

$conn->close();
?>
