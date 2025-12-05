<?php

namespace App\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\ImageRepository; 
use App\Models\Product;

class ProductController
{
    private ProductRepository $productRepository;
    private ImageRepository $imageRepository; 

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->imageRepository = new ImageRepository($this->productRepository->db);
    }

    /**
     * Отримує всі активні товари та їхні зображення для відображення в меню.
     * @return void
     */
    public function getAllProducts(): void
    {
        $products = $this->productRepository->findAllActive();
        $responseProducts = [];

        foreach ($products as $product) {
            // Отримуємо зображення для кожного продукту
            $images = $this->imageRepository->findImagesByProductId($product->id);
            
            // Форматуємо дані для відправки у JSON
            $responseProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'weight' => $product->weight,
                'category_id' => $product->categoryId,
                'images' => $images,
                // Головне зображення або заглушка, якщо зображень немає
                'main_image' => $images[0] ?? './image/placeholder.png' 
            ];
        }

        // Надсилаємо відповідь у форматі JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'products' => $responseProducts]);
        exit();
    }

    /**
     * Отримання товару за ID. Виводить JSON.
     * @param int $id
     * @return void
     */
    public function getProductById(int $id): void
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Товар з таким ID не знайдено.']);
            exit();
        }

        // 1. Отримуємо всі зображення
        $images = $this->imageRepository->findImagesByProductId($product->id);
        
        // 2. Отримуємо додаткові дані (наприклад, відгуки, рекомендовані)
        // (Ця логіка має бути реалізована у відповідних репозиторіях, але тут ми використовуємо заглушки для прикладу)
        $reviews = $this->productRepository->findReviewsByProductId($product->id);
        $recommended = $this->productRepository->findRecommendedProducts(3); // Припускаємо, що такий метод існує
        $productDetails = $this->productRepository->findAdditionalDetails($product->id); // Наприклад, склад, терміни

        // 3. Форматуємо відповідь
        $responseData = [
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'weight' => $product->weight,
                'category_id' => $product->categoryId,
                // Поля для динамічного заповнення additionalInfoText
                'storage_time' => $productDetails['storage_time'] ?? '2 доби',
                'storage_conditions' => $productDetails['storage_conditions'] ?? '(6±2) °С',
                'ingredients' => $productDetails['ingredients'] ?? 'Ванільний бісквіт, крем, конфітюр.',
                'allergens' => $productDetails['allergens'] ?? 'Яйця, пшениця.',
                'size' => $productDetails['size'] ?? '20*20*40 см.',
                'packaging' => $productDetails['packaging'] ?? 'Брендована упаковка.',
                // Зображення
                'main_image' => $images[0] ?? './image/placeholder.png',
                'images_array' => array_slice($images, 1), // Решта зображень як мініатюри
            ],
            'reviews' => $reviews,
            'recommended_products' => $recommended
        ];
        
        header('Content-Type: application/json');
        echo json_encode($responseData);
        exit();
    }

    /**
     * Нормалізує масив $_FILES для зручної ітерації при множинному завантаженні.
     * @param array $files Масив $_FILES['images']
     * @return array Нормалізований масив файлів
     */
    private function reArrayFiles(array $files): array
    {
        $file_ary = [];
        if (empty($files['name']) || !is_array($files['name'])) {
            return [];
        }
        
        $file_count = count($files['name']);
        $file_keys = array_keys($files);

        for ($i = 0; $i < $file_count; $i++) {
            $file_ary[$i] = [];
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $files[$key][$i];
            }
        }
        return $file_ary;
    }


    /**
     * Обробляє запит на створення нового товару.
     * @param array $data Дані нового товару з $_POST.
     * @return array Результат операції (успіх/помилка).
     */
    public function createProduct(array $data): array
    {
        // 1. Валідація даних
        if (empty($data['name']) || empty($data['price']) || empty($data['category_id']) || empty($data['weight'])) {
            return ['success' => false, 'message' => 'Помилка валідації. Відсутні обов\'язкові поля (Назва, Ціна, Категорія, Вага).'];
        }
        if (!is_numeric($data['price']) || !is_numeric($data['weight'])) {
            return ['success' => false, 'message' => 'Поля Ціна та Вага повинні бути числами.'];
        }
        
        // 2. Обробка завантаження файлів зображень
        $uploadedFilePaths = [];
        
        if (!empty($_FILES['images'])) {
            $imageFiles = $this->reArrayFiles($_FILES['images']);
            
            // Шлях для збереження файлів у нову папку 'uploads/'
            $uploadDir = dirname(__DIR__, 3) . '/uploads/'; 
            
            // Перевірка та СТВОРЕННЯ ПАПКИ (з правами 0777, які запитає PHP)
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                     return ['success' => false, 'message' => 'Помилка: Сервер блокує створення папки "uploads" для запису файлів.'];
                }
            }

            foreach ($imageFiles as $file) {
                if ($file['error'] === UPLOAD_ERR_OK && $file['size'] > 0 && strpos($file['type'], 'image/') === 0) {
                    
                    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
                    $safeBaseName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $baseName);
                    
                    $fileName = time() . '_' . uniqid() . '_' . $safeBaseName . '.' . $fileExtension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $uploadedFilePaths[] = 'uploads/' . $fileName; 
                    } else {
                        error_log("Помилка переміщення файлу: " . $file['name'] . " до " . $targetPath);
                        return ['success' => false, 'message' => 'Помилка: Не вдалося перемістити завантажений файл. Перевірте, чи не заблокована папка "uploads".'];
                    }
                }
            }
        }
        
        // 3. Збереження моделі Product
        try {
            $newProduct = new Product(
                $data['name'],
                (float)$data['price'],
                $data['description'] ?? '',
                (float)$data['weight'],
                (int)$data['category_id'],
                ($data['is_active'] ?? '1') === '1'
            );

            $success = $this->productRepository->save($newProduct);

            if ($success) {
                // 4. Зберігаємо шляхи до зображень у таблицю product_images
                if (!empty($uploadedFilePaths)) {
                    $imageSuccess = $this->imageRepository->saveProductImagePaths($newProduct->id, $uploadedFilePaths);
                    
                    if (!$imageSuccess) {
                        error_log("Помилка: Не вдалося зберегти шляхи до зображень у БД для ID: {$newProduct->id}");
                        return ['success' => true, 'message' => "Товар збережено, але сталася помилка при збереженні шляхів до зображень у БД."];
                    }
                }
                
                return [
                    'success' => true, 
                    'message' => "Товар та його зображення успішно збережено (ID: {$newProduct->id}).",
                    'product_id' => $newProduct->id,
                    'image_paths' => $uploadedFilePaths 
                ];
            } else {
                return ['success' => false, 'message' => 'Помилка збереження товару в базі даних.'];
            }

        } catch (\Exception $e) {
            error_log("Помилка: " . $e->getMessage());
            return ['success' => false, 'message' => "Неочікувана помилка при створенні товару."];
        }
    }
}