<?php

namespace App\Repositories;

use App\Models\CartItem;
use \PDO;

class CartItemRepository extends ProductRepository 
{

    public function findByCartIdAndProductId(int $cartId, int $productId): ?CartItem
    {
        try {
            $stmt = $this->db->prepare("SELECT id, quantity, unit_price FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cartId, $productId]);
            $data = $stmt->fetch();

            if (!$data) {
                return null;
            }

            return new CartItem(
                $cartId,
                $productId,
                (int)$data['quantity'],
                (float)$data['unit_price'],
                (int)$data['id']
            );

        } catch (\PDOException $e) {
            error_log("SQL Error on findByCartIdAndProductId: " . $e->getMessage());
            return null;
        }
    }

    public function insert(CartItem $item): bool
    {
        try {
            $sql = "INSERT INTO cart_items (cart_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            $success = $stmt->execute([
                $item->cartId,
                $item->productId,
                $item->qty,
                $item->unitPrice
            ]);
            
            if ($success) {
                $item->id = (int)$this->db->lastInsertId();
            }

            return $success;
            
        } catch (\PDOException $e) {
            error_log("SQL Error on CartItem insert: " . $e->getMessage());
            throw new \Exception("DB Error during CartItem insert: " . $e->getMessage());
        }
    }

    public function updateQuantity(CartItem $item): bool
    {
        try {
            $sql = "UPDATE cart_items SET quantity = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([$item->qty, $item->id]);

        } catch (\PDOException $e) {
            error_log("SQL Error on CartItem updateQuantity: " . $e->getMessage());
            throw new \Exception("DB Error during CartItem updateQuantity: " . $e->getMessage());
        }
    }
    

    public function findItemsByCartId(int $cartId): array
    {
        try {
            $sql = "SELECT 
                        ci.id AS item_id, 
                        ci.product_id, 
                        ci.quantity, 
                        ci.unit_price,
                        p.name AS product_name,
                        p.description AS product_description,
                        p.weight
                    FROM cart_items ci
                    JOIN products p ON ci.product_id = p.id
                    WHERE ci.cart_id = ?";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$cartId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
            $imageRepository = new ImageRepository($this->db);
            
            foreach ($items as &$item) {
                $images = $imageRepository->findImagesByProductId($item['product_id']);
                $item['main_image'] = $images[0] ?? './image/placeholder.png';
            }
            
            return $items;

        } catch (\PDOException $e) {
            error_log("SQL Error on findItemsByCartId: " . $e->getMessage());
            return [];
        }
    }
}