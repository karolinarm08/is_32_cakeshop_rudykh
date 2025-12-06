<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class OrderRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createOrder(int $userId, float $total, ?int $addressId = null): int
    {
        $createdAt = date('Y-m-d H:i:s');
        $query = "INSERT INTO orders (user_id, total_price, status, address_id, created_at) 
                  VALUES (:user_id, :total, 'new', :address_id, :created_at)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':address_id', $addressId);
        $stmt->bindParam(':created_at', $createdAt);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }
        return 0;
    }

    public function addItems(int $orderId, array $items)
    {
        $query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                  VALUES (:order_id, :product_id, :qty, :price)";
        $stmt = $this->conn->prepare($query);

        foreach ($items as $item) {
            $stmt->execute([
                ':order_id' => $orderId,
                ':product_id' => $item['product_id'],
                ':qty' => $item['qty'],
                ':price' => $item['price']
            ]);
        }
    }

    public function findByUserId(int $userId): array
    {
        $query = "SELECT o.*, a.city, a.street, a.house 
                  FROM orders o 
                  LEFT JOIN addresses a ON o.address_id = a.id 
                  WHERE o.user_id = :user_id 
                  ORDER BY o.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- АДМІНСЬКІ МЕТОДИ ---

    // Отримати ВСІ замовлення (з іменами користувачів і адресами)
    public function findAllOrders(): array
    {
        $query = "
            SELECT 
                o.*, 
                u.email, u.first_name, u.last_name, u.phone,
                a.city, a.street, a.house, a.apartment
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN addresses a ON o.address_id = a.id
            ORDER BY o.created_at DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Оновити статус замовлення
    public function updateStatus(int $orderId, string $status): bool
    {
        $query = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $orderId);
        return $stmt->execute();
    }
    
    // Отримати товари конкретного замовлення (для деталей в адмінці)
    public function getOrderItems(int $orderId): array
    {
        $query = "
            SELECT oi.*, p.name 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById(int $orderId)
    {
        $query = "SELECT * FROM orders WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}