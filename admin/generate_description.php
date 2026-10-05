<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);

    exit;
}

$product = trim($_POST['product'] ?? '');
$category = trim($_POST['category'] ?? '');
$keywords = trim($_POST['keywords'] ?? '');

if ($product === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Enter a product name first.'
    ]);

    exit;
}

if ($keywords === '') {
    $keywords = 'practical features and reliable performance';
}

$templateFile =
    __DIR__ . '/../data/description_templates.json';

if (!file_exists($templateFile)) {
    echo json_encode([
        'success' => false,
        'message' => 'Description templates could not be found.'
    ]);

    exit;
}

$templateData =
    json_decode(file_get_contents($templateFile), true);

if (!is_array($templateData)) {
    echo json_encode([
        'success' => false,
        'message' => 'Description templates are invalid.'
    ]);

    exit;
}

$templates =
    $templateData[$category]
    ?? $templateData['default'];

$template = $templates[array_rand($templates)];

$description = str_replace(
    ['{product}', '{keywords}'],
    [$product, $keywords],
    $template
);

echo json_encode([
    'success' => true,
    'description' => $description
]);