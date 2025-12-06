<?php
// product_api.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

date_default_timezone_set('Europe/Kiev'); 

// Підключення класів
require_once 'src/Models/Product.php';
require_once 'src/Repositories/ProductRepository.php';
require_once 'src/Repositories/ImageRepository.php'; 
require_once 'src/Controllers/ProductController.php';

use App\Controllers\ProductController;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? null;
$controller = new ProductController();

// 1. СТВОРЕННЯ
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode($controller->createProduct($_POST));
    exit();
} 
// 2. ОНОВЛЕННЯ
elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode($controller->updateProduct($_POST));
    exit();
} 
// 3. ВИДАЛЕННЯ ТОВАРУ
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? $_POST['id'] ?? 0;
    echo json_encode($controller->deleteProduct((int)$id));
    exit();
} 
// 4. ВИДАЛЕННЯ ФОТО (окремо)
elseif ($action === 'delete_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $controller->deleteImage($input);
    exit();
} 
// 5. СПИСОК ТОВАРІВ
elseif ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getAllProducts();
    exit();
} 
// 6. ОТРИМАННЯ ОДНОГО ТОВАРУ
elseif ($action === 'get' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id === null) {
        http_response_code(400); 
        echo json_encode(['success' => false, 'message' => 'Не вказано ID.']);
        exit();
    }
    $controller->getProductById((int)$id);
    exit();
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Маршрут не знайдено.']);
exit();