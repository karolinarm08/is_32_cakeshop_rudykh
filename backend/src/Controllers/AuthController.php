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

    public function register($data)
    {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $name = $data['name'] ?? 'User';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Заповніть поля']); return;
        }
        echo json_encode($this->authService->registerUser($email, $password, $name));
    }

    public function login($data)
    {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Заповніть поля']); return;
        }
        echo json_encode($this->authService->loginUser($email, $password));
    }

    public function getProfile($data)
    {
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Не вказано email']); return;
        }
        echo json_encode($this->authService->getUserProfile($email));
    }

    public function update($data)
    {
        $email = $data['email'] ?? '';
        $fname = $data['firstName'] ?? '';
        $lname = $data['lastName'] ?? '';
        $phone = $data['phone'] ?? '';

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email обов\'язковий']); return;
        }
        echo json_encode($this->authService->updateUserData($email, $fname, $lname, $phone));
    }

    public function saveAddress($data)
    {
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email обов\'язковий']); return;
        }
        
        $addrData = [
            'city' => $data['city'] ?? '',
            'street' => $data['street'] ?? '',
            'house' => $data['house'] ?? '',
            'apartment' => $data['apartment'] ?? '',
            'floor' => $data['floor'] ?? ''
        ];

        echo json_encode($this->authService->updateUserAddress($email, $addrData));
    }
}