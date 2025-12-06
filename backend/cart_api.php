<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

date_default_timezone_set('Europe/Kiev'); 

require_once 'src/Models/Product.php';
require_once 'src/Models/Cart.php';
require_once 'src/Models/CartItem.php';

require_once 'src/Repositories/ProductRepository.php';
require_once 'src/Repositories/ImageRepository.php'; 
require_once 'src/Repositories/CartRepository.php';
require_once 'src/Repositories/CartItemRepository.php';

require_once 'src/Services/CartService.php';

require_once 'src/Controllers/CartController.php';

use App\Controllers\CartController;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? null;
$controller = new CartController();


if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->addToCart();
    exit();
    
} elseif ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getCartContent();
    exit();

} elseif ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(501); 
    echo json_encode(['success' => false, 'message' => 'Маршрут для оновлення кількості ще не реалізовано.']);
    exit();

} elseif ($action === 'remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(501); 
    echo json_encode(['success' => false, 'message' => 'Маршрут для видалення товару ще не реалізовано.']);
    exit();
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Маршрут кошика не знайдено.']);
exit();