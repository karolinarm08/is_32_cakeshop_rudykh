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

    // Створити нове замовлення
    public function createOrder(int $userId, float $total, ?int $addressId = null): int
    {
        // ВИПРАВЛЕНО: Використовуємо PHP час (Київський), а не MySQL час (UTC)
        $createdAt = date('Y-m-d H:i:s');

        $query = "INSERT INTO orders (user_id, total_price, status, address_id, created_at) 
                  VALUES (:user_id, :total, 'new', :address_id, :created_at)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':address_id', $addressId); // Тут тепер буде ID адреси, а не NULL
        $stmt->bindParam(':created_at', $createdAt);

        if ($stmt->execute()) {
            return (int) $this->conn->lastInsertId();
        }
        return 0;
    }

    // Додавання товарів (залишаємо робочий варіант з execute)
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
        // Вибираємо разом з назвою вулиці для відображення в історії
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

    // Метод для підрахунку кількості товарів (для відображення в історії)
    public function getOrderItemsCount(int $orderId): int
    {
        $query = "SELECT COUNT(*) as count FROM order_items WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (int)$res['count'] : 0;
    }

    // Знайти замовлення за ID
    public function findById(int $orderId)
    {
        $query = "SELECT * FROM orders WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}