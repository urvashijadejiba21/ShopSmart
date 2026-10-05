<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query(
    "SELECT
        p.product_id,
        p.sku,
        p.name,
        p.price,
        p.stock_qty,
        p.is_active,
        c.name AS category_name
     FROM products p
     JOIN categories c
        ON p.category_id = c.category_id
     ORDER BY p.product_id DESC"
);

$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Manage Products | ShopSmart</title>

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
    flex-wrap: wrap;
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
    width: 92%;
    max-width: 1200px;
    margin: 40px auto;
}

.top-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.button {
    display: inline-block;
    padding: 11px 17px;
    background: #222;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}

.table-wrapper {
    overflow-x: auto;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 14px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #eee;
}

.action {
    margin-right: 10px;
}

.edit {
    color: #1a5fb4;
}

.delete {
    color: #b42318;
}

.active {
    font-weight: bold;
}

@media (max-width: 650px) {

    header {
        align-items: flex-start;
    }

    nav a {
        margin: 0 12px 0 0;
    }
}

</style>

</head>

<body>

<header>

<h1>ShopSmart Admin</h1>

<nav>

<a href="dashboard.php">
Dashboard
</a>

<a href="../customer/catalogue.php">
View Store
</a>

<a href="../logout.php">
Logout
</a>

</nav>

</header>

<main>

<div class="top-section">

<div>

<h2>Product Management</h2>

<p>
Manage products available in the ShopSmart catalogue.
</p>

</div>

<a class="button"
   href="product_add.php">
+ Add Product
</a>

</div>


<div class="table-wrapper">

<table>

<thead>

<tr>

<th>SKU</th>

<th>Product</th>

<th>Category</th>

<th>Price</th>

<th>Stock</th>

<th>Status</th>

<th>Actions</th>

</tr>

</thead>


<tbody>

<?php if (count($products) > 0): ?>

<?php foreach ($products as $product): ?>

<tr>

<td>
<?= htmlspecialchars($product['sku']) ?>
</td>

<td>
<?= htmlspecialchars($product['name']) ?>
</td>

<td>
<?= htmlspecialchars($product['category_name']) ?>
</td>

<td>
$<?= number_format((float)$product['price'], 2) ?>
</td>

<td>
<?= (int)$product['stock_qty'] ?>
</td>

<td class="active">

<?php if ($product['is_active']): ?>

Active

<?php else: ?>

Inactive

<?php endif; ?>

</td>

<td>

<a
class="action edit"
href="product_edit.php?id=<?= $product['product_id'] ?>">
Edit
</a>

<a
class="action delete"
href="product_delete.php?id=<?= $product['product_id'] ?>">
Delete
</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="7">
No products available.
</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</main>

</body>

</html>