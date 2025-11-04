<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function registerUser(string $email, string $password): ?User
    {
        echo "Сервіс: Реєстрація користувача $email\n";
        $passwordHash = $password; 

        $user = new User($email, $passwordHash);

        return $user;
    }

    public function loginUser(string $email, string $password): ?User
    {
        echo "Сервіс: Вхід користувача $email\n";
        return new User($email, $password);
    }
}

