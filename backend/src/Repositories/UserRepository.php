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
            $user->lastName = $row['last_name'] ?? null;
            $user->phone = $row['phone'] ?? null;
            $user->createdAt = $row['created_at'] ?? null;
            return $user;
        }
        return null;
    }

    // НОВЫЙ МЕТОД: Найти пользователя по ID
    public function findById(int $id): ?User
    {
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $user = new User($row['email'], $row['password_hash'], $row['first_name'], $row['role']);
            $user->id = $row['id'];
            $user->lastName = $row['last_name'] ?? null;
            $user->phone = $row['phone'] ?? null;
            $user->createdAt = $row['created_at'] ?? null;
            return $user;
        }
        return null;
    }

    public function save(User $user): bool
    {
        $query = "INSERT INTO users (email, password_hash, first_name, last_name, phone, role) 
                  VALUES (:email, :password_hash, :first_name, :last_name, :phone, :role)";
        $stmt = $this->conn->prepare($query);

        $email = htmlspecialchars(strip_tags($user->email));
        $firstName = htmlspecialchars(strip_tags($user->firstName));
        $lastName = htmlspecialchars(strip_tags($user->lastName ?? ''));
        $phone = htmlspecialchars(strip_tags($user->phone ?? ''));
        
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $user->passwordHash);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':role', $user->role);

        if ($stmt->execute()) {
            $user->id = (int)$this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // НОВЫЙ МЕТОД: Получить ID последнего вставленного пользователя
    public function getLastInsertId(): int
    {
        return (int)$this->conn->lastInsertId();
    }

    public function update(User $user): bool
    {
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

    // НОВЫЙ МЕТОД: Получить адреса пользователя
    public function getUserAddresses(int $userId): array
    {
        $query = "SELECT * FROM addresses WHERE user_id = :user_id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // НОВЫЙ МЕТОД: Добавить адрес пользователя
    public function addUserAddress(int $userId, array $addressData): int
    {
        $query = "INSERT INTO addresses (user_id, city, street, house, apartment, floor) 
                  VALUES (:user_id, :city, :street, :house, :apartment, :floor)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':city', $addressData['city']);
        $stmt->bindParam(':street', $addressData['street']);
        $stmt->bindParam(':house', $addressData['house']);
        $stmt->bindParam(':apartment', $addressData['apartment'] ?? null);
        $stmt->bindParam(':floor', $addressData['floor'] ?? null);

        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }
        
        return 0;
    }

    // НОВЫЙ МЕТОД: Получить пользователя с адресами
    public function getUserWithAddresses(string $email): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        $addresses = $this->getUserAddresses($user->id);

        return [
            'user' => $user,
            'addresses' => $addresses
        ];
    }

    // НОВЫЙ МЕТОД: Проверить существование пользователя
    public function userExists(string $email): bool
    {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // НОВЫЙ МЕТОД: Получить всех пользователей (для админки)
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $query = "SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($row['email'], $row['password_hash'], $row['first_name'], $row['role']);
            $user->id = $row['id'];
            $user->lastName = $row['last_name'] ?? null;
            $user->phone = $row['phone'] ?? null;
            $user->createdAt = $row['created_at'] ?? null;
            $users[] = $user;
        }

        return $users;
    }
}