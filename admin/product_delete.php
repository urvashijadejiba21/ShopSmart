<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$productId = (int) ($_GET['id'] ?? 0);

if ($productId <= 0) {
    header('Location: products.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT product_id, name, sku
     FROM products
     WHERE product_id = ?"
);

$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['confirm_delete'])) {

        try {
            $oldValue =
             $product['name']
             . ' | SKU: '
             . $product['sku'];



            $deleteStmt = $pdo->prepare(
                "DELETE FROM products
                 WHERE product_id = ?"
            );

            $deleteStmt->execute([$productId]);
            $auditStmt = $pdo->prepare(
    "INSERT INTO audit_logs
    (user_id, action, entity_type, entity_id, old_value, new_value)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$auditStmt->execute([
    $_SESSION['user_id'],
    'DELETE',
    'product',
    $productId,
    $oldValue,
    null
]);

            header('Location: products.php?deleted=1');
            exit;

        } catch (PDOException $e) {

            $message =
                'This product cannot be deleted because it is linked to existing order records.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Delete Product | ShopSmart</title>

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
    width: 90%;
    max-width: 550px;
    margin: 80px auto;
}

.card {
    background: white;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.warning {
    padding: 15px;
    background: #f7eeee;
    border-radius: 4px;
    margin: 20px 0;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

.delete-button {
    padding: 12px 18px;
    background: #b42318;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.cancel {
    padding: 11px 18px;
    border: 1px solid #aaa;
    border-radius: 4px;
    text-decoration: none;
    color: #222;
}

.message {
    padding: 12px;
    background: #f1f1f1;
}

</style>

</head>

<body>

<main>

<div class="card">

<h1>Delete Product</h1>

<?php if ($message !== ''): ?>

<p class="message">
<?= htmlspecialchars($message) ?>
</p>

<?php endif; ?>

<div class="warning">

<strong>
Are you sure you want to delete this product?
</strong>

<p>
<?= htmlspecialchars($product['name']) ?>
</p>

<p>
SKU:
<?= htmlspecialchars($product['sku']) ?>
</p>

</div>

<p>
This action cannot be undone.
</p>

<form method="POST">

<div class="actions">

<button
    type="submit"
    name="confirm_delete"
    class="delete-button"
>
Yes, Delete Product
</button>

<a
    href="products.php"
    class="cancel"
>
Cancel
</a>

</div>

</form>

</div>

</main>

</body>

</html>