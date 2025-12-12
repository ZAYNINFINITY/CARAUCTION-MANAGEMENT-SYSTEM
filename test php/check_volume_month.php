<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT SUM(final_price) as total FROM transactions WHERE MONTH(transaction_date) = MONTH(NOW()) AND YEAR(transaction_date) = YEAR(NOW())");
$row = $result->fetch_assoc();
echo 'Volume this month: $' . number_format($row['total'] ?? 0);

$conn->close();
?>
