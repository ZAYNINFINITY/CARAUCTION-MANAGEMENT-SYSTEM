<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE MONTH(transaction_date) = MONTH(NOW()) AND YEAR(transaction_date) = YEAR(NOW())");
echo 'Transactions this month: ' . $result->fetch_assoc()['count'];

$conn->close();
?>
