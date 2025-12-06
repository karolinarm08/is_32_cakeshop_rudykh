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

    private function handleImageUploads(): array 
    {
        $uploadedFilePaths = [];
        
        if (!empty($_FILES['images'])) {
            $imageFiles = $this->reArrayFiles($_FILES['images']);
            
            $uploadDir = dirname(__DIR__, 3) . '/uploads/'; 
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($imageFiles as $file) {
                if ($file['error'] === UPLOAD_ERR_OK && $file['size'] > 0 && strpos($file['type'], 'image/') === 0) {
                    
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = time() . '_' . uniqid() . '.' . $ext;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $uploadedFilePaths[] = 'uploads/' . $fileName; 
                    }
                }
            }
        }
        return $uploadedFilePaths;
    }

    public function getAllProducts(): void
    {
        $products = $this->productRepository->findAllActive();
        $responseProducts = [];

        foreach ($products as $product) {
            $images = $this->imageRepository->findImagesByProductId($product->id);
            $responseProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'weight' => $product->weight,
                'category_id' => $product->categoryId,
                'images' => $images,
                'main_image' => $images[0] ?? './image/placeholder.png' 
            ];
        }

        echo json_encode(['success' => true, 'products' => $responseProducts]);
    }

    public function getProductById(int $id): void
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Товар не знайдено.']);
            exit();
        }

        $images = $this->imageRepository->findImagesByProductId($product->id);
        $productDetails = $this->productRepository->findAdditionalDetails($product->id);

        $responseData = [
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'weight' => $product->weight,
                'category_id' => $product->categoryId,
                'is_active' => $product->isActive ? 1 : 0,
                'main_image' => $images[0] ?? './image/placeholder.png',
                'images_array' => $images, 
                'storage_time' => $productDetails['storage_time'] ?? '',
                'storage_conditions' => $productDetails['storage_conditions'] ?? '',
                'ingredients' => $productDetails['ingredients'] ?? '',
                'allergens' => $productDetails['allergens'] ?? '',
            ]
        ];
        
        echo json_encode($responseData);
    }

    public function createProduct(array $data): array
    {
        if (empty($data['name']) || empty($data['price']) || empty($data['category_id'])) {
            return ['success' => false, 'message' => 'Заповніть обов\'язкові поля (Назва, Ціна, Категорія).'];
        }
        
        $uploadedFilePaths = $this->handleImageUploads();
        
        try {
            $newProduct = new Product(
                $data['name'],
                (float)$data['price'],
                $data['description'] ?? '',
                (float)$data['weight'] ?? 1.0,
                (int)$data['category_id'],
                ($data['is_active'] ?? '1') === '1'
            );

            $success = $this->productRepository->save($newProduct);

            if ($success) {
                if (!empty($uploadedFilePaths)) {
                    $this->imageRepository->saveProductImagePaths($newProduct->id, $uploadedFilePaths);
                }
                
                return [
                    'success' => true, 
                    'message' => "Товар успішно створено (ID: {$newProduct->id}).",
                    'product_id' => $newProduct->id
                ];
            } else {
                return ['success' => false, 'message' => 'Помилка збереження товару в базі даних.'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Помилка сервера: " . $e->getMessage()];
        }
    }

    public function updateProduct(array $data): array
    {
        if (empty($data['product_id']) || empty($data['name'])) {
            return ['success' => false, 'message' => 'ID товару та назва обов\'язкові.'];
        }

        $uploadedFilePaths = $this->handleImageUploads();

        try {
            $product = new Product(
                $data['name'],
                (float)$data['price'],
                $data['description'] ?? '',
                (float)$data['weight'] ?? 1.0,
                (int)$data['category_id'],
                ($data['is_active'] ?? '1') === '1',
                (int)$data['product_id']
            );

            $success = $this->productRepository->update($product);

            if ($success) {
                if (!empty($uploadedFilePaths)) {
                    $this->imageRepository->saveProductImagePaths($product->id, $uploadedFilePaths);
                }
                return ['success' => true, 'message' => "Товар успішно оновлено."];
            } else {
                return ['success' => false, 'message' => 'Помилка оновлення в БД.'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Помилка: " . $e->getMessage()];
        }
    }

    public function deleteProduct(int $id): array
    {
        if ($id <= 0) return ['success' => false, 'message' => 'Невірний ID.'];

        if ($this->productRepository->delete($id)) {
            return ['success' => true, 'message' => 'Товар видалено.'];
        }
        return ['success' => false, 'message' => 'Помилка видалення.'];
    }

    public function deleteImage(array $data): void
    {
        $imageUrl = $data['image_url'] ?? '';
        
        if (empty($imageUrl)) {
            echo json_encode(['success' => false, 'message' => 'URL зображення відсутній.']);
            exit();
        }

        if ($this->imageRepository->deleteByUrl($imageUrl)) {
            echo json_encode(['success' => true, 'message' => 'Фото видалено.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не вдалося видалити фото з БД або сервера.']);
        }
    }
}