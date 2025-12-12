<?php
// Test script for API endpoints
require_once 'config/db.php';

// Test check_higher_bids endpoint
echo "Testing check_higher_bids endpoint:\n";
$_GET['action'] = 'check_higher_bids';
$_GET['auctionId'] = 1;
$_GET['currentBid'] = 1000;
include 'api/api.php';
echo "\n\n";

// Test auto_complete_transaction endpoint
echo "Testing auto_complete_transaction endpoint:\n";
$_POST['action'] = 'auto_complete_transaction';
$_POST['auctionId'] = 1;
$_POST['buyerEmail'] = 'test@example.com';
$_POST['finalPrice'] = 1500;
$_POST['paymentMethod'] = 'Card';
$_POST['cardNumber'] = '1234567890123456';
include 'api/api.php';
echo "\n\n";

echo "API testing completed.\n";
?>
