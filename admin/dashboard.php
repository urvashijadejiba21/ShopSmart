<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | ShopSmart</title>

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
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        main {
            width: 90%;
            max-width: 1100px;
            margin: 50px auto;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .card h2 {
            margin-top: 0;
        }

        .button {
            display: inline-block;
            background: #222;
            color: white;
            padding: 11px 17px;
            margin-top: 10px;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>

<body>

<header>

    <h1>ShopSmart Admin</h1>

    <nav>
        <a href="../index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="../customer/catalogue.php">View Store</a>
        <a href="../logout.php">Logout</a>
    </nav>

</header>

<main>

    <section class="welcome">

        <h2>
            Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>
        </h2>

        <p>
            Manage your ShopSmart store from the admin dashboard.
        </p>

    </section>

    <section class="cards">

        <div class="card">

            <h2>Product Management</h2>

            <p>
                Add, edit, view and remove products from the store.
            </p>

            <a href="products.php" class="button">
                Manage Products
            </a>

        </div>

        <div class="card">

            <h2>Orders</h2>

            <p>
                View and manage customer orders.
            </p>

        </div>

        <div class="card">

            <h2>Audit Activity</h2>

            <p>
                Review important administrative changes made in ShopSmart.
            </p>

        </div>

    </section>

</main>

</body>

</html>