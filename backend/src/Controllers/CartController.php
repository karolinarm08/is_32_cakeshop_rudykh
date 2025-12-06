<?php

namespace App\Controllers;

use App\Services\CartService;

class CartController
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
    }

    // Додавання товару в кошик
    public function add($data)
    {
        $email = $data['email'] ?? '';
        // В product.html ми передаємо 'product_id', але давайте підтримаємо і 'productId' про всяк випадок
        $productId = $data['product_id'] ?? $data['productId'] ?? 0;
        $quantity = $data['quantity'] ?? 1;

        if (empty($email) || $productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Некоректні дані (Email або ID продукту)']);
            return;
        }

        echo json_encode($this->cartService->addToCart($email, (int)$productId, (int)$quantity));
    }

    // Отримання вмісту кошика
    public function get($data)
    {
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Необхідна авторизація']);
            return;
        }
        echo json_encode($this->cartService->getUserCart($email));
    }
    
    // Метод для list (аліас для get, якщо ви використовуєте action=list)
    public function getCartContent()
    {
        // Отримуємо дані з потоку, оскільки getCartContent не приймає параметрів у вашому старому коді
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true) ?? [];
        $this->get($data);
    }

    // Видалення товару
    public function remove($data)
    {
        $itemId = $data['itemId'] ?? 0;
        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID товару обов\'язковий']);
            return;
        }
        echo json_encode($this->cartService->removeCartItem((int)$itemId));
    }
}