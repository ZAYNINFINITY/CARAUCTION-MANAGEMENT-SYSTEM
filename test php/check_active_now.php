<?php
require_once __DIR__ . '/../config/db.php';

$result = $conn->query("SELECT COUNT(*) as count FROM auction WHERE status='active' AND end_date > NOW()");
$activeAuctions = $result->fetch_assoc()['count'] ?? 0;

echo "Active auctions: $activeAuctions\n";

$conn->close();
?>
