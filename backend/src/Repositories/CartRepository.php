<?php

namespace App\Repositories;

use App\Config\Database;
use PDO;

class CartRepository
{
    private PDO $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Знайти активний кошик користувача
    public function findCartByUserId(int $userId)
    {
        $query = "SELECT * FROM carts WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Створити новий кошик
    public function createCart(int $userId): int
    {
        $query = "INSERT INTO carts (user_id, created_at) VALUES (:user_id, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    // Перевірити, чи є вже такий товар у кошику
    public function findCartItem(int $cartId, int $productId)
    {
        $query = "SELECT * FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Додати товар або оновити кількість
    public function addOrUpdateItem(int $cartId, int $productId, int $quantity)
    {
        $item = $this->findCartItem($cartId, $productId);

        if ($item) {
            // Якщо товар є, збільшуємо кількість
            $query = "UPDATE cart_items SET quantity = quantity + :quantity WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':id', $item['id']);
        } else {
            // Якщо немає, додаємо новий
            $query = "INSERT INTO cart_items (cart_id, product_id, quantity, created_at) VALUES (:cart_id, :product_id, :quantity, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cart_id', $cartId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->bindParam(':quantity', $quantity);
        }
        return $stmt->execute();
    }

    // Отримати всі товари кошика з деталями про продукт
    public function getCartItemsWithProductDetails(int $cartId): array
    {
        // JOIN з таблицями products та product_images, щоб отримати назву, ціну і фото
        $query = "
            SELECT 
                ci.id as item_id, 
                ci.quantity, 
                p.id as product_id, 
                p.name, 
                p.price, 
                p.weight,
                (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = :cart_id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Видалити товар
    public function removeItem(int $itemId)
    {
        $query = "DELETE FROM cart_items WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $itemId);
        return $stmt->execute();
    }
    
    // Очистити кошик (наприклад після замовлення)
    public function clearCart(int $cartId) {
        $query = "DELETE FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cart_id', $cartId);
        return $stmt->execute();
    }
}