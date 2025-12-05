<?php
// order.php - API для обработки заказов

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

date_default_timezone_set('Europe/Kiev');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Автозагрузка классов
    spl_autoload_register(function ($class_name) {
        if (strpos($class_name, 'App\\') === 0) {
            $relative_class = substr($class_name, 4);
            $file = __DIR__ . '/../src/' . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    });

    require_once __DIR__ . '/../config/Database.php';
    
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true) ?? [];
    $action = $_GET['action'] ?? '';

    // Подключаем необходимые классы
    require_once __DIR__ . '/../src/Repositories/UserRepository.php';
    require_once __DIR__ . '/../src/Repositories/CartRepository.php';
    require_once __DIR__ . '/../src/Repositories/OrderRepository.php';
    require_once __DIR__ . '/../src/Services/OrderService.php';
    require_once __DIR__ . '/../src/Controllers/OrderController.php';

    $controller = new App\Controllers\OrderController();

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->create($data);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Дія не знайдена']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Помилка сервера: ' . $e->getMessage()]);
}

// В order.php добавляем после существующего кода:

elseif ($action === 'getUserOrders' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение заказов пользователя
    $email = $data['email'] ?? '';
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email обов\'язковий']);
        exit();
    }

    $userRepository = new App\Repositories\UserRepository();
    $orderRepository = new App\Repositories\OrderRepository();

    $user = $userRepository->findByEmail($email);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
        exit();
    }

    // Получаем заказы пользователя
    $orders = $orderRepository->findByUserId($user->id);
    
    // Получаем количество товаров для каждого заказа
    foreach ($orders as &$order) {
        $order['items_count'] = $orderRepository->getOrderItemsCount($order['id']);
    }

    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
    exit();
}
?>