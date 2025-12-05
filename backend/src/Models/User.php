<?php

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $passwordHash;
    public string $firstName;
    public string $lastName;
    public string $phone;
    public string $role;
    public string $createdAt;

    public function __construct(
        string $email,
        string $passwordHash,
        string $firstName,
        string $role = 'user'
    ) {
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->firstName = $firstName;
        $this->role = $role;
        $this->createdAt = date('Y-m-d H:i:s');
    }

    // Проверка пароля
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    // Хеширование пароля
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Полное имя
    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    // Проверка, является ли админом
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}