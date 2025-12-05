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

    // Создать новый заказ
    public function createOrder(int $userId, float $total, ?int $addressId = null): int
    {
        $query = "INSERT INTO orders (user_id, total_price, status, address_id, created_at) 
                  VALUES (:user_id, :total, 'new', :address_id, NOW())";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':address_id', $addressId);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return 0;
    }

    public function addItems(int $orderId, array $items)
    {
        $query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                  VALUES (:order_id, :product_id, :qty, :price)";
        $stmt = $this->conn->prepare($query);

        foreach ($items as $item) {
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':qty', $item['qty']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
        }
    }

    public function findByUserId(int $userId): array
    {
        $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Найти заказ по ID
    public function findById(int $orderId)
    {
        $query = "SELECT * FROM orders WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getOrderItemsCount(int $orderId): int
{
    $query = "SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$result['count'];
}

// И обновляем метод findByUserId, чтобы он возвращал больше информации:
public function findByUserId(int $userId): array
{
    $query = "SELECT o.*, a.city, a.street, a.house, a.apartment 
              FROM orders o 
              LEFT JOIN addresses a ON o.address_id = a.id 
              WHERE o.user_id = :user_id 
              ORDER BY o.created_at DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}