<?php
require 'config/db.php';

$result = $conn->query("SELECT COUNT(*) as total FROM auction");
$totalAuctions = $result->fetch_assoc()['total'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as active FROM auction WHERE status='active' AND end_date > NOW()");
$activeAuctions = $result->fetch_assoc()['active'] ?? 0;

echo "Total auctions: $totalAuctions\n";
echo "Active auctions: $activeAuctions\n";

$conn->close();
?>
