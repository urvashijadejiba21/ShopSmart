<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    header('Location: orders.php');
    exit;
}

/* Get order */
$stmt = $pdo->prepare(
    "SELECT
        o.order_id,
        o.status,
        o.order_date,
        u.full_name
     FROM orders o
     JOIN users u ON o.user_id = u.user_id
     WHERE o.order_id = ?"
);

$stmt->execute([$orderId]);

$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit;
}


/* Delete after confirmation */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $pdo->beginTransaction();

        /* Audit BEFORE deleting */
        $auditStmt = $pdo->prepare(
            "INSERT INTO audit_logs
            (user_id, action, entity_type, entity_id, old_value, new_value)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $auditStmt->execute([
            $_SESSION['user_id'],
            'DELETE',
            'order',
            $orderId,
            'Customer: ' . $order['full_name']
                . ' | Status: ' . $order['status'],
            null
        ]);

        /*
        order_items are automatically deleted because
        your database uses ON DELETE CASCADE.
        */
        $deleteStmt = $pdo->prepare(
            "DELETE FROM orders
             WHERE order_id = ?"
        );

        $deleteStmt->execute([$orderId]);

        $pdo->commit();

        header('Location: orders.php?deleted=1');
        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $message = 'The order could not be deleted.';
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

<title>Delete Order | ShopSmart</title>

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
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
}

.warning {
    background: #fff3f3;
    border: 1px solid #d99;
    padding: 15px;
    border-radius: 5px;
}

.actions {
    margin-top: 25px;
}

button {
    padding: 11px 18px;
    border: 0;
    background: #a00000;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

.cancel {
    display: inline-block;
    margin-left: 12px;
    color: #222;
}

</style>

</head>

<body>

<main>

<div class="box">

    <h1>Delete Order</h1>

    <div class="warning">

        <p>
            Are you sure you want to delete this order?
        </p>

        <p>
            <strong>Order:</strong>
            #<?= (int)$order['order_id'] ?>
        </p>

        <p>
            <strong>Customer:</strong>
            <?= htmlspecialchars($order['full_name']) ?>
        </p>

        <p>
            <strong>Status:</strong>
            <?= htmlspecialchars(ucfirst($order['status'])) ?>
        </p>

    </div>

    <?php if (!empty($message)): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>

    <div class="actions">

        <form method="POST">

            <button type="submit">
                Delete Order
            </button>

            <a
                href="orders.php"
                class="cancel"
            >
                Cancel
            </a>

        </form>

    </div>

</div>

</main>

</body>
</html>