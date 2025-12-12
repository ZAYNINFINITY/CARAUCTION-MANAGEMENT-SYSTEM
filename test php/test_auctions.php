



<?php
require 'config/db.php';

$sql = "SELECT a.auction_id, c.make, c.model, c.year, c.base_price, c.mileage, col.color_name,
               COALESCE((SELECT MAX(b2.amount) FROM bid b2 WHERE b2.auction_id = a.auction_id), c.base_price) AS current_price,
               (SELECT COUNT(*) FROM bid b2 WHERE b2.auction_id = a.auction_id) AS total_bids,
               TIMESTAMPDIFF(SECOND, NOW(), a.end_date) AS time_remaining_seconds
        FROM auction a
        JOIN car c ON a.car_id = c.car_id
        JOIN colors col ON c.color_id = col.color_id
        WHERE a.status='active' AND a.end_date > NOW()";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$auctions = $result->fetch_all(MYSQLI_ASSOC);

// Calculate time_remaining object
foreach ($auctions as &$auction) {
    $seconds = intval($auction['time_remaining_seconds']);
    $auction['time_remaining'] = [
        'total_seconds' => $seconds,
        'days' => floor($seconds / 86400),
        'hours' => floor(($seconds % 86400) / 3600),
        'minutes' => floor(($seconds % 3600) / 60)
    ];
    unset($auction['time_remaining_seconds']);
}

echo json_encode(['success' => true, 'auctions' => $auctions]);

$conn->close();
?>
