<?php

namespace App\Repositories;

use App\Models\User;
use App\Config\Database;
use PDO;

class UserRepository
{
    private PDO $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function findByEmail(string $email): ?User
    {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user = new User($row['email'], $row['password_hash'], $row['first_name'], $row['role']);
            $user->id = $row['id'];
            // Додаємо інші поля, якщо вони є в БД
            $user->lastName = $row['last_name'] ?? null;
            $user->phone = $row['phone'] ?? null;
            return $user;
        }
        return null;
    }

    public function save(User $user): bool
    {
        $query = "INSERT INTO users (email, password_hash, first_name, role) VALUES (:email, :password_hash, :first_name, :role)";
        $stmt = $this->conn->prepare($query);

        $email = htmlspecialchars(strip_tags($user->email));
        $firstName = htmlspecialchars(strip_tags($user->firstName));
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $user->passwordHash);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':role', $user->role);

        return $stmt->execute();
    }

    // НОВИЙ МЕТОД: Оновлення даних
    public function update(User $user): bool
    {
        // Переконайтеся, що у вашій БД є колонки last_name та phone
        // Якщо їх немає, видаліть відповідні рядки з цього запиту
        $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, phone = :phone WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);

        $fname = htmlspecialchars(strip_tags($user->firstName));
        $lname = htmlspecialchars(strip_tags($user->lastName ?? ''));
        $phone = htmlspecialchars(strip_tags($user->phone ?? ''));
        $email = htmlspecialchars(strip_tags($user->email));

        $stmt->bindParam(':first_name', $fname);
        $stmt->bindParam(':last_name', $lname);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }
}