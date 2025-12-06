<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/src/Config/Database.php';

require_once __DIR__ . '/src/Models/User.php';
require_once __DIR__ . '/src/Models/Product.php';
require_once __DIR__ . '/src/Models/Order.php';
require_once __DIR__ . '/src/Models/Payment.php';
require_once __DIR__ . '/src/Models/Shipment.php';
require_once __DIR__ . '/src/Models/Cart.php';
require_once __DIR__ . '/src/Models/CartItem.php';

require_once __DIR__ . '/src/Repositories/UserRepository.php';
require_once __DIR__ . '/src/Repositories/ProductRepository.php';
require_once __DIR__ . '/src/Repositories/OrderRepository.php';
require_once __DIR__ . '/src/Repositories/ImageRepository.php';
require_once __DIR__ . '/src/Services/AuthService.php';
require_once __DIR__ . '/src/Services/PaymentService.php';
require_once __DIR__ . '/src/Services/OrderService.php';

require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Controllers/ProductController.php';
require_once __DIR__ . '/src/Controllers/OrderController.php';

use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;


$entity = $_GET['entity'] ?? null; 
$action = $_GET['action'] ?? null;

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true) ?? [];


if ($action === 'login' || $action === 'register' || $action === 'logout') {
    $controller = new AuthController();
    if ($action === 'register') {
        $controller->register($input);
    } elseif ($action === 'login') {
        $controller->login($input);
    }
    exit;
}

if ($entity === 'products') {
    $controller = new ProductController();
    if ($action === 'getAll') {
        $controller->getAllProducts();
    } elseif ($action === 'getOne') {
        $id = $_GET['id'] ?? 0;
        $controller->getProductById((int)$id);
    }
    exit;
}

if ($entity === 'orders') {
    $controller = new OrderController();
    if ($action === 'create') {
        $controller->createOrder($input);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Невідомий запит API']);