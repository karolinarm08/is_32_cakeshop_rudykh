<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Kiev');
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

    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? 0;

    $controllerClass = '\App\Controllers\ProductController';
    if (!class_exists($controllerClass)) throw new Exception("Клас ProductController не знайдено.");

    $controller = new $controllerClass();

    if ($action === 'getAll') {
        $controller->getAllProducts();
    } elseif ($action === 'getOne') {
        $controller->getProductById((int)$id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Невідома дія (action).']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Помилка сервера: ' . $e->getMessage()]);
}
?>