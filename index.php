<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopSmart</title>

   <style>
body {
    font-family: Arial, sans-serif;
    background: #f5f6f8;
    color: #222;
}

header {
    padding: 20px;
    background: white;
}

.hero {
    text-align: center;
    padding: 80px 20px;
    background: #e9eef5;
}

.features {
    display: flex;
    gap: 20px;
    padding: 40px;
}

.feature-card {
    flex: 1;
    background: white;
    padding: 25px;
    border: 1px solid #ddd;
}

footer {
    text-align: center;
    padding: 20px;
    background: #222;
    color: white;
}
</style>
 </head>

<body>

<header class="site-header">
    <div class="container nav-container">

        <h1 class="logo">ShopSmart</h1>

        <nav>
            <a href="index.php">Home</a>
            <a href="customer/catalogue.php">Shop</a>
            <a href="login.php">Login</a>
        </nav>

    </div>
</header>


<main>

    <section class="hero">

        <div class="container">

            <h2>Smart Shopping Made Simple</h2>

            <p>
                Browse products, manage your orders and enjoy
                a simple and convenient shopping experience.
            </p>

            <a class="button" href="customer/catalogue.php">
                Browse Products
            </a>

        </div>

    </section>


    <section class="features container">

        <div class="feature-card">
            <h3>Product Catalogue</h3>
            <p>Browse and search available ShopSmart products.</p>
        </div>

        <div class="feature-card">
            <h3>Easy Ordering</h3>
            <p>Add products to your cart and manage your orders.</p>
        </div>

        <div class="feature-card">
            <h3>Secure Accounts</h3>
            <p>Customer and administrator accounts use secure authentication.</p>
        </div>

    </section>

</main>


<footer>
    <p>&copy; 2026 ShopSmart</p>
</footer>

</body>
</html>