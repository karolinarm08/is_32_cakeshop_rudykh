<?php

namespace App\Repositories;

use App\Models\User;
use App\Config\Database;
use PDO;

class UserRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Пошук користувача за Email
    public function findByEmail(string $email)
    {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            // Повертаємо об'єкт User або масив даних
            return new User($data['email'], $data['password_hash'], $data['id'], $data['role']);
        }

        return null;
    }

    // Збереження нового користувача
    public function save(User $user): bool
    {
        $query = "INSERT INTO users (email, password_hash, role, first_name, last_name, phone) 
                  VALUES (:email, :password, :role, :first_name, :last_name, :phone)";
        
        $stmt = $this->conn->prepare($query);

        // Прив'язка даних
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':password', $user->passwordHash);
        $role = 'user'; // Значення за замовчуванням
        $stmt->bindParam(':role', $role);
        // Додаткові поля можна додати в модель User, поки лишимо null або пусті
        $empty = '';
        $stmt->bindParam(':first_name', $empty);
        $stmt->bindParam(':last_name', $empty);
        $stmt->bindParam(':phone', $empty);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Отримання ID останнього створеного запису
    public function getLastId() {
        return $this->conn->lastInsertId();
    }
}