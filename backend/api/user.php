<?php

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

    require_once __DIR__ . '/../src/Repositories/UserRepository.php';
    require_once __DIR__ . '/../src/Repositories/OrderRepository.php';

    $userRepository = new App\Repositories\UserRepository();
    $orderRepository = new App\Repositories\OrderRepository();

    if ($action === 'updateProfile') {
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email обов\'язковий']);
            exit();
        }

        $user = $userRepository->findByEmail($email);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
            exit();
        }

        if (isset($data['first_name'])) $user->firstName = $data['first_name'];
        if (isset($data['last_name'])) $user->lastName = $data['last_name'];
        if (isset($data['phone'])) $user->phone = $data['phone'];

        if ($userRepository->update($user)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Дані успішно оновлено',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->firstName,
                    'last_name' => $user->lastName,
                    'phone' => $user->phone,
                    'role' => $user->role
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка оновлення даних']);
        }

    } elseif ($action === 'getProfile') {
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email обов\'язковий']);
            exit();
        }

        $user = $userRepository->findByEmail($email);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
            exit();
        }

        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->firstName,
                'last_name' => $user->lastName,
                'phone' => $user->phone,
                'role' => $user->role,
                'created_at' => $user->createdAt
            ]
        ]);

    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Дія не знайдена']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Помилка сервера: ' . $e->getMessage()]);
}
?>