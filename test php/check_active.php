<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT COUNT(*) as count FROM auction WHERE status='active' AND end_date > NOW()");
$row = $result->fetch_assoc();
echo "Active auctions: " . $row['count'] . PHP_EOL;

$result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE MONTH(transaction_date) = MONTH(NOW()) AND YEAR(transaction_date) = YEAR(NOW())");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Successful sales this month: " . $row['count'] . PHP_EOL;
} else {
    echo "Error in successful sales query: " . $conn->error . PHP_EOL;
}

$result = $conn->query("SELECT COALESCE(SUM(final_price), 0) as total FROM transactions WHERE MONTH(transaction_date) = MONTH(NOW()) AND YEAR(transaction_date) = YEAR(NOW())");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total volume this month: $" . number_format($row['total']) . PHP_EOL;
} else {
    echo "Error in total volume query: " . $conn->error . PHP_EOL;
}

$conn->close();
?>
