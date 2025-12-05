<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'FATAL ERROR: ' . $error['message']]);
    }
});

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit;
}

try {
    spl_autoload_register(function ($class_name) {
        if (strpos($class_name, 'App\Config\\') === 0) {
             $relative = str_replace('App\Config\\', '', $class_name);
             $file = __DIR__ . '/../config/' . str_replace('\\', '/', $relative) . '.php';
             if (file_exists($file)) { require_once $file; return; }
        }
        $prefix = 'App\\';
        if (strncmp($prefix, $class_name, strlen($prefix)) === 0) {
            $relative_class = substr($class_name, strlen($prefix));
            $file = __DIR__ . '/../src/' . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) { require_once $file; }
        }
    });

    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true) ?? [];
    $action = $_GET['action'] ?? '';

    $controllerClass = '\App\Controllers\AuthController';
    if (!class_exists($controllerClass)) throw new Exception("Клас AuthController не знайдено.");

    $controller = new $controllerClass();

    if ($action === 'register') {
        $controller->register($data);
    } elseif ($action === 'login') {
        $controller->login($data);
    } elseif ($action === 'update') {
        $controller->update($data);
    } elseif ($action === 'getProfile') { // Отримати свіжі дані
        $controller->getProfile($data);
    } elseif ($action === 'saveAddress') { // Зберегти адресу
        $controller->saveAddress($data);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невідома дія (action).']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Помилка сервера: ' . $e->getMessage()]);
}
?>