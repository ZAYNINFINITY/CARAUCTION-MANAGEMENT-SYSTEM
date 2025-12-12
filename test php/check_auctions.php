<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT COUNT(*) as count FROM auction");
$row = $result->fetch_assoc();
echo "Total auctions: " . $row['count'] . PHP_EOL;

$result = $conn->query("SELECT COUNT(*) as count FROM auction WHERE status='active'");
$row = $result->fetch_assoc();
echo "Active auctions: " . $row['count'] . PHP_EOL;

$result = $conn->query("SELECT COUNT(*) as count FROM auction WHERE status='closed'");
$row = $result->fetch_assoc();
echo "Closed auctions: " . $row['count'] . PHP_EOL;

$result = $conn->query("SELECT * FROM auction LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "Auction ID: " . $row['auction_id'] . ", Status: " . $row['status'] . ", End Date: " . $row['end_date'] . PHP_EOL;
}

$conn->close();
?>
