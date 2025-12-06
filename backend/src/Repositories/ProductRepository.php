<?php

namespace App\Repositories;

use App\Models\Product;
use \PDO;
use \PDOException;

class ProductRepository
{
    public PDO $db; // Зроблено public для передачі в ImageRepository

    public function __construct()
    {
        // -----------------------------------------------------
        // !!! ВАШІ РЕАЛЬНІ ДАНІ ХОСТИНГУ !!!
        // -----------------------------------------------------
        $host = 'sql100.infinityfree.com';      // Наприклад: 'localhost' або IP хостингу
        $dbName = 'if0_40472805_cakeshop';      // Назва вашої бази даних
        $user = 'if0_40472805';      // Ім'я користувача бази даних
        $pass = 'dcmRXnx3yUO78';     // MySQL Password
        // -----------------------------------------------------

        try {
            // Використовуємо utf8mb4 для повної підтримки Unicode (кирилиця)
            $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE             => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES    => false,
                // Явно встановлюємо кодування для гарантії
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci" 
            ];
            
            $this->db = new PDO($dsn, $user, $pass, $options);
            
        } catch (PDOException $e) {
            http_response_code(500);
            error_log("DB Connection Error: " . $e->getMessage()); 
            die(json_encode(['success' => false, 'message' => 'Помилка підключення до бази даних. Перевірте облікові дані в ProductRepository.php.']));
        }
    }

    /**
     * Отримує всі активні продукти з бази даних.
     * @return array Масив об'єктів Product.
     */
    public function findAllActive(): array
    {
        try {
            // Вибираємо всі необхідні поля для відображення
            $stmt = $this->db->query("SELECT id, name, description, price, weight, category_id, is_active FROM products WHERE is_active = 1 ORDER BY created_at DESC");
            $productsData = $stmt->fetchAll();
            $products = [];

            foreach ($productsData as $data) {
                // Створення об'єкта Product з даними з БД
                $product = new Product(
                    $data['name'],
                    (float)$data['price'],
                    $data['description'],
                    (float)$data['weight'],
                    (int)$data['category_id'],
                    (bool)$data['is_active'],
                    (int)$data['id'] // Передаємо ID для подальшої роботи
                );
                $products[] = $product;
            }
            return $products;

        } catch (PDOException $e) {
            error_log("SQL Error on findAllActive: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Отримує один продукт за ID з бази даних.
     * @param int $id ID продукту.
     * @return ?Product Об'єкт Product або null.
     */
    public function findById(int $id): ?Product
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, description, price, weight, category_id, is_active FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if (!$data) {
                return null;
            }

            return new Product(
                $data['name'],
                (float)$data['price'],
                $data['description'],
                (float)$data['weight'],
                (int)$data['category_id'],
                (bool)$data['is_active'],
                (int)$data['id']
            );

        } catch (PDOException $e) {
            error_log("SQL Error on findById: " . $e->getMessage());
            return null;
        }
    }
    
    // --- ДОДАТКОВІ МЕТОДИ ДЛЯ getProductById ---
    
    public function findAdditionalDetails(int $id): array
    {
        return [
            'storage_time' => '2 доби',
            'storage_conditions' => '(6±2) °С при вологості не більше 75%',
            'ingredients' => 'Ванільний бісквіт, вершковий крем з маскарпоне, ягідний конфітюр.',
            'allergens' => 'Яйця, пшениця (глютен), молочні продукти, харчові барвники.',
            'size' => '20*20*40 см.',
            'packaging' => 'Брендована упаковка, стрічка, крафтовий пакет.',
        ];
    }

    public function findReviewsByProductId(int $id): array
    {
        return [
            ['user' => 'Олена К.', 'rating' => 5, 'text' => 'Торт "Ягідна ніжність" просто неперевершений! Свіжий та ідеально солодкий.'],
            ['user' => 'Петро М.', 'rating' => 4, 'text' => 'Дуже смачно, але доставка затрималася на годину.'],
        ];
    }
    
    public function findRecommendedProducts(int $limit): array
    {
        // Для спрощення повертаємо випадкові 3 товари з БД
        try {
            $stmt = $this->db->query("SELECT p.*, (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as main_image FROM products p WHERE is_active = 1 ORDER BY RAND() LIMIT 3");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Зберігає новий продукт у базі даних.
     */
    public function save(Product $product): bool
    {
        try {
            $sql = "INSERT INTO products (category_id, name, description, price, weight, is_active, created_at) 
                    VALUES (:category_id, :name, :description, :price, :weight, :is_active, NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $success = $stmt->execute([
                ':category_id' => $product->categoryId,
                ':name' => $product->name,
                ':description' => $product->description,
                ':price' => $product->price,
                ':weight' => $product->weight,
                ':is_active' => $product->isActive ? 1 : 0
            ]);

            if ($success && !isset($product->id)) {
                $product->id = (int)$this->db->lastInsertId();
            }
            
            return $success;

        } catch (PDOException $e) {
            error_log("SQL Error on Product Save: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Оновлює існуючий продукт.
     */
    public function update(Product $product): bool
    {
        try {
            $sql = "UPDATE products 
                    SET category_id = :category_id, 
                        name = :name, 
                        description = :description, 
                        price = :price, 
                        weight = :weight, 
                        is_active = :is_active 
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':category_id' => $product->categoryId,
                ':name' => $product->name,
                ':description' => $product->description,
                ':price' => $product->price,
                ':weight' => $product->weight,
                ':is_active' => $product->isActive ? 1 : 0,
                ':id' => $product->id
            ]);

        } catch (PDOException $e) {
            error_log("SQL Error on Product Update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Видаляє продукт за ID.
     */
    public function delete(int $id): bool
    {
        try {
            // Спочатку видаляємо пов'язані зображення (якщо немає каскадного видалення в БД)
            $stmtImg = $this->db->prepare("DELETE FROM product_images WHERE product_id = ?");
            $stmtImg->execute([$id]);

            // Видаляємо сам продукт
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            return $stmt->execute([$id]);

        } catch (PDOException $e) {
            error_log("SQL Error on Product Delete: " . $e->getMessage());
            return false;
        }
    }
}