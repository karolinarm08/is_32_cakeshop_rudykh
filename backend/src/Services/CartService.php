<?php

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\UserRepository;

class CartService
{
    private CartRepository $cartRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->cartRepository = new CartRepository();
        $this->userRepository = new UserRepository();
    }

    public function addToCart(string $email, int $productId, int $quantity): array
    {
        // 1. Знаходимо користувача за email
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Користувача не знайдено'];
        }

        // 2. Шукаємо кошик користувача
        $cart = $this->cartRepository->findCartByUserId($user->id);
        
        // 3. Якщо кошика немає - створюємо
        $cartId = $cart ? $cart['id'] : $this->cartRepository->createCart($user->id);

        // 4. Додаємо товар
        if ($this->cartRepository->addOrUpdateItem($cartId, $productId, $quantity)) {
            return ['success' => true, 'message' => 'Товар додано до кошика'];
        }

        return ['success' => false, 'message' => 'Помилка додавання товару'];
    }

    public function getUserCart(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) return ['success' => false, 'message' => 'Користувача не знайдено'];

        $cart = $this->cartRepository->findCartByUserId($user->id);
        
        if (!$cart) {
            return ['success' => true, 'items' => [], 'total' => 0];
        }

        $items = $this->cartRepository->getCartItemsWithProductDetails($cart['id']);
        
        // Рахуємо загальну суму
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return [
            'success' => true,
            'items' => $items,
            'total' => $total
        ];
    }

    public function removeCartItem(int $itemId): array
    {
        if ($this->cartRepository->removeItem($itemId)) {
            return ['success' => true, 'message' => 'Товар видалено'];
        }
        return ['success' => false, 'message' => 'Помилка видалення'];
    }
}