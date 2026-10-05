<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    header('Location: my_orders.php');
    exit;
}


/* Get the order and make sure it belongs to this customer */
$stmt = $pdo->prepare(
    "SELECT
        o.order_id,
        o.status,
        p.name AS product_name,
        oi.product_id,
        oi.quantity
     FROM orders o
     JOIN order_items oi
        ON o.order_id = oi.order_id
     JOIN products p
        ON oi.product_id = p.product_id
     WHERE o.order_id = ?
       AND o.user_id = ?"
);

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);

$order = $stmt->fetch();

if (!$order) {
    header('Location: my_orders.php');
    exit;
}


/* Only pending orders can be cancelled */
if ($order['status'] !== 'pending') {
    header('Location: my_orders.php');
    exit;
}


/* Confirm cancellation */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $pdo->beginTransaction();


        /* Change order status */

        $updateOrder = $pdo->prepare(
            "UPDATE orders
             SET status = 'cancelled'
             WHERE order_id = ?
               AND user_id = ?
               AND status = 'pending'"
        );

        $updateOrder->execute([
            $orderId,
            $_SESSION['user_id']
        ]);


        /* Restore product stock */

        $restoreStock = $pdo->prepare(
            "UPDATE products
             SET stock_qty = stock_qty + ?
             WHERE product_id = ?"
        );

        $restoreStock->execute([
            $order['quantity'],
            $order['product_id']
        ]);


        $pdo->commit();

        header('Location: my_orders.php?cancelled=1');
        exit;


    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $message = 'Unable to cancel this order.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cancel Order | ShopSmart</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f6f8;
    color: #222;
}

main {
    max-width: 600px;
    margin: 70px auto;
    padding: 20px;
}

.box {
    background: white;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.warning {
    background: #fff3f3;
    border: 1px solid #d99;
    padding: 15px;
    border-radius: 5px;
}

button {
    margin-top: 20px;
    padding: 11px 18px;
    background: #a00000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.back {
    margin-left: 15px;
    color: #222;
}

</style>

</head>

<body>

<main>

<div class="box">

    <h1>Cancel Order</h1>

    <div class="warning">

        <p>
            Are you sure you want to cancel this order?
        </p>

        <p>
            <strong>Order:</strong>
            #<?= (int)$order['order_id'] ?>
        </p>

        <p>
            <strong>Product:</strong>
            <?= htmlspecialchars($order['product_name']) ?>
        </p>

        <p>
            <strong>Quantity:</strong>
            <?= (int)$order['quantity'] ?>
        </p>

    </div>


    <?php if (!empty($message)): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <button type="submit">
            Confirm Cancellation
        </button>

        <a href="my_orders.php" class="back">
            Keep Order
        </a>

    </form>

</div>

</main>

</body>
</html>