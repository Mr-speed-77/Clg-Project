<?php
require 'vendor/autoload.php'; // Ensure the Razorpay SDK is loaded

use Razorpay\Api\Api;

$api_key = 'rzp_test_1f5XNZHBNXVv9E';
$api_secret = 'xUQ6HMOvg1rgvZqohn9umxqV';

$api = new Api($api_key, $api_secret);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_amount = $_POST['amount']; // Assuming the amount is already in the smallest currency unit (paise)
    $receipt = 'receipt_' . uniqid();

    try {
        $order = $api->order->create([
            'receipt' => $receipt,
            'amount' => $order_amount,
            'currency' => 'INR'
        ]);

        echo json_encode(['id' => $order['id'], 'amount' => $order_amount]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
