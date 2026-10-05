<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$message = '';

// Get categories for dropdown
$categoryStmt = $pdo->query(
    "SELECT category_id, name
     FROM categories
     ORDER BY name"
);

$categories = $categoryStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stockQty = $_POST['stock_qty'] ?? '';

    // Server-side validation
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

        // Check for duplicate SKU
        $check = $pdo->prepare(
            "SELECT product_id
             FROM products
             WHERE sku = ?"
        );

        $check->execute([$sku]);

        if ($check->fetch()) {

            $message = 'That SKU already exists.';

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO products
                (
                    category_id,
                    sku,
                    name,
                    description,
                    price,
                    stock_qty,
                    is_active
                )
                VALUES (?, ?, ?, ?, ?, ?, 1)"
            );

            $stmt->execute([
                $categoryId,
                $sku,
                $name,
                $description,
                $price,
                $stockQty
            ]);

            $productId = (int)$pdo->lastInsertId();

$auditStmt = $pdo->prepare(
    "INSERT INTO audit_logs
    (user_id, action, entity_type, entity_id, old_value, new_value)
    VALUES (?, ?, ?, ?, ?, ?)"
);

$auditStmt->execute([
    $_SESSION['user_id'],
    'CREATE',
    'product',
    $productId,
    null,
    $name . ' | Price: $' . $price . ' | Stock: ' . $stockQty
]);

            header('Location: products.php?added=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Add Product | ShopSmart</title>

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

.form-card h2 {
    margin-top: 0;
}

label {
    display: block;
    font-weight: bold;
    margin-top: 18px;
    margin-bottom: 6px;
}
.assistant-box {
    margin-top: 25px;
    padding: 20px;
    background: #f5f6f8;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.assistant-box h3 {
    margin-top: 0;
}

.generate-button {
    margin-top: 12px;
    background: #444;
}

#assistant-message {
    margin-bottom: 0;
    font-size: 14px;
}

input,
select,
textarea {
    width: 100%;
    padding: 11px;
    border: 1px solid #bbb;
    border-radius: 4px;
    font-family: Arial, sans-serif;
}


textarea {
    min-height: 110px;
    resize: vertical;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

button {
    border: none;
    background: #222;
    color: white;
    padding: 12px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.cancel {
    display: inline-block;
    padding: 11px 20px;
    border: 1px solid #aaa;
    border-radius: 4px;
    text-decoration: none;
    color: #222;
}

.message {
    padding: 12px;
    background: #f1f1f1;
    border-radius: 4px;
}

.required {
    color: #b42318;
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

<h2>Add Product</h2>

<p>
Create a new product for the ShopSmart catalogue.
</p>

<?php if ($message !== ''): ?>

<p class="message">
<?= htmlspecialchars($message) ?>
</p>

<?php endif; ?>


<form method="POST">

<label for="sku">
SKU <span class="required">*</span>
</label>

<input
    id="sku"
    name="sku"
    type="text"
    maxlength="30"
    required
    value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>"
>


<label for="name">
Product Name <span class="required">*</span>
</label>

<input
    id="name"
    name="name"
    type="text"
    maxlength="120"
    required
    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
>


<label for="category_id">
Category <span class="required">*</span>
</label>

<select
    id="category_id"
    name="category_id"
    required
>

<option value="">
Select a category
</option>

<?php foreach ($categories as $category): ?>

<option
    value="<?= $category['category_id'] ?>"
    <?= (
        ($_POST['category_id'] ?? '') ==
        $category['category_id']
    ) ? 'selected' : '' ?>
>
    <?= htmlspecialchars($category['name']) ?>
</option>

<?php endforeach; ?>

</select>


<div class="assistant-box">

    <h3>Content Suggestion Assistant</h3>

    <p>
        Enter keywords or product features and ShopSmart can
        generate a suggested description. Review or edit the
        suggestion before saving the product.
    </p>

    <label for="keywords">
        Product Keywords / Features
    </label>

    <input
        id="keywords"
        type="text"
        placeholder="e.g. reusable, lightweight, insulated"
    >

    <button
        type="button"
        id="generate-description"
        class="generate-button"
    >
        Generate Description
    </button>

    <p id="assistant-message"></p>

</div>


<label for="description">
Description
</label>

<textarea
    id="description"
    name="description"
><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>


<label for="price">
Price ($) <span class="required">*</span>
</label>

<input
    id="price"
    name="price"
    type="number"
    min="0"
    step="0.01"
    required
    value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
>


<label for="stock_qty">
Stock Quantity <span class="required">*</span>
</label>

<input
    id="stock_qty"
    name="stock_qty"
    type="number"
    min="0"
    step="1"
    required
    value="<?= htmlspecialchars($_POST['stock_qty'] ?? '') ?>"
>


<div class="actions">

<button type="submit">
Add Product
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
<script>
const generateButton =
    document.getElementById('generate-description');

generateButton.addEventListener('click', async function () {

    const product =
        document.getElementById('name').value.trim();

    const categorySelect =
        document.getElementById('category_id');

    const category =
        categorySelect.options[
            categorySelect.selectedIndex
        ]?.text || '';

    const keywords =
        document.getElementById('keywords').value.trim();

    const message =
        document.getElementById('assistant-message');

    if (product === '') {
        message.textContent =
            'Please enter the product name first.';
        return;
    }

    message.textContent =
        'Generating suggestion...';

    const formData = new FormData();

    formData.append('product', product);
    formData.append('category', category);
    formData.append('keywords', keywords);

    try {

        const response = await fetch(
            'generate_description.php',
            {
                method: 'POST',
                body: formData
            }
        );

        const data = await response.json();

        if (data.success) {

            document.getElementById('description').value =
                data.description;

            message.textContent =
                'Suggestion generated. Review or edit it before saving.';

        } else {

            message.textContent = data.message;

        }

    } catch (error) {

        message.textContent =
            'Unable to generate a suggestion.';

    }
});
</script>
</body>

</html>