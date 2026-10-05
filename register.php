<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullName === '' || $email === '' || $password === '') {
        $message = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } else {

        $check = $pdo->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            $message = 'An account with this email already exists.';
        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (full_name, email, password_hash, role)
                VALUES (?, ?, ?, 'customer')"
            );

            $stmt->execute([
                $fullName,
                $email,
                $passwordHash
            ]);

            header('Location: login.php?registered=1');
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

    <title>Register | ShopSmart</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            margin: 0;
        }

        .form-container {
            max-width: 420px;
            margin: 80px auto;
            background: white;
            padding: 35px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        input {
            width: 100%;
            padding: 11px;
            margin: 7px 0 18px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #222;
            color: white;
            border: none;
            cursor: pointer;
        }

        .message {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="form-container">

    <h1>Create Account</h1>

    <?php if ($message !== ''): ?>
        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label for="full_name">Full Name</label>
        <input
            id="full_name"
            name="full_name"
            type="text"
            required
        >

        <label for="email">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            required
        >

        <label for="password">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            minlength="6"
            required
        >

        <button type="submit">Register</button>

    </form>

    <p>
        Already registered?
        <a href="login.php">Login</a>
    </p>

    <p>
        <a href="index.php">Return to ShopSmart</a>
    </p>

</div>

</body>
</html>