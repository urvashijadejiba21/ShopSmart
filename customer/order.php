<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$productId = (int)($_GET['id'] ?? 0);
$message = '';

$stmt = $pdo->prepare(
    "SELECT product_id, name, price, stock_qty
     FROM products
     WHERE product_id = ? AND is_active = 1"
);

$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    die('Product not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $quantity = (int)($_POST['quantity'] ?? 1);
    $address = trim($_POST['shipping_address'] ?? '');

    if ($quantity < 1) {
        $message = 'Quantity must be at least 1.';
    } elseif ($quantity > $product['stock_qty']) {
        $message = 'Not enough stock available.';
    } elseif ($address === '') {
        $message = 'Shipping address is required.';
    } else {

        try {

            $pdo->beginTransaction();

            $orderStmt = $pdo->prepare(
                "INSERT INTO orders
                (user_id, shipping_address, status)
                VALUES (?, ?, 'pending')"
            );

            $orderStmt->execute([
                $_SESSION['user_id'],
                $address
            ]);

            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                "INSERT INTO order_items
                (order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)"
            );

            $itemStmt->execute([
                $orderId,
                $productId,
                $quantity,
                $product['price']
            ]);

            $stockStmt = $pdo->prepare(
                "UPDATE products
                 SET stock_qty = stock_qty - ?
                 WHERE product_id = ?"
            );

            $stockStmt->execute([
                $quantity,
                $productId
            ]);

            $pdo->commit();

            header(
                'Location: order_success.php?id='
                . $orderId
            );

            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = 'Unable to place order.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Place Order | ShopSmart</title>

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
    background: #222;
    color: white;
    padding: 18px 6%;
}

header a {
    color: white;
    text-decoration: none;
}

main {
    max-width: 650px;
    margin: 50px auto;
    padding: 0 20px;
}

.order-box {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
}

label {
    display: block;
    margin-top: 18px;
    margin-bottom: 7px;
    font-weight: bold;
}

input,
textarea {
    width: 100%;
    padding: 11px;
    border: 1px solid #bbb;
    border-radius: 5px;
}

textarea {
    min-height: 100px;
}

button {
    margin-top: 22px;
    padding: 12px 20px;
    border: 0;
    background: #222;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

.error {
    color: #a00000;
}

</style>

</head>

<body>

<header>
    <a href="catalogue.php">← Back to Catalogue</a>
</header>

<main>

<div class="order-box">

    <h1>Place Order</h1>

    <h2>
        <?= htmlspecialchars($product['name']) ?>
    </h2>

    <p>
        Price:
        $<?= number_format($product['price'], 2) ?>
    </p>

    <p>
        Available stock:
        <?= (int)$product['stock_qty'] ?>
    </p>

    <?php if ($message): ?>

        <p class="error">
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label for="quantity">
            Quantity
        </label>

        <input
            type="number"
            id="quantity"
            name="quantity"
            value="1"
            min="1"
            max="<?= (int)$product['stock_qty'] ?>"
            required
        >

        <label for="shipping_address">
            Shipping Address
        </label>

        <textarea
            id="shipping_address"
            name="shipping_address"
            required
        ></textarea>

        <button type="submit">
            Place Order
        </button>

    </form>

</div>

</main>

</body>
</html>