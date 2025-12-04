<?php

// --- 1. Налаштування CORS (щоб Frontend міг робити запити) ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Обробка попередніх запитів (OPTIONS) - це потрібно для браузера
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- 2. Підключення файлів (Порядок важливий!) ---
require_once __DIR__ . '/src/Config/Database.php';

// Models
require_once __DIR__ . '/src/Models/User.php';
require_once __DIR__ . '/src/Models/Product.php';
require_once __DIR__ . '/src/Models/Order.php';
require_once __DIR__ . '/src/Models/Payment.php';
require_once __DIR__ . '/src/Models/Shipment.php';
require_once __DIR__ . '/src/Models/Cart.php';
require_once __DIR__ . '/src/Models/CartItem.php';

// Repositories
require_once __DIR__ . '/src/Repositories/UserRepository.php';
require_once __DIR__ . '/src/Repositories/ProductRepository.php';
require_once __DIR__ . '/src/Repositories/OrderRepository.php';

// Services
require_once __DIR__ . '/src/Services/AuthService.php';
require_once __DIR__ . '/src/Services/PaymentService.php';
require_once __DIR__ . '/src/Services/OrderService.php';

// Controllers
require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Controllers/ProductController.php';
require_once __DIR__ . '/src/Controllers/OrderController.php';

// Використання класів
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\OrderController;

// --- 3. Маршрутизація (Router) ---

// Отримуємо параметри з URL (наприклад: index.php?action=login)
$entity = $_GET['entity'] ?? null; // Наприклад: 'products', 'orders'
$action = $_GET['action'] ?? null; // Наприклад: 'login', 'register', 'getAll'

// Отримуємо дані, які прислав JavaScript (JSON)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true) ?? [];

// --- 4. Вибір контролера ---

// Варіант 1: Робота з АВТОРИЗАЦІЄЮ (якщо action=login або register)
if ($action === 'login' || $action === 'register' || $action === 'logout') {
    $controller = new AuthController();
    if ($action === 'register') {
        $controller->register($input);
    } elseif ($action === 'login') {
        $controller->login($input);
    }
    exit; // Зупиняємо виконання після відповіді
}

// Варіант 2: Робота з ТОВАРАМИ (якщо entity=products)
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

// Варіант 3: Робота із ЗАМОВЛЕННЯМИ (якщо entity=orders)
if ($entity === 'orders') {
    $controller = new OrderController();
    if ($action === 'create') {
        $controller->createOrder($input);
    }
    exit;
}

// Якщо нічого не підійшло
echo json_encode(['success' => false, 'message' => 'Невідомий запит API']);