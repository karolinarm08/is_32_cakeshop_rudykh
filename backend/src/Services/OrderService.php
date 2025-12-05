<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CartRepository;
use App\Repositories\UserRepository;

class OrderService
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->productRepository = new ProductRepository();
        $this->userRepository = new UserRepository();
    }

    public function createNewOrder(int $userId, array $cartItems, int $addressId = null): array
    {
        $total = 0;
        $orderItems = [];

        // Розрахунок суми та підготовка товарів
        foreach ($cartItems as $item) {
            $product = $this->productRepository->findById($item['product_id']);
            if ($product) {
                $total += $product->price * $item['qty'];
                $orderItems[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $product->price
                ];
            }
        }

        if (empty($orderItems)) {
            return ['success' => false, 'message' => 'Кошик порожній'];
        }

        // Создаем заказ в БД
        $orderId = $this->orderRepository->createOrder($userId, $total, $addressId);
        
        if ($orderId) {
            // Збереження товарів замовлення
            $this->orderRepository->addItems($orderId, $orderItems);
            
            // Отправляем email подтверждения
            $this->sendOrderConfirmationEmail($userId, $orderId);

            return [
                'success' => true, 
                'order_id' => $orderId, 
                'total' => $total,
                'message' => 'Замовлення успішно створено!'
            ];
        }

        return ['success' => false, 'message' => 'Помилка створення замовлення'];
    }
    
    private function sendOrderConfirmationEmail($userId, $orderId) {
        // Получаем email пользователя
        $user = $this->userRepository->findById($userId);
        if ($user) {
            // Здесь должна быть логика отправки email
            // mail($user->email, "Замовлення #$orderId підтверджено", "Дякуємо за покупку!");
            error_log("Email отправлен на: " . $user->email . " для заказа #" . $orderId);
        }
    }
}