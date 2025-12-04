<?php

namespace App\Repositories;

use App\Models\Product;
use App\Config\Database;
use PDO;

class ProductRepository
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function findAll(): array
    {
        // Отримуємо самі товари
        $query = "SELECT * FROM products WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $product = new Product(
                $row['name'], 
                (float)$row['price'], 
                $row['description'], 
                (int)$row['category_id']
            );
            $product->id = $row['id'];
            
            // Тепер завантажуємо картинки для цього товару
            $product->images = $this->getImagesByProductId($product->id);
            
            $products[] = $product;
        }
        return $products;
    }

    public function findById(int $id): ?Product
    {
        $query = "SELECT * FROM products WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $product = new Product(
                $row['name'], 
                (float)$row['price'], 
                $row['description'], 
                (int)$row['category_id']
            );
            $product->id = $row['id'];
            
            // Завантажуємо всі картинки для цього конкретного товару
            $product->images = $this->getImagesByProductId($id);
            
            return $product;
        }
        return null;
    }

    // Допоміжний метод для отримання картинок
    private function getImagesByProductId(int $productId): array
    {
        $query = "SELECT image_url FROM product_images WHERE product_id = :id ORDER BY display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $productId);
        $stmt->execute();
        
        // Отримуємо просто масив рядків-посилань
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}