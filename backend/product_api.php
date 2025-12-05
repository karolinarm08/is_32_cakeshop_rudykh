<?php
// product_api.php

// Налаштування заголовків для CORS та JSON відповіді
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Налаштування часового поясу (рекомендовано)
date_default_timezone_set('Europe/Kiev'); 

// 1. Підключення моделей та репозиторіїв
require_once 'src/Models/Product.php';
require_once 'src/Repositories/ProductRepository.php';
require_once 'src/Repositories/ImageRepository.php'; 
require_once 'src/Controllers/ProductController.php';

use App\Controllers\ProductController;

// Обробка попереднього OPTIONS запиту (потрібно для CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? null;
$controller = new ProductController();

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $result = $controller->createProduct($data);

    if ($result['success']) {
        http_response_code(201); 
    } else {
        http_response_code(400); 
    }
    
    echo json_encode($result);
    exit();
    
} elseif ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // МАРШРУТ GET для отримання списку товарів (menu.html)
    $controller->getAllProducts();
    exit();

} elseif ($action === 'get' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // МАРШРУТ GET для отримання ОДНОГО товару за ID (product.html)
    $id = $_GET['id'] ?? null;

    if ($id === null) {
        http_response_code(400); 
        echo json_encode(['success' => false, 'message' => 'Необхідно вказати ID продукту.']);
        exit();
    }
    
    // Припускаємо, що ProductController має метод getProductById,
    // який відповідає за отримання даних, включаючи всі додаткові деталі
    // (зображення, відгуки, додаткову інформацію) та виведення JSON.
    $controller->getProductById((int)$id);
    exit();
}

// Якщо невірний маршрут або метод
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Маршрут не знайдено.']);
exit();