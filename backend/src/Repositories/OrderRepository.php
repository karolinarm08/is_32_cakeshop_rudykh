<?php

namespace App\Repositories;

use App\Models\Order;
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

    public function save(Order $order): int
    {
        $query = "INSERT INTO orders (user_id, total_price, status, address_id) VALUES (:user_id, :total, :status, :address_id)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $order->userId);
        $stmt->bindParam(':total', $order->total);
        $stmt->bindParam(':status', $order->status);
        
        // Тимчасово null для адреси, якщо не передано
        $addrId = $order->shipment ? $order->shipment->id : null; 
        $stmt->bindParam(':address_id', $addrId);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return 0;
    }

    public function addItems(int $orderId, array $items)
    {
        $query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (:order_id, :product_id, :qty, :price)";
        $stmt = $this->conn->prepare($query);

        foreach ($items as $item) {
            // $item очікується як масив або об'єкт CartItem
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
}