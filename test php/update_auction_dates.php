<?php
require_once __DIR__ . '/../config/db.php';

$sql = "UPDATE auction SET end_date = DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE status = 'active'";
if ($conn->query($sql) === TRUE) {
    echo "Auction end dates updated successfully.";
} else {
    echo "Error updating dates: " . $conn->error;
}

$conn->close();
?>
