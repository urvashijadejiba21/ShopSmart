<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->prepare(
    "SELECT
        o.order_id,
        o.order_date,
        o.status,
        o.shipping_address,
        p.name AS product_name,
        oi.quantity,
        oi.unit_price,
        (oi.quantity * oi.unit_price) AS total
     FROM orders o
     JOIN order_items oi
        ON o.order_id = oi.order_id
     JOIN products p
        ON oi.product_id = p.product_id
     WHERE o.user_id = ?
     ORDER BY o.order_date DESC"
);

$stmt->execute([$_SESSION['user_id']]);

$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Orders | ShopSmart</title>

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

header {
    background: white;
    padding: 25px 6%;
    border-bottom: 1px solid #ddd;
}

header h1 {
    margin: 0 0 20px;
    font-size: 38px;
}

nav {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

nav a {
    color: #222;
    text-decoration: none;
}

main {
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}

.order-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
}

.order-top {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.status {
    font-weight: bold;
    text-transform: capitalize;
}

.total {
    font-size: 20px;
    font-weight: bold;
}

.cancel-button {
    display: inline-block;
    margin-top: 12px;
    padding: 10px 16px;
    background: #a00000;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.empty {
    background: white;
    border: 1px solid #ddd;
    padding: 35px;
    border-radius: 8px;
    text-align: center;
}

.shop-button {
    display: inline-block;
    margin-top: 15px;
    padding: 11px 18px;
    background: #222;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

</style>

</head>

<body>

<header>

<h1>ShopSmart</h1>

<nav>
    <a href="../index.php">Home</a>
    <a href="catalogue.php">Shop</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="my_orders.php">My Orders</a>
    <a href="../logout.php">Logout</a>
</nav>

</header>

<main>

<h2>My Orders</h2>

<p>
    View your ShopSmart order history and current order status.
</p>


<?php if ($orders): ?>

    <?php foreach ($orders as $order): ?>

        <div class="order-card">

            <div class="order-top">

                <h3>
                    Order #<?= (int)$order['order_id'] ?>
                </h3>

                <p class="status">
                    <?= htmlspecialchars($order['status']) ?>
                </p>

            </div>

            <p>
                <strong>Product:</strong>
                <?= htmlspecialchars($order['product_name']) ?>
            </p>

            <p>
                <strong>Quantity:</strong>
                <?= (int)$order['quantity'] ?>
            </p>

            <p>
                <strong>Order Date:</strong>
                <?= htmlspecialchars($order['order_date']) ?>
            </p>

            <p>
                <strong>Shipping Address:</strong>
                <?= htmlspecialchars($order['shipping_address']) ?>
            </p>

            <p class="total">
                Total:
                $<?= number_format((float)$order['total'], 2) ?>
            </p>


            <?php if ($order['status'] === 'pending'): ?>

                <a
                    href="cancel_order.php?id=<?= (int)$order['order_id'] ?>"
                    class="cancel-button"
                >
                    Cancel Order
                </a>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>


<?php else: ?>

    <div class="empty">

        <h3>No orders yet</h3>

        <p>
            You haven't placed any ShopSmart orders yet.
        </p>

        <a href="catalogue.php" class="shop-button">
            Start Shopping
        </a>

    </div>

<?php endif; ?>

</main>

</body>
</html>