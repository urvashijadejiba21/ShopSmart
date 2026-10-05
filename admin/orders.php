<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$message = '';

/* UPDATE ORDER STATUS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    $allowedStatuses = [
        'pending',
        'paid',
        'shipped',
        'delivered',
        'cancelled'
    ];

    if ($orderId > 0 && in_array($newStatus, $allowedStatuses, true)) {

        // Get old status first
        $oldStmt = $pdo->prepare(
            "SELECT status
             FROM orders
             WHERE order_id = ?"
        );

        $oldStmt->execute([$orderId]);
        $oldStatus = $oldStmt->fetchColumn();

        if ($oldStatus !== false) {

            $updateStmt = $pdo->prepare(
                "UPDATE orders
                 SET status = ?
                 WHERE order_id = ?"
            );

            $updateStmt->execute([
                $newStatus,
                $orderId
            ]);

            // Audit the status change
            $auditStmt = $pdo->prepare(
                "INSERT INTO audit_logs
                (user_id, action, entity_type, entity_id, old_value, new_value)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $auditStmt->execute([
                $_SESSION['user_id'],
                'UPDATE',
                'order',
                $orderId,
                'Status: ' . $oldStatus,
                'Status: ' . $newStatus
            ]);

            $message = 'Order status updated successfully.';
        }
    }
}


/* GET ORDERS */
$stmt = $pdo->query(
    "SELECT
        o.order_id,
        o.order_date,
        o.status,
        o.shipping_address,
        u.full_name,
        u.email,
        COUNT(oi.order_item_id) AS line_count,
        COALESCE(
            SUM(oi.quantity * oi.unit_price),
            0
        ) AS order_total
     FROM orders o
     JOIN users u
        ON o.user_id = u.user_id
     LEFT JOIN order_items oi
        ON o.order_id = oi.order_id
     GROUP BY
        o.order_id,
        o.order_date,
        o.status,
        o.shipping_address,
        u.full_name,
        u.email
     ORDER BY o.order_date DESC"
);

$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Orders | ShopSmart</title>

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
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h1 {
    margin: 0;
}

nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
}

main {
    width: 94%;
    max-width: 1300px;
    margin: 40px auto;
}

.message {
    background: #e8f5e9;
    border: 1px solid #9ccc9c;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.table-wrapper {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 13px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    vertical-align: top;
}

th {
    background: #eee;
}

select {
    padding: 8px;
    border: 1px solid #bbb;
    border-radius: 4px;
}

button {
    padding: 8px 12px;
    background: #222;
    color: white;
    border: 0;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 5px;
}

.empty {
    padding: 30px;
    text-align: center;
}

.status {
    font-weight: bold;
    text-transform: capitalize;
}

</style>

</head>

<body>

<header>

<h1>ShopSmart Orders</h1>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="audit_logs.php">Audit Log</a>
    <a href="../logout.php">Logout</a>
</nav>

</header>

<main>

<h2>Order Management</h2>

<p>
    Review customer orders and update their current status.
</p>

<?php if ($message): ?>

    <div class="message">
        <?= htmlspecialchars($message) ?>
    </div>

<?php endif; ?>


<div class="table-wrapper">

<table>

<thead>

<tr>
    <th>Order</th>
    <th>Customer</th>
    <th>Date</th>
    <th>Address</th>
    <th>Items</th>
    <th>Total</th>
    <th>Status</th>
    <th>Update</th>
    <th>Delete</th>
</tr>

</thead>

<tbody>

<?php if ($orders): ?>

    <?php foreach ($orders as $order): ?>

        <tr>

            <td>
                #<?= (int)$order['order_id'] ?>
            </td>

            <td>
                <?= htmlspecialchars($order['full_name']) ?>
                <br>

                <small>
                    <?= htmlspecialchars($order['email']) ?>
                </small>
            </td>

            <td>
                <?= htmlspecialchars($order['order_date']) ?>
            </td>

            <td>
                <?= htmlspecialchars($order['shipping_address']) ?>
            </td>

            <td>
                <?= (int)$order['line_count'] ?>
            </td>

            <td>
                $<?= number_format(
                    (float)$order['order_total'],
                    2
                ) ?>
            </td>

            <td class="status">
                <?= htmlspecialchars($order['status']) ?>
            </td>

            <td>

                <form method="POST">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= (int)$order['order_id'] ?>"
                    >

                    <select name="status">

                        <?php
                        $statuses = [
                            'pending',
                            'paid',
                            'shipped',
                            'delivered',
                            'cancelled'
                        ];
                        ?>

                        <?php foreach ($statuses as $status): ?>

                            <option
                                value="<?= $status ?>"
                                <?= $order['status'] === $status
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= ucfirst($status) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <br>

                    <button type="submit">
                        Update
                    </button>

                </form>

            </td>
            <td>

    <a
        href="order_delete.php?id=<?= (int)$order['order_id'] ?>"
    >
        Delete
    </a>

</td>

        </tr>

    <?php endforeach; ?>

<?php else: ?>

    <tr>
        <td colspan="9" class="empty">
            No customer orders yet.
        </td>
    </tr>

<?php endif; ?>

</tbody>

</table>

</div>

</main>

</body>
</html>