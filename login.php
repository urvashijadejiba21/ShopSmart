<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/includes/db.php';

$message = '';

if (isset($_GET['registered'])) {
    $message = 'Registration successful. You can now log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT user_id, full_name, email, password_hash, role
         FROM users
         WHERE email = ?"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: customer/dashboard.php');
        }

        exit;

    } else {
        $message = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | ShopSmart</title>

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

        .form-container {
            width: 90%;
            max-width: 420px;
            margin: 80px auto;
            padding: 35px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px;
            border: 1px solid #bbb;
            border-radius: 4px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background: #222;
            color: white;
            cursor: pointer;
        }

        .message {
            padding: 10px;
            background: #f1f1f1;
            border-radius: 4px;
        }

        a {
            color: #222;
        }
    </style>
</head>

<body>

<div class="form-container">

    <h1>ShopSmart Login</h1>

    <?php if ($message !== ''): ?>
        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="">

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <button type="submit">Login</button>

    </form>

    <p>
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>

    <p>
        <a href="index.php">Return to ShopSmart</a>
    </p>

</div>

</body>
</html>