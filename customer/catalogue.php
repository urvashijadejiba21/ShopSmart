<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Search and filter values
$search = trim($_GET['search'] ?? '');
$category = isset($_GET['category']) ? (int) $_GET['category'] : 0;

// Pagination
$perPage = 4;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Get categories for filter
$categoryStmt = $pdo->query(
    "SELECT category_id, name
     FROM categories
     ORDER BY name"
);
$categories = $categoryStmt->fetchAll();

// Build product query
$where = ["p.is_active = 1"];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
    $term = '%' . $search . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($category > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}

$whereSQL = implode(' AND ', $where);

// Count matching products
$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM products p
     WHERE $whereSQL"
);
$countStmt->execute($params);

$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));

// Get products
$sql = "
    SELECT
        p.product_id,
        p.sku,
        p.name,
        p.description,
        p.price,
        p.stock_qty,
        p.image_url,
        c.name AS category_name
    FROM products p
    JOIN categories c
        ON p.category_id = c.category_id
    WHERE $whereSQL
    ORDER BY p.product_id DESC
    LIMIT $perPage OFFSET $offset
";

$productStmt = $pdo->prepare($sql);
$productStmt->execute($params);
$products = $productStmt->fetchAll();

function catalogueUrl($page, $search, $category)
{
    return '?' . http_build_query([
        'search' => $search,
        'category' => $category,
        'page' => $page
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Shop | ShopSmart</title>

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
            gap: 15px;
        }

        header h1 {
            margin: 0;
            font-size: 25px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        main {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h2 {
            margin-bottom: 8px;
            font-size: 32px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 30px;

            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filters input,
        .filters select {
            padding: 11px;
            border: 1px solid #bbb;
            border-radius: 4px;
            font-size: 15px;
        }

        .filters input {
            flex: 1;
            min-width: 200px;
        }

        .filters button {
            padding: 11px 22px;
            border: none;
            background: #222;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .clear {
            padding: 11px 18px;
            color: #222;
            text-decoration: none;
            border: 1px solid #aaa;
            border-radius: 4px;
        }

        .product-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(230px, 1fr));
            gap: 22px;
        }

        .product-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .product-image {
            height: 170px;
            background: #e9e9e9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
            font-weight: bold;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-content {
            padding: 20px;
            flex: 1;
        }

        .category {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
        }

        .product-content h3 {
            margin: 8px 0;
        }

        .description {
            color: #555;
            line-height: 1.5;
            min-height: 65px;
        }

        .price {
            font-size: 22px;
            font-weight: bold;
            margin: 15px 0 5px;
        }

        .stock {
            font-size: 14px;
            color: #555;
        }

        .pagination {
            margin-top: 35px;
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 9px 13px;
            border: 1px solid #bbb;
            text-decoration: none;
            color: #222;
            background: white;
            border-radius: 4px;
        }

        .pagination a.active {
            background: #222;
            color: white;
        }

        .no-products {
            background: white;
            border: 1px solid #ddd;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
        }

        @media (max-width: 650px) {
            header {
                align-items: flex-start;
            }

            nav a {
                margin: 0 15px 0 0;
            }

            .filters {
                flex-direction: column;
            }

            .filters input,
            .filters select,
            .filters button,
            .clear {
                width: 100%;
            }
        }
        .order-button {
    display: inline-block;
    margin-top: 12px;
    padding: 10px 16px;
    background: #222;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
}

.order-button:hover {
    opacity: 0.85;
}
    </style>
</head>

<body>

<header>

    <h1>ShopSmart</h1>

    <nav>
        <a href="../index.php">Home</a>
        <a href="catalogue.php">Shop</a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="dashboard.php">Dashboard</a>
            <a href="../logout.php">Logout</a>

        <?php else: ?>

            <a href="../login.php">Login</a>
            <a href="../register.php">Register</a>

        <?php endif; ?>

    </nav>

</header>

<main>

    <section class="page-heading">

        <h2>Product Catalogue</h2>

        <p>
            Browse products or use the search and category filters.
        </p>

    </section>


    <form method="GET"
          action="catalogue.php"
          class="filters">

        <input
            type="search"
            name="search"
            placeholder="Search products..."
            value="<?= htmlspecialchars($search) ?>"
            aria-label="Search products"
        >

        <select
            name="category"
            aria-label="Filter by category"
        >

            <option value="0">
                All Categories
            </option>

            <?php foreach ($categories as $cat): ?>

                <option
                    value="<?= $cat['category_id'] ?>"
                    <?= $category === (int) $cat['category_id']
                        ? 'selected'
                        : '' ?>
                >
                    <?= htmlspecialchars($cat['name']) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <button type="submit">
            Search
        </button>

        <a href="catalogue.php"
           class="clear">
            Clear
        </a>

    </form>


    <?php if (count($products) > 0): ?>

        <div class="product-grid">

            <?php foreach ($products as $product): ?>

                <article class="product-card">

                    <div class="product-image">

                        <?php if (!empty($product['image_url'])): ?>

                            <img
                                src="<?= htmlspecialchars($product['image_url']) ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                            >

                        <?php else: ?>

                            No Image

                        <?php endif; ?>

                    </div>

                    <div class="product-content">

                        <span class="category">
                            <?= htmlspecialchars(
                                $product['category_name']
                            ) ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($product['name']) ?>
                        </h3>

                        <p class="description">
                            <?= htmlspecialchars(
                                $product['description']
                            ) ?>
                        </p>

                        <p class="price">
                            $<?= number_format(
                                (float) $product['price'],
                                2
                            ) ?>
                        </p>

                        <p class="stock">

                           <?php if ($product['stock_qty'] > 0): ?>

                                <?= (int) $product['stock_qty'] ?>
                                 in stock

                           <?php else: ?>

                                 Out of stock

                         <?php endif; ?>

                        </p>


                         <?php if (
                         isset($_SESSION['user_id']) &&
                         ($_SESSION['role'] ?? '') === 'customer' &&
                          $product['stock_qty'] > 0
): ?>

                         <a
                            href="order.php?id=<?= (int) $product['product_id'] ?>"
                            class="order-button"
    >
                              Order Now
    </a>

<?php endif; ?>


</div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="no-products">

            <h3>No products found</h3>

            <p>
                Try changing your search or category.
            </p>

        </div>

    <?php endif; ?>


    <?php if ($totalPages > 1): ?>

        <nav
            class="pagination"
            aria-label="Product pages"
        >

            <?php for (
                $i = 1;
                $i <= $totalPages;
                $i++
            ): ?>

                <a
                    href="<?= htmlspecialchars(
                        catalogueUrl(
                            $i,
                            $search,
                            $category
                        )
                    ) ?>"
                    class="<?= $i === $page
                        ? 'active'
                        : '' ?>"
                >
                    <?= $i ?>
                </a>

            <?php endfor; ?>

        </nav>

    <?php endif; ?>

</main>

</body>
</html>