<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Services\CartService;

class OrderController
{
    private OrderService $orderService;
    private CartService $cartService;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->cartService = new CartService();
    }

    public function create(array $data)
    {
        // Получаем email пользователя из данных
        $email = $data['email'] ?? '';
        
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Необхідна авторизація']);
            return;
        }

        // Получаем корзину пользователя
        $cartResult = $this->cartService->getUserCart($email);
        
        if (!$cartResult['success']) {
            echo json_encode($cartResult);
            return;
        }

        // Проверяем, что корзина не пуста
        if (empty($cartResult['items'])) {
            echo json_encode(['success' => false, 'message' => 'Кошик порожній']);
            return;
        }

        // Получаем данные пользователя
        $userData = $data['userData'] ?? [];
        
        // Преобразуем товары корзины в формат для заказа
        $cartItems = [];
        foreach ($cartResult['items'] as $item) {
            $cartItems[] = [
                'product_id' => $item['product_id'],
                'qty' => $item['quantity']
            ];
        }

        // Получаем ID пользователя через UserRepository
        $userRepository = new \App\Repositories\UserRepository();
        $user = $userRepository->findByEmail($email);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
            return;
        }

        // ID адреса (пока null, можно добавить логику выбора адреса)
        $addressId = null;

        // Создаем заказ
        $orderResult = $this->orderService->createNewOrder(
            $user->id,
            $cartItems,
            $addressId
        );

        if ($orderResult['success']) {
            // Очищаем корзину после успешного оформления заказа
            $cartRepository = new \App\Repositories\CartRepository();
            $cart = $cartRepository->findCartByUserId($user->id);
            if ($cart) {
                $cartRepository->clearCart($cart['id']);
            }
        }

        echo json_encode($orderResult);
    }
}