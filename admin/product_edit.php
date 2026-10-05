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

$productStmt = $pdo->prepare(
    "SELECT * FROM products WHERE product_id = ?"
);
$productStmt->execute([$productId]);
$product = $productStmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$categoryStmt = $pdo->query(
    "SELECT category_id, name
     FROM categories
     ORDER BY name"
);
$categories = $categoryStmt->fetchAll();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stockQty = $_POST['stock_qty'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (
        $sku === '' ||
        $name === '' ||
        $categoryId <= 0 ||
        $price === '' ||
        $stockQty === ''
    ) {
        $message = 'Please complete all required fields.';

    } elseif (!is_numeric($price) || $price < 0) {

        $message = 'Please enter a valid price.';

    } elseif (
        filter_var(
            $stockQty,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        ) === false
    ) {

        $message = 'Stock quantity must be 0 or greater.';

    } else {

        $duplicateStmt = $pdo->prepare(
            "SELECT product_id
             FROM products
             WHERE sku = ?
             AND product_id != ?"
        );

        $duplicateStmt->execute([
            $sku,
            $productId
        ]);

        if ($duplicateStmt->fetch()) {

            $message = 'That SKU is already being used.';

        } else {
            $oldValue =
    $product['name']
    . ' | Price: $'
    . $product['price']
    . ' | Stock: '
    . $product['stock_qty'];

            $updateStmt = $pdo->prepare(
                "UPDATE products
                 SET category_id = ?,
                     sku = ?,
                     name = ?,
                     description = ?,
                     price = ?,
                     stock_qty = ?,
                     is_active = ?
                 WHERE product_id = ?"
            );

            $updateStmt->execute([
                $categoryId,
                $sku,
                $name,
                $description,
                $price,
                $stockQty,
                $isActive,
                $productId
            ]);

            $newValue =
    $name
    . ' | Price: $'
    . $price
    . ' | Stock: '
    . $stockQty;

$auditStmt = $pdo->prepare(
    "INSERT INTO audit_logs
    (user_id, action, entity_type, entity_id, old_value, new_value)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$auditStmt->execute([
    $_SESSION['user_id'],
    'UPDATE',
    'product',
    $productId,
    $oldValue,
    $newValue
]);

            header('Location: products.php?updated=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Product | ShopSmart</title>

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

header a {
    color: white;
    text-decoration: none;
}

main {
    width: 90%;
    max-width: 650px;
    margin: 45px auto;
}

.form-card {
    background: white;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

label {
    display: block;
    font-weight: bold;
    margin-top: 18px;
    margin-bottom: 6px;
}

input,
select,
textarea {
    width: 100%;
    padding: 11px;
    border: 1px solid #bbb;
    border-radius: 4px;
}

textarea {
    min-height: 110px;
    resize: vertical;
}

.checkbox-row {
    margin-top: 20px;
}

.checkbox-row input {
    width: auto;
    margin-right: 8px;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

button {
    background: #222;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.cancel {
    padding: 11px 20px;
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

<header>

<h1>ShopSmart Admin</h1>

<a href="products.php">
Back to Products
</a>

</header>

<main>

<div class="form-card">

<h2>Edit Product</h2>

<?php if ($message !== ''): ?>

<p class="message">
<?= htmlspecialchars($message) ?>
</p>

<?php endif; ?>

<form method="POST">

<label for="sku">
SKU
</label>

<input
    id="sku"
    name="sku"
    type="text"
    required
    maxlength="30"
    value="<?= htmlspecialchars($_POST['sku'] ?? $product['sku']) ?>"
>


<label for="name">
Product Name
</label>

<input
    id="name"
    name="name"
    type="text"
    required
    maxlength="120"
    value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>"
>


<label for="category_id">
Category
</label>

<select
    id="category_id"
    name="category_id"
    required
>

<?php
$selectedCategory =
    (int) ($_POST['category_id'] ?? $product['category_id']);
?>

<?php foreach ($categories as $category): ?>

<option
    value="<?= $category['category_id'] ?>"
    <?= $selectedCategory === (int)$category['category_id']
        ? 'selected'
        : '' ?>
>
<?= htmlspecialchars($category['name']) ?>
</option>

<?php endforeach; ?>

</select>


<label for="description">
Description
</label>

<textarea
    id="description"
    name="description"
><?= htmlspecialchars(
    $_POST['description'] ?? $product['description']
) ?></textarea>


<label for="price">
Price ($)
</label>

<input
    id="price"
    name="price"
    type="number"
    min="0"
    step="0.01"
    required
    value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>"
>


<label for="stock_qty">
Stock Quantity
</label>

<input
    id="stock_qty"
    name="stock_qty"
    type="number"
    min="0"
    step="1"
    required
    value="<?= htmlspecialchars(
        $_POST['stock_qty'] ?? $product['stock_qty']
    ) ?>"
>


<div class="checkbox-row">

<?php
$activeValue = isset($_POST['is_active'])
    ? true
    : (
        $_SERVER['REQUEST_METHOD'] !== 'POST'
        && $product['is_active']
    );
?>

<input
    type="checkbox"
    id="is_active"
    name="is_active"
    <?= $activeValue ? 'checked' : '' ?>
>

<label
    for="is_active"
    style="display:inline;"
>
Active product
</label>

</div>


<div class="actions">

<button type="submit">
Save Changes
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