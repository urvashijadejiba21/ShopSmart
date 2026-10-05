<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$stmt = $pdo->query(
    "SELECT
        a.audit_id,
        a.action,
        a.entity_type,
        a.entity_id,
        a.old_value,
        a.new_value,
        a.created_at,
        u.full_name
     FROM audit_logs a
     JOIN users u ON a.user_id = u.user_id
     ORDER BY a.created_at DESC"
);

$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Audit Log | ShopSmart</title>

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

nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
}

main {
    width: 94%;
    max-width: 1250px;
    margin: 40px auto;
}

.table-wrapper {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 13px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background: #eee;
}

.empty {
    text-align: center;
    padding: 30px;
}
</style>

</head>

<body>

<header>

<h1>ShopSmart Audit Log</h1>

<nav>
<a href="dashboard.php">Dashboard</a>
<a href="products.php">Products</a>
<a href="../logout.php">Logout</a>
</nav>

</header>

<main>

<h2>Administrative Activity</h2>

<p>
This log records important changes made by ShopSmart administrators.
</p>

<div class="table-wrapper">

<table>

<thead>
<tr>
    <th>Date</th>
    <th>Admin</th>
    <th>Action</th>
    <th>Entity</th>
    <th>ID</th>
    <th>Old Value</th>
    <th>New Value</th>
</tr>
</thead>

<tbody>

<?php if ($logs): ?>

<?php foreach ($logs as $log): ?>

<tr>

<td>
<?= htmlspecialchars($log['created_at']) ?>
</td>

<td>
<?= htmlspecialchars($log['full_name']) ?>
</td>

<td>
<?= htmlspecialchars($log['action']) ?>
</td>

<td>
<?= htmlspecialchars($log['entity_type']) ?>
</td>

<td>
<?= (int)$log['entity_id'] ?>
</td>

<td>
<?= htmlspecialchars($log['old_value'] ?? '-') ?>
</td>

<td>
<?= htmlspecialchars($log['new_value'] ?? '-') ?>
</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="7" class="empty">
No audit activity recorded yet.
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</main>

</body>
</html>