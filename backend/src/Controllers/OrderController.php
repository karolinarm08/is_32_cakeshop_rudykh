<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Services\CartService;
use App\Repositories\UserRepository;
use App\Repositories\CartRepository;
use App\Repositories\AddressRepository;
use App\Models\Address;

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
        // 1. Перевірка авторизації
        $email = $data['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Необхідна авторизація']);
            return;
        }

        // 2. Перевірка кошика
        $cartResult = $this->cartService->getUserCart($email);
        if (!$cartResult['success'] || empty($cartResult['items'])) {
            echo json_encode(['success' => false, 'message' => 'Кошик порожній']);
            return;
        }

        // 3. Отримання користувача
        $userRepository = new UserRepository();
        $user = $userRepository->findByEmail($email);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
            return;
        }

        // 4. ОБРОБКА ТА ЗБЕРЕЖЕННЯ АДРЕСИ (Виправлено)
        $addressId = null;
        
        // Якщо прийшли дані адреси, зберігаємо їх
        if (isset($data['deliveryAddress'])) {
            $addrData = $data['deliveryAddress'];
            $addressRepo = new AddressRepository();
            
            // Створюємо об'єкт адреси
            $address = new Address(
                $user->id,
                $addrData['city'] ?? '',
                $addrData['street'] ?? '',
                $addrData['house'] ?? '',
                $addrData['apartment'] ?? null,
                $addrData['floor'] ?? null
            );
            
            // Зберігаємо в БД (оновить існуючу або створить нову)
            if ($addressRepo->save($address)) {
                // Отримуємо ID (знаходимо адресу користувача, яку щойно зберегли)
                $savedAddr = $addressRepo->findByUserId($user->id);
                if ($savedAddr) {
                    $addressId = $savedAddr->id;
                }
            }
        }

        // 5. Підготовка товарів
        $cartItems = [];
        foreach ($cartResult['items'] as $item) {
            $cartItems[] = [
                'product_id' => $item['product_id'],
                'qty' => $item['quantity']
            ];
        }

        // 6. Створення замовлення (передаємо знайдений addressId)
        $orderResult = $this->orderService->createNewOrder(
            $user->id,
            $cartItems,
            $addressId
        );

        // 7. Очищення кошика при успіху
        if ($orderResult['success']) {
            $cartRepository = new CartRepository();
            $cart = $cartRepository->findCartByUserId($user->id);
            if ($cart) {
                $cartRepository->clearCart($cart['id']);
            }
        }

        echo json_encode($orderResult);
    }
}