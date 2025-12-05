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

    public function add($data)
    {
        $email = $data['email'] ?? '';
        $productId = $data['productId'] ?? 0;
        $quantity = $data['quantity'] ?? 1;

        if (empty($email) || $productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Некоректні дані']);
            return;
        }

        echo json_encode($this->cartService->addToCart($email, (int)$productId, (int)$quantity));
    }

    public function get($data)
    {
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Необхідна авторизація']);
            return;
        }
        echo json_encode($this->cartService->getUserCart($email));
    }

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