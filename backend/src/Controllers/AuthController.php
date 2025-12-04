<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handleRequest()
    {
        $action = $_GET['action'] ?? null;
        
        // Отримуємо JSON дані
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data && !empty($_POST)) {
            $data = $_POST;
        }

        switch ($action) {
            case 'register':
                $this->register($data);
                break;
            case 'login':
                $this->login($data);
                break;
            case 'logout':
                $this->authService->logout();
                echo json_encode(['success' => true]);
                break;
            default:
                http_response_code(404);
                echo json_encode(['message' => 'Action not found']);
        }
    }

    public function register(array $data)
    {
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Неповні дані']);
            return;
        }
        
        $result = $this->authService->registerUser($data['email'], $data['password']);
        echo json_encode($result);
    }

    public function login(array $data)
    {
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Неповні дані']);
            return;
        }

        $result = $this->authService->loginUser($data['email'], $data['password']);
        
        // Не повертаємо пароль або хеш назад
        if(isset($result['user'])) {
            unset($result['user']->passwordHash);
        }
        
        echo json_encode($result);
    }
}