<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

/* Get number of orders for this customer */
$orderStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM orders
     WHERE user_id = ?"
);

$orderStmt->execute([
    $_SESSION['user_id']
]);

$orderCount = (int)$orderStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Customer Dashboard | ShopSmart</title>

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

/* HEADER */

header {
    background: white;
    padding: 22px 6%;
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
    font-size: 17px;
}

nav a:hover {
    text-decoration: underline;
}

/* MAIN */

main {
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}

/* WELCOME */

.welcome {
    background: #e8eef5;
    padding: 45px;
    border-radius: 8px;
    margin-bottom: 35px;
}

.welcome h2 {
    margin-top: 0;
    font-size: 32px;
}

.welcome p {
    font-size: 17px;
    line-height: 1.6;
}

/* DASHBOARD CARDS */

.dashboard-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fit, minmax(230px, 1fr));

    gap: 25px;
}

.card {
    background: white;
    border: 1px solid #ddd;
    padding: 28px;
    border-radius: 8px;
}

.card h3 {
    margin-top: 0;
    font-size: 22px;
}

.card p {
    line-height: 1.5;
    color: #555;
}

.button {
    display: inline-block;
    margin-top: 10px;
    padding: 11px 17px;
    background: #222;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.button:hover {
    opacity: 0.85;
}

.order-number {
    font-size: 32px;
    font-weight: bold;
    margin: 10px 0;
}

/* MOBILE */

@media (max-width: 650px) {

    header {
        padding: 20px;
    }

    header h1 {
        font-size: 30px;
    }

    main {
        width: 92%;
    }

    .welcome {
        padding: 28px;
    }

}

</style>

</head>

<body>

<header>

    <h1>ShopSmart</h1>

    <nav>

        <a href="../index.php">
            Home
        </a>

        <a href="catalogue.php">
            Shop
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="my_orders.php">
            My Orders
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </nav>

</header>


<main>

    <section class="welcome">

        <h2>
            Welcome,
            <?= htmlspecialchars($_SESSION['full_name']) ?>!
        </h2>

        <p>
            Browse ShopSmart products, place orders and manage
            your shopping activity from your customer dashboard.
        </p>

        <a
            href="catalogue.php"
            class="button"
        >
            Browse Products
        </a>

    </section>


    <section class="dashboard-grid">

        <!-- SHOP -->

        <div class="card">

            <h3>Shop Products</h3>

            <p>
                Browse available products, search the catalogue
                and filter products by category.
            </p>

            <a
                href="catalogue.php"
                class="button"
            >
                View Catalogue
            </a>

        </div>


        <!-- ORDERS -->

        <div class="card">

            <h3>My Orders</h3>

            <div class="order-number">
                <?= $orderCount ?>
            </div>

            <p>
                Orders placed using your ShopSmart account.
            </p>
            <a href="my_orders.php" class="button">
                View My Orders
            </a>

        </div>


        <!-- ACCOUNT -->

        <div class="card">

            <h3>My Account</h3>

            <p>
                You are securely logged in as a ShopSmart
                customer.
            </p>

            <a
                href="../logout.php"
                class="button"
            >
                Logout
            </a>

        </div>

    </section>

</main>

</body>
</html>