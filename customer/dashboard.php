<?php
session_start();

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] !== 'customer'
) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Dashboard | ShopSmart</title>
</head>

<body>

<h1>Customer Dashboard</h1>

<p>
    Welcome,
    <?= htmlspecialchars($_SESSION['full_name']) ?>!
</p>

<p>You are logged in as a customer.</p>

<a href="../index.php">Home</a>
<a href="../logout.php">Logout</a>

</body>
</html>