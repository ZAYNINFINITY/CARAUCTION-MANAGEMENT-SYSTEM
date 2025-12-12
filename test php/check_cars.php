<?php
require_once 'config/db.php';

$result = $conn->query('SELECT c.car_id, c.make, c.model, a.status, a.end_date > NOW() as is_active, COUNT(b.bid_id) as bid_count FROM car c LEFT JOIN auction a ON c.car_id = a.car_id LEFT JOIN bid b ON a.auction_id = b.auction_id GROUP BY c.car_id, a.auction_id ORDER BY c.car_id DESC');

echo "Car Status Check:\n";
echo "=================\n\n";

while ($row = $result->fetch_assoc()) {
    $status = $row['status'] ?? 'No Auction';
    $active = $row['is_active'] ? 'Yes' : 'No';
    $bids = $row['bid_count'];

    echo "Car ID: {$row['car_id']} - {$row['make']} {$row['model']}\n";
    echo "  Status: $status\n";
    echo "  Active Auction: $active\n";
    echo "  Total Bids: $bids\n";
    echo "  Can Delete: " . (($status !== 'active' || !$row['is_active']) && $bids == 0 ? 'YES' : 'NO') . "\n";
    echo "---\n";
}
?>
