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
        $pass = 'dcmRXnx3yUO78';      // MySQL Password
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
    
    /**
     * Заглушка: Отримує деталі продукту (склад, термін, алергени).
     * У реальному проекті це може бути окрема таблиця або поля в `products`.
     */
    public function findAdditionalDetails(int $id): array
    {
        // Це заглушка, оскільки ви не надали схему БД для додаткових полів
        // Повертаємо тестові дані для коректного заповнення product.html
        return [
            'storage_time' => '2 доби',
            'storage_conditions' => '(6±2) °С при вологості не більше 75%',
            'ingredients' => 'Ванільний бісквіт, вершковий крем з маскарпоне, ягідний конфітюр.',
            'allergens' => 'Яйця, пшениця (глютен), молочні продукти, харчові барвники.',
            'size' => '20*20*40 см.',
            'packaging' => 'Брендована упаковка, стрічка, крафтовий пакет.',
        ];
    }

    /**
     * Заглушка: Отримує відгуки для продукту.
     */
    public function findReviewsByProductId(int $id): array
    {
        // Повертаємо тестові відгуки
        return [
            ['user' => 'Олена К.', 'rating' => 5, 'text' => 'Торт "Ягідна ніжність" просто неперевершений! Свіжий та ідеально солодкий.'],
            ['user' => 'Петро М.', 'rating' => 4, 'text' => 'Дуже смачно, але доставка затрималася на годину.'],
        ];
    }
    
    /**
     * Заглушка: Отримує рекомендовані товари.
     */
    public function findRecommendedProducts(int $limit): array
    {
        // Повертаємо тестові дані для карток рекомендованих товарів
        return [
            ['id' => 10, 'name' => 'Еклер Шоколад', 'description' => 'Класичний смак', 'price' => 100, 'weight' => 0.1, 'main_image' => 'uploads/eclair.jpg'],
            ['id' => 11, 'name' => 'Тістечко Манго', 'description' => 'Тропічний мус', 'price' => 120, 'weight' => 0.2, 'main_image' => 'uploads/mango.jpg'],
            ['id' => 12, 'name' => 'Торт Трюфель', 'description' => 'Насичений шоколад', 'price' => 900, 'weight' => 1.0, 'main_image' => 'uploads/truffle.jpg'],
            ['id' => 13, 'name' => 'Макарон Фісташка', 'description' => 'Мигдальне печиво', 'price' => 50, 'weight' => 0.05, 'main_image' => 'uploads/macaron.jpg'],
        ];
    }

    /**
     * Зберігає новий продукт у базі даних.
     * @param Product $product
     * @return bool
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
}