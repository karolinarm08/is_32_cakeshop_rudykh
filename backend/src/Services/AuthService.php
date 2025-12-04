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

    public function registerUser(string $email, string $password): array
    {
        // Перевірка чи існує користувач
        if ($this->userRepository->findByEmail($email)) {
            return ['success' => false, 'message' => 'Користувач з таким email вже існує'];
        }

        // Хешування пароля
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $user = new User($email, $passwordHash);

        if ($this->userRepository->save($user)) {
            return ['success' => true, 'message' => 'Реєстрація успішна'];
        }

        return ['success' => false, 'message' => 'Помилка реєстрації'];
    }

    public function loginUser(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user && password_verify($password, $user->passwordHash)) {
            // Починаємо сесію
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            $_SESSION['user_email'] = $user->email;

            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'Невірний email або пароль'];
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
    }
}