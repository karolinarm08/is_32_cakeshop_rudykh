<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
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

        $orderId = $this->orderRepository->createOrder($userId, $total, $addressId);
        
        if ($orderId) {
            $this->orderRepository->addItems($orderId, $orderItems);
            $this->sendOrderEmail($userId, $orderId, $total, 'new'); // Лист при створенні

            return [
                'success' => true, 
                'order_id' => $orderId, 
                'total' => $total,
                'message' => 'Замовлення успішно створено!'
            ];
        }

        return ['success' => false, 'message' => 'Помилка створення замовлення'];
    }

    // --- ІСТОРІЯ ЗАМОВЛЕНЬ (для користувача) ---
    public function getUserOrders(int $userId): array
    {
        $orders = $this->orderRepository->findByUserId($userId);
        
        // Додаємо список товарів до кожного замовлення
        foreach ($orders as &$order) {
            $order['items'] = $this->orderRepository->getOrderItems($order['id']);
        }
        
        return ['success' => true, 'orders' => $orders];
    }

    // --- ДЛЯ АДМІНА ---
    public function getAllOrders(): array
    {
        $orders = $this->orderRepository->findAllOrders();
        foreach ($orders as &$order) {
            $order['items'] = $this->orderRepository->getOrderItems($order['id']);
        }
        return ['success' => true, 'orders' => $orders];
    }

    public function changeStatus(int $orderId, string $newStatus): array
    {
        $allowedStatuses = ['new', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($newStatus, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Невірний статус'];
        }

        if ($this->orderRepository->updateStatus($orderId, $newStatus)) {
            $order = $this->orderRepository->findById($orderId);
            if ($order) {
                $this->sendOrderEmail($order->user_id, $orderId, $order->total_price, $newStatus); // Лист при зміні
            }
            return ['success' => true, 'message' => "Статус змінено на '$newStatus'"];
        }
        
        return ['success' => false, 'message' => 'Помилка оновлення статусу'];
    }

    // --- ВІДПРАВКА EMAIL ---
    private function sendOrderEmail($userId, $orderId, $total, $status) {
        $user = $this->userRepository->findById($userId);
        if ($user) {
            $to = $user->email;
            
            $statusLabels = [
                'new' => 'Прийнято в обробку',
                'processing' => 'Готується',
                'shipped' => 'Відправлено',
                'delivered' => 'Доставлено',
                'cancelled' => 'Скасовано'
            ];
            
            $statusText = $statusLabels[$status] ?? $status;
            $subject = "Замовлення #$orderId: $statusText";

            $message = "Вітаємо, {$user->firstName}!\n\n";
            $message .= "Статус замовлення #$orderId: $statusText.\n";
            if ($status === 'new') {
                $message .= "Сума: $total грн.\n";
            }
            $message .= "\nRUBY Cake Shop";

            $headers = "From: no-reply@cakeshop.great-site.net\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail($to, $subject, $message, $headers);
        }
    }
}