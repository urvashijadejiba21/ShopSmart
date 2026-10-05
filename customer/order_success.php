<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$orderId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT order_id, order_date, status
     FROM orders
     WHERE order_id = ? AND user_id = ?"
);

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);

$order = $stmt->fetch();

if (!$order) {
    die('Order not found.');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Order Confirmed | ShopSmart</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f6f8;
}

.box {
    max-width: 600px;
    margin: 80px auto;
    background: white;
    padding: 40px;
    border: 1px solid #ddd;
    border-radius: 8px;
    text-align: center;
}

a {
    display: inline-block;
    margin-top: 20px;
    background: #222;
    color: white;
    padding: 12px 18px;
    text-decoration: none;
    border-radius: 5px;
}

</style>

</head>

<body>

<div class="box">

    <h1>Order Confirmed</h1>

    <p>
        Your ShopSmart order has been created successfully.
    </p>

    <p>
        <strong>Order ID:</strong>
        #<?= (int)$order['order_id'] ?>
    </p>

    <p>
        <strong>Status:</strong>
        <?= htmlspecialchars(ucfirst($order['status'])) ?>
    </p>

    <a href="catalogue.php">
        Continue Shopping
    </a>

</div>

</body>
</html>