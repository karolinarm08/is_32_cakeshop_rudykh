<?php
// order.php - API для обробки замовлень
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit();
}

try {
    spl_autoload_register(function ($class_name) {
        if (strpos($class_name, 'App\\') === 0) {
            $relative_class = substr($class_name, 4);
            $file = __DIR__ . '/../src/' . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) require_once $file;
        }
    });

    require_once __DIR__ . '/../config/Database.php';
    
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true) ?? [];
    $action = $_GET['action'] ?? '';

    // Підключаємо класи вручну для надійності
    require_once __DIR__ . '/../src/Repositories/UserRepository.php';
    require_once __DIR__ . '/../src/Repositories/CartRepository.php';
    require_once __DIR__ . '/../src/Repositories/OrderRepository.php';
    require_once __DIR__ . '/../src/Services/OrderService.php';
    require_once __DIR__ . '/../src/Controllers/OrderController.php';

    $controller = new App\Controllers\OrderController();

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->create($data);
    } 
    // Маршрути для адміна (вимагають admin_email в тілі запиту)
    elseif ($action === 'getAll' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->getAll($data);
    } 
    elseif ($action === 'updateStatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->updateStatus($data);
    } 
    else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Дія не знайдена']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Помилка сервера: ' . $e->getMessage()]);
}
?>