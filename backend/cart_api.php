<?php
// cart_api.php

// Налаштування заголовків для CORS та JSON відповіді
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Налаштування часового поясу (рекомендовано)
date_default_timezone_set('Europe/Kiev'); 

// 1. Підключення моделей, репозиторіїв та сервісів
require_once 'src/Models/Product.php';
require_once 'src/Models/Cart.php';
require_once 'src/Models/CartItem.php';

require_once 'src/Repositories/ProductRepository.php';
require_once 'src/Repositories/ImageRepository.php'; // Потрібен для отримання зображень товарів
require_once 'src/Repositories/CartRepository.php';
require_once 'src/Repositories/CartItemRepository.php';

require_once 'src/Services/CartService.php';

require_once 'src/Controllers/CartController.php';

use App\Controllers\CartController;

// Обробка попереднього OPTIONS запиту (потрібно для CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? null;
$controller = new CartController();

// --- Маршрутизація CartController ---

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Додавання товару в кошик
    $controller->addToCart();
    exit();
    
} elseif ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Отримання вмісту кошика користувача
    $controller->getCartContent();
    exit();

} elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Оновлення кількості товару в кошику
    // $controller->updateCartItem();
    http_response_code(501); 
    echo json_encode(['success' => false, 'message' => 'Маршрут для оновлення кількості ще не реалізовано.']);
    exit();

} elseif ($action === 'remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Видалення товару з кошика
    // $controller->removeCartItem();
    http_response_code(501); 
    echo json_encode(['success' => false, 'message' => 'Маршрут для видалення товару ще не реалізовано.']);
    exit();
}


// Якщо невірний маршрут або метод
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Маршрут кошика не знайдено.']);
exit();