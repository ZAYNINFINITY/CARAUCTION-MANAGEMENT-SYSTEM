<?php
$host = 'localhost';
$user = 'root';
$password = 'ZAYN1990';
$dbname = 'car_auction';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
