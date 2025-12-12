<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add_car': addCar(); break;
    case 'delete_car': deleteCar(); break;
    case 'get_cars': getCars(); break;
    case 'auctions': getAuctions(); break;
    case 'transactions': getTransactions(); break;
    case 'stats': getStats(); break;
    case 'auction': getAuctionDetails(); break;
    case 'bids': getBids(); break;
    case 'place_bid': placeBid(); break;
    case 'complete_transaction': completeTransaction(); break;
    case 'check_higher_bids': checkHigherBids(); break;
    case 'auto_complete_transaction': autoCompleteTransaction(); break;
    case 'colors': getColors(); break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

// -------- FUNCTIONS -------- //

function addCar() {
    global $conn;
    $carName   = $_POST['carName'] ?? '';
    $year      = intval($_POST['modelYear'] ?? 0);
    $mileage   = intval($_POST['mileage'] ?? 0);
    $basePrice = floatval($_POST['basePrice'] ?? 0);
    $color_id  = intval($_POST['colorId'] ?? 0);

    if (!$carName || $year <= 0 || $mileage < 0 || $basePrice <= 0 || $color_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        return;
    }

    $parts = explode(' ', $carName, 2);
    $make = $parts[0] ?? '';
    $model = $parts[1] ?? '';
    $user_id = 11; // placeholder user

    $stmt = $conn->prepare("INSERT INTO car (user_id, color_id, make, model, year, mileage, base_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissiid", $user_id, $color_id, $make, $model, $year, $mileage, $basePrice);

    if ($stmt->execute()) {
        $car_id = $stmt->insert_id;
        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime('+7 days'));

        $stmt2 = $conn->prepare("INSERT INTO auction (car_id, start_date, end_date, status) VALUES (?, ?, ?, 'active')");
        $stmt2->bind_param("iss", $car_id, $start_date, $end_date);
        $stmt2->execute();
        $stmt2->close();

        echo json_encode(['success' => true, 'car_id' => $car_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add car']);
    }
    $stmt->close();
}

function getAuctions() {
    global $conn;
    $sql = "SELECT a.auction_id, c.make, c.model, c.year, c.base_price, c.mileage, col.color_name,
                   COALESCE((SELECT MAX(b2.amount) FROM bid b2 WHERE b2.auction_id = a.auction_id), c.base_price) AS current_price,
                   (SELECT COUNT(*) FROM bid b2 WHERE b2.auction_id = a.auction_id) AS total_bids,
                   TIMESTAMPDIFF(SECOND, NOW(), a.end_date) AS time_remaining_seconds
            FROM auction a
            JOIN car c ON a.car_id = c.car_id
        JOIN color col ON c.color_id = col.color_id
            WHERE a.status='active' AND a.end_date > NOW()";
    $result = $conn->query($sql);
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
}

function getTransactions() {
    global $conn;
    $stmt = $conn->prepare("
        SELECT t.transaction_id,
               a.auction_id,
               c.make,
               c.model,
               u.user_name AS buyer,
               u.email AS buyer_email,
               t.final_price,
               t.transaction_date,
               t.payment_method
        FROM transactions t
        JOIN auction a ON t.auction_id = a.auction_id
        JOIN car c ON a.car_id = c.car_id
        JOIN users u ON t.buyer_id = u.user_id
    ");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to load transactions: ' . $conn->error]);
        return;
    }
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to load transactions: ' . $stmt->error]);
        $stmt->close();
        return;
    }
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'transactions' => $transactions]);
    $stmt->close();
}

function getStats() {
    global $conn;

    // Active Auctions Count
    $result = $conn->query("SELECT COUNT(*) as count FROM auction WHERE status='active' AND end_date > NOW()");
    $activeAuctions = $result->fetch_assoc()['count'] ?? 0;

    // Total Volume this month
    $result = $conn->query("SELECT COALESCE(SUM(final_price), 0) as total FROM transactions WHERE MONTH(transaction_date) = MONTH(NOW()) AND YEAR(transaction_date) = YEAR(NOW())");
    $totalVolume = $result->fetch_assoc()['total'] ?? 0;
    $totalVolume = '$' . number_format($totalVolume);

    // Successful Sales this month
    $result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE MONTH(transaction_date) = MONTH(NOW()) AND YEAR(transaction_date) = YEAR(NOW())");
    $successfulSales = $result->fetch_assoc()['count'] ?? 0;

    echo json_encode([
        'success' => true,
        'activeAuctions' => $activeAuctions,
        'totalVolume' => $totalVolume,
        'successfulSales' => $successfulSales
    ]);
}

$conn->close();

function getAuctionDetails() {
    global $conn;
    $auctionId = intval($_GET['auctionId'] ?? 0);
    if ($auctionId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid auction ID']);
        return;
    }

    $stmt = $conn->prepare("
        SELECT a.auction_id, a.status, a.end_date, a.start_date,
               c.car_id, c.make, c.model, c.year, c.mileage, c.base_price,
               COALESCE(MAX(b.amount), c.base_price) AS highest_bid
        FROM auction a
        JOIN car c ON a.car_id = c.car_id
        LEFT JOIN bid b ON b.auction_id = a.auction_id
        WHERE a.auction_id = ?
        GROUP BY a.auction_id, c.car_id
    ");
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Auction not found']);
        return;
    }

    // Calculate time remaining
    $seconds = strtotime($data['end_date']) - time();
    $data['time_remaining'] = [
        'total_seconds' => max(0, $seconds),
        'days' => floor($seconds / 86400),
        'hours' => floor(($seconds % 86400) / 3600),
        'minutes' => floor(($seconds % 3600) / 60)
    ];

    echo json_encode(['success' => true, 'auction' => $data, 'car' => $data, 'highest_bid' => $data['highest_bid']]);
}

function getBids() {
    global $conn;
    $auctionId = intval($_GET['auctionId'] ?? 0);
    if ($auctionId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid auction ID']);
        return;
    }

    $stmt = $conn->prepare("
        SELECT b.bid_id, b.amount, b.created_at, u.user_name AS bidder_name
        FROM bid b
        JOIN users u ON b.buyer_id = u.user_id
        WHERE b.auction_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $bids = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['success' => true, 'bids' => $bids]);
}

function placeBid() {
    global $conn;
    $auction_id = intval($_POST['auctionId'] ?? 0);
    $bid_amount = floatval($_POST['bidAmount'] ?? 0);
    $bidder_name = $_POST['bidderName'] ?? '';
    $bidder_email = $_POST['bidderEmail'] ?? '';

    if ($auction_id <= 0 || $bid_amount <= 0 || !$bidder_name || !$bidder_email) {
        echo json_encode(['success' => false, 'error' => 'Invalid bid data']);
        return;
    }

    // Find or create user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("s", $bidder_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $buyer_id = $user['user_id'];
    } else {
        $role = 'buyer';
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (user_name, email, role) VALUES (?, ?, ?)");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
            return;
        }
        $stmt->bind_param("sss", $bidder_name, $bidder_email, $role);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => 'Failed to create user']);
            return;
        }
        $buyer_id = $stmt->insert_id;
    }
    $stmt->close();

    // Check if auction is active and not ended
    $stmt = $conn->prepare("SELECT end_date, status FROM auction WHERE auction_id = ?");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Auction not found']);
        return;
    }
    $auction = $result->fetch_assoc();
    $stmt->close();

    if ($auction['status'] !== 'active' || strtotime($auction['end_date']) < time()) {
        echo json_encode(['success' => false, 'error' => 'Auction is closed']);
        return;
    }

    // Get current highest bid or base price
    $stmt = $conn->prepare("SELECT COALESCE(MAX(b.amount), c.base_price) AS current_price FROM bid b RIGHT JOIN auction a ON b.auction_id = a.auction_id JOIN car c ON a.car_id = c.car_id WHERE a.auction_id = ? GROUP BY a.auction_id");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $current_price = floatval($row['current_price']);

    if ($bid_amount <= $current_price) {
        echo json_encode(['success' => false, 'error' => 'Bid must be higher than current price']);
        return;
    }

    // Insert bid
    $stmt = $conn->prepare("INSERT INTO bid (auction_id, buyer_id, amount) VALUES (?, ?, ?)");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("iid", $auction_id, $buyer_id, $bid_amount);
    if ($stmt->execute()) {
        // Check if highest bid
        $isHighest = $bid_amount > $current_price;
        echo json_encode(['success' => true, 'message' => 'Bid placed successfully', 'isHighest' => $isHighest]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to place bid: ' . $stmt->error]);
    }
    $stmt->close();
}

function completeTransaction() {
    global $conn;
    $auction_id      = intval($_POST['auctionId'] ?? 0);
    $buyer_email     = trim($_POST['buyerEmail'] ?? '');
    $final_price_raw = $_POST['finalPrice'] ?? '';
    $payment_method  = $_POST['paymentMethod'] ?? '';
    $card_number     = $_POST['cardNumber'] ?? '';

    // Basic validation
    if ($auction_id <= 0 || $buyer_email === '' || $final_price_raw === '' || $payment_method === '') {
        echo json_encode(['success' => false, 'error' => 'Invalid transaction data']);
        return;
    }

    // Normalize numeric inputs to floats
    $final_price = floatval($final_price_raw);
    if (!is_finite($final_price) || $final_price <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid final price']);
        return;
    }

    // Find buyer by email
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("s", $buyer_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Buyer not found']);
        $stmt->close();
        return;
    }
    $buyer = $result->fetch_assoc();
    $buyer_id = $buyer['user_id'];
    $stmt->close();

    // Validate auction is active and fetch highest bid safely
    $stmt = $conn->prepare("
        SELECT a.status,
               a.end_date,
               c.base_price,
               COALESCE(MAX(b.amount), c.base_price) AS highest_bid
        FROM auction a
        JOIN car c ON a.car_id = c.car_id
        LEFT JOIN bid b ON b.auction_id = a.auction_id
        WHERE a.auction_id = ?
        GROUP BY a.auction_id, c.base_price, a.status, a.end_date
    ");
    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Auction not found']);
        $stmt->close();
        return;
    }
    $auction = $result->fetch_assoc();
    $stmt->close();

    if ($auction['status'] !== 'active' || strtotime($auction['end_date']) < time()) {
        echo json_encode(['success' => false, 'error' => 'Auction is closed']);
        return;
    }

    // Numeric-safe comparison with tolerance for minor float differences
    $highest_bid = floatval($auction['highest_bid']);
    $tolerance = 0.01; // allow minor rounding differences (e.g., cents)
    if (abs($final_price - $highest_bid) > $tolerance) {
        echo json_encode([
            'success' => false,
            'error' => 'Final price does not match highest bid',
            'highestBid' => $highest_bid
        ]);
        return;
    }

    // Validate card if payment is Card
    if ($payment_method === 'Card' && (!preg_match('/^\d{16}$/', $card_number) || strlen($card_number) !== 16)) {
        echo json_encode(['success' => false, 'error' => 'Invalid card number']);
        return;
    }

    // Start DB transaction
    $conn->begin_transaction();

    try {
        // Mark auction as sold/completed
        $stmt = $conn->prepare("UPDATE auction SET status = 'closed' WHERE auction_id = ?");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        $stmt->close();

        // Insert transaction record
        $transaction_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("
            INSERT INTO transactions (auction_id, buyer_id, final_price, payment_method, transaction_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("iidss", $auction_id, $buyer_id, $final_price, $payment_method, $transaction_date);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert transaction: ' . $stmt->error);
        }
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaction completed successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deleteCar() {
    global $conn;
    $car_id = intval($_POST['carId'] ?? 0);

    if ($car_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid car ID']);
        return;
    }

    // Check if car has active auctions
    $stmt = $conn->prepare("SELECT COUNT(*) as active_auctions FROM auction WHERE car_id = ? AND status = 'active' AND end_date > NOW()");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['active_auctions'] > 0) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete car with active auctions']);
        return;
    }

    // Check if car has any bids
    $stmt = $conn->prepare("SELECT COUNT(*) as total_bids FROM bid b JOIN auction a ON b.auction_id = a.auction_id WHERE a.car_id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row['total_bids'] > 0) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete car with existing bids']);
        return;
    }

    // Delete car (cascading will handle related records)
    $stmt = $conn->prepare("DELETE FROM car WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Car deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete car']);
    }
    $stmt->close();
}

function getCars() {
    global $conn;
    $stmt = $conn->prepare("
        SELECT c.car_id, c.make, c.model, c.year, c.mileage, c.base_price, col.color_name,
               CASE
                   WHEN a.status = 'active' AND a.end_date > NOW() THEN 'active_auction'
                   WHEN a.auction_id IS NOT NULL THEN 'has_auction'
                   ELSE 'no_auction'
               END as auction_status,
               COALESCE(bid_counts.total_bids, 0) as total_bids
        FROM car c
        JOIN color col ON c.color_id = col.color_id
        LEFT JOIN auction a ON c.car_id = a.car_id
        LEFT JOIN (
            SELECT auction_id, COUNT(*) as total_bids
            FROM bid
            GROUP BY auction_id
        ) bid_counts ON a.auction_id = bid_counts.auction_id
        ORDER BY c.car_id DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $cars = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['success' => true, 'cars' => $cars]);
}

function checkHigherBids() {
    global $conn;
    $auction_id = intval($_GET['auctionId'] ?? 0);
    $current_bid = floatval($_GET['currentBid'] ?? 0);

    if ($auction_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid auction ID']);
        return;
    }

    // Check if there's a higher bid than the current bid
    $stmt = $conn->prepare("SELECT COUNT(*) as higher_bids FROM bid WHERE auction_id = ? AND amount > ?");
    $stmt->bind_param("id", $auction_id, $current_bid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    echo json_encode(['success' => true, 'hasHigherBid' => $row['higher_bids'] > 0]);
}

function autoCompleteTransaction() {
    global $conn;
    $auction_id = intval($_POST['auctionId'] ?? 0);
    $buyer_email = $_POST['buyerEmail'] ?? '';
    $final_price = floatval($_POST['finalPrice'] ?? 0);
    $payment_method = $_POST['paymentMethod'] ?? '';
    $card_number = $_POST['cardNumber'] ?? '';

    if ($auction_id <= 0 || !$buyer_email || $final_price <= 0 || !$payment_method) {
        echo json_encode(['success' => false, 'error' => 'Invalid transaction data']);
        return;
    }

    // Find buyer
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $buyer_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Buyer not found']);
        $stmt->close();
        return;
    }
    $buyer = $result->fetch_assoc();
    $buyer_id = $buyer['user_id'];
    $stmt->close();

    // Validate auction is still active and get highest bid
    $stmt = $conn->prepare("SELECT a.status, a.end_date, c.base_price, COALESCE(MAX(b.amount), c.base_price) AS highest_bid FROM auction a JOIN car c ON a.car_id = c.car_id LEFT JOIN bid b ON b.auction_id = a.auction_id WHERE a.auction_id = ? GROUP BY a.auction_id");
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Auction not found']);
        $stmt->close();
        return;
    }
    $auction = $result->fetch_assoc();
    $stmt->close();

    if ($auction['status'] !== 'active' || strtotime($auction['end_date']) < time()) {
        echo json_encode(['success' => false, 'error' => 'Auction is closed']);
        return;
    }

    $highest_bid = floatval($auction['highest_bid']);
    if ($final_price != $highest_bid) {
        echo json_encode(['success' => false, 'error' => 'Final price does not match highest bid']);
        return;
    }

    // Validate card if payment is Card
    if ($payment_method === 'Card' && (!preg_match('/^\d{16}$/', $card_number) || strlen($card_number) !== 16)) {
        echo json_encode(['success' => false, 'error' => 'Invalid card number']);
        return;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update auction status to closed
        $stmt = $conn->prepare("UPDATE auction SET status = 'closed' WHERE auction_id = ?");
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        $stmt->close();

        // Insert transaction
        $transaction_date = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO transactions (auction_id, buyer_id, final_price, payment_method, transaction_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iidss", $auction_id, $buyer_id, $final_price, $payment_method, $transaction_date);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert transaction');
        }
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaction completed successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getColors() {
    global $conn;
    $stmt = $conn->prepare("SELECT color_id, color_name FROM color ORDER BY color_name");
    $stmt->execute();
    $result = $stmt->get_result();
    $colors = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode(['success' => true, 'colors' => $colors]);
}
