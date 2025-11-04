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

    public function register(array $data)
    {
        echo "Контролер: Викликано метод register()\n";
        
        $user = $this->authService->registerUser($data['email'], $data['password']);

        print_r($user);
    }

    public function login(array $data)
    {
        echo "Контролер: Викликано метод login()\n";

        $user = $this->authService->loginUser($data['email'], $data['password']);

        print_r($user);
    }
}

