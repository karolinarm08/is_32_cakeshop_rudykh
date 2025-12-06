<?php

namespace App\Repositories;

use \PDO;
use \PDOException;

class ImageRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findImagesByProductId(int $productId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY display_order ASC");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("SQL Error on Find Images: " . $e->getMessage());
            return [];
        }
    }

    public function saveProductImagePaths(int $productId, array $paths): bool
    {
        if (empty($paths)) {
            return true;
        }
        
        $placeholders = [];
        $values = [];
        
        $sql = "INSERT INTO product_images (product_id, image_url, alt_text, display_order) VALUES ";
        
        $order = 1; 
        foreach ($paths as $path) {
            $placeholders[] = "(?, ?, ?, ?)";
            $values[] = $productId;
            $values[] = $path; 
            $values[] = 'Фото продукту ' . $productId;
            $values[] = $order++;
        }
        
        $sql .= implode(", ", $placeholders);

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("SQL Error on Save Images: " . $e->getMessage());
            return false;
        }
    }

    public function deleteByUrl(string $imageUrl): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM product_images WHERE image_url = ?");
            $stmt->execute([$imageUrl]);

            $filePath = dirname(__DIR__, 3) . '/' . $imageUrl;
            
            if (file_exists($filePath)) {
                unlink($filePath); 
            }

            return true;
        } catch (PDOException $e) {
            error_log("Delete Image Error: " . $e->getMessage());
            return false;
        }
    }
}